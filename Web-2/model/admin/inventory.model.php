<?php
$ds = DIRECTORY_SEPARATOR;
$base_dir = realpath(dirname(__FILE__)  . $ds . '..') . $ds;
include_once("{$base_dir}connect.php");

function inventory_getSuccessfulOrderCondition($orderAlias = 'o')
{
  return $orderAlias . ".status_id = 5";
}

function inventory_ensureOutOfStockQtyColumn($database)
{
  $checkSql = "SHOW COLUMNS FROM products LIKE 'out_of_stock_qty'";
  $checkResult = $database->query($checkSql);
  if (!$checkResult || $checkResult->num_rows === 0) {
    $database->execute("ALTER TABLE products ADD COLUMN out_of_stock_qty INT(11) NOT NULL DEFAULT 0 AFTER alert_qty");
  }
}

// Lấy lịch sử tồn kho từ các phiếu nhập và đơn hàng
function inventory_getHistory($filters = [])
{
  $database = new connectDB();
  
  $whereNhap = "1=1";
  $whereXuat = "1=1";
  
  // Lọc theo ngày bắt đầu
  if (!empty($filters['date_start'])) {
    $dateStart = mysqli_real_escape_string($database->conn, $filters['date_start']);
    $whereNhap .= " AND gr.date_create >= '$dateStart'";
    $whereXuat .= " AND o.date_create >= '$dateStart'";
  }
  
  // Lọc theo ngày kết thúc
  if (!empty($filters['date_end'])) {
    $dateEnd = mysqli_real_escape_string($database->conn, $filters['date_end']);
    $whereNhap .= " AND gr.date_create <= '$dateEnd'";
    $whereXuat .= " AND o.date_create <= '$dateEnd'";
  }
  
  // Lọc theo mã sản phẩm
  if (!empty($filters['product_id'])) {
    $productId = mysqli_real_escape_string($database->conn, $filters['product_id']);
    $whereNhap .= " AND gd.product_id = '$productId'";
    $whereXuat .= " AND od.product_id = '$productId'";
  }
  
  // Lọc theo tên sản phẩm
  if (!empty($filters['product_name'])) {
    $productName = mysqli_real_escape_string($database->conn, $filters['product_name']);
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
    WHERE $whereNhap AND gr.status = 'completed'
    
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
    WHERE $whereXuat AND " . inventory_getSuccessfulOrderCondition('o') . "
    
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
        JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
        WHERE gd.product_id = p.id
          AND gr.status = 'completed'
      ) as total_input,
      (
        SELECT COALESCE(SUM(od.quantity), 0)
        FROM order_details od
        JOIN orders o ON od.order_id = o.id
        WHERE od.product_id = p.id AND " . inventory_getSuccessfulOrderCondition('o') . "
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
    WHERE gd.product_id = '$product_id' AND gr.status = 'completed'
    
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
    WHERE od.product_id = '$product_id' AND " . inventory_getSuccessfulOrderCondition('o') . "
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

// Lấy tổng số sản phẩm
function inventory_getStockBaseQuery($date)
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  $date = mysqli_real_escape_string($database->conn, $date);
  $database->close();

  return "
    SELECT 
      p.id,
      p.name,
      s.name as supplier_name,
      COALESCE(p.alert_qty, 0) as alert_qty,
      COALESCE(p.out_of_stock_qty, 0) as out_of_stock_qty,
      (
        (
          SELECT COALESCE(SUM(gd.quantity), 0)
          FROM goodsreceipt_details gd
          JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
          WHERE gd.product_id = p.id
            AND gr.status = 'completed'
            AND DATE(gr.date_create) <= '$date'
        )
        -
        (
          SELECT COALESCE(SUM(od.quantity), 0)
          FROM order_details od
          JOIN orders o ON od.order_id = o.id
          WHERE od.product_id = p.id
            AND " . inventory_getSuccessfulOrderCondition('o') . "
            AND DATE(o.date_create) <= '$date'
        )
      ) as stock_at_date
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.status = 1
  ";
}

function inventory_getTotalProducts($date = '', $filters = [])
{
  $database = new connectDB();

  if (empty($date)) {
    $date = date('Y-m-d');
  }

  $date = mysqli_real_escape_string($database->conn, $date);

  $search = isset($filters['search']) ? mysqli_real_escape_string($database->conn, $filters['search']) : '';
  $status = isset($filters['status']) ? mysqli_real_escape_string($database->conn, $filters['status']) : '';
  $baseQuery = inventory_getStockBaseQuery($date);
  $whereConditions = [];

  if ($search !== '') {
    $whereConditions[] = "(CAST(stock_data.id AS CHAR) LIKE '%$search%' OR stock_data.name LIKE '%$search%' OR stock_data.supplier_name LIKE '%$search%')";
  }

  if ($status === 'instock') {
    $whereConditions[] = "stock_data.stock_at_date > stock_data.alert_qty";
  } elseif ($status === 'lowstock') {
    $whereConditions[] = "stock_data.stock_at_date > stock_data.out_of_stock_qty";
    $whereConditions[] = "stock_data.stock_at_date <= stock_data.alert_qty";
  } elseif ($status === 'outstock') {
    $whereConditions[] = "stock_data.stock_at_date <= stock_data.out_of_stock_qty";
  }

  $whereSql = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
  $sql = "SELECT COUNT(*) as total FROM ($baseQuery) stock_data $whereSql";

  $result = $database->query($sql);
  $total = 0;

  if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total = intval($row['total'] ?? 0);
  }

  $database->close();
  return $total;
}

