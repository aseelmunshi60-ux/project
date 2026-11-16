<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// قائمة الـ 6 دروس الأساسية المعتمدة فقط
$desired_lessons = [
    'الحروف',
    'الأرقام',
    'الألوان',
    'المشاعر',
    'أفراد العائلة',
    'الكلمات الأساسية'
];
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
    <main class="main-container" style="padding: 30px 20px; max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #2c3e50; margin: 0;">قائمة الدروس المتاحة</h2>
            <a href="add_lesson.php" class="card-btn" style="width: auto; padding: 10px 20px; background: #28a745; color: #fff; text-decoration: none; border-radius: 8px;">+ إضافة درس جديد</a>
        </div>

        <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php
            foreach ($desired_lessons as $lesson_title) {
                $safe_title = mysqli_real_escape_string($conn, $lesson_title);
                $query = "SELECT * FROM lesson WHERE title = '$safe_title' LIMIT 1";
                $result = mysqli_query($conn, $query);
                
                $row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
                $id = $row ? (isset($row['lesson_id']) ? $row['lesson_id'] : (isset($row['id']) ? $row['id'] : 0)) : 0;
                
                $description = ($row && !empty($row['description'])) ? $row['description'] : 'تعلم إشارات ' . $lesson_title . ' بطريقة ممتعة وتفاعلية وسهلة.';

                echo '<div class="card" style="background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
                echo '<h3 style="color: #007bff; margin-bottom: 10px;">' . htmlspecialchars($lesson_title) . '</h3>';
                echo '<p style="color: #666; font-size: 0.95rem; margin-bottom: 15px; line-height: 1.6;">' . htmlspecialchars($description) . '</p>';
                
                if ($id > 0) {
                    echo '<a href="lesson_details.php?id=' . $id . '" class="card-btn" style="display: inline-block; padding: 8px 16px; background: #007bff; color: #fff; text-decoration: none; border-radius: 6px;">عرض الدرس</a>';
                } else {
                    echo '<a href="lesson_details.php?title=' . urlencode($lesson_title) . '" class="card-btn" style="display: inline-block; padding: 8px 16px; background: #007bff; color: #fff; text-decoration: none; border-radius: 6px;">عرض الدرس</a>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </main>
</body>
</html>