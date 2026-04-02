
<?php
$ds = DIRECTORY_SEPARATOR;
$base_dir = realpath(dirname(__FILE__)  . $ds . '..') . $ds;
include_once("{$base_dir}connect.php");
$database = new connectDB();

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

  date_default_timezone_set('Asia/Ho_Chi_Minh');
  $date = date('Y-m-d', time());

  // $supplierId = $field['supplierId'];
  $totalPrice = $field['totalPrice'];
  $staffId = $field['staffId'];
  $receiptId = getNewReceiptId($database);



  $sqlInsertReceipt = "INSERT INTO goodsreceipts (id, staff_id, total_price, date_create) 
                         VALUES ('" . $receiptId . "', '" . $staffId . "', '" . $totalPrice . "', '" . $date . "')";
  $resultReceipt = $database->query($sqlInsertReceipt);

  if ($resultReceipt) {

    foreach ($field['details'] as $detail) {
      $productId = $detail['productId'];
      $quantity = $detail['quantity'];
      $inputPrice = $detail['inputPrice'];

      $sqlInsertDetail = "INSERT INTO goodsreceipt_details (product_id, goodsreceipt_id, quantity, input_price) 
                                    VALUES ('" . $productId . "', '" . $receiptId . "', '" . $quantity . "', '" . $inputPrice . "')";
      $resultDetail = $database->query($sqlInsertDetail);

      $sqlUpdateQuantity = "UPDATE products SET quantity = quantity + '" . $quantity . "' WHERE id = '" . $productId . "'";
      $resultUpdateQuantity = $database->query($sqlUpdateQuantity);

      if (!$resultDetail || !$resultUpdateQuantity) {
        return "<span class='failed'>Tạo đơn nhập hàng không thành công</span>";
      }
    }
    return "<span class='success'>Tạo đơn nhập hàng thành công</span>";
  } else {
    return "<span class='failed'>Error retrieving last inserted ID</span>";
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
  if (!isset($field['id'])) {
    return json_encode([
      'success' => false,
      'message' => 'Thiếu mã phiếu nhập'
    ]);
  }

  $id = intval($field['id']);
  $sql = "SELECT gr.id, gr.staff_id, gr.total_price, DATE_FORMAT(gr.date_create, '%Y-%m-%d') AS date_create
          FROM goodsreceipts gr
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

  if (!isset($field['id']) || !isset($field['details']) || !is_array($field['details'])) {
    return "<span class='failed'>Dữ liệu cập nhật không hợp lệ</span>";
  }

  $receiptId = intval($field['id']);
  $newDetails = $field['details'];
  $newDateCreate = isset($field['date_create']) ? $field['date_create'] : null;

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

  mysqli_begin_transaction($database->conn);

  try {
    // Cập nhật/Thêm chi tiết mới
    foreach ($normalized as $pid => $detail) {
      $newQty = intval($detail['quantity']);
      $newPrice = floatval($detail['input_price']);

      $oldQty = isset($oldDetails[$pid]) ? intval($oldDetails[$pid]['quantity']) : 0;
      $deltaQty = $newQty - $oldQty;

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

      if ($deltaQty !== 0) {
        $sqlGetStock = "SELECT quantity FROM products WHERE id = '$pid' FOR UPDATE";
        $stockResult = $database->query($sqlGetStock);
        if (!$stockResult || $stockResult->num_rows === 0) {
          throw new Exception("Không tìm thấy sản phẩm để cập nhật tồn kho");
        }
        $currentStock = intval($stockResult->fetch_assoc()['quantity']);
        if ($currentStock + $deltaQty < 0) {
          throw new Exception("Không đủ tồn kho để cập nhật phiếu nhập");
        }

        $sqlUpdateStock = "UPDATE products SET quantity = quantity + ($deltaQty) WHERE id = '$pid'";
        if (!$database->execute($sqlUpdateStock)) {
          throw new Exception("Không thể cập nhật tồn kho sản phẩm");
        }
      }
    }

    // Xóa các dòng cũ không còn trong chi tiết mới
    foreach ($oldDetails as $pid => $oldDetail) {
      if (!isset($normalized[$pid])) {
        $oldQty = intval($oldDetail['quantity']);

        $sqlGetStock = "SELECT quantity FROM products WHERE id = '$pid' FOR UPDATE";
        $stockResult = $database->query($sqlGetStock);
        if (!$stockResult || $stockResult->num_rows === 0) {
          throw new Exception("Không tìm thấy sản phẩm để cập nhật tồn kho");
        }
        $currentStock = intval($stockResult->fetch_assoc()['quantity']);
        if ($currentStock - $oldQty < 0) {
          throw new Exception("Không đủ tồn kho để xóa dòng chi tiết");
        }

        $sqlDeleteDetail = "DELETE FROM goodsreceipt_details
                            WHERE goodsreceipt_id = '$receiptId' AND product_id = '$pid'";
        if (!$database->execute($sqlDeleteDetail)) {
          throw new Exception("Không thể xóa chi tiết phiếu nhập");
        }

        $sqlUpdateStock = "UPDATE products SET quantity = quantity - ($oldQty) WHERE id = '$pid'";
        if (!$database->execute($sqlUpdateStock)) {
          throw new Exception("Không thể cập nhật tồn kho khi xóa dòng");
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

    mysqli_commit($database->conn);
    return "<span class='success'>Cập nhật phiếu nhập thành công</span>";
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    return "<span class='failed'>Cập nhật phiếu nhập thất bại: " . $e->getMessage() . "</span>";
  }
}