// Lấy tồn kho của tất cả sản phẩm tại 1 thời điểm cụ thể (có phân trang)
function inventory_getStockAtDate($date = '', $page = 1, $perPage = 8, $filters = [])
{
  $database = new connectDB();

  if (empty($date)) {
    $date = date('Y-m-d');
  }

  $date = mysqli_real_escape_string($database->conn, $date);
  $search = isset($filters['search']) ? mysqli_real_escape_string($database->conn, $filters['search']) : '';
  $status = isset($filters['status']) ? mysqli_real_escape_string($database->conn, $filters['status']) : '';
  $page = max(1, intval($page));
  $perPage = intval($perPage);
  $offset = ($page - 1) * $perPage;

  error_log("inventory_getStockAtDate: Processing date: " . $date . ", page: " . $page . ", offset: " . $offset . ", search: " . $search . ", status: " . $status);

  $baseQuery = inventory_getStockBaseQuery($date);
  $whereConditions = [];

  if ($search !== '') {
    $whereConditions[] = "(CAST(stock_data.id AS CHAR) LIKE '%$search%' OR stock_data.name LIKE '%$search%' OR stock_data.supplier_name LIKE '%$search%')";
  }

  if ($status === 'instock') {
    $whereConditions[] = "stock_data.stock_at_date > stock_data.alert_qty";
  } elseif ($status === 'lowstock') {
    $whereConditions[] = "stock_data.stock_at_date > stock_data.out_of_stock_qty";
    $whereConditions[] = "stock_data.stock_at_date <= stock_data.alert_qty";
  } elseif ($status === 'outstock') {
    $whereConditions[] = "stock_data.stock_at_date <= stock_data.out_of_stock_qty";
  }

  $whereSql = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

  $sql = "
    SELECT *
    FROM ($baseQuery) stock_data
    $whereSql
    ORDER BY stock_data.id ASC
    LIMIT $perPage OFFSET $offset
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
    error_log("inventory_getStockAtDate: Query failed - " . $database->conn->error);
  }
  
  $database->close();
  return $data;
}

