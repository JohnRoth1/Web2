
<?php
$ds = DIRECTORY_SEPARATOR;
$base_dir = realpath(dirname(__FILE__)  . $ds . '..') . $ds;
include_once("{$base_dir}connect.php");
$database = new connectDB();

function receipt_ensureStatusColumn($database)
{
  $checkSql = "SHOW COLUMNS FROM goodsreceipts LIKE 'status'";
  $checkResult = $database->query($checkSql);
  if (!$checkResult || $checkResult->num_rows === 0) {
    $database->execute("ALTER TABLE goodsreceipts ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft'");
  }
}

function receipt_ensureSupplierColumn($database)
{
  $checkSql = "SHOW COLUMNS FROM goodsreceipts LIKE 'supplier_id'";
  $checkResult = $database->query($checkSql);
  if (!$checkResult || $checkResult->num_rows === 0) {
    $database->execute("ALTER TABLE goodsreceipts ADD COLUMN supplier_id INT(11) DEFAULT NULL");
  }
}

function receipt_getAverageCostPrice($database, $productId)
{
  $pid = intval($productId);
  $sql = "SELECT COALESCE(price, 0) as price
          FROM products
          WHERE id = '$pid'
          LIMIT 1";
  $result = $database->query($sql);
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $costPrice = floatval($row['price']);
    if ($costPrice > 0) {
      return $costPrice;
    }
  }

  $sqlFallback = "SELECT gd.quantity, gd.input_price
                  FROM goodsreceipt_details gd
                  JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
                  WHERE gd.product_id = '$pid' AND gr.status = 'completed'";
  $resultFallback = $database->query($sqlFallback);

  $totalQty = 0;
  $totalCost = 0;
  while ($resultFallback && ($row = $resultFallback->fetch_assoc())) {
    $qty = intval($row['quantity']);
    $price = floatval($row['input_price']);
    $totalQty += $qty;
    $totalCost += ($qty * $price);
  }

  if ($totalQty <= 0) return 0;
  return $totalCost / $totalQty;
}

function receipt_applyMovingAverageCost($database, $productId, $importQty, $importPrice)
{
  $pid = intval($productId);
  $qty = intval($importQty);
  $price = floatval($importPrice);

  if ($qty <= 0 || $price <= 0) {
    return true;
  }

  $sqlGet = "SELECT quantity, COALESCE(price, 0) as price
             FROM products
             WHERE id = '$pid'
             FOR UPDATE";
  $resultGet = $database->query($sqlGet);
  if (!$resultGet || $resultGet->num_rows === 0) {
    return false;
  }

  $row = $resultGet->fetch_assoc();
  $currentQty = intval($row['quantity']);
  $currentCost = floatval($row['price']);
  $newTotalQty = $currentQty + $qty;

  if ($newTotalQty <= 0) {
    $newCost = $price;
  } else {
    $newCost = (($currentQty * $currentCost) + ($qty * $price)) / $newTotalQty;
  }

  $newCostSafe = mysqli_real_escape_string($database->conn, strval($newCost));
  $sqlUpdate = "UPDATE products SET price = '$newCostSafe' WHERE id = '$pid'";
  return $database->execute($sqlUpdate);
}

function receipt_recalculateSellingPrice($database, $productId)
{
  // Với quy ước hiện tại, products.price chính là giá vốn.
  // Giá bán được tính động ở giao diện từ giá vốn và profit_margin.
  return true;
}

