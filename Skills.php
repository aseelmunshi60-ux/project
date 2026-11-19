<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// تحديد الطالب المسجل حالياً من الجلسة
$student_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 'guest_student';
$student_key = 'student_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$student_id);
$student_name = trim((string)(
    $_SESSION['user_name']
    ?? $_SESSION['name']
    ?? $_SESSION['username']
    ?? 'أسيل'
));
if ($student_name === '') {
    $student_name = 'أسيل';
}

$default_skills = [
    [
        'id' => 'sk_letters',
        'title' => 'إشارات الحروف الهجائية',
        'category' => 'الحروف',
        'level' => 85,
        'hand' => '☝️',
        'notes' => 'تمييز وتقليد إشارات الحروف الأساسية بدقة.'
    ],
    [
        'id' => 'sk_numbers',
        'title' => 'إشارات الأرقام والعد',
        'category' => 'الأرقام',
        'level' => 60,
        'hand' => '☝️',
        'notes' => 'قراءة الأرقام اليدوية واختيار الإشارة الصحيحة.'
    ],
    [
        'id' => 'sk_greetings',
        'title' => 'التحية والتواصل اليومي',
        'category' => 'المحادثة',
        'level' => 45,
        'hand' => '✋',
        'notes' => 'استخدام الإشارات التفاعلية في التحية والترحيب.'
    ],
    [
        'id' => 'sk_focus',
        'title' => 'سرعة الملاحظة والتطابق',
        'category' => 'المهارات الذهنية',
        'level' => 80,
        'hand' => '✌️',
        'notes' => 'الربط السريع بين الشكل والإشارة المناسبة.'
    ]
];

if (!isset($_SESSION['student_skills']) || !is_array($_SESSION['student_skills'])) {
    $_SESSION['student_skills'] = [];
}

if (!isset($_SESSION['student_skills'][$student_key]) || !is_array($_SESSION['student_skills'][$student_key])) {
    $_SESSION['student_skills'][$student_key] = $default_skills;
}

$skills = &$_SESSION['student_skills'][$student_key];
$success_msg = '';
$error_msg = '';

// معالجة التفاعلات على مهارات الطالب المسجل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'adjust_skill') {
        $skill_id = trim((string)($_POST['skill_id'] ?? ''));
        $delta = isset($_POST['delta']) ? max(-50, min(50, (int)$_POST['delta'])) : 10;

        foreach ($skills as &$sk) {
            if ($sk['id'] === $skill_id) {
                $sk['level'] = max(0, min(100, (int)$sk['level'] + $delta));
                $success_msg = 'تم تحديث مهارة "' . $sk['title'] . '" إلى ' . $sk['level'] . '%!';
                break;
            }
        }
        unset($sk);
    } elseif ($action === 'add_skill') {
        $title = trim((string)($_POST['skill_title'] ?? ''));
        $category = trim((string)($_POST['skill_category'] ?? 'عام'));
        $level = isset($_POST['skill_level']) ? max(0, min(100, (int)$_POST['skill_level'])) : 50;
        $hand = trim((string)($_POST['skill_hand'] ?? '☝️'));
        $notes = trim((string)($_POST['skill_notes'] ?? 'مهارة مضافة للمتابعة التفاعلية.'));

        if ($title === '') {
            $error_msg = 'يرجى إدخال اسم المهارة.';
        } else {
            $skills[] = [
                'id' => 'sk_' . uniqid(),
                'title' => $title,
                'category' => $category !== '' ? $category : 'عام',
                'level' => $level,
                'hand' => $hand !== '' ? $hand : '☝️',
                'notes' => $notes !== '' ? $notes : 'مهارة مضافة للمتابعة التفاعلية.'
            ];
            $success_msg = 'تمت إضافة المهارة "' . $title . '" للطالب ' . $student_name . '!';
        }
    } elseif ($action === 'reset_skills') {
        $skills = $default_skills;
        $success_msg = 'تمت إعادة ضبط مهارات الطالب إلى الوضع الافتراضي.';
    }
}

