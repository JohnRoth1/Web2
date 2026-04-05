<?php 
  include_once('connect.php');

  function getProductsByIdCategoryModel($category_id, $item_amount, $page) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT p.id product_id,
                      p.name product_name, 
                      (COALESCE(p.price, 0) * (1 + COALESCE(p.profit_margin, 0))) AS price, 
                      p.image_path
              FROM category_details cd
              INNER JOIN products p ON p.id = cd.product_id
              INNER JOIN categories c ON c.id = cd.category_id
              WHERE c.id = $category_id AND p.status = 1";

      if ($item_amount && $page) {
        $offset = ($page - 1) * $item_amount;
        $sql .= " LIMIT $item_amount OFFSET $offset";
      }

      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }

  function getProductsByUnknownCategoryModel() {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT DISTINCT 
      p.id AS id,
      p.name AS product_name, 
      p.image_path, 
      p.quantity, 
        (COALESCE(p.price, 0) * (1 + COALESCE(p.profit_margin, 0))) AS price, 
      CASE WHEN c.status = 1 THEN CONCAT(' ', c.name) ELSE NULL END AS category_names
      FROM 
          products p  
          INNER JOIN publishers pub ON p.publisher_id = pub.id
          LEFT JOIN category_details cd ON cd.product_id = p.id  
          LEFT JOIN categories c ON cd.category_id = c.id
          WHERE p.status = 1
      GROUP BY 
          p.id
      HAVING
          category_names IS NULL;
    ";

      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }

  // function getProductsForPaginationModel($item_per_page, $page) {
  //   $database = new connectDB();
  //   if ($database->conn) {
  //     $offset = ($page - 1) * $item_per_page;
  //     $sql = "SELECT * 
  //             FROM products 
  //             ORDER BY id ASC
  //             LIMIT $item_per_page OFFSET $offset;";
  //     $result = $database->query($sql);
  //     $database->close();
  //     return $result;     
  //   } else {
  //     $database->close();
  //     return false;
  //   }
  // }

  function getAmountProductModel() {
    $database = new connectDB();
    if ($database->conn) {
      $products = $database->selectAll('products'); 
      $database->close();
      return $products->num_rows;    
    } else {
      $database->close();
      return false;
    }
  }

  function getProductsByFilter($keyword, $listCategoryIds, $startRange, $endRange, $minPrice = null, $maxPrice = null, $itemsPerPage = null, $page = null) {
    $database = new connectDB();
    if ($database->conn) {
      $sellingPriceExpr = "(COALESCE(p.price, 0) * (1 + COALESCE(p.profit_margin, 0)))";
      $escapedKeyword = mysqli_real_escape_string($database->conn, trim((string)$keyword));
      $sql = "SELECT DISTINCT p.id id,
                      p.name product_name, 
                      $sellingPriceExpr AS price, 
                      p.image_path,
                      p.quantity
              FROM products p
              LEFT JOIN category_details cd ON cd.product_id = p.id
              LEFT JOIN categories c ON c.id = cd.category_id
              WHERE p.status = 1   ";
      // Câu lệnh query theo tên sản phẩm
      if ($escapedKeyword !== '') {
        $sql .= " AND p.name LIKE '%$escapedKeyword%'";
      }     
      
      // Câu lệnh query theo thể loại
      if (count($listCategoryIds) > 0) {
        $safeCategoryIds = array_map('intval', $listCategoryIds);
        $sql .= " AND (";
        foreach ($safeCategoryIds as $key => $categoryId) {
          $sql .= "c.id = $categoryId";
          if ($key < count($safeCategoryIds) - 1) {
            $sql .= " OR ";
          }
        }
        $sql .= ")";
      }

      // Câu lệnh query theo khoảng giá
      if ($minPrice !== null && $maxPrice !== null) {
        $minPrice = intval($minPrice);
        $maxPrice = intval($maxPrice);
        $sql .= " AND $sellingPriceExpr BETWEEN $minPrice AND $maxPrice";
      } else if ($minPrice !== null) {
        $minPrice = intval($minPrice);
        $sql .= " AND $sellingPriceExpr >= $minPrice";
      } else if ($maxPrice !== null) {
        $maxPrice = intval($maxPrice);
        $sql .= " AND $sellingPriceExpr <= $maxPrice";
      } else if (($startRange == 0 || $startRange) && $endRange) {
        $sql .= " AND $sellingPriceExpr BETWEEN $startRange AND $endRange";
      }

      // Câu lệnh sắp theo thứ tự price/p.id
      if (count($listCategoryIds) > 0) {
        $sql .= " ORDER BY $sellingPriceExpr ASC";
      } else {
        $sql .= " ORDER BY p.id ASC";
      }

      if ($itemsPerPage && $page) {
        $itemsPerPage = intval($itemsPerPage);
        $page = intval($page);
        $offset = ($page - 1) * $itemsPerPage;
        $sql .= " LIMIT $itemsPerPage OFFSET $offset;";
      }

      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }

  function checkProductEnoughQuantity($id, $quantity) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT *
              FROM products
              WHERE id = $id AND quantity >= $quantity";
      $result = $database->query($sql);
      if ($result && $result->num_rows > 0) {
        $database->close();
        return true;
      } else {
        $database->close();
        return false;
      }
    } else {
      $database->close();
      return false;
    }
  } 

  function updateQuantityProductByIdModel($id, $quantity) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT *
              FROM products
              WHERE id = $id";
      $isExist = $database->query($sql);
      
      // Nếu sản phẩm tồn tại
      if ($isExist && $isExist->num_rows > 0) {
          $row = $isExist->fetch_assoc();
          $newQuantity = $row['quantity'] + $quantity;
          if ($newQuantity < 0) {
            $database->close();
            return false;
          }
        $sqlUpdateAmount = "UPDATE products
                            SET quantity = quantity + $quantity
                            WHERE id = $id";
        $result = $database->execute($sqlUpdateAmount);
        $database->close();
        return $result;
      } 
      $database->close();
      return false;
    } else {
      $database->close();
      return false;
    }
  }

  function searchProductsByKeywordModel($keyword) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT * 
              FROM products
              WHERE name LIKE '%$keyword%'  AND status = 1
              ORDER BY id DESC";
      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }

  function getNewProductsModel($status) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT p.*, (COALESCE(p.price, 0) * (1 + COALESCE(p.profit_margin, 0))) AS price
              FROM products p";
           
      if ($status) {
        $sql .= " WHERE p.status = $status";
      }
      
      $sql .= " ORDER BY p.id DESC";
      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }
?>