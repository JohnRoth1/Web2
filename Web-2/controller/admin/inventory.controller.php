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
    case 'getProductTransactionsAtDate':
      getProductTransactionsAtDate();
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
  $data = inventory_getStockAtDate($date);
  echo json_encode($data);
}

function getProductTransactionsAtDate()
{
  $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
  $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
  $data = inventory_getProductTransactionsAtDate($product_id, $date);
  echo json_encode($data);
}
