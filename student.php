<?php
$student = [
  ['name' => 'احمد',    'grade' => 80,  'skills' => 70,  'present' => true,
   'details' => ['دقة الحركات' => 80, 'المواظبة' => 100, 'سرعة الاتقان' => 30]],
  ['name' => 'عبدالله', 'grade' => 60,  'skills' => 30,  'present' => false,
   'details' => ['دقة الحركات' => 50, 'المواظبة' => 40,  'سرعة الاتقان' => 20]],
  ['name' => 'حسن',     'grade' => 100, 'skills' => 100, 'present' => true,
   'details' => ['دقة الحركات' => 100, 'المواظبة' => 100, 'سرعة الاتقان' => 90]],
  ['name' => 'عمر',     'grade' => 20,  'skills' => 10,  'present' => false,
   'details' => ['دقة الحركات' => 20, 'المواظبة' => 30,  'سرعة الاتقان' => 10]],
];

function level_class($v) { return $v >= 70 ? 'good' : ($v >= 40 ? 'mid' : 'low'); }

function bar($percent) {
  $total  = 15;
  $filled = (int) round($percent / 100 * $total);
  $colors = ['#ff8f8f','#ff9a8d','#ffa58c','#ffb48a','#ffc388','#ffd286','#ffe086',
             '#f3e784','#e0ec82','#cdee82','#b9ee82','#a8ec86','#98ea8a','#8ae88e','#7fe692'];
  $html = '<div class="bar">';
  for ($i = 0; $i < $total; $i++) {
    $c = $i < $filled ? $colors[$i] : '#ddd';
    $html .= '<span style="background:' . $c . '"></span>';
  }
  return $html . '</div>';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>الطلاب</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Tajawal',sans-serif;background:#222;display:flex;justify-content:center;min-height:100vh}
  .app{width:100%;max-width:400px;min-height:100vh;position:relative;padding-bottom:110px;
       background:linear-gradient(180deg,#bff3ee 0%,#fff3c4 45%,#f6d9ee 100%)}
  header{display:flex;align-items:center;justify-content:center;position:relative;padding:18px 16px 10px}
  header h1{font-size:26px;font-weight:800}
  .menu{position:absolute;left:16px;top:18px;width:32px;height:32px;border-radius:50%;border:2px solid #111;
        background:#fff;font-weight:800;display:grid;place-items:center;cursor:pointer}
  .actions{display:flex;gap:12px;padding:12px 14px}
  .btn{flex:1;padding:12px;border:2px solid #8aa;border-radius:16px;font:700 15px 'Tajawal';
       display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}
  .btn.blue{background:#b9d3ff;border-color:#7fa6e6}
  .btn.green{background:#bff0b4;border-color:#8cd37e}
  .list{padding:4px 14px;display:flex;flex-direction:column;gap:12px}
  .card{background:#fff;border:2px solid #444;border-radius:18px;padding:12px 14px}
  .row{display:flex;align-items:center;gap:10px}
  .avatar{width:56px;height:56px;border-radius:50%;background:#aaa;display:grid;place-items:center;flex-shrink:0}
  .avatar svg{width:36px;height:36px;fill:#eee}
  .info{flex:1}
  .info h2{font-size:21px;font-weight:800}
  .meta{font-size:14px;font-weight:500;margin-top:2px}
  .badge{font-size:11px;font-weight:700;padding:0 8px;border-radius:8px;border:1px solid #0002;margin-inline-start:4px}
  .good{background:#c9f5a8}.mid{background:#ffd9a0}.low{background:#ffb9a6}
  .status{width:34px;height:34px;border-radius:50%;border:2px solid #333;display:grid;place-items:center;
          font-weight:800;font-size:18px}
  .status.yes{background:#b9f5b0;color:#1a7a22}
  .status.no{background:#ff9f9f;color:#a11}
  .toggle{background:none;border:none;font-size:22px;cursor:pointer;transition:transform .2s}
  .card.open .toggle{transform:rotate(180deg)}
  .details{display:none;margin-top:12px}
  .card.open .details{display:block}
  .d-head{display:flex;justify-content:space-between;font-size:12px;font-weight:700;margin-top:10px}
  .d-head b{color:#2e9b3a}
  .bar{display:flex;gap:3px;margin-top:4px}
  .bar span{flex:1;height:10px;border-radius:6px}
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
    <h1>الطلاب</h1>
    <div class="menu">•••</div>
  </header>

  <div class="actions">
    <button class="btn green">✎ تحرير الدرجات</button>
    <button class="btn blue">📢 اشعار الطلاب</button>
  </div>

  <div class="list">
    <?php foreach ($students as $i => $s): ?>
    <div class="card <?= $i === 0 ? 'open' : '' ?>">
      <div class="row">
        <div class="avatar"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></div>
        <div class="info">
          <h2>الطالب <?= htmlspecialchars($s['name']) ?></h2>
          <div class="meta">درجات الطالب: <span class="badge <?= level_class($s['grade']) ?>"><?= $s['grade'] ?>%</span></div>
          <div class="meta">المهارات: <span class="badge <?= level_class($s['skills']) ?>"><?= $s['skills'] ?>%</span></div>
        </div>
        <div class="status <?= $s['present'] ? 'yes' : 'no' ?>"><?= $s['present'] ? '✓' : '✕' ?></div>
        <button class="toggle" onclick="this.closest('.card').classList.toggle('open')" aria-label="التفاصيل">⌄</button>
      </div>
      <div class="details">
        <?php foreach ($s['details'] as $label => $val): ?>
          <div class="d-head"><span><?= $label ?>.</span><b><?= $val ?>%</b></div>
          <?= bar($val) ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <nav>
    <a href="profile.php"><i>⚙️</i>الإعدادات</a>
    <a href="lesson.php"><i>✋</i>الدروس</a>
    <a href="home.php" class="home"><i>🏠</i>الرئيسية</a>
    <a href="game.php"><i>🎮</i>الالعاب</a>
    <a href="student.php" class="active"><i>👥</i>الطلاب</a>
  </nav>
</div>
</body>
</html>