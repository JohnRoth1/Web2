<?php

// Lấy giá nhập bình quân của sản phẩm
function pricing_getAverageCostPrice($product_id)
{
  $database = new connectDB();
  $product_id = mysqli_real_escape_string($database->conn, $product_id);

  $sql = "SELECT COALESCE(price, 0) as cost_price FROM products WHERE id = '$product_id' LIMIT 1";
  $result = $database->query($sql);
  $averagePrice = 0;

  if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $averagePrice = floatval($row['cost_price']);
  }

  $database->close();
  return $averagePrice > 0 ? $averagePrice : 0;
}

function pricing_getProducts()
{
  $database = new connectDB();
  error_log("Pricing Model - Database connection: " . ($database->conn ? "Connected" : "Failed"));
  
  $sql = "
    SELECT 
      p.id,
      p.name,
      p.price,
      p.profit_margin,
      p.supplier_id,
      s.name as supplier_name,
      COALESCE(p.price, 0) as average_cost_price
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

    $sql = "UPDATE products 
      SET price = '$new_price', update_date = '$date' 
      WHERE id = '$product_id'";
  
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
  $margin_ratio = $margin_percent / 100;
  
  error_log("pricing_applyMargin - Product ID: $product_id, Margin: $margin_percent%");
  
  // Lấy giá nhập bình quân
  $average_cost_price = pricing_getAverageCostPrice($product_id);
  
  if ($average_cost_price > 0) {
    // Tính giá bán: Giá nhập × (100% + tỷ lệ lợi nhuận)
    // VD: giá nhập 100, lợi nhuận 20% => giá bán = 100 × 1.2 = 120
    $new_price = $average_cost_price * (1 + $margin_ratio);
    
    error_log("pricing_applyMargin - Cost: $average_cost_price, New Price: $new_price");
    
    // Chỉ lưu tỷ lệ lợi nhuận trong DB. Giá bán được tính động từ giá vốn.
    $margin_ratio_safe = mysqli_real_escape_string($database->conn, strval($margin_ratio));
    $sql_update = "UPDATE products 
         SET profit_margin = '$margin_ratio_safe', update_date = '$date' 
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
  $increase_ratio = $increase / 100;
  if ($increase < 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Phần trăm tăng phải >= 0'
    ];
  }

  // Lấy toàn bộ sản phẩm đang hoạt động cùng % lợi nhuận hiện tại
  $sqlProducts = "SELECT id, profit_margin FROM products WHERE status = 1 ORDER BY id ASC";
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
    $current_margin = isset($row['profit_margin']) ? floatval($row['profit_margin']) : 0;
    $new_margin = $current_margin + $increase_ratio;

    $product_id_safe = mysqli_real_escape_string($database->conn, $product_id);
    $new_margin_safe = mysqli_real_escape_string($database->conn, strval($new_margin));

    $sql_update = "UPDATE products 
                   SET profit_margin = '$new_margin_safe', update_date = '$date' 
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

// Lấy danh sách lô hàng (goods receipts) với pagination - OPTIMIZED
function pricing_getReceipts($search = '', $page = 1, $perPage = 10)
{
  $database = new connectDB();
  $search = trim($search);
  $limit = 1000; // Prevent excessive search results
  
  $searchCondition = "";
  $searchParams = [];
  
  if (!empty($search)) {
    // Escape search term for LIKE queries
    $searchTerm = '%' . mysqli_real_escape_string($database->conn, $search) . '%';
    $searchCondition = "WHERE 
      gr.id LIKE '$searchTerm' 
      OR DATE_FORMAT(gr.date_create, '%d/%m/%Y') LIKE '$searchTerm'
      OR grd.product_id IN (
        SELECT id FROM products WHERE id LIKE '$searchTerm' OR name LIKE '$searchTerm'
      )
      OR p.supplier_id IN (
        SELECT id FROM suppliers WHERE name LIKE '$searchTerm'
      )";
  }
  
  // OPTIMIZED: Simplified COUNT query without unnecessary JOINs
  $countSql = "
    SELECT COUNT(DISTINCT gr.id) as total
    FROM goodsreceipts gr
    LEFT JOIN goodsreceipt_details grd ON gr.id = grd.goodsreceipt_id
    LEFT JOIN products p ON grd.product_id = p.id
    $searchCondition
    LIMIT $limit
  ";
  
  error_log("pricing_getReceipts - CountSQL performance optimized");
  
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
  
  // OPTIMIZED: Simplified main query - fetch only needed data
  $sql = "
    SELECT 
      gr.id as batch_id,
      DATE_FORMAT(gr.date_create, '%d/%m/%Y') as date_create,
      (SELECT COUNT(*) FROM goodsreceipt_details WHERE goodsreceipt_id = gr.id) as product_count,
      (SELECT GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ')
       FROM goodsreceipt_details grd2
       JOIN products p2 ON grd2.product_id = p2.id
       JOIN suppliers s ON p2.supplier_id = s.id
       WHERE grd2.goodsreceipt_id = gr.id) as supplier_name
    FROM goodsreceipts gr
    $searchCondition
    GROUP BY gr.id
    ORDER BY gr.date_create DESC
    LIMIT $offset, $perPage
  ";
  
  error_log("pricing_getReceipts - Getting paginated data");
  
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

// Lấy chi tiết sản phẩm trong một lô hàng - OPTIMIZED
function pricing_getBatchDetails($batch_id)
{
  $database = new connectDB();
  $batch_id = mysqli_real_escape_string($database->conn, $batch_id);
  
  // OPTIMIZED: Simplified query - fetch batch info separately
  // First get batch header info
  $sqlHeader = "
    SELECT 
      gr.id as batch_id,
      DATE_FORMAT(gr.date_create, '%d/%m/%Y') as batch_date,
      (SELECT GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ')
       FROM goodsreceipt_details grd
       JOIN products p ON grd.product_id = p.id
       JOIN suppliers s ON p.supplier_id = s.id
       WHERE grd.goodsreceipt_id = gr.id) as supplier_name
    FROM goodsreceipts gr
    WHERE gr.id = '$batch_id'
  ";
  
  $batchInfo = [];
  $headerResult = $database->query($sqlHeader);
  if ($headerResult && mysqli_num_rows($headerResult) > 0) {
    $batchInfo = mysqli_fetch_assoc($headerResult);
  }
  
  // OPTIMIZED: Fetch details with minimal calculations
  // Calculate prices once, not per-row
  $sql = "
    SELECT 
      grd.product_id,
      p.name as product_name,
      grd.quantity as input_quantity,
      grd.input_price as cost_price,
      p.price as average_cost_price,
      COALESCE(p.profit_margin, 0) as margin_ratio,
      ROUND(COALESCE(p.profit_margin, 0) * 100, 2) as margin_percent,
      s.name as supplier_name
    FROM goodsreceipt_details grd
    JOIN products p ON grd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE grd.goodsreceipt_id = '$batch_id'
    ORDER BY p.id ASC
  ";
  
  error_log("pricing_getBatchDetails - Batch ID: $batch_id, optimized query");
  
  $result = $database->query($sql);
  $data = [];
  
  if (!$result) {
    error_log("pricing_getBatchDetails - Query failed: " . $database->conn->error);
    $database->close();
    return [
      'batchInfo' => [],
      'products' => []
    ];
  }
  
  // Calculate prices in PHP once instead of SQL multiple times
  while ($row = mysqli_fetch_assoc($result)) {
    $costPrice = floatval($row['average_cost_price']);
    $marginRatio = floatval($row['margin_ratio']);
    $sellingPrice = $costPrice * (1 + $marginRatio);
    
    $row['batch_selling_price'] = round($sellingPrice, 2);
    $row['current_selling_price'] = round($sellingPrice, 2);
    
    $data[] = $row;
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