function inventory_getStockRangeBaseQuery($date_start, $date_end)
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);

  $date_start = mysqli_real_escape_string($database->conn, $date_start);
  $date_end = mysqli_real_escape_string($database->conn, $date_end);
  $database->close();

  return "
    SELECT
      p.id,
      p.name,
      s.name as supplier_name,
      p.alert_qty,
      p.out_of_stock_qty,
      (
        (
          SELECT COALESCE(SUM(gd.quantity), 0)
          FROM goodsreceipt_details gd
          JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
          WHERE gd.product_id = p.id
            AND gr.status = 'completed'
            AND DATE(gr.date_create) <= '$date_end'
        )
        -
        (
          SELECT COALESCE(SUM(od.quantity), 0)
          FROM order_details od
          JOIN orders o ON od.order_id = o.id
          WHERE od.product_id = p.id
            AND " . inventory_getSuccessfulOrderCondition('o') . "
            AND DATE(o.date_create) <= '$date_end'
        )
      ) as stock_at_end_date,
      (
        SELECT COALESCE(SUM(gd.quantity), 0)
        FROM goodsreceipt_details gd
        JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
        WHERE gd.product_id = p.id
          AND gr.status = 'completed'
          AND DATE(gr.date_create) >= '$date_start'
          AND DATE(gr.date_create) <= '$date_end'
      ) as total_input_range,
      (
        SELECT COALESCE(SUM(od.quantity), 0)
        FROM order_details od
        JOIN orders o ON od.order_id = o.id
        WHERE od.product_id = p.id
          AND " . inventory_getSuccessfulOrderCondition('o') . "
          AND DATE(o.date_create) >= '$date_start'
          AND DATE(o.date_create) <= '$date_end'
      ) as total_output_range,
      (
        (
          SELECT COALESCE(SUM(gd.quantity), 0)
          FROM goodsreceipt_details gd
          JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
          WHERE gd.product_id = p.id
            AND gr.status = 'completed'
            AND DATE(gr.date_create) >= '$date_start'
            AND DATE(gr.date_create) <= '$date_end'
        )
        -
        (
          SELECT COALESCE(SUM(od.quantity), 0)
          FROM order_details od
          JOIN orders o ON od.order_id = o.id
          WHERE od.product_id = p.id
            AND " . inventory_getSuccessfulOrderCondition('o') . "
            AND DATE(o.date_create) >= '$date_start'
            AND DATE(o.date_create) <= '$date_end'
        )
      ) as net_change_range
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.status = 1
    GROUP BY p.id, p.name, s.name, p.alert_qty, p.out_of_stock_qty, p.quantity
  ";
}

function inventory_getTotalProductsInRange($date_start, $date_end, $filters = [])
{
  $database = new connectDB();

  $search = isset($filters['search']) ? mysqli_real_escape_string($database->conn, $filters['search']) : '';
  $status = isset($filters['status']) ? mysqli_real_escape_string($database->conn, $filters['status']) : '';
  $baseQuery = inventory_getStockRangeBaseQuery($date_start, $date_end);
  $whereConditions = [];

  if ($search !== '') {
    $whereConditions[] = "(CAST(stock_data.id AS CHAR) LIKE '%$search%' OR stock_data.name LIKE '%$search%' OR stock_data.supplier_name LIKE '%$search%')";
  }

  if ($status === 'instock') {
    $whereConditions[] = "stock_data.stock_at_end_date > stock_data.alert_qty";
  } elseif ($status === 'lowstock') {
    $whereConditions[] = "stock_data.stock_at_end_date > stock_data.out_of_stock_qty";
    $whereConditions[] = "stock_data.stock_at_end_date <= stock_data.alert_qty";
  } elseif ($status === 'outstock') {
    $whereConditions[] = "stock_data.stock_at_end_date <= stock_data.out_of_stock_qty";
  }

  $whereSql = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
  $sql = "SELECT COUNT(*) as total FROM ($baseQuery) stock_data $whereSql";

  $result = $database->query($sql);
  $total = 0;

  if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total = intval($row['total'] ?? 0);
  }

  $database->close();
  return $total;
}

