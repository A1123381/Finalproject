<?php
session_start();
if(!isset($_SESSION['uUser'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "trading");

// 處理確認收貨
if(isset($_POST['action']) && isset($_POST['trade_id'])) {
    $action = $_POST['action'];
    $trade_id = intval($_POST['trade_id']);
    
    if($action === 'confirm') {
        // 檢查是否為交易的一方
        $check_sql = "SELECT * FROM trade WHERE id = ? AND (sender_id = ? OR receiver_id = ?) AND status = 'approved'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iii", $trade_id, $uid, $uid);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if($result->num_rows > 0) {
            $trade = $result->fetch_assoc();
            // 更新確認狀態
            if($trade['sender_id'] == $uid) {
                $conn->query("UPDATE trade SET sender_confirmed = 1 WHERE id = $trade_id");
            } else {
                $conn->query("UPDATE trade SET receiver_confirmed = 1 WHERE id = $trade_id");
            }
            
            // 檢查是否雙方都已確認
            $confirm_sql = "SELECT sender_confirmed, receiver_confirmed FROM trade WHERE id = ?";
            $confirm_stmt = $conn->prepare($confirm_sql);
            $confirm_stmt->bind_param("i", $trade_id);
            $confirm_stmt->execute();
            $confirm_result = $confirm_stmt->get_result();
            $confirm_data = $confirm_result->fetch_assoc();
            
            // 如果雙方都已確認，更新狀態為已完成
            if($confirm_data['sender_confirmed'] && $confirm_data['receiver_confirmed']) {
                $conn->query("UPDATE trade SET status = 'done' WHERE id = $trade_id");
            }
        }
    }
    echo "<script>location.href='my_trades.php';</script>";
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
        WHERE (t.sender_id = $uid OR t.receiver_id = $uid) AND t.status != 'done'
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的交易</title>
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
        .trade-status { 
            font-weight: bold; 
        }
        .status-pending {
            color: #ffc107;
        }
        .status-approved {
            color: #17a2b8;
        }
        .status-rejected {
            color: #dc3545;
        }
        .status-completed {
            color: #28a745;
        }
        .trade-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .trade-btn {
            padding: 8px 18px;
            border-radius: 4px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            background: #007bff;
            color: #fff;
        }
        .trade-btn:hover {
            background: #0056b3;
        }
        .header a {
            text-decoration: none;
            color: #007bff;
            font-size: 50px;
        }
        .confirmation-status {
            margin-top: 5px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="header">
    我的交易
    <a href="user_dashboard.php">返回控制台</a>
</div>
<div class="trade-list">
<?php
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='trade-card'>";
        echo "<div>你用 <b>" . htmlspecialchars($row['my_name'] ?? '') . "</b> 交換 <b>" . 
             htmlspecialchars($row['target_name'] ?? '') . "</b></div>";
        echo "<div>對方：" . htmlspecialchars($row['receiver_name'] ?? '') . "</div>";
        echo "<div>狀態：<span class='trade-status status-" . ($row['status'] ?? 'pending') . "'>";
        
        switch($row['status']) {
            case 'pending':
                echo "待管理員審核";
                break;
            case 'approved':
                echo "已核准，運輸中";
                if(($row['sender_id'] == $uid && !$row['sender_confirmed']) || 
                   ($row['receiver_id'] == $uid && !$row['receiver_confirmed'])) {
                    echo "<form method='POST' class='trade-actions'>";
                    echo "<input type='hidden' name='trade_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='action' value='confirm' class='trade-btn'>確認收貨</button>";
                    echo "</form>";
                }
                echo "<div class='confirmation-status'>";
                if($row['sender_confirmed']) {
                    echo "發送方已確認收貨<br>";
                }
                if($row['receiver_confirmed']) {
                    echo "接收方已確認收貨";
                }
                echo "</div>";
                break;
            case 'rejected':
                echo "已拒絕";
                break;
            case 'done':
                echo "已完成";
                break;
            default:
                echo "處理中";
        }
        
        echo "</span></div>";
        echo "<div>交易時間：" . htmlspecialchars($row['created_at'] ?? '') . "</div>";
        echo "</div>";
    }
} else {
    echo "<div class='trade-card'>";
    echo "<p style='text-align: center; color: #666;'>目前沒有任何交易記錄</p>";
    echo "</div>";
}
?>
</div>
</body>
</html> 