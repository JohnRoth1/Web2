<?php
  include_once('connect.php');

  function getAllUserInfoByUserIdModel($userId) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT * FROM delivery_infoes WHERE user_id = '$userId'";

      $result = $database->query($sql);
      $database->close();
      return $result;
    } else {
      $database->close();
      return false;
    }
  }

  function updateUserInfoByIdModel($id, $fullname, $phone_number, $address, $city, $district, $ward) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "UPDATE delivery_infoes
              SET 
                  fullname = '$fullname',
                  phone_number = '$phone_number',
                  address = '$address',
                  city = '$city',
                  district = '$district',
                  ward = '$ward'
              WHERE 
                  user_info_id = $id;
              ";

      $result = $database->execute($sql);
      $database->close();
      return (object) array (
        "success" => $result,
        "id" => $id,
        "fullname" => $fullname,
        "phone_number" => $phone_number,
        "address" => $address,
        "city" => $city,
        "district" => $district,
        "ward" => $ward        
      );
    } else {
      $database->close();
      return false;
    }
  }
  function createUserInfoByIdModel($username, $fullname, $phone_number, $address, $city, $district, $ward) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT user_info_id FROM `delivery_infoes` ORDER BY user_info_id DESC LIMIT 1";
      $result = $database->query($sql); 
      $id=mysqli_fetch_array($result);
      $id=$id['user_info_id']+1;
      $sql = "INSERT INTO delivery_infoes (user_info_id,user_id,fullname,phone_number,address,city,district,ward)  
              VALUE('$id','$username','$fullname','$phone_number','$address','$city','$district','$ward')";

      $result = $database->execute($sql);
      $database->close();
      return (object) array (
        "success" => $result,
        "id" => $id,
        "fullname" => $fullname,
        "phone_number" => $phone_number,
        "address" => $address,
        "city" => $city,
        "district" => $district,
        "ward" => $ward,         
      );
    } else {
      $database->close();
      return false;
    }
  }

  function getDetailDeliveryInfoById($deliveryInfoId) {
    $database = new connectDB();
    if ($database->conn) {
      $sql = "SELECT * FROM delivery_infoes WHERE user_info_id = $deliveryInfoId";
      $result = $database->query($sql);
      if ($result) {
        $database->close();
        return $result;
      } 
    } else {
      $database->close();
      return false;
    }
  }

  function deleteUserInfoByIdModel($id, $username) {
    $database = new connectDB();
    if ($database->conn) {
      $id = intval($id);
      $username = mysqli_real_escape_string($database->conn, $username);

      $sqlCheckOwner = "SELECT * FROM delivery_infoes WHERE user_info_id = $id AND user_id = '$username'";
      $resultCheckOwner = $database->query($sqlCheckOwner);
      if (!$resultCheckOwner || mysqli_num_rows($resultCheckOwner) === 0) {
        $database->close();
        return (object) array(
          "success" => false,
          "message" => "Địa chỉ không tồn tại hoặc không thuộc tài khoản của bạn"
        );
      }

      $sqlCheckUsedOrder = "SELECT COUNT(*) AS total FROM orders WHERE delivery_info_id = $id";
      $resultCheckUsedOrder = $database->query($sqlCheckUsedOrder);
      $rowCheckUsedOrder = $resultCheckUsedOrder ? mysqli_fetch_assoc($resultCheckUsedOrder) : null;
      if ($rowCheckUsedOrder && intval($rowCheckUsedOrder['total']) > 0) {
        $database->close();
        return (object) array(
          "success" => false,
          "message" => "Địa chỉ này đã được dùng trong đơn hàng nên không thể xóa"
        );
      }

      $sqlDelete = "DELETE FROM delivery_infoes WHERE user_info_id = $id AND user_id = '$username'";
      $resultDelete = $database->execute($sqlDelete);
      $database->close();

      return (object) array(
        "success" => (bool)$resultDelete,
        "message" => $resultDelete ? "Xóa địa chỉ thành công" : "Xóa địa chỉ thất bại"
      );
    } else {
      $database->close();
      return (object) array(
        "success" => false,
        "message" => "Không thể kết nối cơ sở dữ liệu"
      );
    }
  }
?>