function inventory_getStockInRange($date_start = '', $date_end = '', $page = 1, $perPage = 8, $filters = [])
{
  $database = new connectDB();

  if (empty($date_start)) {
    $date_start = date('Y-m-d', strtotime('-30 days'));
  }
  if (empty($date_end)) {
    $date_end = date('Y-m-d');
  }

  $search = isset($filters['search']) ? mysqli_real_escape_string($database->conn, $filters['search']) : '';
  $status = isset($filters['status']) ? mysqli_real_escape_string($database->conn, $filters['status']) : '';
  $page = max(1, intval($page));
  $perPage = intval($perPage);
  $offset = ($page - 1) * $perPage;

  $baseQuery = inventory_getStockRangeBaseQuery($date_start, $date_end);
  $whereConditions = [];

  if ($search !== '') {
    $whereConditions[] = "(CAST(stock_data.id AS CHAR) LIKE '%$search%' OR stock_data.name LIKE '%$search%' OR stock_data.supplier_name LIKE '%$search%')";
  }

  if ($status === 'instock') {
    $whereConditions[] = "stock_data.stock_at_end_date > stock_data.alert_qty";
  } elseif ($status === 'lowstock') {
    $whereConditions[] = "stock_data.stock_at_end_date > stock_data.out_of_stock_qty";
    $whereConditions[] = "stock_data.stock_at_end_date <= stock_data.alert_qty";
  } elseif ($status === 'outstock') {
    $whereConditions[] = "stock_data.stock_at_end_date <= stock_data.out_of_stock_qty";
  }

  $whereSql = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

  $sql = "
    SELECT *
    FROM ($baseQuery) stock_data
    $whereSql
    ORDER BY stock_data.id ASC
    LIMIT $perPage OFFSET $offset
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

// Lấy chi tiết giao dịch của 1 sản phẩm tại 1 thời điểm
function inventory_getProductTransactionsAtDate($product_id, $date = '')
{
  $database = new connectDB();
  
  if (empty($date)) {
    $date = date('Y-m-d');
  }
  
  $date = mysqli_real_escape_string($database->conn, $date);
  $product_id = mysqli_real_escape_string($database->conn, $product_id);
  
  $sql = "
    SELECT 
      'Nhập' as type,
      'receipt' as document_type,
      gd.quantity,
      gr.date_create as transaction_date,
      gr.id as receipt_id,
      gr.id as transaction_id,
      s.name as source,
      gr.staff_id as staff_name,
      gd.input_price as price
    FROM goodsreceipt_details gd
    JOIN goodsreceipts gr ON gd.goodsreceipt_id = gr.id
    JOIN products p ON gd.product_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE gd.product_id = '$product_id' AND gr.status = 'completed' AND DATE(gr.date_create) <= '$date'
    
    UNION ALL
    
    SELECT 
      'Xuất' as type,
      'order' as document_type,
      od.quantity,
      o.date_create as transaction_date,
      od.order_id as receipt_id,
      od.order_id as transaction_id,
      'Khách hàng' as source,
      o.staff_id as staff_name,
      od.price
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    WHERE od.product_id = '$product_id' AND DATE(o.date_create) <= '$date' AND " . inventory_getSuccessfulOrderCondition('o') . "
    
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

function inventory_getTransactionDocumentDetail($documentType, $documentId)
{
  $database = new connectDB();

  $documentType = mysqli_real_escape_string($database->conn, $documentType);
  $documentId = intval($documentId);

  if ($documentId <= 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Mã chứng từ không hợp lệ'
    ];
  }

  if ($documentType === 'receipt') {
    $headerSql = "
      SELECT
        gr.id,
        DATE_FORMAT(gr.date_create, '%Y-%m-%d %H:%i:%s') as document_date,
        gr.staff_id,
        gr.total_price,
        gr.status,
        COALESCE(s.name, '') as partner_name
      FROM goodsreceipts gr
      LEFT JOIN suppliers s ON s.id = gr.supplier_id
      WHERE gr.id = '$documentId'
      LIMIT 1
    ";

    $detailsSql = "
      SELECT
        gd.product_id,
        p.name as product_name,
        gd.quantity,
        gd.input_price as unit_price,
        (gd.quantity * gd.input_price) as line_total
      FROM goodsreceipt_details gd
      JOIN products p ON p.id = gd.product_id
      WHERE gd.goodsreceipt_id = '$documentId'
      ORDER BY gd.product_id ASC
    ";
  } elseif ($documentType === 'order') {
    $headerSql = "
      SELECT
        o.id,
        DATE_FORMAT(o.date_create, '%Y-%m-%d %H:%i:%s') as document_date,
        o.staff_id,
        o.total_price,
        o.status_id,
        'Khách hàng' as partner_name
      FROM orders o
      WHERE o.id = '$documentId'
      LIMIT 1
    ";

    $detailsSql = "
      SELECT
        od.product_id,
        p.name as product_name,
        od.quantity,
        od.price as unit_price,
        (od.quantity * od.price) as line_total
      FROM order_details od
      JOIN products p ON p.id = od.product_id
      WHERE od.order_id = '$documentId'
      ORDER BY od.product_id ASC
    ";
  } else {
    $database->close();
    return [
      'success' => false,
      'message' => 'Loại chứng từ không hợp lệ'
    ];
  }

  $headerResult = $database->query($headerSql);
  if (!$headerResult || $headerResult->num_rows === 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Không tìm thấy chứng từ'
    ];
  }

  $header = mysqli_fetch_assoc($headerResult);
  $detailsResult = $database->query($detailsSql);
  $details = [];

  if ($detailsResult) {
    while ($row = mysqli_fetch_assoc($detailsResult)) {
      $details[] = $row;
    }
  }

  $database->close();

  return [
    'success' => true,
    'document_type' => $documentType,
    'header' => $header,
    'details' => $details
  ];
}

