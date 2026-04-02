<?php

// Lấy giá nhập bình quân của sản phẩm
function pricing_getAverageCostPrice($product_id)
{
  $database = new connectDB();
  $product_id = mysqli_real_escape_string($database->conn, $product_id);
  
  // Lấy tất cả phiếu nhập cho sản phẩm (sắp xếp theo ngày)
  $sql = "
    SELECT gd.quantity as input_quantity, gd.input_price, gr.date_create
    FROM goodsreceipt_details gd
    JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    WHERE gd.product_id = '$product_id'
    ORDER BY gr.date_create ASC, gr.id ASC
  ";
  
  $result = $database->query($sql);
  $totalQuantity = 0;
  $totalCost = 0;
  $averagePrice = 0;
  
  error_log("pricing_getAverageCostPrice - Product ID: $product_id");
  
  if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $inputQty = intval($row['input_quantity']);
      $inputPrice = floatval($row['input_price']);
      
      // Công thức BÌNH QUÂN: (số lượng tồn × giá hiện tại + số lượng nhập × giá nhập mới) / (số lượng tồn + số lượng nhập)
      $totalCost = ($totalQuantity * $averagePrice) + ($inputQty * $inputPrice);
      $totalQuantity += $inputQty;
      $averagePrice = $totalQuantity > 0 ? $totalCost / $totalQuantity : $inputPrice;
      
      error_log("  Step - Qty: $inputQty, Price: $inputPrice, Avg: $averagePrice");
    }
  }
  
  error_log("pricing_getAverageCostPrice - Final Average Price: $averagePrice");
  $database->close();
  return $averagePrice > 0 ? $averagePrice : 0;
}

function pricing_getProducts()
{
  $database = new connectDB();
  error_log("Pricing Model - Database connection: " . ($database->conn ? "Connected" : "Failed"));
  
  $sql = "
    SELECT p.id, p.name, p.price, p.profit_margin, p.supplier_id, s.name as supplier_name
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.status = 1
    ORDER BY p.id ASC
  ";
  
  error_log("Pricing Model - Executing query: " . $sql);
  $result = $database->query($sql);
  $data = [];
  
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
    error_log("Pricing Model - Retrieved " . count($data) . " products");
  } else {
    error_log("Pricing Model - Query failed: " . $database->conn->error);
  }
  
  $database->close();
  return $data;
}

function pricing_updatePrice($product_id, $new_price)
{
  $database = new connectDB();
  date_default_timezone_set('Asia/Ho_Chi_Minh');
  $date = date('Y-m-d', time());
  
  $product_id = mysqli_real_escape_string($database->conn, $product_id);
  $new_price_val = floatval($new_price);
  $new_price = mysqli_real_escape_string($database->conn, strval($new_price_val));

  // Chuẩn hóa logic: nếu có giá nhập bình quân thì suy ra margin tương ứng với giá mới
  $avg_cost = pricing_getAverageCostPrice($product_id);
  if ($avg_cost > 0) {
    $derived_margin = (($new_price_val - $avg_cost) / $avg_cost) * 100;
    $derived_margin_safe = mysqli_real_escape_string($database->conn, strval($derived_margin));
    $sql = "UPDATE products 
            SET price = '$new_price', profit_margin = '$derived_margin_safe', update_date = '$date' 
            WHERE id = '$product_id'";
  } else {
    $sql = "UPDATE products 
            SET price = '$new_price', update_date = '$date' 
            WHERE id = '$product_id'";
  }
  
  $result = $database->execute($sql);
  $database->close();
  
  return $result;
}

