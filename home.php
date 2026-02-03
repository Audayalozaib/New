<?php
// home.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

 $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
 $stmt->execute([$_SESSION['user_id']]);
 $user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>أهلاً بك، <?php echo htmlspecialchars($user['username']); ?>!</h2>
        
        <div class="user-info">
            <h3>معلوماتك الشخصية:</h3>
            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>تاريخ إنشاء الحساب:</strong> <?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></p>
        </div>
        
        <p>هذه صفحة محمية. لا يمكن للأشخاص الذين لم يسجلوا الدخول رؤيتها.</p>
        
        <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
    </div>
</body>
</html>