function inventory_deleteTransactionDocument($documentType, $documentId)
{
  $database = new connectDB();

  $documentType = mysqli_real_escape_string($database->conn, $documentType);
  $documentId = intval($documentId);

  if ($documentId <= 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Mã chứng từ không hợp lệ'
    ];
  }

  if ($documentType !== 'receipt') {
    $database->close();
    return [
      'success' => false,
      'message' => 'Hiện chỉ hỗ trợ xóa phiếu nhập'
    ];
  }

  $checkSql = "SELECT status FROM goodsreceipts WHERE id = '$documentId' LIMIT 1";
  $checkResult = $database->query($checkSql);
  if (!$checkResult || $checkResult->num_rows === 0) {
    $database->close();
    return [
      'success' => false,
      'message' => 'Không tìm thấy phiếu nhập'
    ];
  }

  $status = $checkResult->fetch_assoc()['status'];
  if ($status === 'completed') {
    $database->close();
    return [
      'success' => false,
      'message' => 'Không thể xóa phiếu nhập đã hoàn thành'
    ];
  }

  mysqli_begin_transaction($database->conn);
  try {
    $deleteDetailsSql = "DELETE FROM goodsreceipt_details WHERE goodsreceipt_id = '$documentId'";
    if (!$database->execute($deleteDetailsSql)) {
      throw new Exception('Không thể xóa chi tiết phiếu nhập');
    }

    $deleteReceiptSql = "DELETE FROM goodsreceipts WHERE id = '$documentId'";
    if (!$database->execute($deleteReceiptSql)) {
      throw new Exception('Không thể xóa phiếu nhập');
    }

    mysqli_commit($database->conn);
    $database->close();
    return [
      'success' => true,
      'message' => 'Đã xóa phiếu nhập thành công'
    ];
  } catch (Exception $e) {
    mysqli_rollback($database->conn);
    $database->close();
    return [
      'success' => false,
      'message' => $e->getMessage()
    ];
  }
}

// Lấy danh sách sản phẩm sắp hết hàng (quantity < alert_qty)
function inventory_getLowStockProducts()
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  
  $sql = "
    SELECT 
      p.id,
      p.name,
      p.quantity as current_quantity,
      p.alert_qty,
      p.out_of_stock_qty,
      CASE 
        WHEN p.alert_qty > 0 THEN ROUND((p.quantity / p.alert_qty) * 100, 1)
        ELSE 0
      END as stock_percentage,
      s.name as supplier_name,
      p.price,
      p.quantity * p.price as total_value
    FROM products p
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.status = 1 AND p.quantity > p.out_of_stock_qty AND p.quantity <= p.alert_qty
    ORDER BY stock_percentage ASC, p.name ASC
  ";
  
  error_log("inventory_getLowStockProducts - SQL: " . $sql);
  
  $result = $database->query($sql);
  $data = [];
  
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
    error_log("inventory_getLowStockProducts - Found " . count($data) . " low stock products");
  } else {
    error_log("inventory_getLowStockProducts - Query failed: " . $database->conn->error);
  }
  
  $database->close();
  return $data;
}

// Đếm số sản phẩm sắp hết hàng
function inventory_getLowStockCount()
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  
  $sql = "
    SELECT COUNT(*) as count
    FROM products p
    WHERE p.status = 1 AND p.quantity > p.out_of_stock_qty AND p.quantity <= p.alert_qty
  ";
  
  $result = $database->query($sql);
  $row = mysqli_fetch_assoc($result);
  $count = isset($row['count']) ? intval($row['count']) : 0;
  
  $database->close();
  return $count;
}

