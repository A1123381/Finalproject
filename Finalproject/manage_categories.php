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

// 處理新增分類
if(isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $sql = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    if($stmt->execute()) {
        echo "<script>alert('分類新增成功！');</script>";
    } else {
        echo "<script>alert('分類新增失敗！');</script>";
    }
}

// 處理刪除分類
if(isset($_POST['delete_category'])) {
    $id = $_POST['category_id'];
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        echo "<script>alert('分類刪除成功！');</script>";
    } else {
        echo "<script>alert('分類刪除失敗！');</script>";
    }
}

// 處理更新分類
if(isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = $_POST['new_category_name'];
    $sql = "UPDATE categories SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    if($stmt->execute()) {
        echo "<script>alert('分類更新成功！');</script>";
    } else {
        echo "<script>alert('分類更新失敗！');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分類管理</title>

    <style>
        html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: Arial, sans-serif;
    box-sizing: border-box;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .category-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .add-category-card {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            padding: 20px;
        }
        .add-category-card:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        .add-icon {
            font-size: 40px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .category-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .category-id {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        .edit-btn, .delete-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            flex: 1;
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
        #addCategoryForm {
            display: none;
            width: 100%;
        }
        #addCategoryForm input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #addCategoryForm button {
            width: 100%;
            padding: 8px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #addCategoryForm button:hover {
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
    color: #940a0a;
    font-size: 50px;
}
.admin-panel{
    width: 100%;
    height: 100%;
    background-color: #f1f1f1;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);

}

    </style>
</head>
<body>
    <div class="header">
        分類管理
        <a href="admin_dashboard.php">返回控制台</a>
    </div>
    <div class="container">
    <div class="admin-panel">
    <div class="category-grid">
            <!-- 新增分類卡片 -->
            <div class="category-card add-category-card" onclick="toggleAddForm()">
                <div id="addButton">
                    <div class="add-icon">+</div>
                    <div class="category-name">新增分類</div>
                </div>
                <form id="addCategoryForm" method="POST" onsubmit="return validateForm()">
                    <input type="text" name="category_name" placeholder="輸入分類名稱" required>
                    <button type="submit" name="add_category">確認新增</button>
                </form>
            </div>

            <!-- 現有分類列表 -->
            <?php
            $sql = "SELECT * FROM categories ORDER BY id DESC";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()) {
                echo "<div class='category-card'>";
                echo "<div>";
                echo "<div class='category-id'>ID: " . htmlspecialchars($row['id']) . "</div>";
                echo "<div class='category-name'>" . htmlspecialchars($row['name']) . "</div>";
                echo "</div>";
                echo "<div class='category-actions'>";
                echo "<button class='edit-btn' onclick='editCategory(" . $row['id'] . ", \"" . htmlspecialchars($row['name']) . "\")'>修改</button>";
                echo "<form method='POST' style='flex: 1;' onsubmit='return confirm(\"確定要刪除此分類嗎？\")'>";
                echo "<input type='hidden' name='category_id' value='" . $row['id'] . "'>";
                echo "<button type='submit' name='delete_category' class='delete-btn'>刪除</button>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    </div>

    <script>
        function toggleAddForm() {
            const form = document.getElementById('addCategoryForm');
            const button = document.getElementById('addButton');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                button.style.display = 'none';
            } else {
                form.style.display = 'none';
                button.style.display = 'block';
            }
        }

        function validateForm() {
            const input = document.querySelector('input[name="category_name"]');
            if (!input.value.trim()) {
                alert('請輸入分類名稱！');
                return false;
            }
            return true;
        }

        function editCategory(id, name) {
            let newName = prompt("請輸入新的分類名稱：", name);
            if (newName !== null && newName.trim() !== "") {
                let form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="category_id" value="${id}">
                    <input type="hidden" name="new_category_name" value="${newName}">
                    <input type="hidden" name="update_category" value="1">
                `;
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