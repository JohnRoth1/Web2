<?php
$ds = DIRECTORY_SEPARATOR;
$base_dir = realpath(dirname(__FILE__)  . $ds . '..' . $ds . '..') . $ds;
include_once("{$base_dir}model" . $ds . "connect.php");
include_once("{$base_dir}model" . $ds . "admin" . $ds . "pricing.model.php");

error_log("Pricing Controller - POST data: " . json_encode($_POST));

if (isset($_POST['function'])) {
  $func = $_POST['function'];
  
  if ($func == 'getProducts') {
    error_log("Pricing Controller - Getting products");
    $products = pricing_getProducts();
    header('Content-Type: application/json');
    echo json_encode($products);
  } 
  elseif ($func == 'updatePrice') {
    if (isset($_POST['product_id']) && isset($_POST['price'])) {
      $result = pricing_updatePrice($_POST['product_id'], $_POST['price']);
      header('Content-Type: application/json');
      echo json_encode(['success' => $result]);
    }
  }
  elseif ($func == 'applyMargin') {
    if (isset($_POST['product_id']) && isset($_POST['margin'])) {
      $result = pricing_applyMargin($_POST['product_id'], $_POST['margin']);
      header('Content-Type: application/json');
      echo json_encode($result);
    }
  }
  elseif ($func == 'getAverageCostPrice') {
    if (isset($_POST['product_id'])) {
      $product_id = $_POST['product_id'];
      $average_cost = pricing_getAverageCostPrice($product_id);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'average_cost_price' => $average_cost
      ]);
    }
  }
  elseif ($func == 'getReceipts') {
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $perPage = 10;
    
    error_log("Pricing Controller - Getting receipts");
    $result = pricing_getReceipts($search, $page, $perPage);
    header('Content-Type: application/json');
    echo json_encode($result);
  }
  elseif ($func == 'getBatchDetails') {
    if (isset($_POST['batch_id'])) {
      error_log("Pricing Controller - Getting batch details for: " . $_POST['batch_id']);
      $result = pricing_getBatchDetails($_POST['batch_id']);
      header('Content-Type: application/json');
      echo json_encode($result);
    }
  }
}
