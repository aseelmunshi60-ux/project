<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشروع وصلة</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- القائمة العلوية -->
    <header class="main-header">
        <div class="nav-container">
            <div class="logo-title">منصة وصلة التعليمية</div>
            <nav class="nav-links">
                <a href="index.php" class="active">الرئيسية</a>
                <a href="lessons.php">الدروس</a>
                <a href="games.php">الألعاب</a>
                <a href="achievements.php">الإنجازات</a>
                <a href="settings.php">الإعدادات</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="auth-btn">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="Login.php" class="auth-btn">تسجيل الدخول</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="container">
        <!-- قسم الترحيب -->
        <section class="welcome-banner">
            <h2>مرحباً بك في منصة وصلة </h2>
            <p>نواصل بالأسارة، العمل المتواصل، استعرض الدروس والألعاب التفاعلية وتابع تقدمك بكل سهولة.</p>
        </section>

        <!-- بطاقات الخدمات الرئيسية -->
        <section class="cards-grid">
            <div class="card">
                <h3>الدروس 📚</h3>
                <p>تعلم الحروف، الأرقام والكلمات بطريقة تفاعلية وممتعة.</p>
                <a href="lessons.php" class="card-btn">استعرض الدروس</a>
            </div>

            <div class="card">
                <h3>الألعاب 🎮</h3>
                <p>العب وتحدّ نفسك على تنمية المهارات الأساسية.</p>
                <a href="games.php" class="card-btn">ابدأ اللعب</a>
            </div>

            <div class="card">
                <h3>الإنجازات 🏆</h3>
                <p>تابع مستواك، أوسمتك، والأنشطة التي حصلت عليها.</p>
                <a href="achievements.php" class="card-btn">عرض الإنجازات</a>
            </div>

            <div class="card">
                <h3>الإعدادات ⚙️</h3>
                <p>إدارة الحساب، والكلمات، وتخصيص الطابع.</p>
                <a href="settings.php" class="card-btn">الإعدادات</a>
            </div>
        </section>

        <!-- قسم الأنشطة والتنبيهات الإضافية -->
        <section class="extra-sections">
            <div class="box">
                <h3>الأنشطة الأخيرة 📌</h3>
                <p>• آخر درس تم إكماله: درس حرف الباء</p>
                <p>• تحدي جديد متاح: مراجعة الأحرف الإنجليزية</p>
            </div>
            <div class="box">
                <h3>التنبيهات والإشعارات 🔔</h3>
                <p>• تهانينا! لقد أكملت مستوى مستخدم جديد.</p>
                <p>• رسالة من المعلم: استمر في مراجعة الواجبة اليومية.</p>
            </div>
        </section>
    </main>

    <!-- الفوتر -->
    <footer>
        <p>جميع الحقوق محفوظة © مشروع وصلة 2026</p>
    </footer>

</body>
</html>