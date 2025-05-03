<?php
session_start(); // 移到最開頭，在任何輸出之前
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>物品交換平台</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 200px;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar a {
            display: block;
            padding: 8px 12px;
            color: #495057;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .sidebar a:hover {
            background-color: #e9ecef;
        }
        
        .sidebar a.active {
            background-color: #007bff;
            color: white;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
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
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .product-id {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .product-condition {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 14px;
            color: #495057;
            margin-bottom: 8px;
            width: fit-content;
        }
        
        .product-category {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .product-description {
            color: #495057;
            font-size: 14px;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .product-footer {
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-seller {
            font-weight: 500;
        }
        
        .product-date {
            font-size: 12px;
            color: #adb5bd;
        }

        /* 確保 header 樣式正確 */
        .header {
            background-color: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-nav, .auth-nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .header a {
            text-decoration: none;
            color: #007bff;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .header a:hover {
            background-color: #f8f9fa;
        }

        .trade-btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff !important;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 16px;
            text-decoration: none;
            margin-top: 8px;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .trade-btn:hover {
            background-color: #0056b3;
            color: #fff !important;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
        }
        .page-btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .page-btn:hover {
            background: #0056b3;
        }
        .page-info {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php
        if (isset($_SESSION['uUser'])) {
            echo "<div class='user-nav'>";
            echo "<span>你好，" . htmlspecialchars($_SESSION['uUser']) . "</span>";
            if ($_SESSION['role'] === 'admin') {
                echo "<a href='admin_dashboard.php'>管理控制台</a>";
            } else {
                echo "<a href='user_dashboard.php'>會員中心</a>";
            }
            echo "<a href='upload_product.php'>上傳物品</a>";
            echo "<a href='logout.php'>登出</a>";
            echo "</div>";
        } else {
            echo "<div class='user-nav'>";
            echo "<a href='login.php'>登入</a>";
            echo "<a href='signup.php'>註冊</a>";
            echo "</div>";
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
            
            // 獲取當前選擇的分類
            $selected_category = isset($_GET['category']) ? $_GET['category'] : null;
            
            // 顯示所有分類
            echo "<a href='index.php'" . ($selected_category === null ? " class='active'" : "") . ">全部物品</a>";
            while($row = $result->fetch_assoc()) {
                $active = $selected_category == $row['id'] ? " class='active'" : "";
                echo "<a href='index.php?category=" . $row['id'] . "'" . $active . ">" . 
                     htmlspecialchars($row['name']) . "</a>";
            }
            ?>
        </div>

        <div class="main-content">
            <div class="products">
                <?php
                // 獲取當前頁碼
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $per_page = 5;
                $offset = ($page - 1) * $per_page;

                // 查詢總數
                $count_sql = "SELECT COUNT(*) as total FROM products WHERE status = 'available'";
                if($selected_category) {
                    $count_sql .= " AND category_id = " . intval($selected_category);
                }
                $count_result = $conn->query($count_sql);
                $total_products = $count_result->fetch_assoc()['total'];
                $total_pages = ceil($total_products / $per_page);

                // 查詢產品列表
                $sql = "SELECT p.*, u.username as seller_name, c.name as category_name
                        FROM products p
                        LEFT JOIN users u ON p.seller_id = u.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.status = 'available'";
                if($selected_category) {
                    $sql .= " AND p.category_id = " . intval($selected_category);
                }
                $sql .= " ORDER BY p.created_at DESC LIMIT $offset, $per_page";
                $result = $conn->query($sql);
                
                while($product = $result->fetch_assoc()) {
                    // 檢查商品是否有待處理或已接受的交易請求，或在 trade 表中
                    $check_trade_sql = "SELECT 
                        (SELECT COUNT(*) FROM trade_requests 
                         WHERE (product_id = ? OR my_product_id = ?) 
                         AND (status = 'pending' OR status = 'accepted')) as pending_count,
                        (SELECT COUNT(*) FROM trade 
                         WHERE (product_id = ? OR my_product_id = ?) 
                         AND status = 'pending') as trade_count";
                    $check_stmt = $conn->prepare($check_trade_sql);
                    $check_stmt->bind_param("iiii", $product['id'], $product['id'], $product['id'], $product['id']);
                    $check_stmt->execute();
                    $trade_result = $check_stmt->get_result();
                    $trade_data = $trade_result->fetch_assoc();
                    $has_pending_trade = $trade_data['pending_count'] > 0 || $trade_data['trade_count'] > 0;
                    $check_stmt->close();

                    echo "<div class='product-card' onclick='showProductDetails(" . json_encode($product) . ")'>";
                    echo "<div class='product-image'>";
                    if($product['image_url']) {
                        echo "<img src='" . htmlspecialchars($product['image_url']) . "' 
                              alt='" . htmlspecialchars($product['name']) . "'>";
                    }
                    echo "</div>";
                    echo "<div class='product-info'>";
                    echo "<div class='product-name'>" . htmlspecialchars($product['name']) . "</div>";
                    echo "<div class='product-id' style='color: #6c757d; font-size: 14px; margin-bottom: 8px;'>ID: " . htmlspecialchars($product['id']) . "</div>";
                    echo "<div class='product-condition'>" . htmlspecialchars($product['condition']) . "</div>";
                    echo "<div class='product-category'>分類：" . htmlspecialchars($product['category_name']) . "</div>";
                    echo "<div class='product-description'>" . htmlspecialchars($product['description']) . "</div>";
                    echo "</div>";
                    echo "<div class='product-footer'>";
                    echo "<div class='product-seller'>賣家：" . htmlspecialchars($product['seller_name']) . "</div>";
                    echo "<div class='product-date'>" . date('Y/m/d', strtotime($product['created_at'])) . "</div>";
                    echo "</div>";
                    // 要求交易按鈕（不能對自己的商品顯示，且商品沒有待處理的交易請求）
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']) {
                        echo "<div style='padding: 15px; text-align: center;'>";
                        if ($has_pending_trade) {
                            echo "<button class='trade-btn' style='background-color: #6c757d; cursor: not-allowed;' disabled>已有交易請求</button>";
                        } else {
                            echo "<a href='trade.php?product_id=" . $product['id'] . "' class='trade-btn'>要求交易</a>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                }
                
                if($result->num_rows === 0) {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #6c757d; padding: 40px;'>
                          目前沒有任何物品</p>";
                }

                // 在產品列表後添加分頁控制
                echo "</div>"; // 關閉 product-list div

                // 添加分頁控制
                if($total_pages > 1) {
                    echo "<div class='pagination'>";
                    if($page > 1) {
                        echo "<a href='index.php?page=" . ($page - 1) . "' class='page-btn'>上一頁</a>";
                    }
                    echo "<span class='page-info'>第 $page 頁，共 $total_pages 頁</span>";
                    if($page < $total_pages) {
                        echo "<a href='index.php?page=" . ($page + 1) . "' class='page-btn'>下一頁</a>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- 交易邀請 Modal -->
    <div id="tradeModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        function showProductDetails(product) {
            const modal = document.getElementById('tradeModal');
            const modalContent = document.getElementById('modalContent');
            
            <?php if(!isset($_SESSION['uUser'])): ?>
                modalContent.innerHTML = `
                    <div class="login-prompt">
                        <h2>請先登入</h2>
                        <p>您需要登入才能發送交易邀請</p>
                        <a href="login.php" class="send-request-btn">前往登入</a>
                    </div>
                `;
            <?php else: ?>
                if(product.seller_id == <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>) {
                    modalContent.innerHTML = `
                        <div class="login-prompt">
                            <h2>這是您的物品</h2>
                            <p>您不能向自己發送交易邀請</p>
                            <a href="my_products.php" class="send-request-btn">查看我的物品</a>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = `
                        <div class="product-details">
                            <img src="${product.image_url}" alt="${product.name}">
                            <div class="product-info-modal">
                                <h2>${product.name}</h2>
                                <p>狀況：${product.condition}</p>
                                <p>分類：${product.category_name}</p>
                                <p>賣家：${product.seller_name}</p>
                                <p>描述：${product.description}</p>
                            </div>
                        </div>
                        <form class="trade-form" onsubmit="sendTradeRequest(event, ${product.id}, ${product.seller_id})">
                            <textarea placeholder="請輸入交易邀請訊息..." required></textarea>
                            <button type="submit" class="send-request-btn">發送交易邀請</button>
                        </form>
                    `;
                }
            <?php endif; ?>
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('tradeModal').style.display = 'none';
        }

        function sendTradeRequest(event, productId, receiverId) {
            event.preventDefault();
            const message = event.target.querySelector('textarea').value;
            
            fetch('send_trade_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    receiver_id: receiverId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('交易邀請已發送！');
                    closeModal();
                } else {
                    alert(data.message || '發送失敗，請稍後再試！');
                }
            })
            .catch(error => {
                alert('發送失敗，請稍後再試！');
            });
        }

        window.onclick = function(event) {
            const modal = document.getElementById('tradeModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 