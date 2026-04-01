<?php
$ds = DIRECTORY_SEPARATOR;
$base_dir = realpath(dirname(__FILE__)  . $ds . '..') . $ds;
include_once("{$base_dir}connect.php");

// Lấy lịch sử tồn kho từ các phiếu nhập và đơn hàng
function inventory_getHistory($filters = [])
{
  $database = new connectDB();
  
  $whereNhap = "1=1";
  $whereXuat = "1=1";
  
  // Lọc theo ngày bắt đầu
  if (!empty($filters['date_start'])) {
    $dateStart = mysqli_real_escape_string($database->link, $filters['date_start']);
    $whereNhap .= " AND gr.date_create >= '$dateStart'";
    $whereXuat .= " AND o.date_create >= '$dateStart'";
  }
  
  // Lọc theo ngày kết thúc
  if (!empty($filters['date_end'])) {
    $dateEnd = mysqli_real_escape_string($database->link, $filters['date_end']);
    $whereNhap .= " AND gr.date_create <= '$dateEnd'";
    $whereXuat .= " AND o.date_create <= '$dateEnd'";
  }
  
  // Lọc theo mã sản phẩm
  if (!empty($filters['product_id'])) {
    $productId = mysqli_real_escape_string($database->link, $filters['product_id']);
    $whereNhap .= " AND gd.product_id = '$productId'";
    $whereXuat .= " AND od.product_id = '$productId'";
  }
  
  // Lọc theo tên sản phẩm
  if (!empty($filters['product_name'])) {
    $productName = mysqli_real_escape_string($database->link, $filters['product_name']);
    $whereNhap .= " AND p.name LIKE '%$productName%'";
    $whereXuat .= " AND p.name LIKE '%$productName%'";
  }
  
  $sql = "
    SELECT 
      'Nhập' as type,
      gd.product_id,
      p.name as product_name,
      p.supplier_id,
      s.name as supplier_name,
      gd.quantity,
      gr.date_create as transaction_date,
      gr.id as receipt_id,
      NULL as order_id,
      gr.staff_id as staff_name
    FROM goodsreceipt_details gd
    JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    JOIN products p ON gd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE $whereNhap
    
    UNION ALL
    
    SELECT 
      'Xuất' as type,
      od.product_id,
      p.name as product_name,
      p.supplier_id,
      s.name as supplier_name,
      od.quantity,
      o.date_create as transaction_date,
      NULL as receipt_id,
      od.order_id,
      o.staff_id as staff_name
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    JOIN products p ON od.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE $whereXuat AND o.status_id IN (5, 4)
    
    ORDER BY transaction_date DESC
  ";
  
  $result = $database->query($sql);
  $data = [];
  
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
  }
  
  $database->close();
  return $data;
}

// Lấy danh sách sản phẩm để lọc
function inventory_getProducts()
{
  $database = new connectDB();
  $sql = "SELECT id, name FROM products WHERE status = 1 ORDER BY name";
  $result = $database->query($sql);
  
  $data = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
  
  $database->close();
  return $data;
}

// Tính tồn kho hiện tại theo từng sản phẩm
function inventory_getCurrentStock()
{
  $database = new connectDB();
  
  $sql = "
    SELECT 
      p.id,
      p.name,
      p.quantity as current_quantity,
      (
        SELECT COALESCE(SUM(gd.quantity), 0)
        FROM goodsreceipt_details gd
        WHERE gd.product_id = p.id
      ) as total_input,
      (
        SELECT COALESCE(SUM(od.quantity), 0)
        FROM order_details od
        JOIN orders o ON od.order_id = o.id
        WHERE od.product_id = p.id AND o.status_id IN (5, 4)
      ) as total_output,
      s.name as supplier_name
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.status = 1
    ORDER BY p.name
  ";
  
  $result = $database->query($sql);
  $data = [];
  
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
  
  $database->close();
  return $data;
}

