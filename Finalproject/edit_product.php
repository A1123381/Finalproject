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

// 獲取物品資料
if(isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // 確保只能編輯自己的物品
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if(!$product) {
        echo "<script>
            alert('找不到該物品或您沒有權限編輯！');
            window.location.href = 'my_products.php';
        </script>";
        exit();
    }
}

// 處理更新請求
if(isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $condition = $_POST['condition'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    
    // 處理圖片上傳
    $image_url = $product['image_url']; // 保持原有圖片
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if(in_array($file_extension, $allowed_types)) {
            if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
                // 刪除舊圖片
                if($product['image_url'] && file_exists($product['image_url'])) {
                    unlink($product['image_url']);
                }
            }
        }
    }
    
    // 更新資料
    $sql = "UPDATE products 
            SET name = ?, `condition` = ?, category_id = ?, image_url = ?, description = ? 
            WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissii", $name, $condition, $category_id, $image_url, $description, $product_id, $user_id);
    
    if($stmt->execute()) {
        echo "<script>
            alert('物品更新成功！');
            window.location.href = 'my_products.php';
        </script>";
    } else {
        echo "<script>alert('更新失敗！');</script>";
    }
}

// 獲取所有分類
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯物品</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .upload-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        .condition-options {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }
        .condition-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .condition-option input[type="radio"] {
            margin: 0;
        }
        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        
    </style>
</head>
<body>
    <div class="header">
        編輯物品
        <a href="my_products.php">返回我的物品</a>
    </div>

    <div class="upload-container">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label for="product_name">物品名稱</label>
                <input type="text" id="product_name" name="product_name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>物品狀況</label>
                <div class="condition-options">
                    <?php
                    $conditions = array('全新', '幾乎全新', '二手', '使用痕跡');
                    foreach($conditions as $cond) {
                        $checked = ($product['condition'] === $cond) ? 'checked' : '';
                        echo "<div class='condition-option'>";
                        echo "<input type='radio' id='condition_" . $cond . "' name='condition' 
                              value='" . $cond . "' " . $checked . " required>";
                        echo "<label for='condition_" . $cond . "'>" . $cond . "</label>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">分類</label>
                <select id="category_id" name="category_id" required>
                    <?php
                    while($category = $categories_result->fetch_assoc()) {
                        $selected = ($category['id'] == $product['category_id']) ? 'selected' : '';
                        echo "<option value='" . $category['id'] . "' " . $selected . ">" . 
                             htmlspecialchars($category['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_image">物品圖片</label>
                <input type="file" id="product_image" name="product_image" accept="image/*" 
                       onchange="previewImage(this)">
                <div class="image-preview" id="imagePreview">
                    <?php
                    if($product['image_url']) {
                        echo "<img src='" . htmlspecialchars($product['image_url']) . "' 
                              alt='當前圖片'>";
                    } else {
                        echo "<span>預覽圖片</span>";
                    }
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for="description">物品描述</label>
                <textarea id="description" name="description" required><?php 
                    echo htmlspecialchars($product['description']); 
                ?></textarea>
            </div>

            <button type="submit" name="update_product" class="btn-submit">更新物品</button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<span>預覽圖片</span>';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 