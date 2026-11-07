<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "interctive_sign_language"; // اسم قاعدة البيانات الخاصة بكم

$conn = mysqli_connect($host, $user, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// ضبط الترميز ليدعم اللغة العربية
mysqli_set_charset($conn, "utf8");
?>