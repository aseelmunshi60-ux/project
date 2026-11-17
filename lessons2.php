<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// قائمة الدروس الثابتة والأساسية للمنصة
$lessons = [
    [
        'id' => 1,
        'title' => 'الحروف',
        'desc' => 'تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.',
        'icon' => '☝️',
    ],
    [
        'id' => 2,
        'title' => 'الأرقام',
        'desc' => 'اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.',
        'icon' => '👉',
    ],
    [
        'id' => 3,
        'title' => 'الكلمات الأساسية',
        'desc' => 'مجموعة مختارة من الكلمات اليومية الشائعة (نعم، لا، لماذا/كيف) للتواصل السريع والسهل.',
        'icon' => '👌',
    ],
    [
        'id' => 4,
        'title' => 'الألوان',
        'desc' => 'تعلم الإشارات الخاصة بالألوان الأساسية وكيفية التعبير عنها بوضوح وسهولة.',
        'icon' => '🤏',
    ],
    [
        'id' => 5,
        'title' => 'المشاعر',
        'desc' => 'اكتشاف طريقة التعبير عن السعادة والحزن والغضب بلغة الإشارة وتعابير الوجه.',
        'icon' => '😊',
    ],
    [
        'id' => 6,
        'title' => 'أفراد العائلة',
        'desc' => 'تعلم إشارات أفراد الأسرة (الأب، الأم، الأخ، الأخت) لتعزيز التواصل العائلي.',
        'icon' => '👨',
    ]
];

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>قائمة الدروس - منصة وصلة التعليمية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Tajawal', sans-serif;
        min-height: 100vh;
        background-color: #f8fafc;
        color: #1e293b;
    }

    /* شريط التنقل العلوي الموحد (اسم المنصة يمين، والروابط يسار) */
    .navbar {
        width: 100%;
        background-color: #1e293b;
        color: #ffffff;
        padding: 15px 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar .brand {
        font-size: 20px;
        font-weight: 800;
        color: #ffffff;
        text-decoration: none;
    }

    .navbar nav {
        display: flex;
        gap: 18px;
        align-items: center;
        flex-wrap: wrap;
    }

    .navbar nav a {
        text-decoration: none;
        color: #cbd5e1;
        font-weight: 600;
        font-size: 15px;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .navbar nav a:hover,
    .navbar nav a.active {
        color: #ffffff;
        background-color: #2563eb;
    }

    .navbar .btn-login,
    .navbar .btn-logout {
        background-color: #2563eb;
        color: #ffffff !important;
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 700;
    }

    /* حاوية المحتوى الرئيسي */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .page-header {
        margin-bottom: 25px;
        text-align: right;
    }

    .page-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
    }

    /* شبكة الدروس */
    .lessons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 22px;
    }

    .lesson-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #1e293b;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .lesson-card:hover {
        transform: translateY(-4px);
        border-color: #2563eb;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.08);
    }

    .lesson-info h2 {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
    }

    .lesson-info p {
        font-size: 14.5px;
        color: #475569;
        font-weight: 500;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .lesson-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 15px;
    }

    .lesson-icon {
        font-size: 32px;
    }

    .card-btn {
        background: #2563eb;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }
</style>
</head>
<body>

    <!-- شريط التنقل العلوي الموحد -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="index.php">الرئيسية</a>
            <a href="lessons2.php" class="active">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="Login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="container">
        
        <div class="page-header">
            <h1>قائمة الدروس التعليمية المتاحة</h1>
        </div>

        <!-- شبكة الدروس -->
        <div class="lessons-grid">
            <?php foreach ($lessons as $l): ?>
                <a class="lesson-card" href="lesson_details.php?id=<?= (int)$l['id'] ?>&title=<?= urlencode($l['title']) ?>">
                    <div class="lesson-info">
                        <h2><?= e($l['title']) ?></h2>
                        <p><?= e($l['desc']) ?></p>
                    </div>
                    <div class="lesson-footer">
                        <span class="lesson-icon"><?= $l['icon'] ?></span>
                        <span class="card-btn">عرض الدرس</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </main>

</body>
</html>