function getNewReceiptId($database)
{
  $sql = "SELECT MAX(id) AS max_id FROM goodsreceipts";
  $result = $database->query($sql);
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    return intval($row['max_id']) + 1;
  } else {
    return 1;
  }
}
function receipt_create($field)
{
  $database = new connectDB();
  receipt_ensureStatusColumn($database);
  receipt_ensureSupplierColumn($database);

  date_default_timezone_set('Asia/Ho_Chi_Minh');
  $date = (!empty($field['dateCreate'])) ? $field['dateCreate'] : date('Y-m-d', time());

  $supplierId = isset($field['supplierId']) ? intval($field['supplierId']) : 'NULL';
  $totalPrice = $field['totalPrice'];
  $staffId = $field['staffId'];
  $receiptId = getNewReceiptId($database);

  $supplierVal = ($supplierId === 'NULL') ? 'NULL' : "'" . $supplierId . "'";
  $status = isset($field['status']) && $field['status'] === 'completed' ? 'completed' : 'draft';

  mysqli_begin_transaction($database->conn);

  try {
    $sqlInsertReceipt = "INSERT INTO goodsreceipts (id, staff_id, total_price, date_create, status, supplier_id) 
                           VALUES ('" . $receiptId . "', '" . $staffId . "', '" . $totalPrice . "', '" . $date . "', '" . $status . "', " . $supplierVal . ")";
    $resultReceipt = $database->query($sqlInsertReceipt);

    if (!$resultReceipt) {
      throw new Exception('Không thể tạo phiếu nhập');
    }

    foreach ($field['details'] as $detail) {
      $productId = $detail['productId'];
      $quantity = $detail['quantity'];
      $inputPrice = $detail['inputPrice'];

      $sqlInsertDetail = "INSERT INTO goodsreceipt_details (product_id, goodsreceipt_id, quantity, input_price) 
                                      VALUES ('" . $productId . "', '" . $receiptId . "', '" . $quantity . "', '" . $inputPrice . "')";
      $resultDetail = $database->query($sqlInsertDetail);

      if (!$resultDetail) {
        throw new Exception('Tạo chi tiết đơn nhập hàng không thành công');
      }
    }

    // Nếu trạng thái là completed -> nhập hàng vào kho ngay
    if ($status === 'completed') {
      foreach ($field['details'] as $detail) {
        $pid = intval($detail['productId']);
        $qty = intval($detail['quantity']);
        $price = floatval($detail['inputPrice']);

        if (!receipt_applyMovingAverageCost($database, $pid, $qty, $price)) {
          throw new Exception("Không thể cập nhật giá vốn bình quân cho sản phẩm $pid");
        }

        $sqlUpdateStock = "UPDATE products SET quantity = quantity + ($qty) WHERE id = '$pid'";
        if (!$database->execute($sqlUpdateStock)) {
          throw new Exception("Không thể cập nhật tồn kho sản phẩm $pid");
        }
      }
    }

    mysqli_commit($database->conn);
    return "<span class='success'>Tạo đơn nhập hàng thành công</span>";
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    return "<span class='failed'>" . $e->getMessage() . "</span>";
  }
}






function receipt_detail($field)
{
  // Kết nối đến cơ sở dữ liệu
  $database = new connectDB();

  // Biến goodsreceipt_id bạn đã biết trước
  $goodsreceipt_id = $field['id']; // Đảm bảo bạn truyền vào $field['id']

  try {
    $sql = "SELECT gd.product_id, p.name, gd.quantity, gd.input_price
              FROM goodsreceipt_details gd
              INNER JOIN products p ON gd.product_id = p.id
              WHERE gd.goodsreceipt_id = '$goodsreceipt_id'";

    $result = $database->query($sql);

    $htmlResult = '<table id="Table">
                      <thead>
                        <tr>
                          <th>Mã Sản Phẩm</th>
                          <th>Tên Sản Phẩm</th>
                          <th>Số Lượng</th>
                          <th>Giá Nhập</th>
                          <th>Tổng Tiền</th>
                        </tr>
                      </thead>
                      <tbody>';

    $totalPrice = 0;
    foreach ($result as $row) {
      $totalAmount = $row['quantity'] * $row['input_price'];
      $totalPrice += $totalAmount;
      $htmlResult .= '<tr>
                            <td style=" width: 10%">' . $row['product_id'] . '</td>
                            <td style=" width: 40%">' . $row['name'] . '</td>
                            <td style=" width: 12%">' . $row['quantity'] . '</td>
                            <td style=" width: 17%">' . number_format($row['input_price']) . '&#8363</td>
                            <td style=" width: 21%">' . number_format($totalAmount) . '&#8363</td>
                            </tr>';
    }

    $htmlResult .= '</tbody>
                    </table>';

    return $htmlResult;
  } catch (PDOException $e) {
    return "Lỗi: " . $e->getMessage();
  }
}
function receipt_getSuppliers()
{
  $database = new connectDB();

  try {
    $sql = "SELECT name,id FROM suppliers WHERE status=1";

    $stmt = $database->query($sql);

    $htmlResult = '<option value="">Chọn nhà cung cấp</option>';
    foreach ($stmt as $row) {
      $htmlResult .= '<option value="' . $row['id'] . '">' . $row['id'] . '-' . $row['name'] . '</option>';
    }

    echo $htmlResult;
  } catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
  }
}

