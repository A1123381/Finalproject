<?php
session_start();
// 檢查是否登入且是管理員
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['trade_id'])) {
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

    $trade_id = $conn->real_escape_string($_POST['trade_id']);

    // 刪除交易
    $sql = "DELETE FROM trade WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trade_id);

    if ($stmt->execute()) {
        // 刪除成功，重定向回管理頁面
        header("Location: manage_trades.php?success=1");
    } else {
        // 刪除失敗，重定向回管理頁面並顯示錯誤
        header("Location: manage_trades.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    // 如果不是POST請求或沒有提供trade_id，重定向回管理頁面
    header("Location: manage_trades.php");
}
exit();
?> 