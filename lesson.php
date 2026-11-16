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
            <div class="logo-title">منصة وصلة التعليمية</div>
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
        <h2>قائمة الدروس المتاحة</h2>
        
        <div class="cards-grid">
            <?php
            // استعلام لجلب الـ 6 دروس المطلوبة فقط بدقة
            $query = "SELECT * FROM lesson WHERE lesson_id IN (1, 2, 8, 9, 10, 16)";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $lesson_id = isset($row['lesson_id']) ? $row['lesson_id'] : (isset($row['id']) ? $row['id'] : 1);
                    $description = !empty($row['description']) ? $row['description'] : 'اضغط على عرض الدرس لمتابعة المحتوى.';
                    
                    echo '<div class="card">';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($description) . '</p>';
                    // تم تعديل اسم الملف هنا إلى lesson_detalis.php
                 echo '<a href="lesson_details.php?id=' . $lesson_id . '" class="btn">عرض الدرس</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>لا توجد دروس مضافة حالياً.</p>';
            }
            ?>
        </div>
    </main>

</body>
</html>