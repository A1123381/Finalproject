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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶管理</title>
    <link rel='stylesheet' href='style.css'>
    <link rel='stylesheet' href='list.css'>
    <style>
        .user-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .edit-btn {
            background-color: #007bff;
            color: white;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        /* 不能刪除自己的帳號時的按鈕樣式 */
        .delete-btn.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
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
        color: #940a0a;
        font-size: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        用戶管理
        <a href="admin_dashboard.php" >返回控制台</a>
    </div>
    
    <div class="container">
        <ul class="user-list">
            <?php
            // 獲取所有用戶資料
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $result = $conn->query($sql);
            
            while($row = $result->fetch_assoc()) {
                echo "<li class='user-card'>";
                echo "<div class='user-id'>ID: " . htmlspecialchars($row['id']) . "</div>";
                echo "<div class='user-name'>" . htmlspecialchars($row['username']) . "</div>";
                
                // 根據角色設置不同的樣式
                $roleClass = $row['role'] === 'admin' ? 'role-admin' : 'role-user';
                echo "<div class='user-role " . $roleClass . "'>" . 
                     ($row['role'] === 'admin' ? '管理員' : '一般用戶') . 
                     "</div>";
                
                echo "<div class='user-date'>創建時間：" . htmlspecialchars($row['created_at']) . "</div>";
                
                // 添加修改和刪除按鈕
                echo "<div class='user-actions'>";
                // 修改按鈕
                echo "<button class='edit-btn' onclick='editUser(" . $row['id'] . ")'>修改</button>";
                
                // 刪除按鈕（不能刪除自己的帳號）
                if($row['id'] != $_SESSION['user_id']) {
                    echo "<button class='delete-btn' onclick='deleteUser(" . $row['id'] . ")'>刪除</button>";
                } else {
                    echo "<button class='delete-btn disabled' disabled>無法刪除</button>";
                }
                echo "</div>";
                
                echo "</li>";
            }
            ?>
        </ul>
    </div>

    <script>
        function editUser(userId) {
            if(confirm('確定要修改此用戶嗎？')) {
                window.location.href = 'edit_user.php?id=' + userId;
            }
        }

        function deleteUser(userId) {
            if(confirm('確定要刪除此用戶嗎？此操作無法撤銷！')) {
                // 使用 POST 方法發送刪除請求
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_user.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_id';
                input.value = userId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>