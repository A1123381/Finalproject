<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首頁</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
<div class="header">
    <?php
    session_start();
    if (isset($_SESSION['uUser'])) {
        echo "你好 " .$_SESSION['uUser']. " ";
        echo "<a href='logout.php'>登出</a>";
    } else {
        echo "<a href='login.php'>登入</a> ";
        echo "<a href='signup.php'>註冊</a>";
    }
    ?>
</div>

<div class="container">
    <div class="sidebar">
        <a href="#">分類</a>
        <a href="#">分類</a>
        <a href="#">分類</a>
        <a href="#">分類</a>
        <a href="#">分類</a>
        <a href="#">分類</a>
    </div>

    <div class="products">
        <div class="product">
            <div class="product-image"></div>
            <div><span class="product-heart">♡</span>商品</div>
        </div>
        <div class="product">
            <div class="product-image"></div>
            <div><span class="product-heart">♡</span>商品</div>
        </div>
        <div class="product">
            <div class="product-image"></div>
            <div><span class="product-heart">♡</span>商品</div>
        </div>
    </div>
</div>

</body>
</html>