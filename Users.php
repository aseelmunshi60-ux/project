<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// التحقق من أن المستخدم الحالي هو "أدمن" (المدير) فقط
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // header("Location: login.php"); exit();
}

if (!function_exists('e')) {
    function e($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// قراءة رسائل الجلسة بعد التحويل ثم تفريغها
$success_msg = (string)($_SESSION['users_flash_success'] ?? '');
$error_msg   = (string)($_SESSION['users_flash_error'] ?? '');
unset($_SESSION['users_flash_success'], $_SESSION['users_flash_error']);

// تهيئة قائمة الإخفاء للدروس في الجلسة
if (!isset($_SESSION['hidden_lessons']) || !is_array($_SESSION['hidden_lessons'])) {
    $_SESSION['hidden_lessons'] = [];
}

// جلب الدروس الـ 6 الأساسية من نفس قائمة lesson.php + أي دروس إضافية من قاعدة البيانات
$all_lessons = [
    ['id' => 1, 'title' => 'الحروف', 'icon' => '☝️'],
    ['id' => 2, 'title' => 'الأرقام', 'icon' => '👉'],
    ['id' => 3, 'title' => 'الكلمات الأساسية', 'icon' => '👌'],
    ['id' => 4, 'title' => 'الألوان', 'icon' => '🤏'],
    ['id' => 5, 'title' => 'المشاعر', 'icon' => '😊'],
    ['id' => 6, 'title' => 'أفراد العائلة', 'icon' => '👨'],
];

if (isset($conn)) {
    try {
        $res = @mysqli_query($conn, "SELECT * FROM lesson");
        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $db_title = trim((string)($row['title'] ?? ''));
                if ($db_title === '') {
                    continue;
                }
                $exists = false;
                foreach ($all_lessons as $l) {
                    if (trim((string)$l['title']) === $db_title) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $all_lessons[] = [
                        'id' => $row['lesson_id'] ?? $row['id'] ?? 99,
                        'title' => $db_title,
                        'icon' => $row['icon'] ?? '📚'
                    ];
                }
            }
        }
    } catch (Throwable $e) {
    }
}

// تهيئة بيانات الألعاب المضافة في الجلسة للتحكم بها
if (!isset($_SESSION['added_games']) || !is_array($_SESSION['added_games'])) {
    $_SESSION['added_games'] = [];
}

