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

// 處理刪除物品請求
if(isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    // 確保只能刪除自己的物品
    $sql = "DELETE FROM products WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    
    if($stmt->execute()) {
        echo "<script>alert('物品已成功刪除！');</script>";
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
    <title>我的物品</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .products-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .product-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .product-condition {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-description {
            color: #555;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-actions {
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            flex: 1;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-edit {
            background-color: #007bff;
            color: white;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .empty-message {
            text-align: center;
            color: #666;
            margin: 40px 0;
            font-size: 18px;
        }
        .upload-btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        .upload-btn:hover {
            background-color: #218838;
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
        我的物品
        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">返回控制台</a>
    </div>

    <div class="products-container">
        <a href="upload_product.php" class="upload-btn">上傳新物品</a>

        <div class="product-grid">
            <?php
            // 獲取用戶的所有物品
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.seller_id = ? 
                    ORDER BY p.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                while($product = $result->fetch_assoc()) {
                    echo "<div class='product-card'>";
                    echo "<div class='product-image'>";
                    if($product['image_url']) {
                        echo "<img src='" . htmlspecialchars($product['image_url']) . "' 
                              alt='" . htmlspecialchars($product['name']) . "'>";
                    }
                    echo "</div>";
                    echo "<div class='product-info'>";
                    echo "<div class='product-name'>" . htmlspecialchars($product['name']) . "</div>";
                    echo "<div class='product-condition'>" . htmlspecialchars($product['condition']) . "</div>";
                    echo "<div class='product-category'>" . htmlspecialchars($product['category_name']) . "</div>";
                    echo "<div class='product-description'>" . htmlspecialchars($product['description']) . "</div>";
                    echo "</div>";
                    echo "<div class='product-actions'>";
                    echo "<a href='edit_product.php?id=" . $product['id'] . "' class='btn btn-edit'>修改</a>";
                    echo "<form method='POST' style='flex: 1;' onsubmit='return confirmDelete()'>";
                    echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
                    echo "<button type='submit' name='delete_product' class='btn btn-delete'>刪除</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='empty-message'>你還沒有上傳任何物品。<br>
                      <a href='upload_product.php'>立即上傳</a></div>";
            }
            ?>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('確定要刪除這個物品嗎？此操作無法復原！');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 