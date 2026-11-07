<?php
include 'db_connect.php';
<header>
    <h1>مشروع وصلة</h1>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="lessons.php">قائمة الدروس</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="Login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2 class="page-title">📖 قائمة الدروس المتاحة</h2>

    <div class="lessons-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="lesson-card">';
                echo '  <div class="lesson-card-body">';
                echo '      <h3 class="lesson-title">' . htmlspecialchars($row["title"]) . '</h3>';
                echo '      <p class="lesson-desc">' . htmlspecialchars($row["description"]) . '</p>';
                echo '  </div>';
                echo '  <div class="lesson-card-footer">';
                echo '      <a href="lesson_details.php?id=' . $row["id"] . '" class="btn-view">عرض الدرس</a>';
                echo '  </div>';
                echo '</div>';
            }
        } else {
            // دروس افتراضية للعرض عند بدء التشغيل وقبل ملء الجدول
            echo '
            <div class="lesson-card">
                <div class="lesson-card-body">
                    <h3 class="lesson-title">درس الإشارات الأساسية</h3>
                    <p class="lesson-desc">تعلم أهم الإشارات والتطبيقات بشكل مبسط وتفاعلي للأطفال.</p>
                </div>
                <div class="lesson-card-footer">
                    <a href="#" class="btn-view">عرض الدرس</a>
                </div>
            </div>
            
            <div class="lesson-card">
                <div class="lesson-card-body">
                    <h3 class="lesson-title">درس الحروف والأرقام</h3>
                    <p class="lesson-desc">مجموعة من الأنشطة التفاعلية والتمارين الخاصة بالحروف والكلمات.</p>
                </div>
                <div class="lesson-card-footer">
                    <a href="#" class="btn-view">عرض الدرس</a>
                </div>
            </div>
            ';
        }
        ?>
    </div>
</div>
