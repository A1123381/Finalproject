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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>交易管理</title>
    <link rel='stylesheet' href='style.css'>
    <link rel='stylesheet' href='list.css'>
    <style>
        .trade-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .delete-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-approved {
            color: #28a745;
        }
        .status-rejected {
            color: #dc3545;
        }
        .status-done {
            color: #007bff;
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
    </style>
</head>
<body>
    <div class="header">
        交易管理
        <a href="admin_dashboard.php">返回控制台</a>
    </div>
    
    <div class="container">
        <ul class="trade-list">
            <?php
            // 獲取所有交易資料，並關聯相關的用戶和商品資訊
            $sql = "SELECT t.*, 
                           p1.name as product_name, 
                           p2.name as my_product_name,
                           u1.username as sender_name,
                           u2.username as receiver_name
                    FROM trade t
                    LEFT JOIN products p1 ON t.product_id = p1.id
                    LEFT JOIN products p2 ON t.my_product_id = p2.id
                    LEFT JOIN users u1 ON t.sender_id = u1.id
                    LEFT JOIN users u2 ON t.receiver_id = u2.id
                    ORDER BY t.created_at DESC";
            $result = $conn->query($sql);
            
            while($row = $result->fetch_assoc()) {
                echo "<li class='trade-card'>";
                echo "<div class='trade-id'>交易ID: " . htmlspecialchars($row['id']) . "</div>";
                echo "<div class='trade-info'>";
                echo "發送者: " . htmlspecialchars($row['sender_name']) . "<br>";
                echo "接收者: " . htmlspecialchars($row['receiver_name']) . "<br>";
                echo "商品: " . htmlspecialchars($row['product_name']) . "<br>";
                if($row['my_product_name']) {
                    echo "交換商品: " . htmlspecialchars($row['my_product_name']) . "<br>";
                }
                echo "</div>";
                
                // 根據狀態設置不同的樣式
                $statusClass = 'status-' . $row['status'];
                $statusText = '';
                switch($row['status']) {
                    case 'pending':
                        $statusText = '待處理';
                        break;
                    case 'approved':
                        $statusText = '已批准';
                        break;
                    case 'rejected':
                        $statusText = '已拒絕';
                        break;
                    case 'done':
                        $statusText = '已完成';
                        break;

                }
                echo "<div class='trade-status " . $statusClass . "'>狀態: " . $statusText . "</div>";
                echo "<div class='trade-date'>創建時間：" . htmlspecialchars($row['created_at']) . "</div>";
                
                // 添加刪除按鈕
                echo "<div class='trade-actions'>";
                echo "<button class='delete-btn' onclick='deleteTrade(" . $row['id'] . ")'>刪除</button>";
                echo "</div>";
                
                echo "</li>";
            }
            ?>
        </ul>
    </div>

    <script>
        function deleteTrade(tradeId) {
            if(confirm('確定要刪除此交易嗎？此操作無法撤銷！')) {
                // 使用 POST 方法發送刪除請求
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_trade.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'trade_id';
                input.value = tradeId;

                form.appendChild(input);
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
