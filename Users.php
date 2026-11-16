<?php
session_start();
include 'db_connect.php';
$users = [
    ['id' => 1, 'name' => 'عبدالله', 'role' => 'طالب'],
    ['id' => 2, 'name' => 'احمد',    'role' => 'طالب'],
    ['id' => 3, 'name' => 'محمد',    'role' => 'ولي امر'],
    ['id' => 4, 'name' => 'علي',     'role' => 'معلم'],
    ['id' => 5, 'name' => 'حسن',     'role' => 'طالب'],
    ['id' => 6, 'name' => 'سعد',     'role' => 'ولي امر'],
];

$current = 'users';

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$avatar = '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="9" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>';

$nav = [
    ['key' => 'profile', 'label' => 'الإعدادات', 'href' => 'profile.php', 'icon' => '⚙'],
    ['key' => 'lessons',  'label' => 'الدروس',    'href' => 'lesson.php',  'icon' => '✋'],
    ['key' => 'home',     'label' => 'الرئيسية',  'href' => 'index.php',    'icon' => '⌂'],
    ['key' => 'game',    'label' => 'الالعاب',   'href' => 'game.php',    'icon' => '🎮'],
    ['key' => 'users',    'label' => 'المستخدمين','href' => 'users.php',    'icon' => '👥'],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>المستخدمين</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #ffffff;
    --header: #b9d6ff;
    --pill: #b9d6ff;
    --icon-circle: #4f7bb0;
    --ink: #111;
    --muted: #9a9a9a;
    --border: #8a8a8a;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Cairo', Tahoma, sans-serif;
    background: #e9e9e9;
    color: var(--ink);
  }
  .app {
    max-width: 420px;
    min-height: 100vh;
    margin: 0 auto;
    background: #f3f3f3;
    position: relative;
    padding-bottom: 110px;
  }

  
  .top {
    background: var(--header);
    display: flex; align-items: center; justify-content: center;
    padding: 12px 16px; position: relative;
  }
  .top h1 { font-size: 26px; font-weight: 800; }
  .menu-btn {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    width: 34px; height: 34px; border-radius: 50%;
    border: 2px solid var(--ink); background: #fff; font-size: 18px;
    cursor: pointer; line-height: 1;
  }

  
  .actions { display: flex; gap: 14px; padding: 16px 14px 8px; }
  .pill {
    flex: 1; display: flex; align-items: center; gap: 8px;
    background: var(--pill); border: 2px solid var(--border);
    border-radius: 18px; padding: 8px 10px;
    text-decoration: none; color: var(--ink);
    font-weight: 800; font-size: 14px; line-height: 1.2;
    box-shadow: 0 2px 4px rgba(0,0,0,.25);
  }
  .pill .ic {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--icon-circle); color: #fff;
    display: grid; place-items: center; font-size: 18px; flex-shrink: 0;
  }

  
  .list { padding: 8px 12px; display: flex; flex-direction: column; gap: 16px; }
  .card {
    background: #fff; border: 2px solid var(--border);
    border-radius: 18px; box-shadow: 0 2px 4px rgba(0,0,0,.2);
    overflow: hidden;
  }
  .card-head {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 12px; cursor: pointer; width: 100%;
    background: none; border: 0; font-family: inherit; text-align: right;
  }
  .avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: #d6d6d6; color: #9a9a9a;
    display: grid; place-items: center; overflow: hidden; flex-shrink: 0;
  }
  .avatar svg { width: 100%; height: 100%; }
  .info { flex: 1; }
  .info .name { font-size: 22px; font-weight: 800; line-height: 1.1; }
  .info .role { font-size: 14px; font-weight: 700; margin-top: 2px; }
  .chev { transition: transform .2s; font-size: 22px; }
  .card.open .chev { transform: rotate(180deg); }
  .card-body {
    display: none; padding: 0 14px 14px; gap: 8px; flex-wrap: wrap;
  }
  .card.open .card-body { display: flex; }
  .btn {
    flex: 1; text-align: center; padding: 8px; border-radius: 12px;
    border: 2px solid var(--border); background: var(--pill);
    font-family: inherit; font-weight: 700; cursor: pointer; text-decoration: none; color: var(--ink);
  }
  .btn.danger { background: #ffd0d0; }

  
  .bottom {
    position: fixed; bottom: 0; left: 50%; transform: translateX(-50%);
    width: 100%; max-width: 420px; height: 72px;
    background: var(--header);
    display: flex; justify-content: space-around; align-items: center;
    padding: 0 6px;
  }
  .nav-item {
    flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px;
    text-decoration: none; color: var(--ink); font-size: 11px; font-weight: 800;
  }
  .nav-item .ic { font-size: 24px; line-height: 1; }
  .nav-item.active .ic {
    background: #fff; border-radius: 50%; width: 36px; height: 36px;
    display: grid; place-items: center; font-size: 20px;
  }
  .nav-item.home .ic {
    background: #fff; border: 3px solid var(--header); outline: 3px solid #fff;
    width: 70px; height: 70px; border-radius: 50%;
    display: grid; place-items: center; font-size: 34px;
    margin-top: -38px; box-shadow: 0 3px 6px rgba(0,0,0,.3);
  }
</style>
</head>
<body>
<div class="app">

  <header class="top">
    <h1>المستخدمين</h1>
    <button class="menu-btn" aria-label="المزيد">•••</button>
  </header>

  <div class="actions">
    <a class="pill" href="notify_users.php">
      <span class="ic">📣</span>
      <span>اشعار<br>المستخدمين</span>
    </a>
    <a class="pill" href="permissions.php">
      <span class="ic">✎</span>
      <span>صلاحيات<br>المستخدمين</span>
    </a>
  </div>

  <main class="list">
    <?php foreach ($users as $u): ?>
      <section class="card">
        <button class="card-head" type="button" onclick="this.parentElement.classList.toggle('open')">
          <span class="avatar"><?= $avatar ?></span>
          <span class="info">
            <div class="name"><?= e($u['name']) ?></div>
            <div class="role"><?= e($u['role']) ?></div>
          </span>
          <span class="chev">⌄</span>
        </button>
        <div class="card-body">
          <a class="btn" href="edit_user.php?id=<?= (int)$u['id'] ?>">تعديل</a>
          <a class="btn danger" href="delete_user.php?id=<?= (int)$u['id'] ?>"
             onclick="return confirm('هل تريد حذف هذا المستخدم؟')">حذف</a>
        </div>
      </section>
    <?php endforeach; ?>
  </main>

  <nav class="bottom">
    <?php foreach ($nav as $n):
      $cls = 'nav-item';
      if ($n['key'] === 'home') $cls .= ' home';
      if ($n['key'] === $current) $cls .= ' active';
    ?>
      <a class="<?= $cls ?>" href="<?= e($n['href']) ?>">
        <span class="ic"><?= $n['icon'] ?></span>
        <span><?= e($n['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

</div>
</body>
</html>
