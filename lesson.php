<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الدروس - منصة وصلة التعليمية</title>
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

        /* تنسيق الصفحة والكروت */
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
        }
        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 25px;
            text-align: right;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: #2563eb;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.08);
        }
        .card h3 {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 12px 0;
        }
        .card p {
            font-size: 14.5px;
            color: #475569;
            line-height: 1.7;
            margin: 0 0 20px 0;
        }
        .card-btn {
            display: block;
            text-align: center;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14.5px;
            transition: background 0.2s ease;
        }
        .card-btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

    <!-- القائمة العلوية -->
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

    <!-- المحتوى الرئيسي -->
    <main class="container">
        <h2 class="page-title">قائمة الدروس المتاحة</h2>

        <!-- شبكة الكروت -->
        <div class="cards-grid">
            <?php
            $query = "SELECT * FROM lesson";
            $result = @mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $les_id = $row['lesson_id'] ?? $row['id'] ?? 0;
                    $les_title = urlencode($row['title']);
                    
                    echo '<div class="card">';
                    echo '    <div>';
                    echo '        <h3>' . htmlspecialchars($row["title"]) . '</h3>';
                    echo '        <p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '    </div>';
                    echo '    <a href="lesson_details.php?id=' . $les_id . '&title=' . $les_title . '" class="card-btn">عرض الدرس</a>';
                    echo '</div>';
                }
            } else {
            ?>
                <div class="card">
                    <div>
                        <h3>الحروف</h3>
                        <p>تعلم الحروف الهجائية الأساسية بلغة الإشارة وكيفية تشكيل كل حرف باليد بطريقة ممتعة وسهلة.</p>
                    </div>
                    <a href="lesson_details.php?title=الحروف" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الأرقام</h3>
                        <p>اكتشاف طريقة عد الأرقام والتعبير عنها بالإشارات اليدوية لبناء أساس قوي في الرياضيات.</p>
                    </div>
                    <a href="lesson_details.php?title=الأرقام" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الكلمات الأساسية</h3>
                        <p>مجموعة مختارة من الكلمات اليومية الشائعة (نعم، لا، لماذا/كيف) للتواصل السريع والسهل.</p>
                    </div>
                    <a href="lesson_details.php?title=الكلمات الأساسية" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>الألوان</h3>
                        <p>تعلم الإشارات الخاصة بالألوان الأساسية وكيفية التعبير عنها بوضوح وسهولة.</p>
                    </div>
                    <a href="lesson_details.php?title=الألوان" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>المشاعر</h3>
                        <p>اكتشاف طريقة التعبير عن السعادة والحزن والغضب بلغة الإشارة وتعابير الوجه.</p>
                    </div>
                    <a href="lesson_details.php?title=المشاعر" class="card-btn">عرض الدرس</a>
                </div>

                <div class="card">
                    <div>
                        <h3>أفراد العائلة</h3>
                        <p>تعلم إشارات أفراد الأسرة (الأب، الأم، الأخ، الأخت) لتعزيز التواصل العائلي.</p>
                    </div>
                    <a href="lesson_details.php?title=أفراد العائلة" class="card-btn">عرض الدرس</a>
                </div>
            <?php } ?>
        </div>
    </main>

</body>
</html>