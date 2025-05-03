<?php
session_start();
if(!isset($_SESSION['uUser'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "trading");

// 處理接受/拒絕
if(isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = intval($_POST['request_id']);
    if($action === 'accept') {
        // 更新 trade_requests 狀態
        $conn->query("UPDATE trade_requests SET status='accepted' WHERE id=$request_id AND receiver_id=$uid");
        // 取得該請求資料
        $req = $conn->query("SELECT * FROM trade_requests WHERE id=$request_id")->fetch_assoc();
        // 寫入 trade
        $stmt = $conn->prepare("INSERT INTO trade (request_id, product_id, my_product_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiiii", $req['id'], $req['product_id'], $req['my_product_id'], $req['sender_id'], $req['receiver_id']);
        $stmt->execute();
    } elseif($action === 'reject') {
        // 更新狀態為拒絕
        $conn->query("UPDATE trade_requests SET status='rejected' WHERE id=$request_id AND receiver_id=$uid");
    }
    echo "<script>location.href='my_messages.php';</script>";
    exit();
}

// 查詢所有與自己有關的 trade_requests
$sql = "SELECT tr.*, 
        p.name as target_name, mp.name as my_name, 
        u1.username as sender_name, u2.username as receiver_name
        FROM trade_requests tr
        LEFT JOIN products p ON tr.product_id = p.id
        LEFT JOIN products mp ON tr.my_product_id = mp.id
        LEFT JOIN users u1 ON tr.sender_id = u1.id
        LEFT JOIN users u2 ON tr.receiver_id = u2.id
        WHERE tr.sender_id = $uid OR tr.receiver_id = $uid
        ORDER BY tr.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的訊息</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .msg-list { max-width: 900px; margin: 40px auto; }
        .msg-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #eee; margin-bottom: 20px; padding: 20px;}
        .msg-actions { margin-top: 10px; display: flex; gap: 10px; }
        .msg-btn { padding: 8px 18px; border-radius: 4px; border: none; font-size: 16px; cursor: pointer;}
        .msg-btn.accept { background: #28a745; color: #fff;}
        .msg-btn.reject { background: #dc3545; color: #fff;}
        .msg-btn.delete { background: #6c757d; color: #fff;}
        .msg-status { font-weight: bold; }
        .header a {
            text-decoration: none;
            color: #007bff;
            font-size: 50px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<div class="header">
    我的訊息
    <a href="user_dashboard.php">返回控制台</a>
</div>
<div class="msg-list">
<?php
while($row = $result->fetch_assoc()) {
    echo "<div class='msg-card' id='msg-" . $row['id'] . "'>";
    echo "<div>你想用 <b>" . htmlspecialchars($row['my_name'] ?? '') . "</b> 交換 <b>" . htmlspecialchars($row['target_name'] ?? '') . "</b></div>";
    echo "<div>對方：" . htmlspecialchars($row['receiver_name'] ?? '') . "</div>";
    echo "<div>狀態：<span class='msg-status'>";
    if($row['status'] == 'pending') {
        echo "待回應";
    } elseif($row['status'] == 'accepted') {
        echo "已接受，等待管理員審核";
    } else {
        echo "已拒絕";
    }
    echo "</span></div>";
    echo "<div>留言：" . htmlspecialchars($row['message'] ?? '') . "</div>";
    
    echo "<div class='msg-actions'>";
    // receiver 且 pending 才能操作接受/拒絕
    if($row['receiver_id'] == $uid && $row['status'] == 'pending') {
        echo "<form method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='request_id' value='" . $row['id'] . "'>";
        echo "<button type='submit' name='action' value='accept' class='msg-btn accept'>接受</button>";
        echo "<button type='submit' name='action' value='reject' class='msg-btn reject'>拒絕</button>";
        echo "</form>";
    }
    
    // 只有在請求被接受或拒絕時才顯示刪除按鈕
    if($row['status'] == 'accepted' || $row['status'] == 'rejected') {
        echo "<button onclick='hideMessage(" . $row['id'] . ")' class='msg-btn delete'>刪除</button>";
    }
    
    echo "</div>";
    echo "</div>";
}
?>
</div>

<script>
function hideMessage(messageId) {
    const messageElement = document.getElementById('msg-' + messageId);
    if (messageElement) {
        messageElement.classList.add('hidden');
    }
}
</script>
</body>
</html> 