// قيم افتراضية آمنة للمستخدمين
$default_users = [
    ['id' => 1, 'name' => 'عبدالله', 'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 2, 'name' => 'احمد',    'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 3, 'name' => 'محمد',    'role' => 'ولي امر', 'status' => 'موقوف'],
    ['id' => 4, 'name' => 'علي',     'role' => 'معلم', 'status' => 'نشط'],
    ['id' => 5, 'name' => 'حسن',     'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 6, 'name' => 'سعد',     'role' => 'ولي امر', 'status' => 'نشط'],
];

if (!isset($_SESSION['admin_users']) || !is_array($_SESSION['admin_users'])) {
    $_SESSION['admin_users'] = $default_users;

    if (isset($conn)) {
        try {
            $result_users = @mysqli_query($conn, "SELECT * FROM user");
            if ($result_users && mysqli_num_rows($result_users) > 0) {
                $db_users = [];
                while ($row = mysqli_fetch_assoc($result_users)) {
                    $db_users[] = [
                        'id' => (int)($row['id'] ?? $row['user_id'] ?? 1),
                        'name' => (string)($row['name'] ?? $row['username'] ?? 'مستخدم'),
                        'role' => (string)($row['role'] ?? 'طالب'),
                        'status' => (string)($row['status'] ?? 'نشط'),
                    ];
                }
                if (!empty($db_users)) {
                    $_SESSION['admin_users'] = $db_users;
                }
            }
        } catch (Throwable $e) {}
    }
}

// معالجة إجراءات POST ثم التحويل الفوري (PRG) لتجنب رسالة إعادة إرسال النموذج عند الرجوع
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $flash_ok  = '';
    $flash_err = '';

    // 1. تبديل حالة المستخدم (نشط <-> موقوف)
    if ($action === 'toggle_user_status') {
        $uid = (int)($_POST['user_id'] ?? 0);
        foreach ($_SESSION['admin_users'] as &$u) {
            if ((int)$u['id'] === $uid) {
                $is_now_active = ($u['status'] === 'نشط' || $u['status'] === 'active');
                $u['status'] = $is_now_active ? 'موقوف' : 'نشط';

                if (isset($conn) && $uid > 0) {
                    try {
                        $stmt = @$conn->prepare("UPDATE user SET status = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("si", $u['status'], $uid);
                            @$stmt->execute();
                            $stmt->close();
                        }
                    } catch (Throwable $e) {}
                }

                $flash_ok = 'تم تحديث حالة المستخدم "' . $u['name'] . '" إلى (' . $u['status'] . ').';
                break;
            }
        }
        unset($u);
    }

    // 2. تغيير حالة الدرس إلى (إظهار أو إخفاء) بالاعتماد على عنوان الدرس مباشرة
    elseif ($action === 'set_lesson_visibility') {
        $l_title = trim((string)($_POST['lesson_title'] ?? ''));
        $state   = trim((string)($_POST['visibility_state'] ?? 'show'));

        if ($l_title !== '') {
            $_SESSION['hidden_lessons'] = array_values(array_filter(
                $_SESSION['hidden_lessons'],
                fn($item) => trim((string)$item) !== $l_title
            ));

            if ($state === 'hide') {
                $_SESSION['hidden_lessons'][] = $l_title;
                $flash_ok = 'تم إخفاء درس "' . $l_title . '" من قائمة الدروس.';
            } else {
                $flash_ok = 'تم إظهار درس "' . $l_title . '" في قائمة الدروس.';
            }
        }
    }

    // 3. إضافة لعبة جديدة من لوحة التحكم
    elseif ($action === 'add_admin_game') {
        $g_title = trim((string)($_POST['game_title'] ?? ''));
        $g_mode  = trim((string)($_POST['game_mode'] ?? 'numbers'));
        $g_pts   = isset($_POST['game_points']) ? max(5, min(100, (int)$_POST['game_points'])) : 15;

        if ($g_title === '') {
            $flash_err = 'يرجى إدخال اسم اللعبة.';
        } else {
            $_SESSION['added_games'][] = [
                'id' => 'custom_' . uniqid(),
                'title' => $g_title,
                'mode' => in_array($g_mode, ['numbers', 'choice', 'mimic'], true) ? $g_mode : 'numbers',
                'points' => $g_pts,
                'questions' => [
                    [
                        'mode' => $g_mode,
                        'letter' => '1',
                        'hand' => '☝️',
                        'correctSign' => '☝️',
                        'options' => ['☝️', '✌️', '✋'],
                        'match' => 90,
                        'scoreInc' => $g_pts
                    ],
                    [
                        'mode' => $g_mode,
                        'letter' => '2',
                        'hand' => '✌️',
                        'correctSign' => '✌️',
                        'options' => ['✌️', '☝️', '🤟'],
                        'match' => 85,
                        'scoreInc' => $g_pts
                    ]
                ]
            ];
            $flash_ok = 'تمت إضافة اللعبة "' . $g_title . '" بنجاح!';
        }
    }

    // 4. حذف لعبة مضافة
    elseif ($action === 'delete_game') {
        $gid = trim((string)($_POST['game_id'] ?? ''));
        $_SESSION['added_games'] = array_values(array_filter(
            $_SESSION['added_games'],
            fn($g) => ($g['id'] ?? '') !== $gid
        ));
        $flash_ok = 'تم حذف اللعبة بنجاح.';
    }

    if ($flash_ok !== '') {
        $_SESSION['users_flash_success'] = $flash_ok;
    }
    if ($flash_err !== '') {
        $_SESSION['users_flash_error'] = $flash_err;
    }

    header("Location: users.php");
    exit;
}

// معالجة حذف المستخدم ثم التحويل إلى users.php نظيفة
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0) {
        if (isset($conn)) {
            try {
                $stmt = @$conn->prepare("DELETE FROM user WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $del_id);
                    @$stmt->execute();
                    $stmt->close();
                }
            } catch (Throwable $e) {}
        }

        $_SESSION['admin_users'] = array_values(array_filter(
            $_SESSION['admin_users'],
            fn($u) => (int)($u['id'] ?? 0) !== $del_id
        ));
        $_SESSION['users_flash_success'] = 'تم حذف المستخدم بنجاح!';
    }
    header("Location: users.php");
    exit;
}

