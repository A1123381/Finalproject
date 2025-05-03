<?php
session_start();
// 檢查是否登入
if(!isset($_SESSION['uUser'])) {
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

// 獲取用戶資料
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 處理更新請求
if(isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    
    // 檢查用戶名是否已存在
    if($new_username !== $user['username']) {
        $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_username, $user_id);
        $check_stmt->execute();
        if($check_stmt->get_result()->num_rows > 0) {
            echo "<script>alert('此用戶名已被使用！');</script>";
        } else {
            // 更新資料
            if(!empty($new_password)) {
                $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($sql);
                $update_stmt->bind_param("ssi", $new_username, $new_password, $user_id);
            } else {
                $sql = "UPDATE users SET username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($sql);
                $update_stmt->bind_param("si", $new_username, $user_id);
            }
            
            if($update_stmt->execute()) {
                $_SESSION['uUser'] = $new_username;
                header("Location: my_profile.php?update=success");
                exit();
            } else {
                echo "<script>alert('更新失敗！');</script>";
            }
        }
    }
}

// 處理刪除帳號請求
if(isset($_POST['delete_account'])) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if($stmt->execute()) {
        session_destroy();
        header("Location: login.php?deleted=true");
        exit();
    } else {
        echo "<script>alert('刪除失敗！');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人資料</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: #007bff;
            border-radius: 60px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        .profile-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            flex: 2;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            flex: 1;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .user-role {
            display: inline-block;
            padding: 5px 15px;
            background-color: #28a745;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        .created-at {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }
        .header {
        background-color: #eee;
        padding: 10px 20px;
        display: flex;
        justify-content: flex-start;
        gap: 20px;
        border-bottom: 1px solid #ccc;
        font-size: 50px;
        }

.header a {
    text-decoration: none;
    color: #007bff;
    font-size: 50px;
    
}
    </style>
</head>
<body>
    <div class="header">
        個人資料
        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">返回控制台</a>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <div class="user-role"><?php echo $user['role'] === 'admin' ? '管理員' : '一般用戶'; ?></div>
                <div class="created-at">註冊時間：<?php echo $user['created_at']; ?></div>
            </div>
        </div>

        <form class="profile-form" method="POST" onsubmit="return confirmUpdate()">
            <div class="form-group">
                <label for="username">用戶名稱</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">新密碼（若不修改請留空）</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="confirm_password">確認新密碼</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="btn-group">
                <button type="submit" name="update_profile" class="btn btn-primary">更新資料</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">刪除帳號</button>
            </div>
        </form>
    </div>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_account" value="1">
    </form>

    <script>
        // 檢查是否有更新成功的訊息
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('update') === 'success') {
                alert('資料更新成功！');
                // 移除 URL 參數
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        function confirmUpdate() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if(password !== confirmPassword) {
                alert('兩次輸入的密碼不一致！');
                return false;
            }
            
            return confirm('確定要更新個人資料嗎？');
        }

        function confirmDelete() {
            if(confirm('確定要刪除帳號嗎？此操作無法復原！')) {
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 