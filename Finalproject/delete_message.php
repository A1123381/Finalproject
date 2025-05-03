<?php
session_start();
if(!isset($_SESSION['uUser'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $user_id = $_SESSION['user_id'];
    
    $conn = new mysqli("localhost", "root", "", "trading");
    
    // 檢查該請求是否屬於當前用戶且狀態為已接受或已拒絕
    $check_sql = "SELECT * FROM trade_requests WHERE id = ? AND (sender_id = ? OR receiver_id = ?) AND (status = 'accepted' OR status = 'rejected')";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $request_id, $user_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if($result->num_rows > 0) {
        // 刪除該請求
        $delete_sql = "DELETE FROM trade_requests WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $request_id);
        $delete_stmt->execute();
    }
    
    $check_stmt->close();
    $delete_stmt->close();
    $conn->close();
}

header("Location: my_messages.php");
exit();
?> 