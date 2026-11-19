<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

// منع تكرار الجلسة واستدعاء الاتصال بقاعدة البيانات
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// التحقق الصارم من أن المستخدم معلم فقط (الطالب لن يظهر له زر أو نموذج الإضافة أو الحذف)
$session_role_candidates = [
    $_SESSION['role'] ?? null,
    $_SESSION['user_role'] ?? null,
    $_SESSION['account_type'] ?? null,
    $_SESSION['user_type'] ?? null,
    $_SESSION['type'] ?? null,
];

$detected_role = '';
foreach ($session_role_candidates as $candidate) {
    if (is_string($candidate) && trim($candidate) !== '') {
        $detected_role = strtolower(trim($candidate));
        break;
    }
}

$is_teacher = in_array($detected_role, ['teacher', 'معلم', 'instructor'], true);

if (!isset($_SESSION['added_games']) || !is_array($_SESSION['added_games'])) {
    $_SESSION['added_games'] = [];
}

$success_msg = '';
$error_msg = '';

$default_number_questions = [
    ['mode' => 'numbers', 'letter' => '1', 'hand' => '☝️', 'correctSign' => '☝️', 'options' => ['☝️', '✌️', '✋'], 'match' => 90, 'scoreInc' => 10],
    ['mode' => 'numbers', 'letter' => '2', 'hand' => '✌️', 'correctSign' => '✌️', 'options' => ['✌️', '☝️', '🤟'], 'match' => 85, 'scoreInc' => 15],
    ['mode' => 'numbers', 'letter' => '3', 'hand' => '🤟', 'correctSign' => '🤟', 'options' => ['🤟', '✋', '☝️'], 'match' => 95, 'scoreInc' => 15],
    ['mode' => 'numbers', 'letter' => '5', 'hand' => '✋', 'correctSign' => '✋', 'options' => ['✋', '✌️', '☝️'], 'match' => 90, 'scoreInc' => 20]
];

// معالجة إضافة لعبة جديدة مع عدة أسئلة من المعلم فقط
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_new_game') {
    if (!$is_teacher) {
        $error_msg = 'عذراً، إضافة الألعاب متاحة للمعلم فقط.';
    } else {
        $game_name = trim($_POST['game_name'] ?? '');
        $game_mode = trim($_POST['game_mode'] ?? 'numbers'); // numbers = لعبة الأرقام | choice = اختر الإشارة الصحيحة | mimic = قلّد الإشارة
        $points = isset($_POST['points']) ? max(5, min(100, (int)$_POST['points'])) : 15;

        $q_letters = $_POST['q_letter'] ?? [];
        $q_hands   = $_POST['q_hand'] ?? [];
        $q_wrong1  = $_POST['q_wrong1'] ?? [];
        $q_wrong2  = $_POST['q_wrong2'] ?? [];

        $built_questions = [];
        $total_rows = max(count((array)$q_letters), count((array)$q_hands));

        for ($i = 0; $i < $total_rows; $i++) {
            $item_text = trim((string)($q_letters[$i] ?? ''));
            $correct_s = trim((string)($q_hands[$i] ?? '☝️'));
            $w1        = trim((string)($q_wrong1[$i] ?? '✋'));
            $w2        = trim((string)($q_wrong2[$i] ?? '✌️'));

            if ($item_text === '') {
                continue;
            }

            $built_questions[] = [
                'mode' => $game_mode,
                'letter' => $item_text,
                'hand' => $correct_s !== '' ? $correct_s : '☝️',
                'correctSign' => $correct_s !== '' ? $correct_s : '☝️',
                'options' => [
                    $correct_s !== '' ? $correct_s : '☝️',
                    $w1 !== '' ? $w1 : '✋',
                    $w2 !== '' ? $w2 : '✌️'
                ],
                'match' => 90,
                'scoreInc' => $points
            ];
        }

        if ($game_name === '') {
            $error_msg = 'يرجى إدخال اسم اللعبة.';
        } elseif ($game_mode === 'numbers' && empty($built_questions)) {
            $built_questions = $default_number_questions;
        } elseif (empty($built_questions)) {
            $error_msg = 'يرجى إدخال سؤال واحد على الأقل.';
        }

        if ($error_msg === '') {
            $_SESSION['added_games'][] = [
                'id' => 'custom_' . uniqid(),
                'title' => $game_name,
                'mode' => $game_mode,
                'points' => $points,
                'questions' => $built_questions
            ];
            $success_msg = 'تمت إضافة لعبة "' . $game_name . '" بعدد (' . count($built_questions) . ') أسئلة بنجاح!';
        }
    }
}

