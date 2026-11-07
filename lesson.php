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
    <title>قائمة الدروس - منصة وصلة التعليمية</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- القائمة العلوية -->
    <header class="main-header">
        <div class="nav-container">
            <!-- الاسم صار على اليمين -->
            <div class="logo-title">منصة وصلة التعليمية</div>
            
            <!-- الروابط صارت على اليسار -->
            <nav class="nav-links">
                <a href="index.php">الرئيسية</a>
                <a href="lesson.php" class="active">الدروس</a>
                <a href="game.php">الألعاب</a>
                <a href="Report.php">الإنجازات</a>
                <a href="profile.php">الإعدادات</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="Login.php">تسجيل الدخول</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="container">
        <h2 class="page-title">قائمة الدروس المتاحة</h2>

        <!-- شبكة الكروت -->
        <div class="cards-grid">
            <?php
            $query = "SELECT * FROM lesson";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="card">';
                    echo '    <div>';
                    echo '        <h3>' . htmlspecialchars($row["title"]) . '</h3>';
                    echo '        <p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '    </div>';
                    echo '    <a href="lesson_details.php?id=' . $row["id"] . '" class="card-btn">عرض الدرس</a>';
                    echo '</div>';
                }
            } else {
            ?>
                <div class="card">
                    <div>
                        <h3>الحروف</h3>
                        <p>تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.</p>
                    </div>
                    <a href="#" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الأرقام</h3>
                        <p>اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.</p>
                    </div>
                    <a href="#" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الكلمات: المجموعة الأولى</h3>
                        <p>مجموعة مختارة من الكلمات اليومية الشائعة (مثل التحيات والأشياء القريبة) للتواصل السريع والسهل.</p>
                    </div>
                    <a href="#" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الكلمات: المجموعة الثانية</h3>
                        <p>كلمات إضافية متقدمة ومتنوعة (مثل الألوان والأطعمة والأنشطة) لتوسيع حصيلة المعرفة الإشارية.</p>
                    </div>
                    <a href="#" class="card-btn">عرض الدرس</a>
                </div>
            <?php } ?>
        </div>
    </main>

</body>
</html>