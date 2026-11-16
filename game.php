<?php
$game = [
  'title'    => 'لعبة: قلّد الإشارة',
  'letter'   => 'ا',
  'match'    => 70,
  'score'    => 10,
];
$stars = (int) round($game['match'] / 100 * 5 * 0.86);
$stars = max(0, min(3, $stars));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($game['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Tajawal',sans-serif;background:#222;display:flex;justify-content:center;min-height:100vh}
  .app{width:100%;max-width:400px;min-height:100vh;position:relative;padding-bottom:110px;
       background:linear-gradient(180deg,#bff3ee 0%,#fff3c4 45%,#f6d9ee 100%)}
  header{display:flex;align-items:center;justify-content:center;position:relative;padding:18px 16px 10px}
  header h1{font-size:24px;font-weight:800}
  .back{position:absolute;right:16px;top:16px;font-size:26px;text-decoration:none;color:#000;font-weight:800}
  .menu{position:absolute;left:16px;top:18px;width:32px;height:32px;border-radius:50%;border:2px solid #111;
        background:#fff;font-weight:800;display:grid;place-items:center;cursor:pointer}
  .actions{display:flex;gap:12px;padding:12px 14px}
  .btn{flex:1;padding:12px;border:2px solid #8aa;border-radius:16px;font:700 15px 'Tajawal';
       display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}
  .btn.blue{background:#b9d3ff;border-color:#7fa6e6}
  .btn.green{background:#bff0b4;border-color:#8cd37e}
  .panel{background:#fff;border-radius:24px;margin:12px 16px;padding:16px;box-shadow:0 4px 12px #0001}
  .sign{height:190px;display:flex;flex-direction:column;justify-content:space-between}
  .sign .letter{flex:1;display:grid;place-items:center;font-size:110px;font-weight:800}
  .feedback{display:flex;align-items:center;justify-content:space-between}
  .feedback small{display:block;font-size:11px;font-weight:700}
  .pct{font-size:20px;font-weight:800;color:#3c9d49}
  .stars{color:#f5b301;font-size:20px;letter-spacing:2px}
  .stars .off{color:#bbb}
  .answer{display:flex;gap:8px}
  .circle{width:34px;height:34px;border-radius:50%;border:2px solid #333;font-size:18px;font-weight:800;cursor:pointer}
  .circle.ok{background:#b9f5b0;color:#1a7a22}
  .circle.no{background:#ff9f9f;color:#a11}
  .trophy{text-align:center;padding:24px 16px;
          background:repeating-conic-gradient(#fde8ea 0 25%,#fff 0 50%) 0 0/60px 60px;border-radius:24px}
  .trophy .cup{font-size:110px;line-height:1}
  .trophy h2{font-size:28px;font-weight:800;margin-top:8px;background:#fffc;display:inline-block;padding:0 12px;border-radius:12px}
  nav{position:fixed;bottom:0;width:100%;max-width:400px;display:flex;justify-content:space-around;align-items:flex-end;
      padding:10px 6px 12px;background:linear-gradient(180deg,#d9f7f3,#bff3ee);border-top:1px solid #0001}
  nav a{text-decoration:none;color:#000;font-size:11px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:2px}
  nav a i{font-style:normal;font-size:24px}
  nav a.home i{width:64px;height:64px;border-radius:50%;background:#b8f0ea;border:3px solid #fff;
               display:grid;place-items:center;margin-top:-34px;font-size:30px;box-shadow:0 4px 10px #0002}
  nav a.active i{background:#fff;border-radius:50%;width:38px;height:38px;display:grid;place-items:center}
</style>
</head>
<body>
<div class="app">
  <header>
    <a class="back" href="games.php" aria-label="رجوع">›</a>
    <h1><?= htmlspecialchars($game['title']) ?></h1>
    <div class="menu">•••</div>
  </header>

  <div class="actions">
    <button class="btn blue">✎ تحرير اللعبة</button>
    <button class="btn green">⌄ تقدم الطلاب</button>
  </div>

  <section class="panel sign">
    <div class="letter"><?= htmlspecialchars($game['letter']) ?></div>
    <div class="feedback">
      <div>
        <small>نتيجة التطابق</small>
        <span class="pct"><?= $game['match'] ?>%</span>
        <span class="stars">
          <?php for ($i = 0; $i < 3; $i++): ?>
            <span class="<?= $i < $stars ? '' : 'off' ?>">★</span>
          <?php endfor; ?>
        </span>
      </div>
      <div class="answer">
        <button class="circle ok" aria-label="صح">✓</button>
        <button class="circle no" aria-label="خطأ">✕</button>
      </div>
    </div>
  </section>

  <section class="panel trophy">
    <div class="cup">🏆</div>
    <h2>Score <?= (int) $game['score'] ?></h2>
  </section>

  <nav>
    <a href="settings.php"><i>⚙️</i>الإعدادات</a>
    <a href="lessons.php"><i>✋</i>الدروس</a>
    <a href="index.php" class="home"><i>🏠</i>الرئيسية</a>
    <a href="games.php" class="active"><i>🎮</i>الالعاب</a>
    <a href="students.php"><i>👥</i>الطلاب</a>
  </nav>
</div>
</body>
</html>
