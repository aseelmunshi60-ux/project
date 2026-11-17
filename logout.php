<?php
// إظهار الأخطاء لتسهيل التتبع
ini_set('display_errors', 1);
error_reporting(E_ALL);

// بدء الجلسة للوصول إليها
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تفريغ كافة متغيرات الجلسة
$_SESSION = array();

// حذف كوكيز الجلسة لضمان التطهير الكامل وأمان الخروج
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة نهائياً من الخادم
session_destroy();

// التوجيه التلقائي المباشر إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>