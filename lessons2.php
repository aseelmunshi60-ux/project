<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$current = 'lessons';
$success_msg = '';
$error_msg = '';

// معالجة إضافة درس جديد في نفس الصفحة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_lesson') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '📚');

    if (!empty($title)) {
        if (isset($conn)) {
            $stmt = $conn->prepare("INSERT INTO lesson (title, description, icon) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $title, $description, $icon);
                if ($stmt->execute()) {
                    $success_msg = 'تم إنشاء الدرس بنجاح!';
                } else {
                    $error_msg = 'حدث خطأ أثناء الحفظ في قاعدة البيانات.';
                }
                $stmt->close();
            } else {
                $error_msg = 'خطأ في تجهيز الاتصال بقاعدة البيانات.';
            }
        } else {
            $error_msg = 'لا يوجد اتصال بقاعدة البيانات.';
        }
    } else {
        $error_msg = 'يرجى إدخال عنوان الدرس على الأقل.';
    }
}

// قيم افتراضية آمنة للدروس
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
        'title' => 'الكلمات: المجموعة الأولى',
        'desc' => 'مجموعة مختارة من الكلمات اليومية الشائعة (مثل التحيات والأشياء القريبة) للتواصل السريع والسهل.',
        'icon' => '👌',
    ],
    [
        'id' => 4,
        'title' => 'الكلمات: المجموعة الثانية',
        'desc' => 'كلمات إضافية متقدمة ومتنوعة (مثل الألوان والأطعمة والأنشطة) لتوسيع حصيلة المعرفة الإشارية.',
        'icon' => '🤏',
    ],
];

// سحب الدروس الحية من قاعدة البيانات
if (isset($conn)) {
    try {
        $result_lessons = @mysqli_query($conn, "SELECT * FROM lesson");
        if ($result_lessons && mysqli_num_rows($result_lessons) > 0) {
            $lessons = [];
            while ($row = mysqli_fetch_assoc($result_lessons)) {
                $lessons[] = [
                    'id' => $row['id'] ?? $row['lesson_id'] ?? 1,
                    'title' => $row['title'] ?? $row['name'] ?? 'درس جديد',
                    'desc' => $row['description'] ?? $row['desc'] ?? 'محتوى تعليمي تفاعلي لغة الإشارة.',
                    'icon' => $row['icon'] ?? '📚',
                ];
            }
        }
    } catch (Throwable $e) {}
}

$items = [
    ['key' => 'profile', 'label' => 'الإعدادات', 'href' => 'profile.php', 'icon' => '⚙'],
    ['key' => 'lessons', 'label' => 'الدروس', 'href' => 'lessons.php', 'icon' => '✋'],
    ['key' => 'home',    'label' => 'الرئيسية', 'href' => 'home.php',    'icon' => '⌂'],
    ['key' => 'game',    'label' => 'الالعاب', 'href' => 'game.php',    'icon' => '🎮'],
    ['key' => 'student', 'label' => 'الطلاب', 'href' => 'student.php', 'icon' => '👥'],
];

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إدارة الدروس - منصة وصلة التعليمية</title>
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

    /* شريط التنقل العلوي الموحد للمنصة */
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

    /* حاوية المحتوى الرئيسي العريضة */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 8px;
        font: 700 14px 'Tajawal';
        cursor: pointer;
        text-decoration: none;
        display: inline-grid;
        place-items: center;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
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

    /* نموذج إضافة درس (يظهر ويخفي بالضغط) */
    .form-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        display: none;
    }

    .form-container.active {
        display: block;
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Tajawal', sans-serif;
        font-size: 14px;
        color: #1e293b;
        background: #f8fafc;
        outline: none;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #2563eb;
        background: #ffffff;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* شبكة الدروس */
    .lessons-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .lesson-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .lesson-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .lesson-info h2 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .lesson-info p {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        line-height: 1.5;
    }

    .lesson-icon {
        font-size: 45px;
        width: 70px;
        height: 70px;
        border-radius: 12px;
        background: #f1f5f9;
        display: grid;
        place-items: center;
        flex-shrink: 0;
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
        .lessons-grid {
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

    <!-- شريط التنقل العلوي الموحد -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="home.php">الرئيسية</a>
            <a href="lessons.php" class="active">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="skills.php">المهارات</a>
            <a href="student.php">الطلاب</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="container">
        
        <div class="page-header">
            <h1>إدارة واستعراض الدروس التعليمية</h1>
            <div>
                <button class="btn btn-primary" onclick="toggleCreateForm()">+ إنشاء درس</button>
            </div>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <!-- نموذج إنشاء درس (مخفي ويظهر بالضغط على الزر) -->
        <div class="form-container" id="createFormContainer">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; color: #0f172a;">إنشاء درس جديد</h2>
            <form method="POST" action="lessons.php">
                <input type="hidden" name="action" value="create_lesson">
                
                <div class="form-group">
                    <label for="title">عنوان الدرس</label>
                    <input type="text" id="title" name="title" required placeholder="مثال: الحروف الإشارية المتقدمة">
                </div>

                <div class="form-group">
                    <label for="description">وصف محتوى الدرس</label>
                    <textarea id="description" name="description" placeholder="اكتب نبذة مختصرة عن محتوى الدرس..."></textarea>
                </div>

                <div class="form-group">
                    <label for="icon">الأيقونة التعبيرية (Emoji)</label>
                    <select id="icon" name="icon">
                        <option value="📚">📚 كتاب</option>
                        <option value="☝️">☝️ إشارة إصبع</option>
                        <option value="👉">👉 توجيه</option>
                        <option value="👌">👌 إشارة موافقة</option>
                        <option value="🤏">🤏 حركة دقيقة</option>
                        <option value="💡">💡 إضاءة</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">حفظ في قاعدة البيانات</button>
                    <button type="button" class="btn" onclick="toggleCreateForm()">إلغاء</button>
                </div>
            </form>
        </div>

        <!-- شبكة الدروس -->
        <div class="lessons-grid">
            <?php foreach ($lessons as $l): ?>
                <a class="lesson-card" href="lesson.php?id=<?= (int)$l['id'] ?>">
                    <div class="lesson-info">
                        <h2><?= e($l['title']) ?></h2>
                        <p><?= e($l['desc']) ?></p>
                    </div>
                    <span class="lesson-icon"><?= $l['icon'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

    </main>

    <script>
        function toggleCreateForm() {
            const form = document.getElementById('createFormContainer');
            form.classList.toggle('active');
        }
    </script>

</body>
</html>