function pricing_applyMargin($product_id, $margin_percent)
{
  $database = new connectDB();
  date_default_timezone_set('Asia/Ho_Chi_Minh');
  $date = date('Y-m-d', time());
  
  $product_id = mysqli_real_escape_string($database->conn, $product_id);
  $margin_percent = floatval($margin_percent);
  
  error_log("pricing_applyMargin - Product ID: $product_id, Margin: $margin_percent%");
  
  // Lấy giá nhập bình quân
  $average_cost_price = pricing_getAverageCostPrice($product_id);
  
  if ($average_cost_price > 0) {
    // Tính giá bán: Giá nhập × (100% + tỷ lệ lợi nhuận)
    // VD: giá nhập 100, lợi nhuận 20% => giá bán = 100 × 1.2 = 120
    $new_price = $average_cost_price * (1 + $margin_percent / 100);
    
    error_log("pricing_applyMargin - Cost: $average_cost_price, New Price: $new_price");
    
    // Update giá bán và lưu % lợi nhuận (ghi đè)
    $margin_percent_safe = mysqli_real_escape_string($database->conn, strval($margin_percent));
    $sql_update = "UPDATE products 
                   SET price = '$new_price', profit_margin = '$margin_percent_safe', update_date = '$date' 
                   WHERE id = '$product_id'";
    
    $update_result = $database->execute($sql_update);
    $database->close();
    
    return array(
      'success' => $update_result,
      'cost_price' => round($average_cost_price, 2),
      'new_price' => round($new_price, 2),
      'margin' => $margin_percent
    );
  }
  
  $database->close();
  return array('success' => false, 'message' => 'Không tìm thấy giá nhập cho sản phẩm này');
}

// Set % lợi nhuận cho tất cả sản phẩm và đồng bộ giá bán theo giá nhập bình quân
function pricing_increaseProfitMarginAll($increase_percent)
{
  $database = new connectDB();
  date_default_timezone_set('Asia/Ho_Chi_Minh');
  $date = date('Y-m-d', time());

  $increase = floatval($increase_percent);
  if ($increase < 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Phần trăm tăng phải >= 0'
    ];
  }

  // Lấy toàn bộ sản phẩm đang hoạt động
  $sqlProducts = "SELECT id FROM products WHERE status = 1 ORDER BY id ASC";
  $result = $database->query($sqlProducts);

  if (!$result) {
    $err = $database->conn->error;
    $database->close();
    return [
      'success' => false,
      'message' => 'Không thể lấy danh sách sản phẩm: ' . $err
    ];
  }

  $updated = 0;
  $skippedNoCost = 0;
  $failed = 0;

  while ($row = mysqli_fetch_assoc($result)) {
    $product_id = $row['id'];
    $average_cost_price = pricing_getAverageCostPrice($product_id);
    if ($average_cost_price <= 0) {
      $skippedNoCost++;
      continue;
    }
    $new_price = $average_cost_price * (1 + ($increase / 100));

    $product_id_safe = mysqli_real_escape_string($database->conn, $product_id);
    $new_price_safe = mysqli_real_escape_string($database->conn, strval($new_price));
    $increase_safe = mysqli_real_escape_string($database->conn, strval($increase));

    $sql_update = "UPDATE products 
                   SET price = '$new_price_safe', profit_margin = '$increase_safe', update_date = '$date' 
                   WHERE id = '$product_id_safe'";

    $ok = $database->execute($sql_update);
    if ($ok) $updated++;
    else $failed++;
  }

  $database->close();

  return [
    'success' => true,
    'increase_percent' => $increase,
    'updated' => $updated,
    'skipped_no_cost' => $skippedNoCost,
    'failed' => $failed
  ];
}

