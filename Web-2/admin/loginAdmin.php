<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.1-web/css/all.min.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/adminLogin.css?v=<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script defer src="../js/admin/adminLogin.js?v=<?php echo time(); ?>"></script>
</head>

<body>
    <div class="backGround">
        <div class="loginShell">
            <aside class="loginIntro">
                <p class="introTag">Web2 Management</p>
                <h1>Trang quản trị dành cho vận hành</h1>
                <p class="introText">
                    Theo dõi đơn hàng, quản lý kho và cập nhật sản phẩm trong một nơi duy nhất.
                </p>
                <ul class="introList">
                    <li><i class="fa-solid fa-chart-line"></i> Theo dõi dữ liệu theo thời gian thực</li>
                    <li><i class="fa-solid fa-box-open"></i> Kiểm soát hàng tồn và nhập kho</li>
                    <li><i class="fa-solid fa-clipboard-check"></i> Xử lý đơn nhanh, giảm sai sót</li>
                </ul>
            </aside>

            <div class="container">
                <div class="adminLoginTitle">
                    Đăng nhập Admin
                </div>
                <p class="loginDescription">Vui lòng nhập thông tin để truy cập khu vực quản trị.</p>

                <div id="loginForm">
                    <div class="form-row">
                        <label for="username">Tên đăng nhập</label>
                        <input id="username" type="text" class="username" placeholder="Nhập username">
                    </div>
                    <div class="form-row">
                        <label for="psw">Mật khẩu</label>
                        <div class="passwordContainer">
                            <input id="psw" type="password" placeholder="Nhập mật khẩu" class="password" required>
                            <button class="loginPasswordView" aria-label="Hiện hoặc ẩn mật khẩu">
                                <i class="fa-solid fa-eye-slash noView-loginPassword"></i>
                                <i class="fa-solid fa-eye view-loginPassword hide"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btnSubmit btnDangNhap">Đăng nhập</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
