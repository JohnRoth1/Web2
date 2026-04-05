<?php
include_once('../../model/connect.php');
include_once('../../model/admin/inventory.model.php');

if (isset($_POST['function'])) {
  $function = $_POST['function'];
  switch ($function) {
    case 'getHistory':
      getHistory();
      break;
    case 'getProducts':
      getProducts();
      break;
    case 'getCurrentStock':
      getCurrentStock();
      break;
    case 'getProductHistory':
      getProductHistory();
      break;
    case 'getStockAtDate':
      getStockAtDate();
      break;
    case 'getStockInRange':
      getStockInRange();
      break;
    case 'getProductTransactionsAtDate':
      getProductTransactionsAtDate();
      break;
    case 'getTransactionDocumentDetail':
      getTransactionDocumentDetail();
      break;
    case 'deleteTransactionDocument':
      deleteTransactionDocument();
      break;
    case 'getLowStockProducts':
      getLowStockProducts();
      break;
    case 'getLowStockCount':
      getLowStockCount();
      break;
    case 'getStockStatusCounts':
      getStockStatusCounts();
      break;
    case 'updateAlertQty':
      updateAlertQty();
      break;
    case 'setBulkAlertQty':
      setBulkAlertQty();
      break;
    case 'getDefaultAlertQty':
      getDefaultAlertQty();
      break;
  }
}

function getHistory()
{
  $filters = [];
  
  if (isset($_POST['date_start'])) {
    $filters['date_start'] = $_POST['date_start'];
  }
  
  if (isset($_POST['date_end'])) {
    $filters['date_end'] = $_POST['date_end'];
  }
  
  if (isset($_POST['product_id'])) {
    $filters['product_id'] = $_POST['product_id'];
  }
  
  if (isset($_POST['product_name'])) {
    $filters['product_name'] = $_POST['product_name'];
  }
  
  if (isset($_POST['type'])) {
    $filters['type'] = $_POST['type'];
  }
  
  $data = inventory_getHistory($filters);
  echo json_encode($data);
}

function getProducts()
{
  $data = inventory_getProducts();
  echo json_encode($data);
}

function getCurrentStock()
{
  $data = inventory_getCurrentStock();
  echo json_encode($data);
}

function getProductHistory()
{
  $filters = [];
  
  if (isset($_POST['date_start'])) {
    $filters['date_start'] = $_POST['date_start'];
  }
  
  if (isset($_POST['date_end'])) {
    $filters['date_end'] = $_POST['date_end'];
  }
  
  $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
  $data = inventory_getProductHistory($product_id, $filters);
  echo json_encode($data);
}

function getStockAtDate()
{
  $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
  $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
  $perPage = 8;
  $search = isset($_POST['search']) ? trim($_POST['search']) : '';
  $status = isset($_POST['status']) ? trim($_POST['status']) : '';
  
  error_log("getStockAtDate called with date: " . $date . ", page: " . $page . ", search: " . $search . ", status: " . $status);
  
  $filters = [
    'search' => $search,
    'status' => $status
  ];

  $data = inventory_getStockAtDate($date, $page, $perPage, $filters);
  $total = inventory_getTotalProducts($date, $filters);
  $totalPages = $total > 0 ? ceil($total / $perPage) : 1;
  
  $result = [
    'data' => $data,
    'total' => $total,
    'page' => $page,
    'perPage' => $perPage,
    'totalPages' => $totalPages
  ];
  
  error_log("getStockAtDate returning: " . json_encode($result));
  echo json_encode($result);
}

function getStockInRange()
{
  $date_start = isset($_POST['date_start']) ? $_POST['date_start'] : date('Y-m-d', strtotime('-30 days'));
  $date_end = isset($_POST['date_end']) ? $_POST['date_end'] : date('Y-m-d');
  $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
  $perPage = 8;
  $search = isset($_POST['search']) ? trim($_POST['search']) : '';
  $status = isset($_POST['status']) ? trim($_POST['status']) : '';

  $filters = [
    'search' => $search,
    'status' => $status
  ];

  $data = inventory_getStockInRange($date_start, $date_end, $page, $perPage, $filters);
  $total = inventory_getTotalProductsInRange($date_start, $date_end, $filters);
  $totalPages = $total > 0 ? ceil($total / $perPage) : 1;

  $result = [
    'data' => $data,
    'total' => $total,
    'page' => $page,
    'perPage' => $perPage,
    'totalPages' => $totalPages
  ];

  echo json_encode($result);
}

function getLowStockProducts()
{
  $products = inventory_getLowStockProducts();
  header('Content-Type: application/json');
  echo json_encode($products);
}

function getLowStockCount()
{
  $count = inventory_getLowStockCount();
  header('Content-Type: application/json');
  echo json_encode(['count' => $count]);
}

function getStockStatusCounts()
{
  $counts = inventory_getStockStatusCounts();
  header('Content-Type: application/json');
  echo json_encode($counts);
}

function updateAlertQty()
{
  if (isset($_POST['product_id']) && isset($_POST['alert_qty'])) {
    $out_of_stock_qty = isset($_POST['out_of_stock_qty']) ? $_POST['out_of_stock_qty'] : 0;
    $result = inventory_updateAlertQty($_POST['product_id'], $_POST['alert_qty'], $out_of_stock_qty);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
  } else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
  }
}

function getProductTransactionsAtDate()
{
  $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
  $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
  $data = inventory_getProductTransactionsAtDate($product_id, $date);
  echo json_encode($data);
}

function getTransactionDocumentDetail()
{
  $document_type = isset($_POST['document_type']) ? $_POST['document_type'] : '';
  $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;

  $data = inventory_getTransactionDocumentDetail($document_type, $document_id);
  header('Content-Type: application/json');
  echo json_encode($data);
}

function deleteTransactionDocument()
{
  header('Content-Type: application/json');
  echo json_encode([
    'success' => false,
    'message' => 'Không cho phép xóa bill/phiếu trong màn hình này'
  ]);
}

function setBulkAlertQty()
{
  if (isset($_POST['alert_qty'])) {
    $alert_qty = $_POST['alert_qty'];
    $out_of_stock_qty = isset($_POST['out_of_stock_qty']) ? $_POST['out_of_stock_qty'] : 0;
    error_log("setBulkAlertQty - Received alert_qty: $alert_qty, out_of_stock_qty: $out_of_stock_qty");
    
    $result = inventory_setBulkAlertQty($alert_qty, $out_of_stock_qty);
    header('Content-Type: application/json');
    error_log("setBulkAlertQty - Result: " . ($result ? "true" : "false"));
    echo json_encode(['success' => (bool)$result]);
  } else {
    header('Content-Type: application/json');
    error_log("setBulkAlertQty - Missing alert_qty parameter");
    echo json_encode(['success' => false, 'message' => 'Missing alert_qty parameter']);
  }
}

function getDefaultAlertQty()
{
  $default = inventory_getDefaultAlertQty();
  header('Content-Type: application/json');
  echo json_encode([
    'default_alert_qty' => $default['alert_qty'],
    'default_out_of_stock_qty' => $default['out_of_stock_qty']
  ]);
}
