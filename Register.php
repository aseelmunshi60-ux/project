<?php

include 'db_connect.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = trim($_POST['password']);
    $role      = trim($_POST['role']); // guardian أو teacher

    if (!empty($full_name) && !empty($email) && !empty($password) && !empty($role)) {
        $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "البريد الإلكتروني مستخدم بالفعل!";
        } else {
            $stmt = $conn->prepare("INSERT INTO user (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $phone, $password, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; 
                if ($role == 'guardian') {
                    $stmt_role = $conn->prepare("INSERT INTO guardian (user_id) VALUES (?)");
                    $stmt_role->bind_param("i", $user_id);
                    $stmt_role->execute();
                    $stmt_role->close();
                } elseif ($role == 'teacher') {
                    $stmt_role = $conn->prepare("INSERT INTO teacher (user_id) VALUES (?)");
                    $stmt_role->bind_param("i", $user_id);
                    $stmt_role->execute();
                    $stmt_role->close();
                }

                $message = "تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
            } else {
                $error = "حدث خطأ أثناء إنشاء الحساب، يرجى المحاولة لاحقاً.";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $error = "يرجى تعبئة جميع الحقول المطلوبة!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - منصة وصلة</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #e0f2fe, #bae6fd); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px 0; }
        .register-card { background: #ffffff; padding: 35px 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 420px; text-align: center; }
        .logo-title { color: #0284c7; font-size: 28px; font-weight: bold; margin-bottom: 6px; }
        .sub-title { color: #64748b; font-size: 14px; margin-bottom: 20px; }
        .form-group { text-align: right; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #334155; font-size: 13px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; outline: none; transition: 0.3s; background-color: #fff; }
        .form-group input:focus, .form-group select:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        .btn-submit { width: 100%; padding: 12px; background-color: #0284c7; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background-color: #0369a1; }
        .error-box { background-color: #fef2f2; color: #dc2626; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #fecaca; }
        .success-box { background-color: #f0fdf4; color: #16a34a; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #bbf7d0; }
        .footer-links { margin-top: 18px; font-size: 14px; color: #64748b; }
        .footer-links a { color: #0284c7; text-decoration: none; font-weight: bold; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="logo-title">وصلة 🤟</div>
    <div class="sub-title">إنشاء حساب جديد في منصة وصلة</div>

    <?php if (!empty($error)): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="success-box"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>الاسم الكامل</label>
            <input type="text" name="full_name" placeholder="أدخل اسمك الثلاثي" required>
        </div>

        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@mail.com" required>
        </div>

        <div class="form-group">
            <label>رقم الجوال</label>
            <input type="text" name="phone" placeholder="05xxxxxxxx">
        </div>

        <div class="form-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label>نوع الحساب</label>
            <select name="role" required>
                <option value="" disabled selected>اختر نوع الحساب</option>
                <option value="guardian">ولي أمر</option>
                <option value="teacher">معلم</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">إنشاء الحساب</button>
    </form>

    <div class="footer-links">
        لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
    </div>
</div>

</body>
</html>