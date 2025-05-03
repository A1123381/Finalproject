<?php
session_start();
// 檢查是否登入且是一般用戶
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'user') {
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
    <title>用戶中心</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .user-panel {
            padding: 20px;
            text-align: center;
            width: 100 vw;
    height: 75%;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background-color: white;    
    justify-content: center; /* Centers items vertically */
    align-items: center; /* Centers items horizontally */
    margin: 0 auto; /* Centers the div itself */

    border-radius: 12px;
        }
        .user-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .menu-item {
            width: 200px;
            height: 200px;
            background-color: #007bff; /* 藍色主題 */
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s, background-color 0.3s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            background-color: #0056b3; /* 深藍色 */
        }
        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .menu-text {
            font-size: 20px;
        }
        .welcome-text {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span>歡迎 <?php echo htmlspecialchars($_SESSION['uUser']); ?></span>
        <a href="logout.php">登出</a>
    </div>
    
    <div class="container">
        <div class="user-panel">
            <h1>用戶中心</h1>
            <p class="welcome-text">歡迎回來，<?php echo htmlspecialchars($_SESSION['uUser']); ?>！</p>
            
            <div class="user-menu">
                <a href="my_profile.php" class="menu-item">
                    <div class="menu-icon">👤</div>
                    <div class="menu-text">個人資料</div>
                </a>
                
                <a href="my_orders.php" class="menu-item">
                    <div class="menu-icon">🛍️</div>
                    <div class="menu-text">我的訂單</div>
                </a>
                
                <a href="my_favorites.php" class="menu-item">
                    <div class="menu-icon">❤️</div>
                    <div class="menu-text">我的收藏</div>
                </a>
                
                <a href="shopping_cart.php" class="menu-item">
                    <div class="menu-icon">🛒</div>
                    <div class="menu-text">購物車</div>
                </a>
                
                <a href="my_messages.php" class="menu-item">
                    <div class="menu-icon">✉️</div>
                    <div class="menu-text">我的訊息</div>
                </a>
                
                <a href="my_reviews.php" class="menu-item">
                    <div class="menu-icon">⭐</div>
                    <div class="menu-text">我的評價</div>
                </a>
                
                <a href="account_settings.php" class="menu-item">
                    <div class="menu-icon">⚙️</div>
                    <div class="menu-text">帳號設定</div>
                </a>
                
                <a href="index.php" class="menu-item">
                    <div class="menu-icon">🏠</div>
                    <div class="menu-text">回到首頁</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 