<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$current = 'report';

// بيانات افتراضية آمنة في حال كان جدول القاعدة فارغاً
$reports = [
    [
        'id' => 1, 
        'name' => 'أحمد', 
        'points' => 150, 
        'badge' => 'محترف الحركات الأساسية', 
        'completion' => 85, 
        'status' => 'ممتاز',
        'achievements' => ['أتم درس الحروف الهوائية بنجاح', 'حقق دقة 90% في اختبار الأرقام', 'مواظبة كاملة خلال الأسبوع']
    ],
    [
        'id' => 2, 
        'name' => 'عبدالله', 
        'points' => 90, 
        'badge' => 'مستمر بانتظام', 
        'completion' => 60, 
        'status' => 'جيد جداً',
        'achievements' => ['أكمل 3 دروس أساسية', 'يحتاج التركيز على سرعة إتقان الإشارات']
    ],
    [
        'id' => 3, 
        'name' => 'حسن', 
        'points' => 200, 
        'badge' => 'سفير لغة الإشارة', 
        'completion' => 98, 
        'status' => 'متفوق',
        'achievements' => ['أنهى جميع مستويات المنصة بنجاح تام', 'حقق المركز الأول في اختبار دقة الحركات', 'ساعد زملائه في الأنشطة التفاعلية']
    ],
    [
        'id' => 4, 
        'name' => 'عمر', 
        'points' => 40, 
        'badge' => 'بداية موفقة', 
        'completion' => 30, 
        'status' => 'يحتاج متابعة',
        'achievements' => ['سجل الدخول وبدأ الدرس الأول', 'بحاجة لمتابعة إضافية في التمارين اليومية']
    ],
];

// سحب بيانات الإنجازات والطلاب حية من قاعدة البيانات
if (isset($conn)) {
    try {
        $result_reports = @mysqli_query($conn, "SELECT * FROM child");
        if ($result_reports && mysqli_num_rows($result_reports) > 0) {
            $reports = [];
            while ($row = mysqli_fetch_assoc($result_reports)) {
                $comp = (int)($row['grade'] ?? $row['completion'] ?? 75);
                $badge_title = $comp >= 90 ? 'سفير لغة الإشارة' : ($comp >= 70 ? 'محترف الحركات الأساسية' : 'مستمر بانتظام');
                $status_text = $comp >= 85 ? 'ممتاز' : ($comp >= 50 ? 'جيد جداً' : 'يحتاج متابعة');
                
                $achv = [
                    'نسبة إتقان الحركات: ' . $comp . '%',
                    $comp >= 70 ? 'اجتاز الاختبارات التقييمية بكفاءة' : 'بحاجة لتكرار تمارين الدروس',
                    'المواظبة العامة في المنصة مستقرة'
                ];
                
                $reports[] = [
                    'id' => $row['id'] ?? 1,
                    'name' => $row['name'] ?? $row['child_name'] ?? 'طالب',
                    'points' => (int)($row['points'] ?? ($comp * 2)),
                    'badge' => $badge_title,
                    'completion' => $comp,
                    'status' => $status_text,
                    'achievements' => $achv
                ];
            }
        }
    } catch (Throwable $e) {}
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إنجازات الطلاب - منصة وصلة التعليمية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
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

    /* زر تسجيل الدخول والخروج باللون الأزرق الموحد */
    .navbar .btn-login,
    .navbar .btn-logout {
        background-color: #2563eb;
        color: #ffffff !important;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .navbar .btn-login:hover,
    .navbar .btn-logout:hover {
        background-color: #1d4ed8;
    }

    /* حاوية المحتوى الرئيسي العريضة */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    /* حقل البحث التفاعلي */
    .search-box {
        margin-bottom: 25px;
    }

    .search-box input {
        width: 100%;
        max-width: 400px;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Tajawal', sans-serif;
        font-size: 14px;
        background: #ffffff;
        outline: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: border-color 0.2s;
    }

    .search-box input:focus {
        border-color: #2563eb;
    }

    /* شبكة التقارير والإنجازات */
    .list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .student-name {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }

    .badge-status {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .badge {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        background: #eff6ff;
        color: #2563eb;
    }

    .chev {
        transition: transform .2s;
        font-size: 20px;
        color: #64748b;
    }

    .card.open .chev {
        transform: rotate(180deg);
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: #475569;
        font-weight: 600;
    }

    .info-row span b {
        color: #0f172a;
    }

    /* شريط التقدم */
    .progress-bar-bg {
        width: 100%;
        height: 10px;
        background: #f1f5f9;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: #2563eb;
        border-radius: 5px;
        transition: width 0.4s ease;
    }

    /* القائمة المنسدلة للتفاصيل */
    .dropdown-details {
        display: none;
        margin-top: 10px;
        padding-top: 14px;
        border-top: 1px dashed #cbd5e1;
        animation: fadeIn 0.2s ease-in-out;
    }

    .card.open .dropdown-details {
        display: block;
    }

    .dropdown-details h4 {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .dropdown-details ul {
        padding-inline-start: 20px;
        font-size: 13px;
        color: #64748b;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
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

    <!-- شريط التنقل العلوي الموحد -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="home.php">الرئيسية</a>
            <a href="lessons.php">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="Report.php" class="active">الإنجازات</a>
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

    <!-- المحتوى الرئيسي لصفحة الإنجازات -->
    <main class="container">
        
        <header>
            <h1>سجل إنجازات وتقارير الطلاب</h1>
        </header>

        <!-- حقل بحث تفاعلي لتصفية الطلاب مباشرة -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="ابحث عن اسم الطالب..." onkeyup="filterStudents()">
        </div>

        <div class="list" id="studentsList">
            <?php foreach ($reports as $i => $r): ?>
                <div class="card <?= $i === 0 ? 'open' : '' ?>" data-name="<?= e($r['name']) ?>">
                    <div class="card-top" onclick="this.closest('.card').classList.toggle('open')">
                        <div class="student-name">الطالب <?= e($r['name']) ?></div>
                        <div class="badge-status">
                            <span class="badge"><?= e($r['status']) ?></span>
                            <span class="chev">⌄</span>
                        </div>
                    </div>

                    <div class="info-row" style="margin-top: 6px;">
                        <span>الشارة: <b><?= e($r['badge']) ?></b></span>
                        <span>النقاط: <b><?= (int)$r['points'] ?> نقطة</b></span>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 5px; color: #64748b;">
                            <span>نسبة الإنجاز الكلي</span>
                            <span><?= (int)$r['completion'] ?>%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: <?= (int)$r['completion'] ?>%;"></div>
                        </div>
                    </div>

                    <!-- القائمة المنسدلة للتفاصيل والإنجازات -->
                    <div class="dropdown-details">
                        <h4>تفاصيل الإنجازات والأنشطة المكتملة:</h4>
                        <ul>
                            <?php foreach ($r['achievements'] as $achv): ?>
                                <li><?= e($achv) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <script>
        // دالة بحث تفاعلية وفورية لتصفية الطلاب
        function filterStudents() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                let name = card.getAttribute('data-name').toLowerCase();
                if (name.includes(input)) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>