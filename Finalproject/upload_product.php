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

// 獲取所有分類
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);

// 處理商品上傳
if(isset($_POST['upload_product'])) {
    $product_name = $_POST['product_name'];
    $condition = $_POST['condition'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $seller_id = $_SESSION['user_id'];
    
    // 處理圖片上傳
    $image_url = '';
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // 檢查檔案類型
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if(in_array($file_extension, $allowed_types)) {
            if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                echo "<script>alert('圖片上傳失敗！');</script>";
            }
        } else {
            echo "<script>alert('只允許上傳 JPG, JPEG, PNG 或 GIF 檔案！');</script>";
        }
    }
    
    // 插入商品資料
    $sql = "INSERT INTO products (name, `condition`, category_id, image_url, description, seller_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissi", $product_name, $condition, $category_id, $image_url, $description, $seller_id);
    
    if($stmt->execute()) {
        echo "<script>
            alert('物品上傳成功！');
            window.location.href = 'my_products.php';
        </script>";
    } else {
        echo "<script>alert('物品上傳失敗！');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上傳物品</title>
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
        .header a{
            text-decoration: none;
            color: #007bff;
            font-size: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        上傳物品
        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">返回控制台</a>
    </div>

    <div class="upload-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">物品名稱</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>

            <div class="form-group">
                <label>物品狀況</label>
                <div class="condition-options">
                    <div class="condition-option">
                        <input type="radio" id="condition_new" name="condition" value="全新" required>
                        <label for="condition_new">全新</label>
                    </div>
                    <div class="condition-option">
                        <input type="radio" id="condition_like_new" name="condition" value="幾乎全新">
                        <label for="condition_like_new">幾乎全新</label>
                    </div>
                    <div class="condition-option">
                        <input type="radio" id="condition_used" name="condition" value="二手">
                        <label for="condition_used">二手</label>
                    </div>
                    <div class="condition-option">
                        <input type="radio" id="condition_worn" name="condition" value="使用痕跡">
                        <label for="condition_worn">使用痕跡</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">分類</label>
                <select id="category_id" name="category_id" required>
                    <option value="">請選擇分類</option>
                    <?php
                    while($category = $categories_result->fetch_assoc()) {
                        echo "<option value='" . $category['id'] . "'>" . 
                             htmlspecialchars($category['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_image">物品圖片</label>
                <input type="file" id="product_image" name="product_image" accept="image/*" onchange="previewImage(this)" required>
                <div class="image-preview" id="imagePreview">
                    <span>預覽圖片</span>
                </div>
            </div>

            <div class="form-group">
                <label for="description">物品描述</label>
                <textarea id="description" name="description" required 
                          placeholder="請描述物品的詳細資訊，例如：使用時間、是否有配件、有無損壞等"></textarea>
            </div>

            <button type="submit" name="upload_product" class="btn-submit">上傳物品</button>
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