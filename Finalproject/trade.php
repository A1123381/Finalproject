<?php
session_start();
if(!isset($_SESSION['uUser'])) {
    header("Location: login.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trading";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// 檢查目標商品是否已有交易請求
$check_target_sql = "SELECT COUNT(*) as count FROM trade_requests 
                    WHERE (product_id = ? OR my_product_id = ?) 
                    AND (status = 'pending' OR status = 'accepted')";
$check_target_stmt = $conn->prepare($check_target_sql);
$check_target_stmt->bind_param("ii", $product_id, $product_id);
$check_target_stmt->execute();
$check_target_result = $check_target_stmt->get_result();
$check_target_data = $check_target_result->fetch_assoc();
$check_target_stmt->close();

if ($check_target_data['count'] > 0) {
    echo "<script>alert('該商品已有交易請求或已接受其他交易！');window.location='index.php';</script>";
    exit();
}

// 取得對方商品資訊
$sql = "SELECT p.*, u.username as seller_name FROM products p LEFT JOIN users u ON p.seller_id = u.id WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if(!$product) {
    echo "<script>alert('找不到該商品');window.location='index.php';</script>";
    exit();
}

// 取得自己的商品
$user_id = $_SESSION['user_id'];
$my_products = [];
$sql = "SELECT * FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    // 檢查每個商品是否已有交易請求
    $check_my_sql = "SELECT COUNT(*) as count FROM trade_requests 
                    WHERE (product_id = ? OR my_product_id = ?) 
                    AND (status = 'pending' OR status = 'accepted')";
    $check_my_stmt = $conn->prepare($check_my_sql);
    $check_my_stmt->bind_param("ii", $row['id'], $row['id']);
    $check_my_stmt->execute();
    $check_my_result = $check_my_stmt->get_result();
    $check_my_data = $check_my_result->fetch_assoc();
    $check_my_stmt->close();
    
    // 只添加沒有交易請求的商品
    if ($check_my_data['count'] == 0) {
        $my_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>選擇交換物品</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .trade-container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px;}
        .target-product, .my-products { margin-bottom: 30px;}
        .my-products-list { display: flex; flex-wrap: wrap; gap: 20px;}
        .my-product-card { background: #f8f9fa; border-radius: 8px; padding: 15px; width: 200px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);}
        .my-product-card input[type="radio"] { margin-right: 8px;}
        .submit-btn { background: #007bff; color: #fff; border: none; border-radius: 6px; padding: 12px 30px; font-size: 18px; cursor: pointer;}
        .submit-btn:hover { background: #0056b3;}
        .disabled-card { 
            background: #e9ecef; 
            color: #6c757d;
            cursor: not-allowed;
        }
        .disabled-card input[type="radio"] { 
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        發起交換
        <a href="index.php">返回首頁</a>
    </div>
    <div class="trade-container">
        <div class="target-product">
            <h2>你想交換的物品</h2>
            <p><b><?php echo htmlspecialchars($product['name']); ?></b>（賣家：<?php echo htmlspecialchars($product['seller_name']); ?>）</p>
            <p>狀況：<?php echo htmlspecialchars($product['condition']); ?></p>
            <p>描述：<?php echo htmlspecialchars($product['description']); ?></p>
        </div>
        <form method="POST" action="send_trade_request.php">
            <input type="hidden" name="target_product_id" value="<?php echo $product['id']; ?>">
            <div class="my-products">
                <h2>選擇你要拿來交換的物品</h2>
                <div class="my-products-list">
                <?php
                if(count($my_products) === 0) {
                    echo "<p>你目前沒有可交換的物品，<a href='upload_product.php'>點此上傳</a></p>";
                } else {
                    foreach($my_products as $my) {
                        echo "<label class='my-product-card'>";
                        echo "<input type='radio' name='my_product_id' value='" . $my['id'] . "' required>";
                        echo htmlspecialchars($my['name']) . "<br>";
                        echo "狀況：" . htmlspecialchars($my['condition']) . "<br>";
                        echo "</label>";
                    }
                }
                ?>
                </div>
            </div>
            <?php if(count($my_products) > 0): ?>
            <div style="margin-top: 30px;">
                <button type="submit" class="submit-btn">送出交換申請</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html> 