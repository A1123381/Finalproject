<?php
session_start();
// 檢查是否登入且是管理員
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if(isset($_POST['user_id'])) {
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

    $user_id = $_POST['user_id'];
    
    // 確保不能刪除自己的帳號
    if($user_id != $_SESSION['user_id']) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if($stmt->execute()) {
            echo "<script>
                alert('用戶已成功刪除！');
                window.location.href = 'manage_users.php';
            </script>";
        } else {
            echo "<script>
                alert('刪除失敗！請稍後再試。');
                window.location.href = 'manage_users.php';
            </script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>
            alert('無法刪除自己的帳號！');
            window.location.href = 'manage_users.php';
        </script>";
    }
    
    $conn->close();
} else {
    header("Location: manage_users.php");
}
?> 