function receipt_getIdProducts($field)
{
  $database = new connectDB();
  $goodsreceipt_id = $field['id'];

  try {
    $sql = "SELECT id, name FROM products p WHERE supplier_id = $goodsreceipt_id AND p.status=1";
    $stmt = $database->query($sql);


    $htmlResult = '<option value="">Chọn sản phẩm</option>';
    foreach ($stmt as $row) {
      $htmlResult .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }

    echo $htmlResult;
  } catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
  }
}
function receipt_getPriceProducts($field)
{
  $database = new connectDB();

  try {
    $id = $field['id'];
    $sql = "SELECT price FROM products p WHERE p.id = '$id'";
    $stmt = $database->query($sql);

    if ($stmt->num_rows > 0) {
      $row = $stmt->fetch_assoc();
      $inputPrice = $row['price'];
      return $inputPrice;
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    return "Lỗi: " . $e->getMessage();
  }
}

function receipt_getProductNumber($field)
{
  // Kết nối đến cơ sở dữ liệu
  $database = new connectDB();
  $id = $field['id'];
  try {
    // Truy vấn đếm số lượng sản phẩm
    $sql = "SELECT quantity AS product_count FROM products WHERE id = '$id'";
    $result = $database->query($sql);

    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return json_encode([
        'success' => true,
        'product_count' => intval($row['product_count'])
      ]);
    } else {
      return json_encode([
        'success' => false,
        'error' => 'Không tìm thấy sản phẩm'
      ]);
    }
  } catch (Exception $e) {
    // Xử lý lỗi và trả về JSON
    return json_encode([
      'success' => false,
      'error' => $e->getMessage()
    ]);
  }
}

function receipt_getById($field)
{
  $database = new connectDB();
  receipt_ensureStatusColumn($database);
  receipt_ensureSupplierColumn($database);
  if (!isset($field['id'])) {
    return json_encode([
      'success' => false,
      'message' => 'Thiếu mã phiếu nhập'
    ]);
  }

  $id = intval($field['id']);
  $sql = "SELECT gr.id, gr.staff_id, gr.total_price, DATE_FORMAT(gr.date_create, '%Y-%m-%d') AS date_create, gr.status,
                 gr.supplier_id, COALESCE(s.name, '') AS supplier_name
          FROM goodsreceipts gr
          LEFT JOIN suppliers s ON s.id = gr.supplier_id
          WHERE gr.id = '$id'
          LIMIT 1";
  $result = $database->query($sql);

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    return json_encode([
      'success' => true,
      'data' => $row
    ]);
  }

  return json_encode([
    'success' => false,
    'message' => 'Không tìm thấy phiếu nhập'
  ]);
}

