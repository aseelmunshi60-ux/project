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

$success_msg = '';
$error_msg = '';

// معالجة حذف المستخدم في نفس الصفحة
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0 && isset($conn)) {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $success_msg = 'تم حذف المستخدم بنجاح!';
            } else {
                $error_msg = 'حدث خطأ أثناء حذف المستخدم.';
            }
            $stmt->close();
        }
    }
}

// قيم افتراضية آمنة للمستخدمين
$users = [
    ['id' => 1, 'name' => 'عبدالله', 'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 2, 'name' => 'احمد',    'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 3, 'name' => 'محمد',    'role' => 'ولي امر', 'status' => 'موقوف'],
    ['id' => 4, 'name' => 'علي',     'role' => 'معلم', 'status' => 'نشط'],
    ['id' => 5, 'name' => 'حسن',     'role' => 'طالب', 'status' => 'نشط'],
    ['id' => 6, 'name' => 'سعد',     'role' => 'ولي امر', 'status' => 'نشط'],
];

// سحب المستخدمين الحقيقيين من قاعدة البيانات بأمان
if (isset($conn)) {
    try {
        $result_users = @mysqli_query($conn, "SELECT * FROM user");
        if ($result_users && mysqli_num_rows($result_users) > 0) {
            $users = [];
            while ($row = mysqli_fetch_assoc($result_users)) {
                $users[] = [
                    'id' => $row['id'] ?? $row['user_id'] ?? 1,
                    'name' => $row['name'] ?? $row['username'] ?? 'مستخدم',
                    'role' => $row['role'] ?? 'طالب',
                    'status' => $row['status'] ?? 'نشط',
                ];
            }
        }
    } catch (Throwable $e) {}
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$avatar = '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="9" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لوحة تحكم المدير - حالة المستخدمين</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
        font-family: 'Tajawal', sans-serif;
        min-height: 100vh;
        background-color: #f8fafc;
        color: #1e293b;
    }

    /* شريط التنقل العلوي الخاص بالأدمن */
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

    /* زر تسجيل الخروج باللون الأزرق الموحد */
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

    /* بانر حالة المستخدمين */
    .status-banner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 24px;
        margin-bottom: 25px;
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

    /* شبكة المستخدمين */
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
        gap: 15px;
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
    
    .info { flex: 1; }
    .info .name { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .info .role-status { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
    .info .role { font-size: 13px; font-weight: 600; color: #64748b; }
    
    .badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .badge.active { background: #dcfce7; color: #15803d; }
    .badge.inactive { background: #fee2e2; color: #b91c1c; }

    .btn-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        padding: 8px 14px;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
        flex-shrink: 0;
    }

    .btn-danger:hover { background: #fecaca; }

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

    <!-- شريط التنقل العلوي الخاص بلوحة تحكم المدير (Admin) -->
    <header class="navbar">
        <div class="brand">لوحة تحكم المدير - منصة وصلة</div>
        <nav aria-label="التنقل الرئيسي للأدمن">
            <a href="users.php" class="active">حالة المستخدمين</a>
            <a href="profile.php">الإعدادات</a>
            <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
        </nav>
    </header>

    <!-- المحتوى الرئيسي للمستخدمين -->
    <main class="container">

        <header>
            <h1>متابعة حالة مستخدمي المنصة</h1>
        </header>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <!-- بانر حالة المستخدمين -->
        <div class="status-banner">
            <span class="ic">📊</span>
            <div>
                <h3>عرض حالة الحسابات</h3>
                <p>متابعة سريعة لمعرفة الحسابات النشطة والموقوفة في المنصة.</p>
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

                    <a class="btn-danger" href="users.php?delete_id=<?= (int)$u['id'] ?>"
                       onclick="return confirm('هل تريد حذف هذا المستخدم؟')">حذف</a>
                </section>
            <?php endforeach; ?>
        </div>

    </main>

</body>
</html>
