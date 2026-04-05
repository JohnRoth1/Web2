<?php
  if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập!')</script>";
    echo "<script>window.location.href = 'index.php?page=signup'</script>";
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đơn hàng của bạn</title>
    <link
      rel="stylesheet"
      href="assets/fontawesome-free-6.5.1-web/css/all.min.css"
    />
    <link rel="stylesheet" href="css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/order/order.css?v=<?php echo time(); ?>" />
    <script defer src="js/order.js?v=<?php echo time(); ?>"></script>
  </head>
  <body>
    <?php
        if (isset($_SESSION['username']) && $_SESSION['username']) {
          include_once('controller/order.controller.php');
          include_once('controller/product_detail.controller.php');
          include_once('controller/order_detail.controller.php');
          
          $username = $_SESSION['username'];
          $orders = getAllOrdersByUsername($username);
          
          // Nếu có order thì render, không thì báo không
          if (count($orders) > 0) {
            echo '<div class="order-wrapper">';
            echo '<h2>Đơn hàng của bạn</h2>';
            foreach ($orders as $order) {
              $isHideCancelOrder = '';
              if ($order['order_status_id'] == 5 || $order['order_status_id'] == 3 || $order['order_status_id'] == 4) {
                $isHideCancelOrder = 'hide';
              }

              $totalPriceOfOrder = 0;
              $totalQuantityOfOrder = 0;
              $timestamp = strtotime($order['date_create']);
              $day = date('d', $timestamp);
              $month = date('m', $timestamp);
              $year = date('Y', $timestamp);
              
              $orderDetails = getAllOrderDetailByOrderId($order['id']);
              $countOrderDetail = 0;
              $productPreviewParts = [];
              foreach ($orderDetails as $orderDetail) {
                $countOrderDetail++;
                $totalPrice = $orderDetail['quantity'] * $orderDetail['price'];
                $totalPriceOfOrder += $totalPrice;
                $totalQuantityOfOrder += $orderDetail['quantity'];

                if ($countOrderDetail <= 2) {
                  $productPreviewParts[] = $orderDetail['product_name'] . ' x' . $orderDetail['quantity'];
                }
              }
  
              $formatTotalPriceOfOrder = number_format($totalPriceOfOrder, 0, ',', '.');
              $formatTotalPriceAfterDiscount = number_format($order['total_price'], 0, ',', '.');
              $productSummary = implode(', ', $productPreviewParts);
              if ($countOrderDetail > 2) {
                $productSummary .= ' và ' . ($countOrderDetail - 2) . ' sản phẩm khác';
              }
              if ($order['discount_code'] == null) {
                echo '
                  <div class="order">
                    <input type="hidden" class="orderId" value="'.$order['id'].'"/>
                    <div class="order-summary-card">
                      <div class="order-summary-top">
                        <div class="order-summary-code">Mã đơn hàng <strong>#'.$order['id'].'</strong></div>
                        <div class="order-summary-status">'.$order['order_status'].'</div>
                      </div>
                      <div class="order-summary-main">
                        <div class="order-summary-products">'.$productSummary.'</div>
                        <div class="order-summary-meta">Ngày đặt: '.$day.' tháng '.$month.', '.$year.' • '.$countOrderDetail.' loại sản phẩm • '.$totalQuantityOfOrder.' cuốn</div>
                        <div class="order-summary-total">Tổng tiền: <strong>'.$formatTotalPriceOfOrder.' đ</strong></div>
                      </div>
                      <div class="order-bottom-actions">
                        <button class="btnXemChiTiet">Xem chi tiết</button>
                        <button class="btnHuyDon '.$isHideCancelOrder.'">Huỷ đơn</button>
                      </div>
                    </div>
                  </div>
                ';
              } else {
                echo '
                  <div class="order">
                    <input type="hidden" class="orderId" value="'.$order['id'].'"/>
                    <div class="order-summary-card">
                      <div class="order-summary-top">
                        <div class="order-summary-code">Mã đơn hàng <strong>#'.$order['id'].'</strong></div>
                        <div class="order-summary-status">'.$order['order_status'].'</div>
                      </div>
                      <div class="order-summary-main">
                        <div class="order-summary-products">'.$productSummary.'</div>
                        <div class="order-summary-meta">Ngày đặt: '.$day.' tháng '.$month.', '.$year.' • '.$countOrderDetail.' loại sản phẩm • '.$totalQuantityOfOrder.' cuốn</div>
                        <div class="order-summary-total">Tổng tiền: <strong><span class="beforePrice">'.$formatTotalPriceOfOrder.' đ</span> '.$formatTotalPriceAfterDiscount.' đ</strong></div>
                      </div>
                      <div class="order-bottom-actions">
                        <button class="btnXemChiTiet">Xem chi tiết</button>
                        <button class="btnHuyDon '.$isHideCancelOrder.'">Huỷ đơn</button>
                      </div>
                    </div>
                  </div>
                ';
              }
              
            }
            echo '</div>';
          } else {
            echo '
            <div class="order-empty-wrapper">
              <div class="order-empty">
                <div style="text-align: center">
                  <div class="icon">
                    <!-- <img src="assets/image/order/ico_emptyorder.svg" alt="" /> -->
                  </div>
                  <p>Chưa có đơn hàng, hãy tạo lên đơn ngay hôm nay!</p>
                  <button class="button-ordering">
                    <a href="index.php?page=cart">Tạo đơn hàng</a>
                  </button>
                </div>
              </div>
            </div>';
          }
        } else {
          header('location: index.php');
        }
      ?>
    <div class="overlayDetailOrder hide"></div>

    <div class="modal hide">
      
    </div>
  </body>
</html>