$users = $_SESSION['admin_users'];
$games = $_SESSION['added_games'];
$hidden_titles = array_map(fn($x) => trim((string)$x), $_SESSION['hidden_lessons']);

$avatar = '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="9" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لوحة تحكم المدير - إدارة المستخدمين والدروس والألعاب</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Tajawal', sans-serif;
        min-height: 100vh;
        background-color: #f8fafc;
        color: #1e293b;
    }

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

    .navbar .btn-logout {
        background-color: #2563eb;
        color: #ffffff !important;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .navbar .btn-logout:hover {
        background-color: #1d4ed8;
    }

    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    header.page-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 12px;
    }

    header.page-top h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .control-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .tab-btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        font-family: inherit;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .tab-btn.active,
    .tab-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .status-banner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 24px;
        margin-bottom: 22px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .status-banner .ic {
        font-size: 28px;
        width: 45px;
        height: 45px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 10px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .status-banner h3 {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
    }

    .status-banner p {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
    }

    .list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        display: grid;
        place-items: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar svg { width: 30px; height: 30px; fill: #64748b; }

    .info { flex: 1; min-width: 140px; }
    .info .name { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .info .role-status { display: flex; align-items: center; gap: 8px; margin-top: 4px; flex-wrap: wrap; }
    .info .role { font-size: 13px; font-weight: 600; color: #64748b; }

    .badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .badge.active { background: #dcfce7; color: #15803d; }
    .badge.inactive { background: #fee2e2; color: #b91c1c; }

    .card-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-sm {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 7px 12px;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 700;
        font-size: 12.5px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-sm:hover { background: #dbeafe; }

    .btn-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        padding: 7px 12px;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 700;
        font-size: 12.5px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-danger:hover { background: #fecaca; }

    .manage-section {
        margin-top: 32px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .manage-section h2 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 14px;
    }

    .inline-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .inline-form input,
    .inline-form select {
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        background: #f8fafc;
        color: #1e293b;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 9px 16px;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 700;
        font-size: 13.5px;
        cursor: pointer;
    }

    .btn-primary:hover { background: #1d4ed8; }

    .items-stack {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .row-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        flex-wrap: wrap;
    }

    .alert-success {
        background: #dcfce7;
        color: #15803d;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 14px;
    }

    .alert-error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 14px 20px;
            flex-direction: column;
            gap: 12px;
        }
        .list {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="brand">لوحة تحكم المدير - منصة وصلة</div>
        <nav aria-label="التنقل الرئيسي للأدمن">
            <a href="users.php" class="active">حالة المستخدمين</a>
            <a href="lesson.php">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="profile.php">الإعدادات</a>
            <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
        </nav>
    </header>

    <main class="container">

        <header class="page-top">
            <h1>متابعة حالة مستخدمي المنصة والتحكم بالمحتوى</h1>
        </header>

        <div class="control-tabs">
            <button type="button" class="tab-btn active" onclick="scrollToBox('usersSection', this)">المستخدمون (<?= count($users) ?>)</button>
            <button type="button" class="tab-btn" onclick="scrollToBox('lessonsSection', this)">التحكم بالدروس (<?= count($all_lessons) ?>)</button>
            <button type="button" class="tab-btn" onclick="scrollToBox('gamesSection', this)">التحكم بالألعاب (<?= count($games) ?>)</button>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <!-- قسم المستخدمين -->
        <div id="usersSection">
            <div class="status-banner">
                <span class="ic">📊</span>
                <div>
                    <h3>عرض والتحكم بحالة الحسابات</h3>
                    <p>تغيير حالة الحساب بين نشط وموقوف أو حذف الحساب مباشرة.</p>
                </div>
            </div>

            <div class="list">
                <?php foreach ($users as $u):
                    $is_active = ($u['status'] === 'نشط' || $u['status'] === 'active');
                ?>
                    <section class="card">
                        <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                            <span class="avatar"><?= $avatar ?></span>
                            <span class="info">
                                <div class="name"><?= e($u['name']) ?></div>
                                <div class="role-status">
                                    <span class="role"><?= e($u['role']) ?></span>
                                    <span class="badge <?= $is_active ? 'active' : 'inactive' ?>"><?= e($u['status']) ?></span>
                                </div>
                            </span>
                        </div>

                        <div class="card-actions">
                            <form method="POST" action="users.php" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_user_status">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn-sm"><?= $is_active ? 'إيقاف' : 'تفعيل' ?></button>
                            </form>

                            <a class="btn-danger" href="users.php?delete_id=<?= (int)$u['id'] ?>"
                               onclick="return confirm('هل تريد حذف هذا المستخدم؟')">حذف</a>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- قسم التحكم بالدروس الأساسية (إظهار / إخفاء) -->
        <section class="manage-section" id="lessonsSection">
            <h2>📚 التحكم بحالة الدروس (إظهار / إخفاء)</h2>

            <div class="items-stack">
                <?php foreach ($all_lessons as $les):
                    $clean_title = trim((string)($les['title'] ?? ''));
                    $is_hidden = in_array($clean_title, $hidden_titles, true);
                    $is_shown = !$is_hidden;
                ?>
                    <div class="row-item">
                        <div>
                            <span style="font-size:20px; margin-left:6px;"><?= e($les['icon'] ?? '📚') ?></span>
                            <strong><?= e($clean_title) ?></strong>
                            <span class="badge <?= $is_shown ? 'active' : 'inactive' ?>" style="margin-right:8px;">
                                <?= $is_shown ? 'إظهار' : 'إخفاء' ?>
                            </span>
                        </div>

                        <form method="POST" action="users.php" style="display:flex; gap:8px; align-items:center;">
                            <input type="hidden" name="action" value="set_lesson_visibility">
                            <input type="hidden" name="lesson_title" value="<?= e($clean_title) ?>">
                            <select name="visibility_state" onchange="this.form.submit()" style="padding:7px 12px; border-radius:8px; border:1px solid #cbd5e1; font-family:inherit; font-weight:700; font-size:13px; background:#ffffff;">
                                <option value="show" <?= $is_shown ? 'selected' : '' ?>>إظهار</option>
                                <option value="hide" <?= !$is_shown ? 'selected' : '' ?>>إخفاء</option>
                            </select>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- قسم التحكم بالألعاب -->
        <section class="manage-section" id="gamesSection">
            <h2>🎮 التحكم بالألعاب</h2>
            <form method="POST" action="users.php" class="inline-form">
                <input type="hidden" name="action" value="add_admin_game">
                <input type="text" name="game_title" required placeholder="اسم اللعبة الجديدة">
                <select name="game_mode">
                    <option value="numbers">لعبة الأرقام</option>
                    <option value="choice">اختر الإشارة الصحيحة</option>
                    <option value="mimic">قلّد الإشارة</option>
                </select>
                <input type="number" name="game_points" min="5" max="100" value="15" placeholder="النقاط">
                <button type="submit" class="btn-primary">+ إضافة لعبة</button>
            </form>

            <div class="items-stack">
                <div class="row-item">
                    <div>
                        <strong>قلّد الإشارة</strong>
                        <span class="badge active" style="margin-right:8px;">لعبة أساسية</span>
                    </div>
                    <div class="card-actions">
                        <a href="game.php?play=default" class="btn-sm">فتح اللعبة</a>
                    </div>
                </div>

                <?php foreach ($games as $g): ?>
                    <div class="row-item">
                        <div>
                            <strong><?= e($g['title']) ?></strong>
                            <span class="badge active" style="margin-right:8px;"><?= e($g['mode'] ?? 'numbers') ?></span>
                        </div>
                        <div class="card-actions">
                            <a href="game.php?play=<?= urlencode((string)$g['id']) ?>" class="btn-sm">فتح اللعبة</a>
                            <form method="POST" action="users.php" style="display:inline;" onsubmit="return confirm('حذف هذه اللعبة؟');">
                                <input type="hidden" name="action" value="delete_game">
                                <input type="hidden" name="game_id" value="<?= e((string)$g['id']) ?>">
                                <button type="submit" class="btn-danger">حذف</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <script>
        function scrollToBox(id, btn) {
            const el = document.getElementById(id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
        }
    </script>

</body>
</html>