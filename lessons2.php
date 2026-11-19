<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$success_msg = '';
$error_msg = '';

// معالجة إضافة درس جديد من قبل المعلم حسب أعمدة جدول lesson الفعلية (title, content, ...)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_lesson') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '📚');
    $video_url = trim($_POST['video_url'] ?? '');

    if (!empty($title)) {
        if (isset($conn)) {
            try {
                $columns = [];
                $col_res = $conn->query("SHOW COLUMNS FROM lesson");
                if ($col_res) {
                    while ($c = $col_res->fetch_assoc()) {
                        $columns[] = $c['Field'];
                    }
                }

                $insert_cols = [];
                $placeholders = [];
                $types = '';
                $values = [];

                if (in_array('title', $columns)) {
                    $insert_cols[] = 'title';
                    $placeholders[] = '?';
                    $types .= 's';
                    $values[] = $title;
                }

                // حفظ الوصف في عمود content الموجود في جدول lesson
                $desc_text = $content;
                if (!empty($video_url)) {
                    $desc_text = ($desc_text !== '' ? $desc_text . "\n" : '') . "رابط الفيديو: " . $video_url;
                }

                if ($desc_text !== '') {
                    if (in_array('content', $columns)) {
                        $insert_cols[] = 'content';
                        $placeholders[] = '?';
                        $types .= 's';
                        $values[] = $desc_text;
                    } elseif (in_array('description', $columns)) {
                        $insert_cols[] = 'description';
                        $placeholders[] = '?';
                        $types .= 's';
                        $values[] = $desc_text;
                    }
                }

                if (in_array('icon', $columns)) {
                    $insert_cols[] = 'icon';
                    $placeholders[] = '?';
                    $types .= 's';
                    $values[] = $icon;
                }

                foreach (['video_url', 'youtube_url', 'video_link', 'url', 'link'] as $url_col) {
                    if (!empty($video_url) && in_array($url_col, $columns)) {
                        $insert_cols[] = $url_col;
                        $placeholders[] = '?';
                        $types .= 's';
                        $values[] = $video_url;
                        break;
                    }
                }

                if (!empty($insert_cols)) {
                    $sql = "INSERT INTO lesson (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param($types, ...$values);
                        if ($stmt->execute()) {
                            $success_msg = 'تم إضافة الدرس بنجاح إلى المنصة!';
                        } else {
                            $error_msg = 'حدث خطأ أثناء الحفظ في قاعدة البيانات: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error_msg = 'خطأ في تجهيز الاستعلام: ' . $conn->error;
                    }
                } else {
                    $error_msg = 'تعذر العثور على أعمدة مناسبة في جدول lesson.';
                }
            } catch (Throwable $e) {
                $error_msg = 'حدث خطأ في قاعدة البيانات: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'لا يوجد اتصال بقاعدة البيانات.';
        }
    } else {
        $error_msg = 'يرجى إدخال عنوان الدرس على الأقل.';
    }
}

// 1. الدروس الـ 6 الأساسية والثابتة
$lessons = [
    [
        'id' => 1,
        'title' => 'الحروف',
        'desc' => 'تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.',
        'icon' => '☝️',
        'video_url' => ''
    ],
    [
        'id' => 2,
        'title' => 'الأرقام',
        'desc' => 'اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.',
        'icon' => '👉',
        'video_url' => ''
    ],
    [
        'id' => 3,
        'title' => 'الكلمات الأساسية',
        'desc' => 'مجموعة مختارة من الكلمات اليومية الشائعة (نعم، لا، لماذا/كيف) للتواصل السريع والسهل.',
        'icon' => '👌',
        'video_url' => ''
    ],
    [
        'id' => 4,
        'title' => 'الألوان',
        'desc' => 'تعلم الإشارات الخاصة بالألوان الأساسية وكيفية التعبير عنها بوضوح وسهولة.',
        'icon' => '🤏',
        'video_url' => ''
    ],
    [
        'id' => 5,
        'title' => 'المشاعر',
        'desc' => 'اكتشاف طريقة التعبير عن السعادة والحزن والغضب بلغة الإشارة وتعابير الوجه.',
        'icon' => '😊',
        'video_url' => ''
    ],
    [
        'id' => 6,
        'title' => 'أفراد العائلة',
        'desc' => 'تعلم إشارات أفراد الأسرة (الأب، الأم، الأخ، الأخت) لتعزيز التواصل العائلي.',
        'icon' => '👨',
        'video_url' => ''
    ]
];