function receipt_update($field)
{
  $database = new connectDB();
  receipt_ensureStatusColumn($database);

  if (!isset($field['id'])) {
    return "<span class='failed'>Dữ liệu cập nhật không hợp lệ</span>";
  }

  $receiptId = intval($field['id']);
  $newDetails = isset($field['details']) ? $field['details'] : [];
  $newDateCreate = isset($field['date_create']) ? $field['date_create'] : null;
  $newStatus = isset($field['status']) ? $field['status'] : null;

  // Lấy status hiện tại
  $statusSql = "SELECT status FROM goodsreceipts WHERE id = '$receiptId' LIMIT 1";
  $statusResult = $database->query($statusSql);
  if (!$statusResult || $statusResult->num_rows === 0) {
    return "<span class='failed'>Không tìm thấy phiếu nhập cần sửa</span>";
  }
  $currentStatus = $statusResult->fetch_assoc()['status'];

  // Nếu phiếu đã hoàn thành, không cho sửa bất kỳ thứ gì
  if ($currentStatus === 'completed') {
    return "<span class='failed'>Phiếu nhập đã hoàn thành, không thể chỉnh sửa.</span>";
  }

  // Lấy chi tiết cũ của phiếu nhập
  $sqlOld = "SELECT product_id, quantity, input_price
             FROM goodsreceipt_details
             WHERE goodsreceipt_id = '$receiptId'";
  $oldResult = $database->query($sqlOld);
  $oldDetails = [];
  while ($row = $oldResult->fetch_assoc()) {
    $pid = intval($row['product_id']);
    $oldDetails[$pid] = [
      'quantity' => intval($row['quantity']),
      'input_price' => floatval($row['input_price'])
    ];
  }

  if (count($oldDetails) === 0) {
    return "<span class='failed'>Không tìm thấy phiếu nhập cần sửa</span>";
  }

  // Chuẩn hóa chi tiết mới (gộp theo product_id)
  $normalized = [];
  if (is_array($newDetails) && count($newDetails) > 0) {
    foreach ($newDetails as $detail) {
      $pid = intval($detail['productId']);
      $qty = intval($detail['quantity']);
      $price = floatval($detail['inputPrice']);

      if ($pid <= 0 || $qty <= 0 || $price <= 0) {
        return "<span class='failed'>Chi tiết sản phẩm không hợp lệ</span>";
      }

      if (!isset($normalized[$pid])) {
        $normalized[$pid] = ['quantity' => 0, 'input_price' => $price];
      }
      $normalized[$pid]['quantity'] += $qty;
      $normalized[$pid]['input_price'] = $price;
    }
  }

  mysqli_begin_transaction($database->conn);

  try {
    // Phiếu draft -> chỉ sửa chi tiết phiếu, KHÔNG động tới kho
    if (count($normalized) > 0) {
      foreach ($normalized as $pid => $detail) {
        $newQty = intval($detail['quantity']);
        $newPrice = floatval($detail['input_price']);

        if (isset($oldDetails[$pid])) {
          $sqlUpdateDetail = "UPDATE goodsreceipt_details
                              SET quantity = '$newQty', input_price = '$newPrice'
                              WHERE goodsreceipt_id = '$receiptId' AND product_id = '$pid'";
          if (!$database->execute($sqlUpdateDetail)) {
            throw new Exception("Không thể cập nhật chi tiết phiếu nhập");
          }
        } else {
          $sqlInsertDetail = "INSERT INTO goodsreceipt_details (product_id, goodsreceipt_id, quantity, input_price)
                              VALUES ('$pid', '$receiptId', '$newQty', '$newPrice')";
          if (!$database->execute($sqlInsertDetail)) {
            throw new Exception("Không thể thêm chi tiết phiếu nhập");
          }
        }
      }

      // Xóa các dòng cũ không còn trong chi tiết mới
      foreach ($oldDetails as $pid => $oldDetail) {
        if (!isset($normalized[$pid])) {
          $sqlDeleteDetail = "DELETE FROM goodsreceipt_details
                              WHERE goodsreceipt_id = '$receiptId' AND product_id = '$pid'";
          if (!$database->execute($sqlDeleteDetail)) {
            throw new Exception("Không thể xóa chi tiết phiếu nhập");
          }
        }
      }

      // Tính lại tổng tiền phiếu nhập
      $totalPrice = 0;
      foreach ($normalized as $detail) {
        $totalPrice += floatval($detail['quantity']) * floatval($detail['input_price']);
      }
      $sqlUpdateReceipt = "UPDATE goodsreceipts
                           SET total_price = '$totalPrice'";
      if (!empty($newDateCreate)) {
        $dateSafe = mysqli_real_escape_string($database->conn, $newDateCreate);
        $sqlUpdateReceipt .= ", date_create = '$dateSafe'";
      }
      $sqlUpdateReceipt .= " WHERE id = '$receiptId'";
      if (!$database->execute($sqlUpdateReceipt)) {
        throw new Exception("Không thể cập nhật tổng tiền phiếu nhập");
      }
    }

    // Cập nhật status nếu có
    if (!empty($newStatus) && in_array($newStatus, ['draft', 'completed'])) {
      // Khi chuyển sang hoàn thành -> nhập hàng vào kho
      if ($newStatus === 'completed' && $currentStatus === 'draft') {
        // Lấy chi tiết hiện tại (đã cập nhật ở trên nếu có)
        $sqlCurrentDetails = "SELECT product_id, quantity, input_price
                              FROM goodsreceipt_details
                              WHERE goodsreceipt_id = '$receiptId'";
        $currentDetailsResult = $database->query($sqlCurrentDetails);
        while ($row = $currentDetailsResult->fetch_assoc()) {
          $pid = intval($row['product_id']);
          $qty = intval($row['quantity']);
          $price = floatval($row['input_price']);

          // Cập nhật giá vốn bình quân
          if (!receipt_applyMovingAverageCost($database, $pid, $qty, $price)) {
            throw new Exception("Không thể cập nhật giá vốn bình quân cho sản phẩm $pid");
          }

          // Cộng số lượng vào kho
          $sqlUpdateStock = "UPDATE products SET quantity = quantity + ($qty) WHERE id = '$pid'";
          if (!$database->execute($sqlUpdateStock)) {
            throw new Exception("Không thể cập nhật tồn kho sản phẩm $pid");
          }
        }
      }

      $statusSafe = mysqli_real_escape_string($database->conn, $newStatus);
      $sqlUpdateStatus = "UPDATE goodsreceipts SET status = '$statusSafe' WHERE id = '$receiptId'";
      if (!$database->execute($sqlUpdateStatus)) {
        throw new Exception("Không thể cập nhật trạng thái phiếu nhập");
      }
    }

    mysqli_commit($database->conn);
    return "<span class='success'>Cập nhật phiếu nhập thành công</span>";
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    return "<span class='failed'>Cập nhật phiếu nhập thất bại: " . $e->getMessage() . "</span>";
  }
}

