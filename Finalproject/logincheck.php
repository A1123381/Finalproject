<?php
session_start();
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trading";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get username and password from POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare and execute SQL query
$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // 設置session
    $_SESSION['uUser'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_id'] = $user['id'];
    
    // 根據角色重定向
    if($user['role'] === 'admin') {
        echo "<script>
            alert('歡迎管理員 " . htmlspecialchars($user['username']) . " 登入成功！');
            window.location.href = 'admin_dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('歡迎 " . htmlspecialchars($user['username']) . " 登入成功！');
            window.location.href = 'user_dashboard.php';
        </script>";
    }
} else {
    // 登入失敗
    echo "<script>
        alert('用戶名或密碼錯誤！請重新輸入。');
        window.location.href = 'login.php';
    </script>";
}

// Close connection
$stmt->close();
$conn->close();
?>