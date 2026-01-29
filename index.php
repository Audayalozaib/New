<?php
// index.php
session_start();

// إذا كان المستخدم مسجل دخوله بالفعل، قم بتوجيهه إلى الصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>مرحباً بك في نظام المصادقة</h1>
        <p>اختر أحد الخيارات التالية للبدء:</p>
        <div class="home-buttons">
            <a href="login.php" class="button">تسجيل الدخول</a>
            <a href="register.php" class="button">إنشاء حساب جديد</a>
        </div>
    </div>
</body>
</html>