function receipt_complete($field)
{
  $database = new connectDB();
  receipt_ensureStatusColumn($database);
  if (!isset($field['id'])) {
    return "<span class='failed'>Thiếu mã phiếu nhập</span>";
  }

  $receiptId = intval($field['id']);
  $sqlCheck = "SELECT status FROM goodsreceipts WHERE id = '$receiptId' LIMIT 1";
  $checkResult = $database->query($sqlCheck);

  if (!$checkResult || $checkResult->num_rows === 0) {
    return "<span class='failed'>Không tìm thấy phiếu nhập</span>";
  }

  $currentStatus = $checkResult->fetch_assoc()['status'];
  if ($currentStatus === 'completed') {
    return "<span class='failed'>Phiếu nhập đã ở trạng thái hoàn thành</span>";
  }

  mysqli_begin_transaction($database->conn);
  try {
    // Lấy chi tiết phiếu nhập để nhập kho
    $sqlDetails = "SELECT product_id, quantity, input_price
                   FROM goodsreceipt_details
                   WHERE goodsreceipt_id = '$receiptId'";
    $detailResult = $database->query($sqlDetails);

    while ($row = $detailResult->fetch_assoc()) {
      $pid = intval($row['product_id']);
      $qty = intval($row['quantity']);
      $price = floatval($row['input_price']);

      // Cập nhật giá vốn bình quân
      if (!receipt_applyMovingAverageCost($database, $pid, $qty, $price)) {
        throw new Exception("Không thể cập nhật giá vốn bình quân cho sản phẩm $pid");
      }

      // Cộng số lượng vào kho
      $sqlUpdateStock = "UPDATE products SET quantity = quantity + ($qty) WHERE id = '$pid'";
      if (!$database->execute($sqlUpdateStock)) {
        throw new Exception("Không thể cập nhật tồn kho sản phẩm $pid");
      }
    }

    $sqlUpdate = "UPDATE goodsreceipts SET status = 'completed' WHERE id = '$receiptId'";
    if (!$database->execute($sqlUpdate)) {
      throw new Exception("Không thể cập nhật trạng thái phiếu nhập");
    }

    mysqli_commit($database->conn);
    return "<span class='success'>Đã hoàn thành phiếu nhập</span>";
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    return "<span class='failed'>Hoàn thành phiếu nhập thất bại: " . $e->getMessage() . "</span>";
  }
}

function receipt_delete($field)
{
  $database = new connectDB();
  receipt_ensureStatusColumn($database);

  if (!isset($field['id'])) {
    return "<span class='failed'>Thiếu mã phiếu nhập</span>";
  }

  $receiptId = intval($field['id']);
  if ($receiptId <= 0) {
    return "<span class='failed'>Mã phiếu nhập không hợp lệ</span>";
  }

  $sqlCheck = "SELECT status FROM goodsreceipts WHERE id = '$receiptId' LIMIT 1";
  $checkResult = $database->query($sqlCheck);
  if (!$checkResult || $checkResult->num_rows === 0) {
    return "<span class='failed'>Không tìm thấy phiếu nhập</span>";
  }

  $status = $checkResult->fetch_assoc()['status'];
  if ($status === 'completed') {
    return "<span class='failed'>Phiếu nhập đã hoàn thành, không thể xóa</span>";
  }

  // Phiếu draft chưa nhập kho -> chỉ xoá dữ liệu phiếu, không cần hoàn tồn kho
  mysqli_begin_transaction($database->conn);
  try {
    $sqlDeleteDetails = "DELETE FROM goodsreceipt_details WHERE goodsreceipt_id = '$receiptId'";
    if (!$database->execute($sqlDeleteDetails)) {
      throw new Exception('Không thể xóa chi tiết phiếu nhập');
    }

    $sqlDeleteReceipt = "DELETE FROM goodsreceipts WHERE id = '$receiptId'";
    if (!$database->execute($sqlDeleteReceipt)) {
      throw new Exception('Không thể xóa phiếu nhập');
    }

    mysqli_commit($database->conn);
    return "<span class='success'>Xóa phiếu nhập thành công</span>";
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    return "<span class='failed'>Xóa phiếu nhập thất bại: " . $e->getMessage() . "</span>";
  }
}
