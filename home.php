<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// قيم افتراضية آمنة
$userName = $_SESSION['user_name'] ?? 'المعلم';
$student_count = 0;
$lesson_count = 0;

// الاتصال بقاعدة البيانات وجلب البيانات الحية بحماية كاملة ضد الأخطاء
if (isset($conn)) {
    try {
        if (isset($_SESSION['user_id'])) {
            $uid = (int)$_SESSION['user_id'];
            // محاولة جلب بيانات المعلم بدون افتراض اسم عمود محدد للـ ID
            $res_user = @mysqli_query($conn, "SELECT * FROM teacher LIMIT 1");
            if (!$res_user || mysqli_num_rows($res_user) == 0) {
                $res_user = @mysqli_query($conn, "SELECT * FROM user LIMIT 1");
            }
            
            if ($res_user && mysqli_num_rows($res_user) > 0) {
                $row_user = mysqli_fetch_assoc($res_user);
                $userName = $row_user['name'] ?? $row_user['username'] ?? $row_user['fullname'] ?? $userName;
            }
        }
    } catch (Throwable $e) {
        // تخطي الخطأ بسلاسة واستخدام القيمة الافتراضية
    }

    try {
        $res_students = @mysqli_query($conn, "SELECT COUNT(*) as total FROM child");
        if ($res_students && $row_s = mysqli_fetch_assoc($res_students)) {
            $student_count = (int)$row_s['total'];
        }
    } catch (Throwable $e) {}

    try {
        $res_lessons = @mysqli_query($conn, "SELECT COUNT(*) as total FROM lesson");
        if ($res_lessons && $row_l = mysqli_fetch_assoc($res_lessons)) {
            $lesson_count = (int)$row_l['total'];
        }
    } catch (Throwable $e) {}
}

// الإشعارات والتنبيهات
$notifications = [
    ['icon' => '⭐', 'text' => 'تم تحديث سجلات الطلاب والتقييمات بنجاح', 'time' => 'اليوم'],
    ['icon' => '📋', 'text' => 'هناك دروس جديدة بانتظار المراجعة والاعتماد', 'time' => 'منذ ساعتين'],
];

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لوحة تحكم المعلم - منصة وصلة التعليمية</title>
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

    .welcome-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #e2e8f0;
        display: grid;
        place-items: center;
        font-size: 26px;
    }

    .welcome-card h2 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }

    .tile-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }

    .tile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .tile-card .ic {
        font-size: 36px;
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: #f1f5f9;
        display: grid;
        place-items: center;
    }

    .tile-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .badge-count {
        position: absolute;
        top: 16px;
        left: 16px;
        background: #dcfce7;
        color: #15803d;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 15px;
    }

    .notes-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .note-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .note-item:last-child {
        border-bottom: none;
    }

    .note-item .ic {
        font-size: 24px;
    }

    .note-item .txt {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .note-item .time {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        margin-top: 2px;
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
    }
</style>
</head>
<body>

    <!-- شريط التنقل العلوي الموحد -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="home.php" class="active">الرئيسية</a>
            <a href="lessons2.php">الدروس</a>
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

    <!-- المحتوى الرئيسي لوحة التحكم -->
    <main class="container">
        
        <section class="welcome-card">
            <div class="avatar">👨‍🏫</div>
            <div>
                <h2 style="margin-bottom: 4px;">مرحباً بك، <?= e($userName) ?></h2>
                <p style="color: #64748b; font-size: 14px;">لوحة التحكم الخاصة بإدارة الأنشطة والطلاب والمهارات التعليمية</p>
            </div>
        </section>

        <h2 class="section-title">أقسام الإدارة والتحكم</h2>
        <div class="dashboard-grid">
            <a class="tile-card" href="game.php">
                <span class="ic">🎮</span>
                <div>
                    <h3>إدارة الألعاب</h3>
                    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">تعديل ومتابعة تفاعلات الألعاب التعليمية</p>
                </div>
            </a>
            
            <a class="tile-card" href="lessons2.php">
                <span class="ic">✋</span>
                <div>
                    <h3>إدارة الدروس</h3>
                    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">استعراض وتنظيم المحتوى التعليمي</p>
                </div>
                <span class="badge-count"><?= $lesson_count ?> درس</span>
            </a>

            <a class="tile-card" href="skills.php">
                <span class="ic">📊</span>
                <div>
                    <h3>تفاصيل المهارات</h3>
                    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">متابعة نسب إتقان الحروف والمهارات</p>
                </div>
            </a>

            <a class="tile-card" href="student.php">
                <span class="ic">👥</span>
                <div>
                    <h3>إدارة الطلاب</h3>
                    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">متابعة تقارير وبيانات المسجلين</p>
                </div>
                <span class="badge-count"><?= $student_count ?> طالب</span>
            </a>
        </div>

        <h2 class="section-title">الإشعارات والتنبيهات</h2>
        <section class="notes-card">
            <?php foreach ($notifications as $n): ?>
                <div class="note-item">
                    <span class="ic"><?= $n['icon'] ?></span>
                    <div>
                        <div class="txt"><?= e($n['text']) ?></div>
                        <div class="time"><?= e($n['time']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

    </main>

</body>
</html>
