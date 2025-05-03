<?php
// 開始 session
session_start();

// 檢查用戶是否已登入
if(isset($_SESSION['uUser'])) {
    // 儲存用戶名以用於顯示訊息
    $username = $_SESSION['uUser'];
    
    // 清除所有 session 變數
    $_SESSION = array();
    
    // 如果有設置 session cookie，也要清除它
    if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // 銷毀 session
    session_destroy();
    
    // 使用 JavaScript 顯示登出訊息並重定向
    echo "<script>
        alert('再見 " . htmlspecialchars($username) . "！您已成功登出。');
        window.location.href = 'login.php';
    </script>";
} else {
    // 如果用戶未登入就訪問此頁面，直接重定向到登入頁面
    echo "<script>
        window.location.href = 'login.php';
    </script>";
}
exit();
?>