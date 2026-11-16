<?php
session_start();
include 'db_connect.php';

$username = $_SESSION['username'] ?? 'مستخدم وصلة';
$email = $_SESSION['email'] ?? 'البريد غير مسجل';
$role = $_SESSION['role'] ?? 'ولي أمر';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>الملف الشخصي - وصلة</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f6fb;
    margin: 0;
    color: #333;
}
header {
    background: #4776a8;
    color: white;
    text-align: center;
    padding: 20px;
}
nav a {
    color: white;
    text-decoration: none;
    margin: 8px;
}
.container {
    max-width: 550px;
    margin: 35px auto;
    padding: 15px;
}
.profile {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 12px #dce4ee;
}
.avatar {
    font-size: 55px;
}
h2 {
    color: #315b83;
}
.info {
    background: #f4f7fb;
    padding: 15px;
    margin: 12px 0;
    border-radius: 8px;
    text-align: right;
    overflow-wrap: anywhere;
}
.label {
    color: #4776a8;
    font-weight: bold;
}
button {
    background: #4776a8;
    color: white;
    border: 0;
    padding: 12px 22px;
    border-radius: 8px;
    font-size: 16px;
}
</style>
</head>
<body>

<header>
    <h1>وصلة التعليمية</h1>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="lesson.php">الدروس</a>
    </nav>
</header>

<div class="container">
    <div class="profile">
        <div class="avatar">👤</div>
        <h2>الملف الشخصي</h2>
        <p>مرحبًا بك في منصة وصلة التعليمية</p>

        <div class="info">
            <span class="label">اسم المستخدم:</span>
            <?= htmlspecialchars($username) ?>
        </div>

        <div class="info">
            <span class="label">البريد الإلكتروني:</span>
            <?= htmlspecialchars($email) ?>
        </div>

        <div class="info">
            <span class="label">نوع الحساب:</span>
            <?= htmlspecialchars($role) ?>
        </div>

        <button onclick="location.href='index.php'">
            العودة للرئيسية
        </button>
    </div>
</div>

</body>
</html>
