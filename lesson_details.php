<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title_param = isset($_GET['title']) ? trim($_GET['title']) : '';

$lesson = null;

// البحث باستخدام lesson_id
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

// محتوى افتراضي مؤقت لضمان فتح الصفحة بسلاسة
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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        /* تنسيق الهيدر بعرض الشاشة الكامل */
        .main-header {
            background-color: #1e293b;
            color: #ffffff;
            padding: 15px 35px;
            width: 100%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .nav-container {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
        }
        .nav-links {
            display: flex;
            gap: 18px;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #ffffff;
            background-color: #2563eb;
        }

        /* تنسيق تفاصيل الدرس */
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
        }
        .lesson-container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .lesson-card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .lesson-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .lesson-header h1 {
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }
        .btn-back {
            background: #f1f5f9;
            color: #334155;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .lesson-desc {
            font-size: 16px;
            color: #334155;
            line-height: 1.8;
            background: #eff6ff;
            padding: 18px 20px;
            border-radius: 12px;
            border-right: 4px solid #2563eb;
            margin-bottom: 30px;
        }
        .section-heading {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 20px;
        }
        .lesson-content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
        }
        .sign-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 22px 16px;
            text-align: center;
            transition: all 0.25s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .sign-item:hover {
            border-color: #2563eb;
            transform: translateY(-4px);
            background: #ffffff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.08);
        }
        .sign-symbol {
            font-size: 46px;
            margin-bottom: 12px;
            display: block;
        }
        .sign-label {
            font-weight: 800;
            color: #0f172a;
            font-size: 17px;
            margin-bottom: 8px;
        }
        .sign-hint {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.6;
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

    <main class="lesson-container">
        <div class="lesson-card-box">
            <div class="lesson-header">
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <a href="lesson.php" class="btn-back">⬅ العودة للدروس</a>
            </div>
            
            <div class="lesson-desc">
                <?php echo htmlspecialchars(isset($lesson['description']) ? $lesson['description'] : 'محتوى تعليمي مميز.'); ?>
            </div>

            <h3 class="section-heading">محتوى الدرس والتدريبات:</h3>
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