// تحديد اللعبة النشطة حالياً (الافتراضية: قلّد الإشارة، أو لعبة مضافة يختارها المستخدم)
$active_game_key = $_GET['play'] ?? 'default';
$active_game = [
    'id' => 'default',
    'title' => 'قلّد الإشارة',
    'mode' => 'mimic',
    'points' => 10
];

foreach ($_SESSION['added_games'] as $g) {
    if ($g['id'] === $active_game_key) {
        $active_game = $g;
        break;
    }
}

// معالجة حذف لعبة مضافة من المعلم فقط
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_game') {
    if (!$is_teacher) {
        $error_msg = 'عذراً، حذف الألعاب متاح للمعلم فقط.';
    } else {
        $delete_id = trim((string)($_POST['delete_game_id'] ?? ''));
        $found = false;
        $updated_games = [];

        foreach ($_SESSION['added_games'] as $g) {
            if ((string)($g['id'] ?? '') === $delete_id) {
                $found = true;
                continue;
            }
            $updated_games[] = $g;
        }

        $_SESSION['added_games'] = $updated_games;

        if ($found) {
            $success_msg = 'تم حذف اللعبة بنجاح.';
            if ($active_game_key === $delete_id) {
                $active_game_key = 'default';
                $active_game = [
                    'id' => 'default',
                    'title' => 'قلّد الإشارة',
                    'mode' => 'mimic',
                    'points' => 10
                ];
            }
        } else {
            $error_msg = 'تعذر العثور على اللعبة المحددة لحذفها.';
        }
    }
}

