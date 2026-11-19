<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$success_msg = '';
$error_msg = '';

// دالة مساعدة للتحقق من وجود سجل في جدول معين
function recordExists(mysqli $conn, string $table, string $column, int $id): bool {
    if ($id <= 0) {
        return false;
    }
    try {
        $stmt = $conn->prepare("SELECT 1 FROM `{$table}` WHERE `{$column}` = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    } catch (Throwable $e) {
        return false;
    }
}

// دالة لإنشاء مستخدم جديد في جدول user وإرجاع user_id الخاص به عند الحاجة
function createUserForChild(mysqli $conn, string $studentName): int {
    // محاولة إيجاد أول user_id موجود كخيار احتياطي إن تعذر الإدخال
    $fallback_id = 0;
    try {
        $res = @$conn->query("SELECT user_id FROM `user` ORDER BY user_id ASC LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $fallback_id = (int)($row['user_id'] ?? 0);
        }
    } catch (Throwable $e) {}

    // محاولة إدخال صف جديد في جدول user بأمان حسب الأعمدة المتاحة
    try {
        $colsRes = @$conn->query("SHOW COLUMNS FROM `user`");
        if ($colsRes) {
            $cols = [];
            while ($c = $colsRes->fetch_assoc()) {
                $cols[] = $c['Field'];
            }

            $insertCols = [];
            $placeholders = [];
            $types = '';
            $values = [];
            $uniqueSuffix = random_int(100, 9999);

            if (in_array('name', $cols, true)) {
                $insertCols[] = '`name`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = $studentName;
            } elseif (in_array('username', $cols, true)) {
                $insertCols[] = '`username`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = 'student_' . $uniqueSuffix;
            }

            if (in_array('email', $cols, true)) {
                $insertCols[] = '`email`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = 'student_' . $uniqueSuffix . '@wasla.local';
            }

            if (in_array('password', $cols, true)) {
                $insertCols[] = '`password`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = password_hash('Wasla@123', PASSWORD_DEFAULT);
            }

            if (in_array('role', $cols, true)) {
                $insertCols[] = '`role`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = 'student';
            }

            if (in_array('status', $cols, true)) {
                $insertCols[] = '`status`';
                $placeholders[] = '?';
                $types .= 's';
                $values[] = 'نشط';
            }

            if (!empty($insertCols)) {
                $sql = "INSERT INTO `user` (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param($types, ...$values);
                    if ($stmt->execute()) {
                        $newId = (int)$conn->insert_id;
                        $stmt->close();
                        if ($newId > 0) {
                            return $newId;
                        }
                    } else {
                        $stmt->close();
                    }
                }
            }
        }
    } catch (Throwable $e) {}

    return $fallback_id;
}

// 1. معالجة إضافة طالب جديد مع الاسم والحقول الكاملة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_child') {
    $name = trim($_POST['name'] ?? '');
    $user_id = (int)($_POST['user_id'] ?? 0);
    $guardian_id = (int)($_POST['guardian_id'] ?? 1);
    $age = (int)($_POST['age'] ?? 0);
    $level = trim($_POST['level'] ?? '');

    if (!empty($name) && $age > 0 && !empty($level)) {
        if (isset($conn)) {
            try {
                // إذا لم يكن user_id موجوداً في جدول user، أنشئ مستخدماً واحصل على user_id صحيح
                if (!recordExists($conn, 'user', 'user_id', $user_id)) {
                    $user_id = createUserForChild($conn, $name);
                }

                // التحقق من guardian_id إذا كان جدول guardian يحتوي على السجل، وإلا استخدام أول guardian_id متاح
                if (!recordExists($conn, 'guardian', 'guardian_id', $guardian_id)) {
                    $gRes = @$conn->query("SELECT guardian_id FROM `guardian` ORDER BY guardian_id ASC LIMIT 1");
                    if ($gRes && $gRow = $gRes->fetch_assoc()) {
                        $guardian_id = (int)($gRow['guardian_id'] ?? $guardian_id);
                    }
                }

                if ($user_id <= 0) {
                    $error_msg = 'تعذر ربط الطالب بحساب مستخدم صالح في جدول user.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO child (name, user_id, guardian_id, age, level) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("siiis", $name, $user_id, $guardian_id, $age, $level);
                        if ($stmt->execute()) {
                            $success_msg = 'تم إضافة الطالب بنجاح! (رقم المستخدم المرتبط: ' . $user_id . ')';
                        } else {
                            $error_msg = 'حدث خطأ أثناء الإدخال في قاعدة البيانات: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error_msg = 'خطأ في تجهيز استعلام الإضافة: ' . $conn->error;
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error_msg = 'تعذر إضافة الطالب بسبب قيد الربط في قاعدة البيانات: ' . $e->getMessage();
            } catch (Throwable $e) {
                $error_msg = 'حدث خطأ غير متوقع أثناء إضافة الطالب: ' . $e->getMessage();
            }
        }
    } else {
        $error_msg = 'يرجى إدخال جميع الحقول (الاسم، العمر، المستوى) بشكل صحيح.';
    }
}

// 2. معالجة حذف الطالب
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0 && isset($conn)) {
        try {
            $stmt = $conn->prepare("DELETE FROM child WHERE child_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $del_id);
                if ($stmt->execute()) {
                    $success_msg = 'تم حذف الطالب بنجاح!';
                } else {
                    $error_msg = 'حدث خطأ أثناء حذف الطالب.';
                }
                $stmt->close();
            }
        } catch (Throwable $e) {
            $error_msg = 'تعذر حذف الطالب: ' . $e->getMessage();
        }
    }
}

