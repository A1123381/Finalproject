<?php
session_start();
// 檢查是否登入且是管理員
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

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

// 獲取要編輯的用戶資料
if(isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if(!$user) {
        echo "<script>
            alert('找不到該用戶！');
            window.location.href = 'manage_users.php';
        </script>";
        exit();
    }
}

// 處理表單提交
if(isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['username'];
    $new_role = $_POST['role'];
    $new_password = $_POST['password'];
    
    // 如果輸入了新密碼則更新密碼
    if(!empty($new_password)) {
        $sql = "UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $new_username, $new_role, $new_password, $user_id);
    } else {
        $sql = "UPDATE users SET username = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_username, $new_role, $user_id);
    }
    
    if($stmt->execute()) {
        echo "<script>
            alert('用戶資料更新成功！');
            window.location.href = 'manage_users.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('更新失敗！請稍後再試。');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯用戶</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php">返回控制台</a>
        <a href="manage_users.php">返回用戶列表</a>
    </div>

    <div class="container">
        <form method="POST">
            <div class="box">
                <h1>編輯用戶</h1>
                <div class="boxcontent">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                    
                    用戶名
                    <input type="text" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           style="width: 300px; height: 30px;" required><br>
                    
                    新密碼
                    <input type="password" name="password" 
                           placeholder="留空表示不修改" 
                           style="width: 300px; height: 30px;"><br>
                    
                    角色
                    <select name="role" style="width: 300px; height: 30px;">
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>
                            一般用戶
                        </option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                            管理員
                        </option>
                    </select><br>
                    
                    <button type="submit" name="update_user" 
                            style="width: 100px; height: 50px; font-size: 40px;">
                        更新
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?> 