$mimic_levels = [
    ['mode' => 'mimic', 'letter' => 'ا', 'hand' => '☝️', 'img' => 'images/alef.png', 'match' => 85, 'scoreInc' => 10],
    ['mode' => 'mimic', 'letter' => 'ب', 'hand' => '✋', 'img' => 'images/baa.png',  'match' => 90, 'scoreInc' => 15],
    ['mode' => 'mimic', 'letter' => 'ت', 'hand' => '✌️', 'img' => 'images/taa.png',  'match' => 75, 'scoreInc' => 10],
    ['mode' => 'mimic', 'letter' => 'ث', 'hand' => '🤟', 'img' => 'images/thaa.png', 'match' => 95, 'scoreInc' => 20],
    ['mode' => 'mimic', 'letter' => 'ج', 'hand' => '🤏', 'img' => 'images/jeem.png', 'match' => 80, 'scoreInc' => 15]
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لعبة <?= e($active_game['title']) ?> - منصة وصلة التعليمية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
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

    /* شريط التنقل العلوي الموحد */
    .navbar {
        width: 100%;
        background-color: #111827;
        color: #ffffff;
        padding: 14px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar .brand {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        text-decoration: none;
    }

    .navbar nav {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }

    .navbar nav a {
        text-decoration: none;
        color: #cbd5e1;
        font-weight: 500;
        font-size: 15px;
        transition: color 0.2s;
    }

    .navbar nav a:hover,
    .navbar nav a.active {
        color: #ffffff;
        font-weight: 700;
    }

    .navbar .btn-login,
    .navbar .btn-logout {
        background-color: #2563eb;
        color: #ffffff !important;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 500;
    }

    /* حاوية المحتوى الرئيسي */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 36px 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font: 700 14px 'Tajawal';
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        background: #ffffff;
        color: #1e293b;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: background 0.2s;
    }

    .btn:hover {
        background: #f1f5f9;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-danger {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .btn-soft {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .btn-soft:hover {
        background: #dbeafe;
    }

    /* شريط التنقل بين اللعبة الأساسية والألعاب المضافة */
    .games-switcher {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 22px;
        align-items: center;
    }

    .game-tab {
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        background: #e2e8f0;
        color: #334155;
        transition: all 0.2s;
    }

    .game-tab.active,
    .game-tab:hover {
        background: #2563eb;
        color: #ffffff;
    }

    .del-mini-btn {
        border: none;
        background: #fee2e2;
        color: #b91c1c;
        font-weight: 800;
        border-radius: 999px;
        width: 26px;
        height: 26px;
        cursor: pointer;
        font-family: 'Tajawal', sans-serif;
    }

    .del-mini-btn:hover {
        background: #fecaca;
    }

    /* نموذج إضافة لعبة جديدة للمعلم فقط */
    .add-game-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        display: none;
    }

    .add-game-box.open {
        display: block;
    }

    .add-game-box h2 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 14px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 14px;
    }

    .question-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .question-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-weight: 800;
        color: #1e293b;
        font-size: 14px;
    }

    .form-field label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-field input,
    .form-field select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Tajawal', sans-serif;
        font-size: 14px;
        color: #1e293b;
        background: #ffffff;
    }

    .alert-success {
        background: #dcfce7;
        color: #15803d;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-weight: 700;
        border-right: 4px solid #16a34a;
    }

    .alert-error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-weight: 700;
        border-right: 4px solid #dc2626;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .panel {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .sign {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 290px;
    }

    .question-counter {
        font-size: 13px;
        font-weight: 700;
        color: #2563eb;
        background: #eff6ff;
        padding: 4px 12px;
        border-radius: 999px;
        align-self: flex-start;
    }

    .sign-stage {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 10px 0;
    }

    /* صورة اليد فوق الحرف أو الرقم */
    .hand-sign-box {
        width: 112px;
        height: 112px;
        border-radius: 18px;
        background: #eff6ff;
        border: 2px solid #bfdbfe;
        display: grid;
        place-items: center;
        overflow: hidden;
    }

    .hand-sign-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 6px;
    }

    .hand-sign-emoji {
        font-size: 62px;
        line-height: 1;
    }

    .sign .letter {
        display: grid;
        place-items: center;
        font-size: 76px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.05;
    }

    /* خيارات اللعبة التفاعلية (الأرقام أو اختر الإشارة) */
    .choice-options {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 8px;
    }

    .choice-btn {
        font-size: 38px;
        padding: 10px 20px;
        border-radius: 12px;
        border: 2px solid #cbd5e1;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .choice-btn:hover {
        border-color: #2563eb;
        background: #eff6ff;
        transform: translateY(-2px);
    }

    .feedback {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #e2e8f0;
        padding-top: 16px;
    }

    .feedback small {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .pct {
        font-size: 22px;
        font-weight: 800;
        color: #16a34a;
    }

    .stars {
        color: #eab308;
        font-size: 20px;
        letter-spacing: 2px;
    }

    .stars .off {
        color: #cbd5e1;
    }

    .answer {
        display: flex;
        gap: 10px;
    }

    .circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        font-size: 18px;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.1s;
    }

    .circle:active {
        transform: scale(0.95);
    }

    .circle.ok {
        background: #dcfce7;
        color: #15803d;
    }

    .circle.no {
        background: #fee2e2;
        color: #b91c1c;
    }

    .trophy {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .trophy .cup {
        font-size: 70px;
        line-height: 1;
        margin-bottom: 12px;
    }

    .trophy h2 {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        background: #ffffff;
        padding: 6px 20px;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 14px 20px;
            flex-direction: column;
            gap: 12px;
        }
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="index.php">الرئيسية</a>
            <a href="lesson.php">الدروس</a>
            <a href="game.php" class="active">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- محتوى الصفحة التفاعلية -->
    <main class="container">

        <div class="page-header">
            <h1 id="game-title">لعبة: <?= e($active_game['title']) ?></h1>
            <div class="actions">
                <?php if ($is_teacher): ?>
                    <button type="button" class="btn btn-primary" onclick="toggleAddGameForm()">+ إضافة لعبة جديدة</button>
                    <?php if ($active_game['id'] !== 'default'): ?>
                        <form method="POST" action="game.php" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه اللعبة؟');">
                            <input type="hidden" name="action" value="delete_game">
                            <input type="hidden" name="delete_game_id" value="<?= e((string)$active_game['id']) ?>">
                            <button type="submit" class="btn btn-danger">🗑️ حذف اللعبة</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <button type="button" class="btn" onclick="resetGame()">🔄 إعادة اللعبة</button>
            </div>
        </div>

        <?php if (!empty($_SESSION['added_games'])): ?>
            <div class="games-switcher">
                <a href="game.php?play=default" class="game-tab <?= $active_game['id'] === 'default' ? 'active' : '' ?>">قلّد الإشارة</a>
                <?php foreach ($_SESSION['added_games'] as $g): ?>
                    <div style="display: inline-flex; align-items: center; gap: 5px;">
                        <a href="game.php?play=<?= urlencode((string)$g['id']) ?>" class="game-tab <?= $active_game['id'] === $g['id'] ? 'active' : '' ?>">
                            <?= e($g['title']) ?>
                        </a>
                        <?php if ($is_teacher): ?>
                            <form method="POST" action="game.php" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف لعبة <?= e($g['title']) ?>؟');">
                                <input type="hidden" name="action" value="delete_game">
                                <input type="hidden" name="delete_game_id" value="<?= e((string)$g['id']) ?>">
                                <button type="submit" class="del-mini-btn" title="حذف اللعبة">✕</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg !== ''): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if ($error_msg !== ''): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <?php if ($is_teacher): ?>
            <!-- نموذج إضافة لعبة جديدة بعدة أسئلة للمعلم فقط -->
            <div class="add-game-box <?= $error_msg !== '' ? 'open' : '' ?>" id="addGameBox">
                <h2>إضافة لعبة جديدة للمعلم</h2>
                <form method="POST" action="game.php">
                    <input type="hidden" name="action" value="add_new_game">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="game_name">اسم اللعبة</label>
                            <input type="text" id="game_name" name="game_name" required placeholder="مثال: لعبة الأرقام">
                        </div>
                        <div class="form-field">
                            <label for="game_mode">نوع اللعبة</label>
                            <select id="game_mode" name="game_mode" onchange="toggleWrongSigns()">
                                <option value="numbers">لعبة الأرقام (اختر إشارة الرقم)</option>
                                <option value="choice">اختر الإشارة الصحيحة (حروف/كلمات)</option>
                                <option value="mimic">قلّد الإشارة</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="points">نقاط كل سؤال</label>
                            <input type="number" id="points" name="points" min="5" max="100" value="15">
                        </div>
                    </div>

                    <div id="questionsList">
                        <div class="question-card">
                            <div class="question-card-header">
                                <span class="q-label">السؤال 1</span>
                            </div>
                            <div class="form-grid" style="margin-bottom: 0;">
                                <div class="form-field">
                                    <label>الرقم أو الحرف المطلوب</label>
                                    <input type="text" name="q_letter[]" placeholder="مثال: 1">
                                </div>
                                <div class="form-field">
                                    <label>الإشارة الصحيحة</label>
                                    <select name="q_hand[]">
                                        <option value="☝️">☝️ إشارة 1 / سبابة</option>
                                        <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                                        <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                                        <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                                        <option value="🤏">🤏 إشارة متقاربة</option>
                                        <option value="✊">✊ قبضة يد</option>
                                    </select>
                                </div>
                                <div class="form-field wrong-sign-field">
                                    <label>خيار إشارة آخر 1</label>
                                    <select name="q_wrong1[]">
                                        <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                                        <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                                        <option value="☝️">☝️ إشارة 1 / سبابة</option>
                                        <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                                    </select>
                                </div>
                                <div class="form-field wrong-sign-field">
                                    <label>خيار إشارة آخر 2</label>
                                    <select name="q_wrong2[]">
                                        <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                                        <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                                        <option value="☝️">☝️ إشارة 1 / سبابة</option>
                                        <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                        <button type="button" class="btn btn-soft" onclick="addQuestionRow()">+ إضافة سؤال آخر</button>
                        <button type="submit" class="btn btn-primary">حفظ اللعبة</button>
                        <button type="button" class="btn" onclick="toggleAddGameForm()">إغلاق</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- لوحة الإشارة والتفاعل -->
            <section class="panel sign">
                <span class="question-counter" id="question-counter">سؤال 1</span>
                <div class="sign-stage">
                    <div class="hand-sign-box" id="hand-sign-box">
                        <span class="hand-sign-emoji" id="hand-sign-emoji">☝️</span>
                    </div>
                    <div class="letter" id="letter-display">ا</div>
                    <div class="choice-options" id="choice-options" style="display:none;"></div>
                </div>
                <div class="feedback">
                    <div>
                        <small>نتيجة التطابق</small>
                        <span class="pct" id="pct-display">85%</span>
                        <span class="stars" id="stars-display">★★★</span>
                    </div>
                    <div class="answer" id="mimic-answer-buttons">
                        <button type="button" class="circle ok" onclick="answerGame(true)" aria-label="صح">✓</button>
                        <button type="button" class="circle no" onclick="answerGame(false)" aria-label="خطأ">✕</button>
                    </div>
                </div>
            </section>

            <!-- لوحة النقاط والكأس -->
            <section class="panel trophy">
                <div class="cup">🏆</div>
                <h2 id="score-display">Score 10</h2>
            </section>
        </div>

    </main>

    <script>
        const activeGame = <?= json_encode($active_game, JSON_UNESCAPED_UNICODE) ?>;
        const mimicLevels = <?= json_encode($mimic_levels, JSON_UNESCAPED_UNICODE) ?>;

        const levels = ((activeGame.mode === 'choice' || activeGame.mode === 'numbers') && Array.isArray(activeGame.questions) && activeGame.questions.length > 0)
            ? activeGame.questions
            : mimicLevels;

        let currentIndex = 0;
        let totalScore = 10;

        function toggleAddGameForm() {
            const box = document.getElementById('addGameBox');
            if (box) {
                box.classList.toggle('open');
            }
        }

        function toggleWrongSigns() {
            const modeEl = document.getElementById('game_mode');
            const fields = document.querySelectorAll('.wrong-sign-field');
            if (!modeEl) return;
            const show = (modeEl.value === 'choice' || modeEl.value === 'numbers');
            fields.forEach(f => {
                f.style.display = show ? 'block' : 'none';
            });
        }

        function refreshQuestionNumbers() {
            const cards = document.querySelectorAll('#questionsList .question-card');
            cards.forEach((card, idx) => {
                const lbl = card.querySelector('.q-label');
                if (lbl) lbl.textContent = 'السؤال ' + (idx + 1);
            });
        }

        function addQuestionRow() {
            const list = document.getElementById('questionsList');
            if (!list) return;

            const card = document.createElement('div');
            card.className = 'question-card';
            card.innerHTML = `
                <div class="question-card-header">
                    <span class="q-label">سؤال جديد</span>
                    <button type="button" class="del-mini-btn" title="حذف السؤال" onclick="this.closest('.question-card').remove(); refreshQuestionNumbers();">✕</button>
                </div>
                <div class="form-grid" style="margin-bottom: 0;">
                    <div class="form-field">
                        <label>الرقم أو الحرف المطلوب</label>
                        <input type="text" name="q_letter[]" placeholder="مثال: 2">
                    </div>
                    <div class="form-field">
                        <label>الإشارة الصحيحة</label>
                        <select name="q_hand[]">
                            <option value="☝️">☝️ إشارة 1 / سبابة</option>
                            <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                            <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                            <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                            <option value="🤏">🤏 إشارة متقاربة</option>
                            <option value="✊">✊ قبضة يد</option>
                        </select>
                    </div>
                    <div class="form-field wrong-sign-field">
                        <label>خيار إشارة آخر 1</label>
                        <select name="q_wrong1[]">
                            <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                            <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                            <option value="☝️">☝️ إشارة 1 / سبابة</option>
                            <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                        </select>
                    </div>
                    <div class="form-field wrong-sign-field">
                        <label>خيار إشارة آخر 2</label>
                        <select name="q_wrong2[]">
                            <option value="✋">✋ إشارة 5 / كف مفتوح</option>
                            <option value="🤟">🤟 إشارة 3 / ثلاثة أصابع</option>
                            <option value="☝️">☝️ إشارة 1 / سبابة</option>
                            <option value="✌️">✌️ إشارة 2 / إصبعان</option>
                        </select>
                    </div>
                </div>
            `;
            list.appendChild(card);
            refreshQuestionNumbers();
            toggleWrongSigns();
        }

        function renderHandAboveLetter(item) {
            const box = document.getElementById('hand-sign-box');
            if (!box) return;

            if (item.mode === 'choice' || item.mode === 'numbers') {
                box.innerHTML = `<span class="hand-sign-emoji">${item.mode === 'numbers' ? '🔢' : '❓'}</span>`;
                return;
            }

            if (item.img) {
                const img = document.createElement('img');
                img.src = item.img;
                img.alt = 'صورة اليد لحرف ' + item.letter;
                img.onload = () => {
                    box.innerHTML = '';
                    box.appendChild(img);
                };
                img.onerror = () => {
                    box.innerHTML = `<span class="hand-sign-emoji">${item.hand || '☝️'}</span>`;
                };
            } else {
                box.innerHTML = `<span class="hand-sign-emoji">${item.hand || '☝️'}</span>`;
            }
        }

        function renderChoiceBoard(item) {
            const optsWrap = document.getElementById('choice-options');
            const mimicBtns = document.getElementById('mimic-answer-buttons');

            if (item.mode === 'choice' || item.mode === 'numbers') {
                if (mimicBtns) mimicBtns.style.display = 'none';
                if (optsWrap) {
                    optsWrap.style.display = 'flex';
                    optsWrap.innerHTML = '';
                    (item.options || []).forEach(sign => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'choice-btn';
                        btn.textContent = sign;
                        btn.onclick = () => answerGame(sign === item.correctSign);
                        optsWrap.appendChild(btn);
                    });
                }
            } else {
                if (optsWrap) optsWrap.style.display = 'none';
                if (mimicBtns) mimicBtns.style.display = 'flex';
            }
        }

        function updateUI() {
            const current = levels[currentIndex];
            renderHandAboveLetter(current);
            renderChoiceBoard(current);
            document.getElementById('question-counter').textContent = `سؤال ${currentIndex + 1} من ${levels.length}`;
            document.getElementById('letter-display').textContent = current.letter;
            document.getElementById('pct-display').textContent = (current.match || 90) + '%';
            document.getElementById('score-display').textContent = 'Score ' + totalScore;

            let starCount = Math.round(((current.match || 90) / 100) * 3);
            let starsHtml = '';
            for (let i = 0; i < 3; i++) {
                starsHtml += `<span class="${i < starCount ? '' : 'off'}">★</span>`;
            }
            document.getElementById('stars-display').innerHTML = starsHtml;
        }

        function answerGame(isCorrect) {
            if (isCorrect) {
                totalScore += Number(levels[currentIndex].scoreInc || 15);
                alert('أحسنت! إجابة صحيحة 🎉');
            } else {
                alert('حاول مرة أخرى! الإشارة غير مطابقة تماماً ❌');
            }

            currentIndex = (currentIndex + 1) % levels.length;
            updateUI();
        }

        function resetGame() {
            currentIndex = 0;
            totalScore = 10;
            updateUI();
            alert('تم إعادة اللعبة من جديد!');
        }

        toggleWrongSigns();
        updateUI();
    </script>

</body>
</html>