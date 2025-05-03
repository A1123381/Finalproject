<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入</title>
    <link rel='stylesheet' href='style.css'>
    <style>
        .header a{
            text-decoration: none;
            color: #007bff;
            font-size: 50px;
        }
        .box {
    width: 500px;
    height: 500px;
    background: #007bff;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    flex-direction: column; /* Allows multiple lines stacking */
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
.box h1 {
    font-size: 100px;
    margin-top:10px;
    color: #fff;
}
.boxcontent{
    font-size: 50px;
    margin-top:-50px;
    color: #fff;
    background: #007bff;

}
    </style>
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