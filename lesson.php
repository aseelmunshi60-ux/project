<?php
include 'db_connect.php';
<<<<<<< HEAD
?>
    <link rel="stylesheet" href="style.css">
<header>
    <h1>مشروع وصلة</h1>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="lesson.php">قائمة الدروس</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="Login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2 class="page-title">📖 قائمة الدروس المتاحة</h2>

    <div class="lessons-grid">
        <?php
        if (@$result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="lesson-card">';
                echo '  <div class="lesson-card-body">';
                echo '      <h3 class="lesson-title">' . htmlspecialchars($row["title"]) . '</h3>';
                echo '      <p class="lesson-desc">' . htmlspecialchars($row["description"]) . '</p>';
                echo '  </div>';
                echo '  <div class="lesson-card-footer">';
                echo '      <a href="lesson_details.php?id=' . $row["id"] . '" class="btn-view">عرض الدرس</a>';
                echo '  </div>';
                echo '</div>';
            }
        } else {
            // دروس افتراضية للعرض عند بدء التشغيل وقبل ملء الجدول
            echo'
=======
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
>>>>>>> e3c084e615e91cbbdbe3d19f8f78049b4db69e92
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