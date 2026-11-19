<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// 1. الدروس الـ 6 الأساسية والثابتة
$lessons = [
    [
        'id' => 1,
        'title' => 'الحروف',
        'description' => 'تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.',
        'icon' => '☝️'
    ],
    [
        'id' => 2,
        'title' => 'الأرقام',
        'description' => 'اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.',
        'icon' => '👉'
    ],
    [
        'id' => 3,
        'title' => 'الكلمات الأساسية',
        'description' => 'مجموعة مختارة من الكلمات اليومية الشائعة (نعم، لا، لماذا/كيف) للتواصل السريع والسهل.',
        'icon' => '👌'
    ],
    [
        'id' => 4,
        'title' => 'الألوان',
        'description' => 'تعلم الإشارات الخاصة بالألوان الأساسية وكيفية التعبير عنها بوضوح وسهولة.',
        'icon' => '🤏'
    ],
    [
        'id' => 5,
        'title' => 'المشاعر',
        'description' => 'اكتشاف طريقة التعبير عن السعادة والحزن والغضب بلغة الإشارة وتعابير الوجه.',
        'icon' => '😊'
    ],
    [
        'id' => 6,
        'title' => 'أفراد العائلة',
        'description' => 'تعلم إشارات أفراد الأسرة (الأب، الأم، الأخ، الأخت) لتعزيز التواصل العائلي.',
        'icon' => '👨'
    ]
];

// 2. دمج الدروس الموجودة في قاعدة البيانات مع الدروس الـ 6 بأمان
if (isset($conn)) {
    try {
        $res = @mysqli_query($conn, "SELECT * FROM lesson");
        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $db_title = trim($row['title'] ?? '');
                if ($db_title === '') {
                    continue;
                }

                $exists = false;
                foreach ($lessons as $l) {
                    if ($l['title'] === $db_title) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $raw_desc = $row['content'] ?? $row['description'] ?? 'محتوى تعليمي تفاعلي.';
                    // إخفاء سطر رابط الفيديو إن وجد داخل content ليبقى الوصف نظيفاً في القائمة
                    $clean_desc = trim(preg_replace('/\s*رابط الفيديو:\s*https?:\/\/\S+/iu', '', $raw_desc));
                    if ($clean_desc === '') {
                        $clean_desc = 'محتوى تعليمي تفاعلي.';
                    }

                    $lessons[] = [
                        'id' => $row['lesson_id'] ?? $row['id'] ?? 99,
                        'title' => $db_title,
                        'description' => $clean_desc,
                        'icon' => $row['icon'] ?? '📚'
                    ];
                }
            }
        }
    } catch (Throwable $e) {
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>الدروس المتاحة - منصة وصلة التعليمية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Tajawal', sans-serif; background: #f8fafc; color: #1e293b; min-height: 100vh; }

    .main-header { background-color: #1e293b; color: #ffffff; padding: 15px 35px; width: 100%; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
    .nav-container { width: 100%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .logo-title { font-size: 20px; font-weight: 800; color: #ffffff; }
    .nav-links { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
    .nav-links a { color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; padding: 6px 12px; border-radius: 8px; transition: all 0.2s ease; }
    .nav-links a:hover, .nav-links a.active { color: #ffffff; background-color: #2563eb; }

    .container { width: 100%; max-width: 1100px; margin: 0 auto; padding: 40px 20px; }
    .page-header { margin-bottom: 25px; text-align: right; }
    .page-header h1 { font-size: 24px; font-weight: 800; color: #0f172a; }

    .lessons-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 22px; }
    .lesson-card { background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0; color: #1e293b; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.25s, box-shadow 0.25s; }
    .lesson-card:hover { transform: translateY(-4px); border-color: #2563eb; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.08); }
    .lesson-info h2 { font-size: 19px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
    .lesson-info p { font-size: 14.5px; color: #475569; font-weight: 500; line-height: 1.7; margin-bottom: 18px; }
    .lesson-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    .lesson-icon { font-size: 32px; }
    .card-btn { background: #2563eb; color: #ffffff; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; display: block; text-align: center; width: 100%; }
    .card-btn:hover { background: #1d4ed8; }
</style>
</head>
<body>

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

    <main class="container">
        <div class="page-header">
            <h1>الدروس المتاحة</h1>
        </div>

        <div class="lessons-grid">
            <?php foreach ($lessons as $l): ?>
                <div class="lesson-card">
                    <div class="lesson-info">
                        <h2><?= e($l['title']) ?></h2>
                        <p><?= e($l['description']) ?></p>
                    </div>
                    <div class="lesson-footer">
                        <a href="lesson_details.php?id=<?= (int)$l['id'] ?>&title=<?= urlencode($l['title']) ?>" class="card-btn">عرض الدرس</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>