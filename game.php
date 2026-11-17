<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

// منع تكرار الجلسة واستدعاء الاتصال بقاعدة البيانات
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لعبة قلّد الإشارة - منصة وصلة التعليمية</title>
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

    /* حاوية المحتوى الرئيسي */
    .container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font: 700 14px 'Tajawal';
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        background: #ffffff;
        color: #1e293b;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: background 0.2s;
    }

    .btn:hover {
        background: #f1f5f9;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .panel {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .sign {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 240px;
    }

    .sign .letter {
        flex: 1;
        display: grid;
        place-items: center;
        font-size: 90px;
        font-weight: 800;
        color: #0f172a;
    }

    .feedback {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #e2e8f0;
        padding-top: 16px;
    }

    .feedback small {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .pct {
        font-size: 22px;
        font-weight: 800;
        color: #16a34a;
    }

    .stars {
        color: #eab308;
        font-size: 20px;
        letter-spacing: 2px;
    }

    .stars .off {
        color: #cbd5e1;
    }

    .answer {
        display: flex;
        gap: 10px;
    }

    .circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        font-size: 18px;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.1s;
    }

    .circle:active {
        transform: scale(0.95);
    }

    .circle.ok {
        background: #dcfce7;
        color: #15803d;
    }

    .circle.no {
        background: #fee2e2;
        color: #b91c1c;
    }

    .trophy {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .trophy .cup {
        font-size: 70px;
        line-height: 1;
        margin-bottom: 12px;
    }

    .trophy h2 {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        background: #ffffff;
        padding: 6px 20px;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 14px 20px;
            flex-direction: column;
            gap: 12px;
        }
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <header class="navbar">
        <div class="brand">منصة وصلة التعليمية</div>
        <nav aria-label="التنقل الرئيسي">
            <a href="index.php">الرئيسية</a>
            <a href="lesson.php">الدروس</a>
            <a href="games.php" class="active">الألعاب</a>
            <a href="Report.php">الإنجازات</a>
            <a href="profile.php">الإعدادات</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- محتوى الصفحة التفاعلية -->
    <main class="container">
        
        <div class="page-header">
            <h1 id="game-title">لعبة: قلّد الإشارة</h1>
            <div class="actions">
                <button class="btn" onclick="resetGame()">🔄 إعادة اللعبة</button>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- لوحة الإشارة والتفاعل -->
            <section class="panel sign">
                <div class="letter" id="letter-display">ا</div>
                <div class="feedback">
                    <div>
                        <small>نتيجة التطابق</small>
                        <span class="pct" id="pct-display">70%</span>
                        <span class="stars" id="stars-display">★★★</span>
                    </div>
                    <div class="answer">
                        <button class="circle ok" onclick="answerGame(true)" aria-label="صح">✓</button>
                        <button class="circle no" onclick="answerGame(false)" aria-label="خطأ">✕</button>
                    </div>
                </div>
            </section>

            <!-- لوحة النقاط والكأس -->
            <section class="panel trophy">
                <div class="cup">🏆</div>
                <h2 id="score-display">Score 10</h2>
            </section>
        </div>

    </main>

    <script>
        const levels = [
            { letter: 'ا', match: 85, scoreInc: 10 },
            { letter: 'ب', match: 90, scoreInc: 15 },
            { letter: 'ت', match: 75, scoreInc: 10 },
            { letter: 'ث', match: 95, scoreInc: 20 },
            { letter: 'ج', match: 80, scoreInc: 15 }
        ];

        let currentIndex = 0;
        let totalScore = 10;

        function updateUI() {
            const current = levels[currentIndex];
            document.getElementById('letter-display').textContent = current.letter;
            document.getElementById('pct-display').textContent = current.match + '%';
            document.getElementById('score-display').textContent = 'Score ' + totalScore;

            let starCount = Math.round((current.match / 100) * 3);
            let starsHtml = '';
            for (let i = 0; i < 3; i++) {
                starsHtml += `<span class="${i < starCount ? '' : 'off'}">★</span>`;
            }
            document.getElementById('stars-display').innerHTML = starsHtml;
        }

        function answerGame(isCorrect) {
            if (isCorrect) {
                totalScore += levels[currentIndex].scoreInc;
                alert('أحسنت! إجابة صحيحة 🎉');
            } else {
                alert('حاول مرة أخرى! الإشارة غير مطابقة تماماً ❌');
            }

            currentIndex = (currentIndex + 1) % levels.length;
            updateUI();
        }

        function resetGame() {
            currentIndex = 0;
            totalScore = 10;
            updateUI();
            alert('تم إعادة اللعبة من جديد!');
        }
    </script>

</body>
</html>