// Lấy lịch sử tồn kho cho 1 sản phẩm cụ thể
function inventory_getProductHistory($product_id, $filters = [])
{
  $database = new connectDB();
  
  $sql = "
    SELECT 
      'Nhập' as type,
      gd.quantity,
      gr.date_create as transaction_date,
      gr.id as receipt_id,
      s.name as supplier_name,
      gr.staff_id as staff_name,
      gd.input_price as price
    FROM goodsreceipt_details gd
    JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    JOIN products p ON gd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE gd.product_id = '$product_id'
    
    UNION ALL
    
    SELECT 
      'Xuất' as type,
      od.quantity,
      o.date_create as transaction_date,
      od.order_id as receipt_id,
      'Khách hàng' as supplier_name,
      o.staff_id as staff_name,
      od.price
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    WHERE od.product_id = '$product_id' AND o.status_id IN (5, 4)
  ";
  
  if (!empty($filters['date_start'])) {
    $sql .= " AND transaction_date >= '" . $filters['date_start'] . "'";
  }
  
  if (!empty($filters['date_end'])) {
    $sql .= " AND transaction_date <= '" . $filters['date_end'] . "'";
  }
  
  $sql .= " ORDER BY transaction_date DESC";
  
  $result = $database->query($sql);
  $data = [];
  
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
  
  $database->close();
  return $data;
}

// Lấy tồn kho của tất cả sản phẩm tại 1 thời điểm cụ thể
function inventory_getStockAtDate($date = '')
{
  $database = new connectDB();
  
  // Nếu không chỉ định ngày, lấy hôm nay
  if (empty($date)) {
    $date = date('Y-m-d');
  }
  
  $date = mysqli_real_escape_string($database->link, $date);
  error_log("inventory_getStockAtDate: Processing date: " . $date);
  
  $sql = "
    SELECT 
      p.id,
      p.name,
      s.name as supplier_name,
      COALESCE(SUM(CASE WHEN gr.date_create IS NOT NULL AND DATE(gr.date_create) <= '$date' THEN gd.quantity ELSE 0 END), 0) as total_input,
      COALESCE(SUM(CASE WHEN o.date_create IS NOT NULL AND DATE(o.date_create) <= '$date' AND o.status_id IN (5, 4) THEN od.quantity ELSE 0 END), 0) as total_output,
      (COALESCE(SUM(CASE WHEN gr.date_create IS NOT NULL AND DATE(gr.date_create) <= '$date' THEN gd.quantity ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN o.date_create IS NOT NULL AND DATE(o.date_create) <= '$date' AND o.status_id IN (5, 4) THEN od.quantity ELSE 0 END), 0)) as stock_at_date
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN goodsreceipt_details gd ON gd.product_id = p.id
    LEFT JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    LEFT JOIN order_details od ON od.product_id = p.id
    LEFT JOIN orders o ON od.order_id = o.id
    WHERE p.status = 1
    GROUP BY p.id, p.name, s.name
    ORDER BY p.name
  ";
  
  error_log("inventory_getStockAtDate: Executing query");
  $result = $database->query($sql);
  $data = [];
  
  if ($result) {
    error_log("inventory_getStockAtDate: Query successful");
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
    error_log("inventory_getStockAtDate: Retrieved " . count($data) . " rows");
  } else {
    error_log("inventory_getStockAtDate: Query failed - " . $database->link->error);
  }
  
  $database->close();
  return $data;
}

// Lấy chi tiết giao dịch của 1 sản phẩm tại 1 thời điểm
function inventory_getProductTransactionsAtDate($product_id, $date = '')
{
  $database = new connectDB();
  
  if (empty($date)) {
    $date = date('Y-m-d');
  }
  
  $date = mysqli_real_escape_string($database->link, $date);
  $product_id = mysqli_real_escape_string($database->link, $product_id);
  
  $sql = "
    SELECT 
      'Nhập' as type,
      gd.quantity,
      gr.date_create as transaction_date,
      gr.id as receipt_id,
      s.name as source,
      gr.staff_id as staff_name,
      gd.input_price as price
    FROM goodsreceipt_details gd
    JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    JOIN products p ON gd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE gd.product_id = '$product_id' AND DATE(gr.date_create) <= '$date'
    
    UNION ALL
    
    SELECT 
      'Xuất' as type,
      od.quantity,
      o.date_create as transaction_date,
      od.order_id as receipt_id,
      'Khách hàng' as source,
      o.staff_id as staff_name,
      od.price
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    WHERE od.product_id = '$product_id' AND DATE(o.date_create) <= '$date' AND o.status_id IN (5, 4)
    
    ORDER BY transaction_date DESC
  ";
  
  $result = $database->query($sql);
  $data = [];
  
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
  }
  
  $database->close();
  return $data;
}
