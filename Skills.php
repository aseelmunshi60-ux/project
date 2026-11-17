<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

// منع تكرار الجلسة واستدعاء الاتصال بقاعدة البيانات
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$skills = [
    ['name' => 'الحروف', 'value' => 95],
    ['name' => 'الأرقام', 'value' => 60],
    ['name' => 'الإشارات الأساسية', 'value' => 96],
];

// جلب قيم المهارات من قاعدة البيانات إن وجدت (اختياري لزيادة التفاعل)
if (isset($conn)) {
    $result_skills = @mysqli_query($conn, "SELECT * FROM sign LIMIT 3");
    if ($result_skills && mysqli_num_rows($result_skills) > 0) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($result_skills)) {
            if (isset($skills[$i]) && isset($row['value'])) {
                $skills[$i]['value'] = (int)$row['value'];
            }
            $i++;
        }
    }
}

$completed = ['a', 'b', 'c', 'd', 'e', 'f'];
$letters = range('a', 'z');

foreach ($skills as &$skill) {
    $skill['value'] = max(0, min(100, (int) $skill['value']));
}
unset($skill);

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function bar_color($value): string
{
    return $value >= 80 ? '#4f8a6a' : '#d9812f';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تفاصيل المهارات - منصة وصلة التعليمية</title>
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

        /* شريط التنقل العلوي الموحد */
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

        h1 {
            text-align: right;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 25px;
            color: #0f172a;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .head .pct {
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
            color: #475569;
        }

        .progress-toggle {
            width: 100%;
            border: 0;
            background: transparent;
            color: inherit;
            font-family: inherit;
            text-align: right;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
        }

        .head .arrow {
            font-size: 14px;
            transition: transform 0.2s;
            color: #64748b;
        }

        .card.open .arrow {
            transform: rotate(180deg);
        }

        .track {
            height: 10px;
            border-radius: 6px;
            background: #e2e8f0;
            margin-top: 10px;
            overflow: hidden;
        }

        .track i {
            display: block;
            height: 100%;
            border-radius: 6px;
        }

        .grid {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(55px, 1fr));
            gap: 12px;
            justify-items: center;
            margin-top: 16px;
            padding-bottom: 10px;
        }

        .card.open .grid {
            display: grid;
        }

        .letter {
            position: relative;
            width: 40px;
            height: 34px;
            background: #f1f5f9;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: grid;
            place-items: center;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .letter.done {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .letter.done::after {
            content: '✔';
            position: absolute;
            top: -7px;
            right: -6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ffffff;
            color: #15803d;
            font-size: 10px;
            display: grid;
            place-items: center;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        }

        button:focus-visible,
        nav a:focus-visible {
            outline: 3px solid #2563eb;
            outline-offset: 3px;
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

    <!-- شريط التنقل العلوي الموحد -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="index.php">الرئيسية</a>
            <a href="lesson.php">الدروس</a>
            <a href="games.php">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- المحتوى الرئيسي للمهارات -->
    <main class="container">

        <h1>تفاصيل المهارات والتقدم</h1>

        <?php foreach ($skills as $skill): ?>
            <section class="card">
                <div class="head">
                    <span><?= e($skill['name']) ?></span>
                    <span class="pct"><?= $skill['value'] ?>%</span>
                </div>

                <div
                    class="track"
                    role="progressbar"
                    aria-label="<?= e($skill['name']) ?>"
                    aria-valuenow="<?= $skill['value'] ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <i
                        style="width: <?= $skill['value'] ?>%; background: <?= bar_color($skill['value']) ?>;"
                    ></i>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="card open" id="progress">
            <button
                class="head progress-toggle"
                type="button"
                id="progressToggle"
                aria-expanded="true"
                aria-controls="lettersGrid"
            >
                <span>قسم التقدم الدراسي</span>
                <span class="arrow" aria-hidden="true">⌃</span>
            </button>

            <div class="grid" id="lettersGrid" dir="ltr">
                <?php foreach ($letters as $letter): ?>
                    <div
                        class="letter <?= in_array($letter, $completed, true) ? 'done' : '' ?>"
                        <?= in_array($letter, $completed, true) ? 'aria-label="' . e($letter) . ' مكتمل"' : 'aria-label="' . e($letter) . '"' ?>
                    >
                        <?= e($letter) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <script>
        'use strict';

        const progressCard = document.getElementById('progress');
        const progressToggle = document.getElementById('progressToggle');

        progressToggle.addEventListener('click', function () {
            const isOpen = progressCard.classList.toggle('open');
            progressToggle.setAttribute('aria-expanded', String(isOpen));
        });
    </script>

</body>
</html>