<?php

$game = [
    'title'  => 'لعبة: قلّد الإشارة',
    'letter' => 'ا',
    'match'  => 70,
    'score'  => 10,
];

$game['match'] = max(0, min(100, (int) $game['match']));

$stars = (int) round($game['match'] / 100 * 3);
$stars = max(0, min(3, $stars));

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($game['title']) ?></title>

    <link
        href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #222;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        button {
            font-family: inherit;
        }

        .app {
            width: 100%;
            max-width: 400px;
            min-height: 100vh;
            position: relative;
            padding-bottom: 110px;
            background: linear-gradient(
                180deg,
                #bff3ee 0%,
                #fff3c4 45%,
                #f6d9ee 100%
            );
        }

        header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 18px 45px 10px;
        }

        header h1 {
            font-size: 22px;
            font-weight: 800;
            text-align: center;
        }

        .back {
            position: absolute;
            right: 16px;
            top: 12px;
            font-size: 30px;
            text-decoration: none;
            color: #000;
            font-weight: 800;
        }

        .menu {
            position: absolute;
            left: 16px;
            top: 18px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #111;
            background: #fff;
            font-weight: 800;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .actions {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
        }

        .btn {
            flex: 1;
            min-width: 0;
            padding: 12px 7px;
            border: 2px solid #8aa;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            color: #111;
            transition: transform 0.15s;
        }

        .btn:active,
        .circle:active {
            transform: scale(0.96);
        }

        .btn.blue {
            background: #b9d3ff;
            border-color: #7fa6e6;
        }

        .btn.green {
            background: #bff0b4;
            border-color: #8cd37e;
        }

        .panel {
            background: #fff;
            border-radius: 24px;
            margin: 12px 16px;
            padding: 16px;
            box-shadow: 0 4px 12px #0001;
        }

        .sign {
            min-height: 230px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sign .letter {
            flex: 1;
            min-height: 140px;
            display: grid;
            place-items: center;
            font-size: 110px;
            font-weight: 800;
            color: #222;
        }

        .feedback {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .feedback small {
            display: block;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .pct {
            font-size: 20px;
            font-weight: 800;
            color: #3c9d49;
        }

        .stars {
            color: #f5b301;
            font-size: 20px;
            letter-spacing: 2px;
            white-space: nowrap;
        }

        .stars .off {
            color: #bbb;
        }

        .answer {
            display: flex;
            gap: 8px;
        }

        .circle {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 50%;
            border: 2px solid #333;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.15s;
        }

        .circle.ok {
            background: #b9f5b0;
            color: #1a7a22;
        }

        .circle.no {
            background: #ff9f9f;
            color: #a11;
        }

        .status {
            margin-top: 10px;
            padding: 9px;
            border-radius: 12px;
            background: #f5f5f5;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            display: none;
        }

        .status.show {
            display: block;
        }

        .status.success {
            background: #e1f8df;
            color: #1a7a22;
        }

        .status.error {
            background: #ffe3e3;
            color: #a11;
        }

        .trophy {
            text-align: center;
            padding: 24px 16px;
            background: repeating-conic-gradient(
                #fde8ea 0 25%,
                #fff 0 50%
            ) 0 0 / 60px 60px;
            border-radius: 24px;
        }

        .trophy .cup {
            font-size: 100px;
            line-height: 1.2;
        }

        .trophy h2 {
            font-size: 26px;
            font-weight: 800;
            margin-top: 8px;
            background: #fffc;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 12px;
            direction: ltr;
        }

        nav {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            padding: 10px 4px 12px;
            background: linear-gradient(180deg, #d9f7f3, #bff3ee);
            border-top: 1px solid #0001;
        }

        nav a {
            flex: 1;
            text-decoration: none;
            color: #000;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        nav a i {
            font-style: normal;
            font-size: 24px;
            min-height: 38px;
            display: grid;
            place-items: center;
        }

        nav a.home i {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #b8f0ea;
            border: 3px solid #fff;
            display: grid;
            place-items: center;
            margin-top: -30px;
            font-size: 28px;
            box-shadow: 0 4px 10px #0002;
        }

        nav a.active i {
            background: #fff;
            border-radius: 50%;
            width: 38px;
            height: 38px;
        }

        button:focus-visible,
        a:focus-visible {
            outline: 3px solid #356ac3;
            outline-offset: 3px;
        }

        @media (max-width: 350px) {
            header h1 {
                font-size: 19px;
            }

            .actions {
                gap: 7px;
                padding-inline: 10px;
            }

            .btn {
                font-size: 12px;
            }

            .panel {
                margin-inline: 10px;
            }
        }
    </style>
</head>

<body>

<div class="app">

    <header>
        <a class="back" href="games.php" aria-label="رجوع">›</a>

        <h1><?= e($game['title']) ?></h1>

        <button
            class="menu"
            type="button"
            id="menuButton"
            aria-label="القائمة"
        >•••</button>
    </header>

    <div class="actions">
        <button
            class="btn blue"
            type="button"
            id="editButton"
        >✎ تحرير اللعبة</button>

        <button
            class="btn green"
            type="button"
            id="progressButton"
        >⌄ تقدم الطلاب</button>
    </div>

    <section class="panel sign">

        <div class="letter"><?= e($game['letter']) ?></div>

        <div class="feedback">
            <div>
                <small>نتيجة التطابق</small>

                <span class="pct">
                    <?= $game['match'] ?>%
                </span>

                <span class="stars" aria-label="<?= $stars ?> من 3 نجوم">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <span class="<?= $i < $stars ? '' : 'off' ?>">★</span>
                    <?php endfor; ?>
                </span>
            </div>

            <div class="answer">
                <button
                    class="circle ok"
                    type="button"
                    id="correctButton"
                    aria-label="إجابة صحيحة"
                >✓</button>

                <button
                    class="circle no"
                    type="button"
                    id="wrongButton"
                    aria-label="إجابة خاطئة"
                >✕</button>
            </div>
        </div>

        <div
            class="status"
            id="status"
            role="status"
            aria-live="polite"
        ></div>

    </section>

    <section class="panel trophy">
        <div class="cup" aria-hidden="true">🏆</div>

        <h2>Score <?= (int) $game['score'] ?></h2>
    </section>

    <nav aria-label="التنقل الرئيسي">
        <a href="settings.php">
            <i aria-hidden="true">⚙️</i>
            <span>الإعدادات</span>
        </a>

        <a href="lessons.php">
            <i aria-hidden="true">✋</i>
            <span>الدروس</span>
        </a>

        <a href="index.php" class="home">
            <i aria-hidden="true">🏠</i>
            <span>الرئيسية</span>
        </a>

        <a href="games.php" class="active" aria-current="page">
            <i aria-hidden="true">🎮</i>
            <span>الألعاب</span>
        </a>

        <a href="students.php">
            <i aria-hidden="true">👥</i>
            <span>الطلاب</span>
        </a>
    </nav>

</div>

<script>
    'use strict';

    const statusBox = document.getElementById('status');

    function showStatus(message, type = '') {
        statusBox.textContent = message;
        statusBox.className = 'status show';

        if (type) {
            statusBox.classList.add(type);
        }
    }

    document.getElementById('correctButton').addEventListener('click', () => {
        showStatus('تم تسجيل الإجابة على أنها صحيحة.', 'success');
    });

    document.getElementById('wrongButton').addEventListener('click', () => {
        showStatus('تم تسجيل الإجابة على أنها خاطئة.', 'error');
    });

    document.getElementById('editButton').addEventListener('click', () => {
        showStatus('ميزة تحرير اللعبة تحتاج إلى صفحة تحرير مخصصة.');
    });

    document.getElementById('progressButton').addEventListener('click', () => {
        window.location.href = 'students.php';
    });

    document.getElementById('menuButton').addEventListener('click', () => {
        showStatus('يمكن إضافة خيارات القائمة هنا.');
    });
</script>

</body>
</html>
