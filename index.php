<?php
session_start();
include 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة وصلة التعليمية</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        /* الهيدر العلوي */
        .header {
            background-color: #1b2a47;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: #3498db;
        }
        .btn-login {
            background-color: #2980b9;
            color: white !important;
            padding: 8px 18px;
            border-radius: 5px;
            text-decoration: none;
        }
        .brand-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* محتوى الصفحة */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* قسم الترحيب */
        .welcome-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .welcome-box h2 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .welcome-box p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* شبكة الكروت الأربعة */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-top: 0;
        }
        .card p {
            color: #7f8c8d;
            font-size: 13px;
            min-height: 40px;
        }
        .card-btn {
            background-color: #f1f2f6;
            color: #2c3e50;
            padding: 8px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
            display: block;
            transition: background 0.2s;
        }
        .card-btn:hover {
            background-color: #dfe4ea;
        }

        /* شبكة الكروت السفلية */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(48%, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .wide-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .wide-card h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-top: 0;
        }
        .wide-card ul {
            padding-right: 20px;
            margin: 10px 0 0 0;
            color: #57606f;
            font-size: 13px;
        }
        .wide-card li {
            margin-bottom: 8px;
        }

        /* الفوتر */
        footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 13px;
            border-top: 1px solid #dfe4ea;
            margin-top: 50px;
            background-color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- الهيدر العلوي -->
    <div class="header">
        <div class="brand-title">منصة وصلة التعليمية</div>
        <div class="nav-links">
            <a href="index.php">الرئيسية</a>
            <a href="lesson.php">الدروس</a>
            <a href="game.php">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-login">تسجيل الخروج</a>
            <?php else: ?>
                <a href="Login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="container">
        
        <!-- الترحيب -->
        <div class="welcome-box">
            <h2>مرحباً بك في منصة وصلة</h2>
            <p>نواصل بالأسارة، العمل المتواصل، استعرض الدروس والألعاب التفاعلية وتابع تقدمك بكل سهولة.</p>
        </div>

        <!-- الكروت الأربعة الأولى -->
        <div class="grid-4">
            <!-- الدروس -->
            <div class="card">
                <div>
                    <h3>الدروس 📚</h3>
                    <p>تعلم الحروف، الأرقام والكلمات بطريقة تفاعلية وممتعة.</p>
                </div>
                <a href="lesson.php" class="card-btn">استعرض الدروس</a>
            </div>

            <!-- الألعاب -->
            <div class="card">
                <div>
                    <h3>الألعاب 🎮</h3>
                    <p>العب واختبر نفسك على تنمية المهارات الأساسية.</p>
                </div>
                <a href="game.php" class="card-btn">ابدأ اللعب</a>
            </div>

            <!-- الإنجازات -->
            <div class="card">
                <div>
                    <h3>الإنجازات 🏆</h3>
                    <p>تابع مستواك، أوسمتك، والأنشطة التي حصلت عليها.</p>
                </div>
                <a href="Report.php" class="card-btn">عرض الإنجازات</a>
            </div>

            <!-- الإعدادات -->
            <div class="card">
                <div>
                    <h3>الإعدادات ⚙️</h3>
                    <p>إدارة الحساب، الكلمات، وتخصيص الطابع.</p>
                </div>
                <a href="profile.php" class="card-btn">الإعدادات</a>
            </div>
        </div>

        <!-- الكرتين السفليين (الأنشطة والتنبيهات) -->
        <div class="grid-2">
            <!-- الأنشطة الأخيرة -->
            <div class="wide-card">
                <h3>الأنشطة الأخيرة 📌</h3>
                <ul>
                    <li>آخر درس تم إكماله: درس حرف الباء</li>
                    <li>تحدي جديد متاحة: مراجعة الأحرف الإنجليزية</li>
                </ul>
            </div>

            <!-- التنبيهات والإشعارات -->
            <div class="wide-card">
                <h3>التنبيهات والإشعارات 🔔</h3>
                <ul>
                    <li>تهانينا! لقد أكملت مستوى مستخدم جديد.</li>
                    <li>رسالة من المعلم: استمر في مراجعة الواجبة اليومية.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- الفوتر -->
    <footer>
        جميع الحقوق محفوظة © مشروع وصلة 2026
    </footer>

</body>
</html>
