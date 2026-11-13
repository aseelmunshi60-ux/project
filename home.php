<?php
session_start();
$current = 'home';
$userName = $_SESSION['user_name'] ?? 'المعلم';

$notifications = [
    ['icon' => '⭐', 'text' => 'قام جميع الطلبة بانهاء الدرس', 'time' => 'منذ ساعة'],
    ['icon' => '📋', 'text' => 'انتهى موعد تسليم النشاط',     'time' => 'منذ ساعتين'],
];

$items = [
    ['key' => 'profile', 'label' => 'الإعدادات', 'href' => 'profile.php', 'icon' => '⚙'],
    ['key' => 'lessons',  'label' => 'الدروس',    'href' => 'lessons.php',  'icon' => '✋'],
    ['key' => 'home',     'label' => 'الرئيسية',  'href' => 'home.php',     'icon' => '⌂'],
    ['key' => 'game',    'label' => 'الالعاب',   'href' => 'game.php',    'icon' => '🎮'],
    ['key' => 'student', 'label' => 'الطلاب',    'href' => 'student.php', 'icon' => '👥'],
];

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$avatar = '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="9" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>الشاشة الرئيسية</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #111;
  --border: #8a8a8a;
  --blue: #b9d6ff;
  --green: #b5ffb5;
  --pink: #ffa8a8;
  --yellow: #ffffb0;
  --lavender: #ece4ff;
  --nav: #b4eeee;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Cairo', Tahoma, sans-serif;
  background: #e6e6e6;
  color: var(--ink);
}
.app {
  max-width: 420px;
  min-height: 100vh;
  margin: 0 auto;
  background: linear-gradient(160deg, #d9f7f7 0%, #fff6d6 45%, #ffe3ee 100%);
  position: relative;
  padding-bottom: 110px;
}
.top {
  background: linear-gradient(90deg, #b4eeee, #d9f3ff);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 16px;
  position: relative;
}
.top h1 { font-size: 26px; font-weight: 800; }
.round-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 2px solid var(--ink);
  background: #fff;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  display: grid;
  place-items: center;
  text-decoration: none;
  color: var(--ink);
  font-weight: 800;
}
.round-btn.left { left: 14px; }
.round-btn.right { right: 14px; border: 0; background: none; font-size: 26px; }

.pill {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 2px solid var(--border);
  border-radius: 18px;
  padding: 8px 10px;
  text-decoration: none;
  color: var(--ink);
  font-weight: 800;
  font-size: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,.25);
}
.pill .ic {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #4f7bb0;
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}
.pill.blue { background: var(--blue); }
.pill.green { background: var(--green); }
.pill.green .ic { background: #4f86a8; }

.bottom {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 420px;
  height: 72px;
  background: var(--nav);
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 0 6px;
}
.nav-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  text-decoration: none;
  color: var(--ink);
  font-size: 11px;
  font-weight: 800;
}
.nav-item .ic { font-size: 24px; line-height: 1; }
.nav-item.active .ic {
  background: #fff;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  font-size: 20px;
}
.nav-item.home .ic {
  background: #fff;
  border: 3px solid var(--nav);
  outline: 3px solid #fff;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 34px;
  margin-top: -38px;
  box-shadow: 0 3px 6px rgba(0,0,0,.3);
}

.welcome {
  background: #fff;
  border: 2px solid var(--border);
  border-radius: 0 0 22px 22px;
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 26px 24px;
  font-size: 22px;
  font-weight: 800;
  box-shadow: 0 2px 4px rgba(0,0,0,.2);
}
.avatar {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #d6d6d6;
  color: #9a9a9a;
  overflow: hidden;
  flex-shrink: 0;
}
.avatar svg { width: 100%; height: 100%; }
.tiles {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  padding: 36px 16px 20px;
}
.tile {
  height: 112px;
  border: 2px solid var(--border);
  border-radius: 22px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  color: var(--ink);
  font-weight: 800;
  font-size: 14px;
  box-shadow: 0 2px 4px rgba(0,0,0,.2);
}
.tile .ic { font-size: 50px; line-height: 1; }
.tile.pink { background: var(--pink); }
.tile.green { background: var(--green); }
.tile.blue { background: var(--blue); }
.tile.yellow { background: var(--yellow); }
.section-title { font-size: 22px; font-weight: 800; padding: 16px 20px 8px; }
.notes {
  margin: 0 14px;
  background: #fff;
  border: 2px solid var(--border);
  border-radius: 22px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.note {
  background: var(--lavender);
  border-radius: 16px;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.note .ic { font-size: 32px; }
.note .txt { flex: 1; font-weight: 800; font-size: 13px; }
.note .time { display: block; color: #777; font-weight: 600; font-size: 13px; margin-top: 4px; }

.actions { display: flex; gap: 14px; padding: 16px 14px 8px; }
.lessons { padding: 8px 14px; display: flex; flex-direction: column; gap: 22px; }
.lesson {
  background: #fff;
  border: 2px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,.2);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  text-decoration: none;
  color: var(--ink);
  min-height: 118px;
}
.lesson .body { flex: 1; }
.lesson h2 { font-size: 22px; font-weight: 800; }
.lesson p { font-size: 11px; font-weight: 600; line-height: 1.5; margin-top: 4px; }
.lesson .hand { font-size: 62px; line-height: 1; }
</style>
</head>
<body>
<div class="app">

  <header class="top">
    <h1>الشاشة الرئيسية</h1>
    <button class="round-btn left" aria-label="المزيد">•••</button>
  </header>

  <section class="welcome">
    <span class="avatar"><?= $avatar ?></span>
    <span>مرحبا (<?= e($userName) ?>)</span>
  </section>

  <section class="tiles">
    <a class="tile pink" href="game.php"><span class="ic">🎮</span>إدارة الالعاب</a>
    <a class="tile green" href="lessons2.php"><span class="ic">✋</span>إدارة الدروس</a>
    <a class="tile blue" href="profile.php"><span class="ic">⚙</span>الإعدادات</a>
    <a class="tile yellow" href="student.php"><span class="ic">👥</span>الطلاب</a>
  </section>

  <h2 class="section-title">الاشعارات:</h2>
  <section class="notes">
    <?php foreach ($notifications as $n): ?>
      <div class="note">
        <span class="ic"><?= $n['icon'] ?></span>
        <span class="txt">
          <?= e($n['text']) ?>
          <span class="time"><?= e($n['time']) ?></span>
        </span>
      </div>
    <?php endforeach; ?>
  </section>

  <nav class="bottom">
  <?php foreach ($items as $n):
    $cls = 'nav-item';
    if ($n['key'] === 'home') $cls .= ' home';
    if ($n['key'] === $current) $cls .= ' active';
  ?>
    <a class="<?= $cls ?>" href="<?= htmlspecialchars($n['href'], ENT_QUOTES, 'UTF-8') ?>">
      <span class="ic"><?= $n['icon'] ?></span>
      <span><?= htmlspecialchars($n['label'], ENT_QUOTES, 'UTF-8') ?></span>
    </a>
  <?php endforeach; ?>
</nav>
</div>
</body>
</html>
