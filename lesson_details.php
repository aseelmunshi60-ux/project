<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title_param = isset($_GET['title']) ? trim($_GET['title']) : '';

$lesson = null;

// البحث باستخدام lesson_id فقط لتجنب أي أخطاء في أسماء الأعمدة
if ($id > 0) {
    $query = "SELECT * FROM lesson WHERE lesson_id = $id LIMIT 1";
    $result = @mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $lesson = mysqli_fetch_assoc($result);
    }
} 

// البحث بالعنوان إذا وُجد
if (!$lesson && !empty($title_param)) {
    $safe_title = mysqli_real_escape_string($conn, $title_param);
    $query = "SELECT * FROM lesson WHERE title = '$safe_title' LIMIT 1";
    $result = @mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $lesson = mysqli_fetch_assoc($result);
    }
}

// إذا لم يوجد في القاعدة، ننشئ له محتوى افتراضي مؤقت لضمان فتح الصفحة بسلاسة
if (!$lesson) {
    $lesson = [
        'title' => !empty($title_param) ? $title_param : 'تفاصيل الدرس',
        'description' => 'محتوى تعليمي تفاعلي لتعلم لغة الإشارة بكل سهولة ويسر.'
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - منصة وصلة التعليمية</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .lesson-content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-top: 25px;
        }
        .sign-item {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.2s;
        }
        .sign-item:hover {
            border-color: #007bff;
            transform: translateY(-3px);
            background: #fff;
        }
        .sign-symbol {
            font-size: 2.5rem;
            margin-bottom: 8px;
            display: block;
        }
        .sign-label {
            font-weight: bold;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .sign-hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
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

    <main class="main-container" style="padding: 30px 20px; max-width: 950px; margin: 0 auto;">
        <div class="card" style="padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h1 style="color: #2c3e50; margin: 0;"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <a href="lesson.php" class="card-btn" style="width: auto; padding: 8px 18px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 6px;">العودة للدروس</a>
            </div>
            
            <p style="font-size: 1.1rem; color: #444; line-height: 1.8; background: #eef6ff; padding: 15px; border-radius: 8px; border-right: 4px solid #007bff;">
                <?php echo htmlspecialchars(isset($lesson['description']) ? $lesson['description'] : 'محتوى تعليمي مميز.'); ?>
            </p>

            <h3 style="margin-top: 25px; color: #2c3e50;">محتوى الدرس والتدريبات:</h3>
            <div class="lesson-content-grid">
                <?php
                $title = trim($lesson['title']);
                
                if ($title === 'الحروف') {
                    $items = [
                        ['symbol' => '☝️', 'label' => 'حرف الألف (أ)', 'hint' => 'رفع السبابة للأعلى مع ثني باقي الأصابع'],
                        ['symbol' => '✋', 'label' => 'حرف الباء (ب)', 'hint' => 'فرد الكف للأمام مع ضم الإبهام'],
                        ['symbol' => '✌️', 'label' => 'حرف التاء (ت)', 'hint' => 'رفع السبابة والوسطى بشكل متباعد'],
                        ['symbol' => '🤟', 'label' => 'حرف الثاء (ث)', 'hint' => 'رفع ثلاث أصابع للأعلى'],
                        ['symbol' => '🤏', 'label' => 'حرف الجيم (ج)', 'hint' => 'تقويس الأصابع بشكل نصف دائري'],
                        ['symbol' => '✊', 'label' => 'حرف الحاء (ح)', 'hint' => 'قبض اليد مع توجيه الكف للأمام']
                    ];
                } elseif ($title === 'الأرقام') {
                    $items = [
                        ['symbol' => '☝️', 'label' => 'الرقم ١', 'hint' => 'إصبع السبابة مرفوع'],
                        ['symbol' => '✌️', 'label' => 'الرقم ٢', 'hint' => 'السبابة والوسطى مرفوعتان'],
                        ['symbol' => '🤟', 'label' => 'الرقم ٣', 'hint' => 'ثلاثة أصابع مرفوعة'],
                        ['symbol' => '🖖', 'label' => 'الرقم ٤', 'hint' => 'أربعة أصابع مرفوعة دون الإبهام'],
                        ['symbol' => '🖐️', 'label' => 'الرقم ٥', 'hint' => 'فتح جميع أصابع اليد الخمسة']
                    ];
                } elseif ($title === 'الألوان') {
                    $items = [
                        ['symbol' => '🔴', 'label' => 'لون أحمر', 'hint' => 'تمرير الإصبع على الشفاه مرتين'],
                        ['symbol' => '🔵', 'label' => 'لون أزرق', 'hint' => 'تحريك اليد بحركة تشير للسماء'],
                        ['symbol' => '🟢', 'label' => 'لون أخضر', 'hint' => 'هز اليد تدريجياً للدلالة على الطبيعة'],
                        ['symbol' => '🟡', 'label' => 'لون أصفر', 'hint' => 'حركة دائرية تدل على الشمس']
                    ];
                } elseif ($title === 'المشاعر') {
                    $items = [
                        ['symbol' => '😊', 'label' => 'سعادة / فرح', 'hint' => 'رسم ابتسامة باليدين على الوجه'],
                        ['symbol' => '😢', 'label' => 'حزن', 'hint' => 'تمرير الأصابع للأسفل بمحاذاة الخد كنزول الدموع'],
                        ['symbol' => '😡', 'label' => 'غضب', 'hint' => 'قبض اليدين وتقطيب الحاجبين بقوة']
                    ];
                } elseif ($title === 'أفراد العائلة') {
                    $items = [
                        ['symbol' => '👨', 'label' => 'أب', 'hint' => 'وضع اليد بالقرب من الجبين'],
                        ['symbol' => '👩', 'label' => 'أم', 'hint' => 'لمس الخد باليد بقبضة خفيفة'],
                        ['symbol' => '👦', 'label' => 'أخ', 'hint' => 'ضم السبابتين معاً للدلالة على الأخوة'],
                        ['symbol' => '👧', 'label' => 'أخت', 'hint' => 'إشارة تدل على الأخت بقرب الخد']
                    ];
                } elseif ($title === 'الكلمات الأساسية') {
                    $items = [
                        ['symbol' => '✔️', 'label' => 'نعم', 'hint' => 'تحريك القبضة للأعلى والأسفل برأس الإيجاب'],
                        ['symbol' => '❌', 'label' => 'لا', 'hint' => 'تلويح اليد أفقياً للرفض'],
                        ['symbol' => '❓', 'label' => 'لماذا / كيف', 'hint' => 'فتح الكفين للأمام باستفهام']
                    ];
                } else {
                    $items = [
                        ['symbol' => '📖', 'label' => $title, 'hint' => 'اقرأ الوصف أعلاه وتدرب على إشارات هذا الدرس بعناية']
                    ];
                }

                foreach ($items as $item) {
                    echo '<div class="sign-item">';
                    echo '<span class="sign-symbol">' . $item['symbol'] . '</span>';
                    echo '<div class="sign-label">' . $item['label'] . '</div>';
                    echo '<div class="sign-hint">' . $item['hint'] . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>