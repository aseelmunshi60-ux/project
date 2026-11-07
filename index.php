<?php
include 'db_connect.php';
?>
<header>
    <h1>مشروع وصلة</h1>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="lessons.php">قائمة الدروس</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="Login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h2>أهلاً بكم في منصة وصلة 🤝</h2>
    <p>جسر التواصل والتعليم التفاعلي للأطفال، أولياء الأمور، والمعلمين.</p>
    <a href="lessons.php" class="btn-main">تصفح الدروس والمحتوى</a>
</section>

<section class="features">
    <div class="card">
        <h3>📚 دروس interactive</h3>
        <p>محتوى تعليمي تفاعلي مبسط مصمم خصيصاً لتطوير المهارات.</p>
    </div>
    <div class="card">
        <h3>👨‍👩‍👧 متابعة أولياء الأمور</h3>
        <p>لوحة خاصة لولي الأمر لمتابعة تقارير التقدم ومستوى أداء الطفل.</p>
    </div>
    <div class="card">
        <h3>👩‍🏫 إشراف المعلمين</h3>
        <p>بيئة متكاملة تسمح للمعلم بإضافة الدروس ومتابعة الطلاب.</p>
    </div>
</section>

<footer>
    <p>جميع الحقوق محفوظة &copy; مشروع وصلة 2026</p>
</footer>
<