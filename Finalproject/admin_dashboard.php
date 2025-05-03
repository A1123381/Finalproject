<?php
session_start();
// 檢查是否登入且是管理員
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員控制台</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .admin-panel {
            padding: 20px;
            text-align: center;
        }
        .admin-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .menu-item {
            width: 200px;
            height: 200px;
            background-color: #ff6b00; /* 橙色主題 */
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
            background-color: #e65100; /* 深橙色 */
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
        .user-name{
    color: #e65100;
}
    </style>
</head>
<body>
    <div class="header">
        <span>歡迎管理員 <?php echo htmlspecialchars($_SESSION['uUser']); ?></span>
        <a href="logout.php">登出</a>
    </div>
    
    <div class="container">
        <div class="admin-panel">
            <h1>管理員控制台</h1>
            <p class="welcome-text">歡迎回來，<span class='user-name'>管理員 <?php echo htmlspecialchars($_SESSION['uUser']); ?></span>！</p>
            
            <div class="admin-menu">
                <a href="manage_users.php" class="menu-item">
                    <div class="menu-icon">👥</div>
                    <div class="menu-text">用戶管理</div>
                </a>
                
                <a href="manage_products.php" class="menu-item">
                    <div class="menu-icon">📦</div>
                    <div class="menu-text">商品管理</div>
                </a>
                
                <a href="manage_categories.php" class="menu-item">
                    <div class="menu-icon">📑</div>
                    <div class="menu-text">分類管理</div>
                </a>
                
                <a href="manage_trades.php" class="menu-item">
                    <div class="menu-icon">🛍️</div>
                    <div class="menu-text">交易管理</div>
                </a>
                
                <a href="manage_messages.php" class="menu-item">
                    <div class="menu-icon">✉️</div>
                    <div class="menu-text">訊息管理</div>
                </a>
                
                <a href="system_settings.php" class="menu-item">
                    <div class="menu-icon">⚙️</div>
                    <div class="menu-text">系統設定</div>
                </a>
                
                <a href="admin_trades.php" class="menu-item">
                    <div class="menu-icon">⚖️</div>
                    <div class="menu-text">交易審核</div>
                </a>
                
                <a href="manage_promotions.php" class="menu-item">
                    <div class="menu-icon">🏷️</div>
                    <div class="menu-text">促銷管理</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html> 