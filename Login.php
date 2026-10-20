<?php

include 'db_connect.php';

$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {

    $stmt = $conn->prepare("SELECT * FROM user WHERE (email = ? OR phone = ?) AND password = ?");
        $stmt->bind_param("sss", $email, $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            

            if ($user['role'] == 'guardian') {
                header("Location: guardian_dashboard.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: teacher_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error_message = "البريد الإلكتروني / رقم الجوال أو كلمة المرور غير صحيحة!";
        }
        $stmt->close();
    } else {
        $error_message = "يرجى تعبئة جميع الحقول!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - منصة وصلة</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #e0f2fe, #bae6fd); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: #ffffff; padding: 35px 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo-container { margin-bottom: 10px; }
        .app-logo { max-width: 160px; height: auto; object-fit: contain; }
        .sub-title { color: #64748b; font-size: 14px; margin-bottom: 25px; }
        .form-group { text-align: right; margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; color: #334155; font-size: 14px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; outline: none; transition: 0.3s; }
        .form-group input:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        .btn-submit { width: 100%; padding: 12px; background-color: #0284c7; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background-color: #0369a1; }
        .error-box { background-color: #fef2f2; color: #dc2626; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #fecaca; }
        .footer-links { margin-top: 20px; font-size: 14px; color: #64748b; }
        .footer-links a { color: #0284c7; text-decoration: none; font-weight: bold; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-container">
        <img src="Logo.jpeg" alt="شعار وصلة" class="app-logo">
    </div>
    <div class="sub-title">مرحباً بك في منصة تعلم لغة الإشارة</div>

    <?php if (!empty($error_message)): ?>
        <div class="error-box"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>رقم الجوال أو البريد</label>
            <input type="text" name="email" placeholder="أدخل البريد أو الرقم" required>
        </div>

        <div class="form-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">تسجيل الدخول</button>
    </form>

    <div class="footer-links">
        ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
    </div>
</div>

</body>
</html>