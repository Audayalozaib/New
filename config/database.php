<?php
// config/database.php

// === الطريقة النهائية والمثبتة ===

// جلب رابط الاتصال من المتغير الذي أنشأناه في خدمة التطبيق
 $dbUrl = getenv('DB_URL');

// التحقق من وجود الرابط
if (!$dbUrl) {
    die("متغير البيئة DB_URL غير موجود. تأكد من إعدادات Railway.");
}

// تفكيك الرابط للحصول على المعلومات
 $dbParts = parse_url($dbUrl);

 $host = $dbParts['host'];
 $port = $dbParts['port'];
 $db_name = ltrim($dbParts['path'], '/');
 $username = $dbParts['user'];
 $password = $dbParts['pass'];

 $charset = 'utf8mb4';
 $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=$charset";

 $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات. الخطأ هو: " . $e->getMessage());
}
?>
