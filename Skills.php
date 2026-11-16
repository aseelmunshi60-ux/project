<?php
seession_start();
include  'db_connect.php';
$skills = [
    ['name' => 'الحروف', 'value' => 95],
    ['name' => 'الأرقام', 'value' => 60],
    ['name' => 'الإشارات الأساسية', 'value' => 96],
];

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

    <title>تفاصيل المهارات</title>

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

        .app {
            width: 100%;
            max-width: 400px;
            min-height: 100vh;
            position: relative;
            padding: 28px 16px 100px;
            background: linear-gradient(
                180deg,
                #b9dcf7 0%,
                #dcecf7 55%,
                #eef5f8 100%
            );
        }

        h1 {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 22px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px #0001;
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .head .pct {
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .progress-toggle {
            width: 100%;
            border: 0;
            background: transparent;
            color: #000;
            font-family: inherit;
            text-align: right;
            cursor: pointer;
        }

        .head .arrow {
            font-size: 12px;
            transition: transform 0.2s;
        }

        .card.open .arrow {
            transform: rotate(180deg);
        }

        .track {
            height: 8px;
            border-radius: 6px;
            background: #e3e3e3;
            margin-top: 6px;
            overflow: hidden;
        }

        .track i {
            display: block;
            height: 100%;
            border-radius: 6px;
        }

        .grid {
            display: none;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px 10px;
            justify-items: center;
            margin-top: 16px;
            padding-bottom: 10px;
        }

        .card.open .grid {
            display: grid;
        }

        .letter {
            position: relative;
            width: 34px;
            height: 28px;
            background: #e8e8e8;
            border-radius: 2px;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 700;
        }

        .letter.done {
            background: #d9f1df;
        }

        .letter.done::after {
            content: '✔';
            position: absolute;
            top: -8px;
            right: -6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            color: #267442;
            font-size: 11px;
            display: grid;
            place-items: center;
            box-shadow: 0 0 0 1px #0002;
        }

        .letter.last {
            grid-column: 3;
        }

        nav {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
            background: #b8f0ea;
            border-top: 1px solid #0001;
        }

        nav a {
            text-decoration: none;
            font-size: 26px;
            color: #000;
            width: 60px;
            min-height: 44px;
            display: grid;
            place-items: center;
        }

        nav a.home {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #b8f0ea;
            border: 3px solid #fff;
            display: grid;
            place-items: center;
            margin-top: -30px;
            font-size: 30px;
            box-shadow: 0 4px 10px #0002;
        }

        button:focus-visible,
        nav a:focus-visible {
            outline: 3px solid #356ac3;
            outline-offset: 3px;
        }

        @media (max-width: 350px) {
            .app {
                padding-inline: 10px;
            }

            .grid {
                gap: 16px 6px;
            }
        }
    </style>
</head>

<body>
<div class="app">

    <h1>تفاصيل المهارات</h1>

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
                    class="letter <?= in_array($letter, $completed, true) ? 'done' : '' ?> <?= $letter === 'z' ? 'last' : '' ?>"
                    <?= in_array($letter, $completed, true) ? 'aria-label="' . e($letter) . ' مكتمل"' : 'aria-label="' . e($letter) . '"' ?>
                >
                    <?= e($letter) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <nav aria-label="التنقل الرئيسي">
        <a href="profile.php" aria-label="الإعدادات">⚙️</a>
        <a href="index.php" class="home" aria-label="الرئيسية">🏠</a>
        <a href="Report.php" aria-label="الإنجازات">🏆</a>
    </nav>

</div>

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
