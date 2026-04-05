<?php
  session_start();
  include_once("../model/product.model.php");
  include_once("../model/product_detail.model.php");
  include_once("../model/order.model.php");
  include_once("../model/order_detail.model.php");
  include_once("../model/delivery_info.model.php");

  function getCheckoutPaymentMethodLabel($paymentMethod)
  {
    switch ($paymentMethod) {
      case 'bank_transfer':
        return 'Chuyển khoản ngân hàng';
      case 'online':
        return 'Thanh toán trực tuyến';
      default:
        return 'Thanh toán tiền mặt khi nhận hàng';
    }
  }

  function buildCheckoutOrderSummary($orderId, $deliveryInfoId, $cartSelected, $discountCode, $totalPrice, $paymentMethod)
  {
    $deliveryInfo = getDetailDeliveryInfoById($deliveryInfoId);
    $deliveryInfo = $deliveryInfo ? $deliveryInfo->fetch_assoc() : null;

    $items = [];
    $subtotal = 0;
    foreach ($cartSelected as $product) {
      $productDetail = getProductDetailByIdModel($product['productId']);
      if (!$productDetail) {
        continue;
      }

      $productDetail = $productDetail->fetch_assoc();
      $unitPrice = isset($productDetail['price']) ? (int)$productDetail['price'] : 0;
      $quantity = isset($product['amount']) ? (int)$product['amount'] : 0;
      $lineTotal = $unitPrice * $quantity;
      $subtotal += $lineTotal;

      $items[] = [
        'product_id' => $product['productId'],
        'product_name' => $productDetail['product_name'],
        'image_path' => $productDetail['image_path'],
        'quantity' => $quantity,
        'price' => $unitPrice,
        'line_total' => $lineTotal,
      ];
    }

    $discountValue = max(0, $subtotal - (int)$totalPrice);

    return [
      'order_id' => $orderId,
      'created_at' => date('Y-m-d H:i:s'),
      'payment_method' => getCheckoutPaymentMethodLabel($paymentMethod),
      'discount_code' => $discountCode === 'null' ? null : trim($discountCode, "'"),
      'subtotal' => $subtotal,
      'discount_value' => $discountValue,
      'total_price' => (int)$totalPrice,
      'delivery_info' => $deliveryInfo,
      'items' => $items,
    ];
  }

  if (isset($_POST['isCheckout']) && $_POST['isCheckout']) {
    $cartSelected = $_SESSION['cart-selected'];

    $isEnoughAll = true;
    $listProductsNotEnoughQuantity = [];
    foreach ($cartSelected as $product) {
      if (!checkProductEnoughQuantity($product['productId'], $product['amount'])) {
        $isEnoughAll = false;
        $listProductsNotEnoughQuantity[] = $product['productId'];
      }
    }

    // Nếu như không đủ 
    if (!$isEnoughAll) {
      $listDetailProductsNotEnough = [];
      foreach ($listProductsNotEnoughQuantity as $productId) {
        $productDetail = getProductDetailByIdModel($productId);
        if ($productDetail) {
          $productDetail = $productDetail->fetch_assoc();
          $listDetailProductsNotEnough[] = $productDetail;
        }
      }
      
      unset($_SESSION['cart-selected']);
      $response = [
        'successEnoughAll' => false, 
        'products' => $listDetailProductsNotEnough,
        'message' => "Hệ thống không cung cấp đủ số lượng sản phẩm, vui lòng chọn lại!"
      ];
      echo json_encode($response);
    } else {
      // Nếu như đã đủ thì thêm dữ liệu vào db bảng `orders`
      $deliveryInfoId = $_POST['deliveryInfoId'];
      $customerId = $_SESSION['username'];
      $totalPrice = $_POST['totalPrice'];
      $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'cod';
      $discountCode = "'".$_POST['discountCode']."'";
      
      if ($discountCode == "''") {
        $discountCode = 'null';
      }

      if ($orderId = addNewOrder($deliveryInfoId, $totalPrice, $discountCode)) {
        foreach ($cartSelected as $product) {
          $productDetail = getProductDetailByIdModel($product['productId']);
          if ($productDetail) {
            $productDetail = $productDetail->fetch_assoc();
            addNewOrderDetail($orderId, $product['productId'], $product['amount'], $productDetail['price']);
            updateQuantityProductByIdModel($product['productId'], -$product['amount']);
          }
        }

        $orderSummary = buildCheckoutOrderSummary(
          $orderId,
          $deliveryInfoId,
          $cartSelected,
          $discountCode,
          $totalPrice,
          $paymentMethod
        );

        // Xoá những `id` có trong cart vì đã xác nhận thanh toán
        foreach ($_SESSION['cart-selected'] as $item) {
          $productId = $item['productId'];

          $_SESSION['cart'] = array_filter($_SESSION['cart'], function($cartItem) use ($productId) {
            return $cartItem['id'] != $productId;
          });
        }
        unset($_SESSION['cart-selected']);

        $response = [
          'successAddNewOrder' => true,
          'message' => "Đặt hàng thành công!",
          'orderSummary' => $orderSummary
        ];
        echo json_encode($response);
      } else {
        $response = [
          'successAddNewOrder' => false, 
          'message' => "Hệ thống bị lỗi khi thêm đơn hàng, vui lòng thử lại sau!"."INSERT INTO orders (customer_id, staff_id, delivery_info_id, date_create, total_price, status_id, discount_code)
          VALUES ('$customerId', null, $deliveryInfoId, NOW(), $totalPrice, 1, '$discountCode);"
        ];
        echo json_encode($response);
      }
    }
  }
?>