<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trading";

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// 驗證密碼
if($password !== $confirm_password) {
    $_SESSION['error'] = "密碼不匹配！";
    header("Location: signup.php");
    exit();
}

// 檢查用戶名是否已存在
$check_sql = "SELECT * FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if($result->num_rows > 0) {
    $_SESSION['error'] = "用戶名已存在！";
    header("Location: signup.php");
    exit();
}

// 直接使用原始密碼，不再使用 hashed_password
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);  // 直接使用 $password

if($stmt->execute()) {
    $_SESSION['success'] = "註冊成功！請登入。";
    header("Location: login.php");
} else {
    $_SESSION['error'] = "註冊失敗！請稍後再試。";
    header("Location: signup.php");
}

$stmt->close();
$conn->close();
?> 