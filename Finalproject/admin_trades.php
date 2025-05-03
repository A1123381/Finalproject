<?php
session_start();
if(!isset($_SESSION['uUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "trading");

// 處理審核
if(isset($_POST['action']) && isset($_POST['trade_id'])) {
    $action = $_POST['action'];
    $trade_id = intval($_POST['trade_id']);
    if($action === 'approve') {
        // 更新為已核准狀態
        $conn->query("UPDATE trade SET status='approved' WHERE id=$trade_id");
    } elseif($action === 'reject') {
        $conn->query("UPDATE trade SET status='rejected' WHERE id=$trade_id");
    }
    echo "<script>location.href='admin_trades.php';</script>";
    exit();
}

$sql = "SELECT t.*, 
        p.name as target_name, mp.name as my_name, 
        u1.username as sender_name, u2.username as receiver_name
        FROM trade t
        LEFT JOIN products p ON t.product_id = p.id
        LEFT JOIN products mp ON t.my_product_id = mp.id
        LEFT JOIN users u1 ON t.sender_id = u1.id
        LEFT JOIN users u2 ON t.receiver_id = u2.id
        WHERE t.status = 'pending'
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>交易審核</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .trade-list { 
            max-width: 900px; 
            margin: 40px auto; 
        }
        .trade-card { 
            background: #fff; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px #eee; 
            margin-bottom: 20px; 
            padding: 20px;
        }
        .trade-info {
            margin-bottom: 15px;
        }
        .trade-info div {
            margin-bottom: 8px;
        }
        .trade-actions { 
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .trade-btn { 
            padding: 8px 18px; 
            border-radius: 4px; 
            border: none; 
            font-size: 16px; 
            cursor: pointer;
            flex: 1;
        }
        .trade-btn.approve { 
            background: #28a745; 
            color: #fff;
        }
        .trade-btn.approve:hover {
            background: #218838;
        }
        .trade-btn.reject { 
            background: #dc3545; 
            color: #fff;
        }
        .trade-btn.reject:hover {
            background: #c82333;
        }
        .empty-message {
            text-align: center;
            color: #666;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #eee;
        }
    </style>
</head>
<body>
<div class="header">
    交易審核
    <a href="admin_dashboard.php">返回控制台</a>
</div>
<div class="trade-list">
    <?php
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='trade-card'>";
            echo "<div class='trade-info'>";
            echo "<div>交換物品：<b>" . htmlspecialchars($row['my_name'] ?? '') . "</b> ⇄ <b>" . 
                 htmlspecialchars($row['target_name'] ?? '') . "</b></div>";
            echo "<div>申請人：" . htmlspecialchars($row['sender_name'] ?? '') . "</div>";
            echo "<div>接收方：" . htmlspecialchars($row['receiver_name'] ?? '') . "</div>";
            echo "<div>申請時間：" . htmlspecialchars($row['created_at'] ?? '') . "</div>";
            echo "</div>";
            
            echo "<form method='POST' class='trade-actions' onsubmit='return confirmAction(this)'>";
            echo "<input type='hidden' name='trade_id' value='" . ($row['id'] ?? '') . "'>";
            echo "<button type='submit' name='action' value='approve' class='trade-btn approve'>核准交易（運輸中）</button>";
            echo "<button type='submit' name='action' value='reject' class='trade-btn reject'>拒絕交易</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<div class='empty-message'>";
        echo "<h2>目前沒有待審核的交易</h2>";
        echo "<p>所有交易都已經處理完成</p>";
        echo "</div>";
    }
    ?>
</div>

<script>
function confirmAction(form) {
    const action = form.querySelector('button[type="submit"]:focus').value;
    const message = action === 'approve' ? '確定要核准這筆交易嗎？交易將進入運輸階段。' : '確定要拒絕這筆交易嗎？';
    return confirm(message);
}
</script>

</body>
</html> 