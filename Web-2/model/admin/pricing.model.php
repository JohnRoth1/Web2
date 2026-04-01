<?php

function pricing_getProducts()
{
  $database = new connectDB();
  error_log("Pricing Model - Database connection: " . ($database->conn ? "Connected" : "Failed"));
  
  $sql = "
    SELECT p.id, p.name, p.price, p.supplier_id, s.name as supplier_name
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
  $new_price = mysqli_real_escape_string($database->conn, $new_price);
  
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
  
  // Get current input price (cost price)
  $sql = "SELECT id FROM goodsreceipt_details WHERE product_id = '$product_id' ORDER BY id DESC LIMIT 1";
  $result = $database->query($sql);
  
  if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $grd_id = $row['id'];
    
    // Get input_price from the latest goods receipt
    $sql_price = "SELECT input_price FROM goodsreceipt_details WHERE product_id = '$product_id' ORDER BY id DESC LIMIT 1";
    $result_price = $database->query($sql_price);
    
    if ($result_price && mysqli_num_rows($result_price) > 0) {
      $price_row = mysqli_fetch_assoc($result_price);
      $input_price = floatval($price_row['input_price']);
      
      // Calculate selling price: input_price * (1 + margin_percent/100)
      $new_price = $input_price * (1 + $margin_percent / 100);
      
      // Update product price
      $sql_update = "UPDATE products 
                     SET price = '$new_price', update_date = '$date' 
                     WHERE id = '$product_id'";
      
      $update_result = $database->execute($sql_update);
      $database->close();
      
      return array(
        'success' => $update_result,
        'input_price' => $input_price,
        'new_price' => round($new_price, 2),
        'margin' => $margin_percent
      );
    }
  }
  
  $database->close();
  return array('success' => false, 'message' => 'Không tìm thấy giá nhập cho sản phẩm này');
}