// 2. دمج الدروس الموجودة في جدول lesson مع الدروس الـ 6 بأمان
if (isset($conn)) {
    try {
        $res = @mysqli_query($conn, "SELECT * FROM lesson");
        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $db_title = trim($row['title'] ?? '');
                if ($db_title === '') {
                    continue;
                }

                $exists = false;
                foreach ($lessons as $l) {
                    if ($l['title'] === $db_title) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $raw_content = $row['content'] ?? $row['description'] ?? 'محتوى تعليمي إضافي.';
                    $extracted_video = $row['video_url'] ?? $row['youtube_url'] ?? $row['url'] ?? '';

                    // إذا كان رابط يوتيوب مكتوباً داخل content نستخرجه لعرضه كزر فيديو
                    if ($extracted_video === '' && preg_match('/https?:\/\/\S+/i', $raw_content, $matches)) {
                        $extracted_video = $matches[0];
                    }

                    $lessons[] = [
                        'id' => $row['lesson_id'] ?? $row['id'] ?? 99,
                        'title' => $db_title,
                        'desc' => $raw_content,
                        'icon' => $row['icon'] ?? '📚',
                        'video_url' => $extracted_video
                    ];
                }
            }
        }
    } catch (Throwable $e) {
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إدارة الدروس - المعلم</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Tajawal', sans-serif; background: #f8fafc; color: #1e293b; min-height: 100vh; }

    .navbar { width: 100%; background-color: #1e293b; color: #ffffff; padding: 15px 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
    .navbar .brand { font-size: 20px; font-weight: 800; color: #ffffff; text-decoration: none; }
    .navbar nav { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
    .navbar nav a { text-decoration: none; color: #cbd5e1; font-weight: 600; font-size: 15px; padding: 6px 12px; border-radius: 8px; transition: all 0.2s ease; }
    .navbar nav a:hover, .navbar nav a.active { color: #ffffff; background-color: #2563eb; }
    .navbar .btn-logout { background-color: #2563eb; color: #ffffff !important; padding: 6px 16px; border-radius: 8px; font-weight: 700; }

    .container { width: 100%; max-width: 1100px; margin: 0 auto; padding: 40px 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { font-size: 24px; font-weight: 800; color: #0f172a; }

    .btn { padding: 10px 20px; border-radius: 8px; font: 700 14px 'Tajawal'; cursor: pointer; text-decoration: none; border: 1px solid #cbd5e1; background: #ffffff; color: #1e293b; transition: background 0.2s; display: inline-block; }
    .btn-primary { background: #2563eb; color: #ffffff; border-color: #2563eb; }
    .btn-primary:hover { background: #1d4ed8; }

    .form-container { background: #ffffff; border-radius: 12px; padding: 24px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; display: none; }
    .form-container.active { display: block; animation: fadeIn 0.2s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    .form-group { margin-bottom: 16px; text-align: right; }
    .form-group label { display: block; font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 6px; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: 'Tajawal', sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; outline: none; }
    .form-group input:focus, .form-group textarea:focus { border-color: #2563eb; background: #ffffff; }
    .form-group textarea { resize: vertical; min-height: 90px; }

    .lessons-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 22px; }
    .lesson-card { background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0; color: #1e293b; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.25s, box-shadow 0.25s; }
    .lesson-card:hover { transform: translateY(-4px); border-color: #2563eb; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.08); }
    .lesson-info h2 { font-size: 19px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
    .lesson-info p { font-size: 14.5px; color: #475569; font-weight: 500; line-height: 1.7; margin-bottom: 14px; white-space: pre-line; }
    .video-link { display: inline-block; margin-bottom: 16px; font-size: 13.5px; font-weight: 700; color: #dc2626; text-decoration: none; background: #fef2f2; padding: 6px 12px; border-radius: 8px; border: 1px solid #fecaca; }
    .video-link:hover { background: #fee2e2; }
    .lesson-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    .lesson-icon { font-size: 32px; }
    .card-btn { background: #2563eb; color: #ffffff; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; }
    .card-btn:hover { background: #1d4ed8; }

    .alert-success { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; border-right: 4px solid #16a34a; }
    .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; border-right: 4px solid #dc2626; }
</style>
</head>
<body>

    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav>
            <a href="index.php">الرئيسية</a>
            <a href="lessons2.php" class="active">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="student.php">الطلاب</a>
            <a href="profile.php">الإعدادات</a>
            <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
        </nav>
    </header>

    <main class="container">

        <div class="page-header">
            <h1>لوحة المعلم: إدارة واستعراض الدروس</h1>
            <button class="btn btn-primary" onclick="toggleCreateForm()">+ إضافة درس جديد</button>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?= e($error_msg) ?></div>
        <?php endif; ?>

        <!-- نموذج إضافة درس جديد -->
        <div class="form-container" id="createFormContainer">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; color: #0f172a;">إضافة درس جديد للقائمة</h2>
            <form method="POST" action="lessons2.php">
                <input type="hidden" name="action" value="create_lesson">

                <div class="form-group">
                    <label for="title">عنوان الدرس</label>
                    <input type="text" id="title" name="title" required placeholder="مثال: الحيوانات بلغة الإشارة">
                </div>

                <div class="form-group">
                    <label for="description">وصف / محتوى الدرس</label>
                    <textarea id="description" name="description" required placeholder="اكتب نبذة مختصرة عن محتوى الدرس..."></textarea>
                </div>

                <div class="form-group">
                    <label for="video_url">رابط فيديو الدرس (YouTube)</label>
                    <input type="url" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=... أو https://youtu.be/...">
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
                    <button type="submit" class="btn btn-primary">حفظ ونشر الدرس</button>
                    <button type="button" class="btn" onclick="toggleCreateForm()">إلغاء</button>
                </div>
            </form>
        </div>

        <!-- شبكة الدروس -->
        <div class="lessons-grid">
            <?php foreach ($lessons as $l): ?>
                <div class="lesson-card">
                    <div class="lesson-info">
                        <h2><?= e($l['title']) ?></h2>
                        <p><?= e($l['desc']) ?></p>
                        <?php if (!empty($l['video_url'])): ?>
                            <a href="<?= e($l['video_url']) ?>" target="_blank" rel="noopener noreferrer" class="video-link">▶ مشاهدة فيديو اليوتيوب</a>
                        <?php endif; ?>
                    </div>
                    <div class="lesson-footer">
                        <span class="lesson-icon"><?= $l['icon'] ?></span>
                        <a href="lesson_details.php?id=<?= (int)$l['id'] ?>&title=<?= urlencode($l['title']) ?><?= !empty($l['video_url']) ? '&video=' . urlencode($l['video_url']) : '' ?>" class="card-btn">عرض الدرس</a>
                    </div>
                </div>
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