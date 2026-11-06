<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "interactive_sign_language";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
