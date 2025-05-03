<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首頁</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
<div class="header">
    <?php
    session_start();
    if (isset($_SESSION['uUser'])) {
        echo "你好 " .$_SESSION['uUser']. " ";
        echo "<a href='logout.php'>登出</a>";
    } else {
        echo "<a href='login.php'>登入</a> ";
        echo "<a href='signup.php'>註冊</a>";
    }
    ?>
</div>

<div class="container">
<div class="sidebar">
    <?php
    // 連接資料庫
    $conn = new mysqli("localhost", "root", "", "trading");
    
    // 獲取所有分類
    $sql = "SELECT * FROM categories";
    $result = $conn->query($sql);
    
    // 獲取當前選擇的分類（如果有）
    $selected_category = isset($_GET['category']) ? $_GET['category'] : null;
    
    // 顯示所有分類
    echo "<a href='index.php'" . ($selected_category === null ? " class='active'" : "") . ">全部商品</a>";
    while($row = $result->fetch_assoc()) {
        $active = $selected_category == $row['id'] ? " class='active'" : "";
        echo "<a href='index.php?category=" . $row['id'] . "'" . $active . ">" . $row['name'] . "</a>";
    }
    ?>
</div>

<div class="products">
    <?php
    // 構建商品查詢
    $products_sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id";
    
    // 如果選擇了分類，添加過濾條件
    if($selected_category) {
        $products_sql .= " WHERE p.category_id = " . intval($selected_category);
    }
    
    $products_result = $conn->query($products_sql);
    
    // 顯示商品
    while($product = $products_result->fetch_assoc()) {
        echo "<div class='product'>";
        echo "<div class='product-image'>";
        if($product['image_url']) {
            echo "<img src='" . htmlspecialchars($product['image_url']) . "' alt='" . htmlspecialchars($product['name']) . "'>";
        }
        echo "</div>";
        echo "<div><span class='product-heart'>♡</span>" . htmlspecialchars($product['name']) . "</div>";
        echo "<div class='product-price'>NT$" . number_format($product['price'], 2) . "</div>";
        echo "<div class='product-category'>" . htmlspecialchars($product['category_name']) . "</div>";
        echo "</div>";
    }
    
    $conn->close();
    ?>
</div>

</body>
</html>