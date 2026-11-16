<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $insert_query = "INSERT INTO lesson (title, description) VALUES ('$title', '$description')";
    if (mysqli_query($conn, $insert_query)) {
        $message = "تم إضافة الدرس بنجاح وقاعدة البيانات محدثة!";
    } else {
        $message = "حدث خطأ أثناء الإضافة: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة درس جديد - منصة وصلة</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="main-container" style="max-width: 600px; margin-top: 50px;">
        <div class="card" style="padding: 30px;">
            <h2>إضافة درس جديد إلى المنصة</h2>
            <?php if (!empty($message)): ?>
                <p style="color: green; font-weight: bold; margin: 15px 0;"><?php echo $message; ?></p>
            <?php endif; ?>
            <form action="add_lesson.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px; font-weight:bold;">عنوان الدرس:</label>
                    <input type="text" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom: 5px; font-weight:bold;">وصف أو تفاصيل الدرس:</label>
                    <textarea name="description" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
                </div>
                <button type="submit" class="card-btn" style="border: none; cursor: pointer; font-size: 1rem;">حفظ وحفظ في قاعدة البيانات</button>
                <a href="lesson.php" style="margin-right: 15px; text-decoration: none; color: #555;">العودة لقائمة الدروس</a>
            </form>
        </div>
    </main>
</body>
</html>