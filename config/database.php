<?php
// config/database.php

// === الطريقة النهائية: استخدام رابط الاتصال الكامل ===

// جلب رابط الاتصال الكامل من متغيرات البيئة
 $dbUrl = getenv('MYSQL_URL');

// التحقق من وجود الرابط
if (!$dbUrl) {
    die("متغير البيئة MYSQL_URL غير موجود. تأكد من إعدادات Railway.");
}

// استخدام دالة مدمجة لتفكيك الرابط
 $dbParts = parse_url($dbUrl);

// استخلاص المعلومات من الرابط
 $host = $dbParts['host'];
 $port = $dbParts['port'];
 $db_name = ltrim($dbParts['path'], '/');
 $username = $dbParts['user'];
 $password = $dbParts['pass'];

 $charset = 'utf8mb4';

// إنشاء مصدر بيانات PDO (DSN)
 $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=$charset";

// خيارات PDO
 $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // محاولة الاتصال
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // عرض الخطأ الحقيقي للاستكشاف
    die("فشل الاتصال بقاعدة البيانات. الخطأ هو: " . $e->getMessage());
}
?>
