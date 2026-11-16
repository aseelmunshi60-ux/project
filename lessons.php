<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشروع وصلة - قائمة الدروس</title>
    <style>
        body { font-family: 'Tahoma', sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; direction: rtl; }
        header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 24px; }
        nav a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; }
        .page-title { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        
        .lessons-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .lesson-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-right: 4px solid #3498db; transition: transform 0.2s; }
        .lesson-card:hover { transform: translateY(-5px); }
        .lesson-title { margin-top: 0; color: #2c3e50; }
        .lesson-desc { color: #666; font-size: 14px; line-height: 1.6; }
        
        .btn-view { display: inline-block; padding: 8px 15px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; margin-top: 15px; font-weight: bold; }
        .btn-view:hover { background-color: #27ae60; }
    </style>
</head>
<body>

<header>
    <h1>منصة وصلة التعليمية</h1>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="lesson.php">الدروس</a>
        <a href="games.php">الألعاب</a>
        <a href="achievements.php">الإنجازات</a>
        <a href="settings.php">الإعدادات</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="Login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2 class="page-title">قائمة الدروس المتاحة</h2>
    
    <div class="lessons-grid">
        <!-- درس الحروف -->
        <div class="lesson-card">
            <h3 class="lesson-title">الحروف</h3>
            <p class="lesson-desc">تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.</p>
            <a href="#" class="btn-view">عرض الدرس</a>
        </div>

        <!-- درس الأرقام -->
        <div class="lesson-card">
            <h3 class="lesson-title">الأرقام</h3>
            <p class="lesson-desc">اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.</p>
            <a href="#" class="btn-view">عرض الدرس</a>
        </div>

        <!-- الكلمات: المجموعة الأولى -->
        <div class="lesson-card">
            <h3 class="lesson-title">الكلمات: المجموعة الأولى</h3>
            <p class="lesson-desc">مجموعة مختارة من الكلمات اليومية الشائعة (مثل التحيات والأشياء القريبة) للتواصل السريع والسهل.</p>
            <a href="#" class="btn-view">عرض الدرس</a>
        </div>

        <!-- الكلمات: المجموعة الثانية -->
        <div class="lesson-card">
            <h3 class="lesson-title">الكلمات: المجموعة الثانية</h3>
            <p class="lesson-desc">كلمات إضافية متقدمة ومتنوعة (مثل الألوان والأطعمة والأنشطة) لتوسيع حصيلة المعرفة الإشارية.</p>
            <a href="#" class="btn-view">عرض الدرس</a>
        </div>
    </div>
</div>

</body>
</html>