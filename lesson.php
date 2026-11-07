<?php
include 'db_connect.php';
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة الدروس</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>قائمة الدروس التعليمية</h1>
        <p>مرحباً بكِ في قسم الدروس الخاصة بالمشروع.</p>
        
        <div class="lessons-list">
            <div class="lesson-card">
                <h3>الدرس الأول: مقدمة في تطوير الويب</h3>
                <p>تعرفي على أساسيات بناء وتطوير صفحات وتجارب الاستخدام التفاعلية.</p>
            </div>
            
            <div class="lesson-card">
                <h3>الدرس الثاني: قواعد البيانات و SQL</h3>
                <p>كيفية ربط الموقع بقاعدة البيانات واسترجاع البيانات بكفاءة.</p>
            </div>
        </div>

        <br>
        <a href="index.php">العودة للرئيسية</a>
    </div>
</body>
</html>