$avg_level = 0;
if (!empty($skills)) {
    $sum = array_sum(array_map(fn($s) => (int)($s['level'] ?? 0), $skills));
    $avg_level = (int)round($sum / count($skills));
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>مهارات الطالب - منصة وصلة التعليمية</title>
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

    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 36px 20px;
    }

    .student-banner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .student-banner h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .student-banner p {
        font-size: 14.5px;
        color: #64748b;
    }

    .summary-pill {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 15px;
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
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
        transition: all 0.15s ease;
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

    .btn-success {
        background: #dcfce7;
        color: #15803d;
        border-color: #86efac;
    }

    .btn-success:hover {
        background: #bbf7d0;
    }

    .btn-danger {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .add-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 22px;
        display: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .add-box.open {
        display: block;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
        margin-bottom: 14px;
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
        background: #f8fafc;
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

    .skills-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
    }

    .skill-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .skill-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .skill-hand {
        font-size: 32px;
        background: #eff6ff;
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: grid;
        place-items: center;
    }

    .skill-tag {
        background: #f1f5f9;
        color: #475569;
        font-size: 12.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .skill-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 6px;
    }

    .skill-notes {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin-top: 4px;
    }

    .progress-wrap {
        margin-top: 6px;
    }

    .progress-meta {
        display: flex;
        justify-content: space-between;
        font-size: 13.5px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .progress-bar {
        width: 100%;
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: #2563eb;
        border-radius: 999px;
        transition: width 0.25s ease;
    }

    .skill-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 14px 20px;
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="index.php">الرئيسية</a>
            <a href="lesson.php">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="skills.php" class="active">المهارات</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">

        <div class="student-banner">
            <div>
                <h1>مهارات الطالب: <?= e($student_name) ?></h1>
                <p>ارفع أو أنقص نسبة إتقان كل مهارة حسب أداء الطالب، أو أضف مهارات جديدة مرتبطة بحسابه.</p>
            </div>
            <div class="summary-pill">متوسط الإتقان: <?= $avg_level ?>%</div>
        </div>

        <?php if ($success_msg !== ''): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if ($error_msg !== ''): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <button type="button" class="btn btn-primary" onclick="toggleAddSkill()">+ إضافة مهارة جديدة</button>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="game.php" class="btn">🎮 الانتقال للألعاب</a>
                <form method="POST" action="skills.php" style="display:inline;" onsubmit="return confirm('إعادة ضبط جميع المهارات؟');">
                    <input type="hidden" name="action" value="reset_skills">
                    <button type="submit" class="btn">🔄 إعادة ضبط</button>
                </form>
            </div>
        </div>

        <div class="add-box <?= $error_msg !== '' ? 'open' : '' ?>" id="addSkillBox">
            <h2 style="font-size:17px; font-weight:800; margin-bottom:12px;">إضافة مهارة جديدة للطالب <?= e($student_name) ?></h2>
            <form method="POST" action="skills.php">
                <input type="hidden" name="action" value="add_skill">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="skill_title">اسم المهارة</label>
                        <input type="text" id="skill_title" name="skill_title" required placeholder="مثال: إشارات الألوان">
                    </div>
                    <div class="form-field">
                        <label for="skill_category">التصنيف</label>
                        <input type="text" id="skill_category" name="skill_category" value="لغة الإشارة">
                    </div>
                    <div class="form-field">
                        <label for="skill_level">المستوى الحالي (%)</label>
                        <input type="number" id="skill_level" name="skill_level" min="0" max="100" value="50">
                    </div>
                    <div class="form-field">
                        <label for="skill_hand">الأيقونة</label>
                        <select id="skill_hand" name="skill_hand">
                            <option value="🎨">🎨 الألوان</option>
                            <option value="🐾">🐾 الحيوانات</option>
                            <option value="👨‍👩‍👧‍👦">👨‍👩‍👧‍👦 العائلة</option>
                            <option value="☝️">☝️ سبابة</option>
                            <option value="✋">✋ كف مفتوح</option>
                            <option value="✌️">✌️ إصبعان</option>
                            <option value="🤟">🤟 ثلاثة أصابع</option>
                            <option value="🤏">🤏 حركة دقيقة</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="skill_notes">ملاحظة قصيرة</label>
                        <input type="text" id="skill_notes" name="skill_notes" placeholder="وصف مختصر للمهارة">
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary">حفظ المهارة</button>
                    <button type="button" class="btn" onclick="toggleAddSkill()">إلغاء</button>
                </div>
            </form>
        </div>

        <div class="skills-grid">
            <?php foreach ($skills as $sk): ?>
                <?php $lvl = (int)($sk['level'] ?? 0); ?>
                <div class="skill-card">
                    <div>
                        <div class="skill-top">
                            <div class="skill-hand"><?= e($sk['hand'] ?? '☝️') ?></div>
                            <span class="skill-tag"><?= e($sk['category'] ?? 'عام') ?></span>
                        </div>
                        <h2 class="skill-title"><?= e($sk['title']) ?></h2>
                        <p class="skill-notes"><?= e($sk['notes'] ?? '') ?></p>

                        <div class="progress-wrap">
                            <div class="progress-meta">
                                <span>نسبة الإتقان</span>
                                <span><?= $lvl ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $lvl ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="skill-actions">
                        <form method="POST" action="skills.php" style="display:inline;">
                            <input type="hidden" name="action" value="adjust_skill">
                            <input type="hidden" name="skill_id" value="<?= e($sk['id']) ?>">
                            <input type="hidden" name="delta" value="10">
                            <button type="submit" class="btn btn-success">+10% تقدم</button>
                        </form>

                        <form method="POST" action="skills.php" style="display:inline;">
                            <input type="hidden" name="action" value="adjust_skill">
                            <input type="hidden" name="skill_id" value="<?= e($sk['id']) ?>">
                            <input type="hidden" name="delta" value="-10">
                            <button type="submit" class="btn btn-danger">-10% تراجع</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <script>
        function toggleAddSkill() {
            const box = document.getElementById('addSkillBox');
            if (box) box.classList.toggle('open');
        }
    </script>

</body>
</html>