function inventory_getStockStatusCounts()
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);

  $sql = "
    SELECT 
      SUM(CASE WHEN p.status = 1 AND p.quantity > p.out_of_stock_qty AND p.quantity <= p.alert_qty THEN 1 ELSE 0 END) as low_stock_count,
      SUM(CASE WHEN p.status = 1 AND p.quantity <= p.out_of_stock_qty THEN 1 ELSE 0 END) as out_of_stock_count
    FROM products p
  ";

  $result = $database->query($sql);
  $lowStockCount = 0;
  $outOfStockCount = 0;

  if ($result) {
    $row = mysqli_fetch_assoc($result);
    $lowStockCount = isset($row['low_stock_count']) ? intval($row['low_stock_count']) : 0;
    $outOfStockCount = isset($row['out_of_stock_count']) ? intval($row['out_of_stock_count']) : 0;
  }

  $database->close();

  return [
    'low_stock_count' => $lowStockCount,
    'out_of_stock_count' => $outOfStockCount
  ];
}

// Cập nhật alert_qty cho một sản phẩm
function inventory_updateAlertQty($product_id, $alert_qty, $out_of_stock_qty = 0)
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  
  $product_id = mysqli_real_escape_string($database->conn, $product_id);
  $alert_qty = intval($alert_qty);
  $out_of_stock_qty = intval($out_of_stock_qty);
  
  if ($alert_qty < 0 || $out_of_stock_qty < 0 || $out_of_stock_qty > $alert_qty) {
    $database->close();
    return false;
  }
  
  $sql = "UPDATE products 
          SET alert_qty = $alert_qty, out_of_stock_qty = $out_of_stock_qty
          WHERE id = '$product_id'";
  
  error_log("inventory_updateAlertQty - SQL: $sql");
  
  $result = $database->execute($sql);
  
  if (!$result) {
    error_log("inventory_updateAlertQty - Error: " . $database->conn->error);
  }
  
  $database->close();
  
  return $result;
}

// Cập nhật mức cảnh báo cho TẤT CẢ sản phẩm
function inventory_setBulkAlertQty($alert_qty, $out_of_stock_qty = 0)
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  
  if (!is_numeric($alert_qty) || !is_numeric($out_of_stock_qty) || $alert_qty < 0 || $out_of_stock_qty < 0 || $out_of_stock_qty > $alert_qty) {
    error_log("inventory_setBulkAlertQty - Invalid thresholds: alert_qty=$alert_qty, out_of_stock_qty=$out_of_stock_qty");
    $database->close();
    return false;
  }
  
  $alert_qty = intval($alert_qty);
  $out_of_stock_qty = intval($out_of_stock_qty);
  $sql = "UPDATE products 
          SET alert_qty = $alert_qty, out_of_stock_qty = $out_of_stock_qty
          WHERE status = 1";
  
  error_log("inventory_setBulkAlertQty - SQL: $sql");
  
  $result = $database->execute($sql);
  
  if (!$result) {
    error_log("inventory_setBulkAlertQty - Error: " . $database->conn->error);
  } else {
    error_log("inventory_setBulkAlertQty - Success: Updated all products to Alert Qty: $alert_qty");
  }
  
  $database->close();
  
  return $result;
}

// Lấy mức cảnh báo mặc định (kiểm tra nếu tất cả sản phẩm có cùng mức)
function inventory_getDefaultAlertQty()
{
  $database = new connectDB();
  inventory_ensureOutOfStockQtyColumn($database);
  
  $sql = "SELECT DISTINCT alert_qty, out_of_stock_qty, COUNT(*) as product_count 
          FROM products 
          WHERE status = 1 
          GROUP BY alert_qty, out_of_stock_qty 
          ORDER BY product_count DESC 
          LIMIT 1";
  
  $result = $database->query($sql);
  $database->close();
  
  if ($result && $row = mysqli_fetch_assoc($result)) {
    return [
      'alert_qty' => (int)$row['alert_qty'],
      'out_of_stock_qty' => (int)$row['out_of_stock_qty']
    ];
  }
  
  return [
    'alert_qty' => 10,
    'out_of_stock_qty' => 0
  ];
}
