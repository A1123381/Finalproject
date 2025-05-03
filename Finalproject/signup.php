<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
    <?php
    session_start();
    if(isset($_SESSION['error'])) {
        echo "<div class='error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>
    <div class="header">
        <a href="index.php">首頁</a>
        <a href="login.php">登入</a>
    </div>
    <div class="container">
        <form action="register_process.php" method="post">
            <div class="box">
                <h1>註冊</h1>
                <div class="boxcontent">
                    用戶名
                    <input type="text" name="username" style="width: 200px; height: 30px;" required><br>
                    密碼
                    <input type="password" name="password" style="width: 200px; height: 30px;" required><br>
                    確認密碼
                    <input type="password" name="confirm_password" style="width: 200px; height: 30px;" required><br>
                    <button type="submit" style="width: 100px; height: 50px; font-size: 40px;">提交</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>