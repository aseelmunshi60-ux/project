<?php

$child = [
    'name'        => 'أحمد',
    'completion'  => 75,
    'minutes'     => 150,
    'weekly'      => [10, 25, 20, 40, 35, 55, 70],
    'last_lesson' => 'درس حرف الياء',
    'badges'      => ['🥇', '🥈', '🥉', '🏅'],
];

$child['completion'] = max(0, min(100, (int) $child['completion']));
$child['minutes'] = max(0, (int) $child['minutes']);

$r = 40;
$circ = 2 * M_PI * $r;
$dash = $circ * $child['completion'] / 100;

$weekly = array_map(
    static fn($v) => max(0, (float) $v),
    $child['weekly']
);

$n = count($weekly);
$max = $n > 0 ? max($weekly) : 0;
$pts = [];

if ($n === 1) {
    $y = $max > 0 ? 54 - ($weekly[0] / $max) * 46 : 54;
    $pts[] = '50,' . round($y, 1);
} elseif ($n > 1) {
    foreach ($weekly as $i => $v) {
        $x = 6 + $i * (88 / ($n - 1));
        $y = $max > 0 ? 54 - ($v / $max) * 46 : 54;
        $pts[] = round($x, 1) . ',' . round($y, 1);
    }
}

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

    <title>تقرير أداء الطفل</title>

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
            padding: 22px 16px 100px;
            background: linear-gradient(
                180deg,
                #b9dcf7 0%,
                #dcecf7 55%,
                #eef5f8 100%
            );
        }

        header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        header svg {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
        }

        header h1 {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.5;
        }

        .card {
            background: #fdf9f8;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px #0001;
        }

        .card h2 {
            font-size: 21px;
            font-weight: 800;
        }

        .split {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .donut {
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .donut svg {
            width: 80px;
            height: 80px;
            display: block;
            margin: 0 auto 4px;
        }

        .time small {
            display: block;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            line-height: 1.7;
        }

        .time svg {
            display: block;
            width: 110px;
            max-width: 100%;
            height: 70px;
            margin-top: 6px;
            overflow: visible;
        }

        .lesson {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .lesson b {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .lesson span {
            font-size: 13px;
        }

        .lesson .ribbon {
            font-size: 34px;
            flex-shrink: 0;
        }

        .badges {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 34px;
            margin-top: 12px;
        }

        nav {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            width: 100%;
            min-height: 60px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 8px 10px;
            background: #b8f0ea;
            border-top: 1px solid #0001;
        }

        nav a {
            text-decoration: none;
            font-size: 26px;
            color: #000;
            width: 60px;
            min-height: 42px;
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

        nav a:focus-visible {
            outline: 3px solid #356ac3;
            outline-offset: 3px;
        }

        @media (max-width: 350px) {
            .app {
                padding-inline: 10px;
            }

            header h1 {
                font-size: 19px;
            }

            .card {
                padding: 14px;
            }

            .card h2 {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
<div class="app">

    <header>
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="#000"
            stroke-width="2"
            aria-hidden="true"
        >
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="10" r="3.5"/>
            <path d="M5.5 19c1.5-3 4-4 6.5-4s5 1 6.5 4"/>
        </svg>

        <h1>تقرير أداء الطفل <?= e($child['name']) ?></h1>
    </header>

    <section class="card split">
        <h2>نسبة الإنجاز</h2>

        <div class="donut">
            <svg
                viewBox="0 0 100 100"
                role="img"
                aria-label="نسبة الإنجاز <?= $child['completion'] ?> بالمئة"
            >
                <circle
                    cx="50"
                    cy="50"
                    r="<?= $r ?>"
                    fill="none"
                    stroke="#e5e5e5"
                    stroke-width="14"
                />

                <circle
                    cx="50"
                    cy="50"
                    r="<?= $r ?>"
                    fill="none"
                    stroke="#000"
                    stroke-width="14"
                    stroke-dasharray="<?= round($dash, 1) ?> <?= round($circ, 1) ?>"
                    transform="rotate(-90 50 50)"
                />
            </svg>

            <?= $child['completion'] ?>%
        </div>
    </section>

    <section class="card split time">
        <h2>وقت التعلم</h2>

        <div>
            <small>
                <?= $child['minutes'] ?> دقيقة
                <br>
                هذا الأسبوع
            </small>

            <svg
                viewBox="0 0 100 60"
                role="img"
                aria-label="رسم بياني لدقائق التعلم خلال الأسبوع"
            >
                <path
                    d="M2 2V58H98"
                    fill="none"
                    stroke="#000"
                    stroke-width="2"
                />

                <?php if (count($pts) > 0): ?>
                    <polyline
                        points="<?= e(implode(' ', $pts)) ?>"
                        fill="none"
                        stroke="#000"
                        stroke-width="3"
                        stroke-linejoin="round"
                        stroke-linecap="round"
                    />
                <?php endif; ?>
            </svg>
        </div>
    </section>

    <section class="card lesson">
        <div>
            <b>آخر درس تم إكماله</b>
            <span><?= e($child['last_lesson']) ?></span>
        </div>

        <div class="ribbon" aria-hidden="true">🏅</div>
    </section>

    <section class="card">
        <h2>أحدث الشارات</h2>

        <div class="badges">
            <?php foreach ($child['badges'] as $badge): ?>
                <span><?= e($badge) ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <nav aria-label="التنقل الرئيسي">
        <a href="profile.php" aria-label="الإعدادات">⚙️</a>
        <a href="index.php" class="home" aria-label="الرئيسية">🏠</a>
        <a href="achievements.php" aria-label="الإنجازات">🏆</a>
    </nav>

</div>
</body>
</html>