// جلب الطلاب من قاعدة البيانات
$students = [];
if (isset($conn)) {
    try {
        $res = @mysqli_query($conn, "SELECT * FROM child");
        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $students[] = $row;
            }
        }
    } catch (Throwable $e) {}
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إدارة الطلاب - منصة وصلة</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Tajawal', sans-serif;
        min-height: 100vh;
        background-color: #f8fafc;
        color: #1e293b;
    }

    /* شريط التنقل العلوي */
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
    }

    /* حاوية المحتوى */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    header.page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    header.page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    /* نموذج الإضافة */
    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .form-card h3 {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 15px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr) repeat(3, 1fr);
        gap: 15px;
        align-items: end;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Tajawal', sans-serif;
        font-size: 14px;
        background: #f8fafc;
        outline: none;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #2563eb;
        background: #ffffff;
    }

    .btn-primary {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
        height: 42px;
        width: 100%;
    }

    .btn-primary:hover { background: #1d4ed8; }

    /* شبكة عرض الطلاب */
    .list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .card-info .title {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }

    .card-info .details {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }

    .btn-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
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

    @media (max-width: 900px) {
        .navbar { padding: 14px 20px; flex-direction: column; gap: 12px; }
        .form-grid { grid-template-columns: 1fr; }
        .list { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav>
            <a href="home.php">الرئيسية</a>
            <a href="lessons.php">الدروس</a>
            <a href="student.php" class="active">الطلاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>
            <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
        </nav>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="container">

        <header class="page-header">
            <h1>إدارة الطلاب</h1>
        </header>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <!-- نموذج إضافة طالب مع خانة الاسم -->
        <div class="form-card">
            <h3>إضافة بيانات طالب جديد</h3>
            <form method="POST" action="student.php">
                <input type="hidden" name="action" value="add_child">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label>اسم الطالب</label>
                        <input type="text" name="name" placeholder="أدخل اسم الطالب" required>
                    </div>
                    <div class="form-group">
                        <label>معرف المستخدم</label>
                        <input type="number" name="user_id" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>معرف ولي الأمر</label>
                        <input type="number" name="guardian_id" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>العمر</label>
                        <input type="number" name="age" placeholder="العمر" required>
                    </div>
                    <div class="form-group" style="grid-column: span 4;">
                        <label>المستوى</label>
                        <input type="text" name="level" placeholder="مثال: مبتدئ / متقدم" required>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-primary">حفظ الطالب</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- قائمة عرض الطلاب مع أسمائهم الحقيقية -->
        <div class="list">
            <?php if (empty($students)): ?>
                <p style="color: #64748b; grid-column: span 2;">لا يوجد طلاب مسجلين حالياً في قاعدة البيانات.</p>
            <?php else: ?>
                <?php foreach ($students as $st): ?>
                    <div class="card">
                        <div class="card-info">
                            <div class="title"><?= e($st['name'] ?? 'طالب بدون اسم') ?></div>
                            <div class="details">العمر: <?= (int)$st['age'] ?> سنوات | المستوى: <?= e($st['level']) ?></div>
                        </div>
                        <a class="btn-danger" href="student.php?delete_id=<?= (int)$st['child_id'] ?>"
                           onclick="return confirm('هل تريد حذف هذا الطالب؟')">حذف</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>