// Lấy danh sách lô hàng (goods receipts) với pagination
function pricing_getReceipts($search = '', $page = 1, $perPage = 10)
{
  $database = new connectDB();
  $search = mysqli_real_escape_string($database->conn, $search);
  
  // Build search condition
  $searchCondition = "";
  if (!empty($search)) {
    $searchCondition = "WHERE 
      gr.id LIKE '%$search%' 
      OR DATE_FORMAT(gr.date_create, '%d/%m/%Y') LIKE '%$search%'
      OR p.id LIKE '%$search%'
      OR p.name LIKE '%$search%'
      OR s.name LIKE '%$search%'";
  }
  
  // Count total records for pagination
  $countSql = "
    SELECT COUNT(DISTINCT gr.id) as total
    FROM goodsreceipts gr
    LEFT JOIN goodsreceipt_details grd ON gr.id = grd.goodsreceipt_id
    LEFT JOIN products p ON grd.product_id = p.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    $searchCondition
  ";
  
  error_log("pricing_getReceipts - CountSQL: " . $countSql);
  
  $countResult = $database->query($countSql);
  if (!$countResult) {
    error_log("pricing_getReceipts - Count query failed: " . $database->conn->error);
    $database->close();
    return [
      'total' => 0,
      'totalPages' => 0,
      'currentPage' => $page,
      'perPage' => $perPage,
      'data' => []
    ];
  }
  
  $countRow = mysqli_fetch_assoc($countResult);
  $totalRecords = isset($countRow['total']) ? intval($countRow['total']) : 0;
  $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;
  
  // Get paginated records
  $offset = ($page - 1) * $perPage;
  
  $sql = "
    SELECT 
      gr.id as batch_id,
      DATE_FORMAT(gr.date_create, '%d/%m/%Y') as date_create,
      COUNT(grd.product_id) as product_count,
      GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as supplier_name
    FROM goodsreceipts gr
    LEFT JOIN goodsreceipt_details grd ON gr.id = grd.goodsreceipt_id
    LEFT JOIN products p ON grd.product_id = p.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    $searchCondition
    GROUP BY gr.id
    ORDER BY gr.date_create DESC
    LIMIT $offset, $perPage
  ";
  
  error_log("pricing_getReceipts - SQL: " . $sql);
  
  $result = $database->query($sql);
  $data = [];
  
  if (!$result) {
    error_log("pricing_getReceipts - Query failed: " . $database->conn->error);
    $database->close();
    return [
      'total' => 0,
      'totalPages' => 0,
      'currentPage' => $page,
      'perPage' => $perPage,
      'data' => []
    ];
  }
  
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
  
  error_log("pricing_getReceipts - Retrieved " . count($data) . " batches");
  
  $database->close();
  
  return [
    'total' => $totalRecords,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'perPage' => $perPage,
    'data' => $data
  ];
}

// Lấy chi tiết sản phẩm trong một lô hàng
function pricing_getBatchDetails($batch_id)
{
  $database = new connectDB();
  $batch_id = mysqli_real_escape_string($database->conn, $batch_id);
  
  $sql = "
    SELECT 
      grd.product_id,
      p.name as product_name,
      grd.quantity as input_quantity,
      grd.input_price as cost_price,
      CASE 
        WHEN grd.input_price > 0 THEN ROUND(((p.price - grd.input_price) / grd.input_price) * 100, 2)
        ELSE 0
      END as margin_percent,
      pricing_avg.avg_cost as average_cost_price,
      p.price as batch_selling_price,
      p.price as current_selling_price,
      s.name as supplier_name,
      DATE_FORMAT(gr.date_create, '%d/%m/%Y') as batch_date
    FROM goodsreceipt_details grd
    JOIN goodsreceipts gr ON grd.goodsreceipt_id = gr.id
    JOIN products p ON grd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN (
      SELECT x.product_id, SUM(x.quantity * x.input_price) / NULLIF(SUM(x.quantity), 0) as avg_cost
      FROM goodsreceipt_details x
      GROUP BY x.product_id
    ) as pricing_avg ON pricing_avg.product_id = p.id
    WHERE grd.goodsreceipt_id = '$batch_id'
    ORDER BY p.id ASC
  ";
  
  error_log("pricing_getBatchDetails - Batch ID: $batch_id, SQL: " . $sql);
  
  $result = $database->query($sql);
  $data = [];
  $batchInfo = [];
  
  if (!$result) {
    error_log("pricing_getBatchDetails - Query failed: " . $database->conn->error);
    $database->close();
    return [
      'batchInfo' => [],
      'products' => []
    ];
  }
  
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    
    // Store batch info from first record
    if (empty($batchInfo)) {
      $batchInfo = [
        'batch_id' => $batch_id,
        'batch_date' => $row['batch_date'],
        'supplier_name' => $row['supplier_name']
      ];
    }
  }
  
  error_log("pricing_getBatchDetails - Retrieved " . count($data) . " products");
  
  $database->close();
  
  return [
    'batchInfo' => $batchInfo,
    'products' => $data
  ];
}

// Calculate margin percent from cost price and selling price
function pricing_calculateMargin($costPrice, $sellingPrice)
{
  if ($costPrice <= 0) return 0;
  
  $margin = (($sellingPrice - $costPrice) / $costPrice) * 100;
  return round($margin, 2);
}
