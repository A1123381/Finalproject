<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
    <?php
    session_start();
    ?>
    <div class="header">
        <a href="index.php">首頁</a>
        <a href="signup.php">註冊</a>
    </div>
    <div class="container">
    <form action="logincheck.php" method="post">

            <div class="box">
                <h1>登入</h1>
                <div class="boxcontent">
                用戶
                <input type="text"  name="username" style="width: 300px; height: 30px;" required><br>
                密碼
                <input type="password"   name="password" style="width: 300px; height: 30px;" required><br>
                <button type="submit" style="width: 100px; height: 50px; font-size: 40px;">提交</button>
                </div>
            </div>

    </div>

    </body>
</html>