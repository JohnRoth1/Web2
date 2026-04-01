<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('model/connect.php');
include_once('model/admin/inventory.model.php');

echo "<h1>Inventory Test</h1>";

// Test connection
$database = new connectDB();
if (!$database->link) {
  echo "<p style='color: red;'>❌ Database connection failed!</p>";
  exit;
}
echo "<p style='color: green;'>✅ Database connected</p>";

// Test products table
$result = $database->query("SELECT COUNT(*) as count FROM products WHERE status = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  echo "<p>✅ Products table accessible. Active products: " . $row['count'] . "</p>";
} else {
  echo "<p style='color: red;'>❌ Cannot access products table</p>";
}

// Test suppliers table
$result = $database->query("SELECT COUNT(*) as count FROM suppliers");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  echo "<p>✅ Suppliers table accessible. Total suppliers: " . $row['count'] . "</p>";
} else {
  echo "<p style='color: red;'>❌ Cannot access suppliers table</p>";
}

// Test goodsreceipt_details table
$result = $database->query("SELECT COUNT(*) as count FROM goodsreceipt_details");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  echo "<p>✅ Goodsreceipt_details table accessible. Total records: " . $row['count'] . "</p>";
} else {
  echo "<p style='color: red;'>❌ Cannot access goodsreceipt_details table</p>";
}

// Test order_details table
$result = $database->query("SELECT COUNT(*) as count FROM order_details");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  echo "<p>✅ Order_details table accessible. Total records: " . $row['count'] . "</p>";
} else {
  echo "<p style='color: red;'>❌ Cannot access order_details table</p>";
}

// Test the actual inventory function
echo "<h2>Testing inventory_getStockAtDate()</h2>";
$date = date('Y-m-d');
echo "<p>Testing with date: " . $date . "</p>";

$data = inventory_getStockAtDate($date);
if (is_array($data)) {
  echo "<p style='color: green;'>✅ Function returned array with " . count($data) . " products</p>";
  if (count($data) > 0) {
    echo "<pre>";
    echo print_r($data[0], true);
    echo "</pre>";
  }
} else {
  echo "<p style='color: red;'>❌ Function did not return array</p>";
}

$database->close();
?>
