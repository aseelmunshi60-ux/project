<?php
$host     = "localhost";
$username = "root";         
$password = "";           
$dbname   = "interactive_sign_language"; 

$conn = new mysqli($host, $username, $password, $dbname);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>