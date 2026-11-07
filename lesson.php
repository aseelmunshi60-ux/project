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
    <title>قائمة الدروس - مشروع وصلة</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- القائمة العلوية -->
    <header class="main-header">
        <div class="nav-container">
            <div class="logo-title">مشروع وصلة</div>
            <nav class="nav-links">
                <a href="index.php">الرئيسية</a>
                <a href="lesson.php" class="active">قائمة الدروس</a>
                <a href="game.php">الألعاب</a>
                <a href="Report.php">الإنجازات</a>
                <a href="profile.php">الإعدادات</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="auth-btn">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="Login.php" class="auth-btn">تسجيل الدخول</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <div class="container">
        <h2 class="page-title">📖 قائمة الدروس المتاحة</h2>

        <div class="lessons-grid">
            <?php
            // استعلام جلب الدروس من قاعدة البيانات إذا أردتِ ربطها لاحقاً
            $query = "SELECT * FROM lesson";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="lesson-card">';
                    echo '  <div class="lesson-card-body">';
                    echo '      <h3 class="lesson-title">' . htmlspecialchars($row["title"]) . '</h3>';
                    echo '      <p class="lesson-desc">' . htmlspecialchars($row["description"]) . '</p>';
                    echo '  </div>';
                    echo '  <div class="lesson-card-footer">';
                    echo '      <a href="lesson_details.php?id=' . $row["id"] . '" class="card-btn">عرض الدرس</a>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                // دروس افتراضية للعرض عند بدء التشغيل وقبل ملء الجدول
            ?>
                <div class="lesson-card">
                    <div class="lesson-card-body">
                        <h3 class="lesson-title">الدرس الأول: مقدمة في تطوير الويب</h3>
                        <p class="lesson-desc">تعرفي على أساسيات بناء وتطوير صفحات وتجارب الاستخدام التفاعلية.</p>
                    </div>
                    <div class="lesson-card-footer">
                        <a href="#" class="card-btn">عرض الدرس</a>
                    </div>
                </div>
                
                <div class="lesson-card">
                    <div class="lesson-card-body">
                        <h3 class="lesson-title">الدرس الثاني: قواعد البيانات و SQL</h3>
                        <p class="lesson-desc">كيفية ربط الموقع بقاعدة البيانات واسترجاع البيانات بكفاءة.</p>
                    </div>
                    <div class="lesson-card-footer">
                        <a href="#" class="card-btn">عرض الدرس</a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <br>
        <a href="index.php" class="card-btn" style="display: inline-block; margin-top: 20px;">العودة للرئيسية</a>
    </div>

    <!-- الفوتر -->
    <footer>
        <p>جميع الحقوق محفوظة © مشروع وصلة 2026</p>
    </footer>

</body>
</html>