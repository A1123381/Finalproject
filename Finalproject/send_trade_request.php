<?php
session_start();
if(!isset($_SESSION['uUser'])) {
    header("Location: login.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trading";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$target_product_id = $_POST['target_product_id'];
$my_product_id = $_POST['my_product_id'];
$sender_id = $_SESSION['user_id'];

// 檢查目標商品是否已有待處理或已接受的交易請求
$check_sql = "SELECT COUNT(*) as count FROM trade_requests 
              WHERE (product_id = ? OR my_product_id = ?) 
              AND (status = 'pending' OR status = 'accepted')";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $target_product_id, $target_product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_data['count'] > 0) {
    echo "<script>alert('該商品已有交易請求或已接受其他交易！');window.location='index.php';</script>";
    exit();
}

// 檢查自己的商品是否已有待處理或已接受的交易請求
$check_my_sql = "SELECT COUNT(*) as count FROM trade_requests 
                 WHERE (product_id = ? OR my_product_id = ?) 
                 AND (status = 'pending' OR status = 'accepted')";
$check_my_stmt = $conn->prepare($check_my_sql);
$check_my_stmt->bind_param("ii", $my_product_id, $my_product_id);
$check_my_stmt->execute();
$check_my_result = $check_my_stmt->get_result();
$check_my_data = $check_my_result->fetch_assoc();
$check_my_stmt->close();

if ($check_my_data['count'] > 0) {
    echo "<script>alert('您選擇的交換商品已有交易請求或已接受其他交易！');window.location='index.php';</script>";
    exit();
}

// 找到對方 id
$sql = "SELECT seller_id FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $target_product_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$receiver_id = $row['seller_id'];

// 寫入 trade_requests
$sql = "INSERT INTO trade_requests (product_id, sender_id, receiver_id, my_product_id, message) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$message = "我想用我的商品(ID: $my_product_id)與你交換";
$stmt->bind_param("iiiis", $target_product_id, $sender_id, $receiver_id, $my_product_id, $message);
if($stmt->execute()) {
    echo "<script>alert('交換申請已送出！');window.location='index.php';</script>";
} else {
    echo "<script>alert('申請失敗，請稍後再試！');window.location='index.php';</script>";
}
?> 