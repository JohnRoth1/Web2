<?php
require "../vendor/Mailer.php";
include_once('connect.php');
$database = new connectDB();

function checkLogin($username, $password)
{
    global $database;
    $sql = "SELECT * FROM accounts WHERE username = '$username'";
    $result = $database->query($sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $role_id = $row['role_id'];
        if ($role_id != '3') {
            $reponse = (object) array(
                "success" => false,
                "message" => "Tài khoản không đủ quyền truy cập!"
            );
            return $reponse;
        }

        $db_password = $row['password'];

        if ($password == $db_password) {
            $database->close();
            if ($row['status'] == 0) {
                $reponse = (object) array(
                    "success" => false,
                    "status" => $row["status"],
                    "message" => "Tài khoản của bạn đã bị khoá!"
                );
                return $reponse;
            } else {
                $reponse = (object) array(
                    "success" => true,
                    "status" => $row["status"],
                    "message" => "Đăng nhập thành công!"
                );
                return $reponse;
            }
        } else {
            $database->close();
            $reponse = (object) array(
                "success" => false,
                "message" => "Tài khoản hoặc mật khẩu không chính xác!"
            );
            return $reponse;
        }
    } else {
        $database->close();
        $reponse = (object) array(
            "success" => false,
            "message" => "Hệ thống không tồn tại tài khoản này!"
        );
        return $reponse;
    }
}

function checkRegister($username, $email)
{
    global $database;
    $sqlCheckExistUsername = "SELECT * FROM accounts WHERE username = '$username'";
    $resultCheckExistUsername = $database->query($sqlCheckExistUsername);
    if (mysqli_num_rows($resultCheckExistUsername) > 0) {
        return (object) array(
            'success' => false,
            'existUsername' => true,
            'message' => "Hệ thống đã tồn tại username: $username"
        );
    }

    $sqlCheckExistEmail = "SELECT * FROM accounts WHERE email = '$email'";
    $resultCheckExistEmail = $database->query($sqlCheckExistEmail);
    if (mysqli_num_rows($resultCheckExistEmail) > 0) {
        return (object) array(
            'success' => false,
            'existEmail' => true,
            'message' => "Hệ thống đã tồn tại email: $email"
        );
    }

    return (object) array(
        'success' => true,
        'message' => "Thông tin hợp lệ, bạn có thể đăng ký"
    );
}

function registerNewAccount($username, $email, $fullname, $phoneNumber, $address, $password, $city, $district, $ward)
{
    global $database;
    $username = mysqli_real_escape_string($database->conn, $username);
    $email = mysqli_real_escape_string($database->conn, $email);
    $fullname = mysqli_real_escape_string($database->conn, $fullname);
    $phoneNumber = mysqli_real_escape_string($database->conn, $phoneNumber);
    $address = mysqli_real_escape_string($database->conn, $address);
    $password = mysqli_real_escape_string($database->conn, $password);
    $city = mysqli_real_escape_string($database->conn, $city);
    $district = mysqli_real_escape_string($database->conn, $district);
    $ward = mysqli_real_escape_string($database->conn, $ward);

    // accounts.email đang FK tới verify_code.email, nên cần đảm bảo email tồn tại ở verify_code.
    $sqlEnsureVerifyCode = "INSERT INTO verify_code (email, code, time_send)
                            SELECT '$email', '000000', NOW()
                            WHERE NOT EXISTS (
                                SELECT 1 FROM verify_code WHERE email = '$email'
                            )";
    $database->execute($sqlEnsureVerifyCode);

    $sqlInsertAccount = "INSERT INTO accounts (username, password, role_id, status, email)
                        VALUES ('$username', '$password', 3, 1, '$email')";
    $sqlInsertUserInfo = "INSERT INTO delivery_infoes (user_id, fullname, phone_number, address, city, district, ward)
                          VALUES ('$username', '$fullname', '$phoneNumber', '$address', '$city', '$district', '$ward')";

    $resultInsertAccount = $database->execute($sqlInsertAccount);
    if (!$resultInsertAccount) {
        return (object) array(
            'success' => false,
            'message' => "Đã xảy ra lỗi khi đăng ký"
        );
    }

    $resultInsertUserInfo = $database->execute($sqlInsertUserInfo);
    if (!$resultInsertUserInfo) {
        // Tránh dữ liệu mồ côi nếu insert delivery_infoes lỗi.
        $database->execute("DELETE FROM accounts WHERE username = '$username'");
    }

    if ($resultInsertAccount && $resultInsertUserInfo) {
        return (object) array(
            'success' => true,
            'message' => "Đăng ký thành công"
        );
    } else {
        return (object) array(
            'success' => false,
            'message' => "Đã xảy ra lỗi khi đăng ký"
        );
    }
}

function checkEmail($email)
{
    global $database;
    $sqlCheckExistEmail = "SELECT * FROM accounts WHERE email = '$email'";
    $resultCheckExistEmail = $database->query($sqlCheckExistEmail);
    if (mysqli_num_rows($resultCheckExistEmail) == 0) {
        return (object) array(
            'success' => false,
            'existEmail' => false,
            'message' => "Email $email chưa được đăng ký"
        );
    }
    return (object) array(
        'success' => true,
        'message' => "Email hợp lệ để đặt lại mật khẩu"
    );
}

function setPassword($email, $password)
{
    global $database;
    $sql = "UPDATE accounts SET password = '$password' WHERE email = '$email'";
    $result = $database->execute($sql);
    if ($result) {
        return (object) array(
            'success' => true,
            'message' => "Đặt lại mật khẩu thành công"
        );
    } else {
        return (object) array(
            'success' => false,
            'message' => "Đặt lại mật khẩu thất bại"
        );
    }
}
