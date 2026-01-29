<?php
// config/database.php

// === الطريقة الجديدة: استخدام رابط الاتصال الكامل (الأكثر موثوقية) ===
 $dbUrl = getenv('DB_URL');

if ($dbUrl) {
    // تفكيك الرابط للحصول على المكونات
    $dbParts = parse_url($dbUrl);
    
    $host = $dbParts['host'];
    $port = $dbParts['port'];
    $db_name = ltrim($dbParts['path'], '/'); // إزالة الشرطة المائلة من البداية
    $username = $dbParts['user'];
    $password = $dbParts['pass'];
} else {
    // في حالة عدم وجود الرابط (للعمل المحلي فقط)
    $host = 'localhost';
    $db_name = 'auth_system';
    $username = 'root';
    $password = '';
    $port = '3306';
}

 $charset = 'utf8mb4';

// مصدر بيانات PDO (Data Source Name)
 $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=$charset";

// خيارات PDO
 $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // إنشاء كائن PDO للاتصال
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // عرض الخطأ الحقيقي للاستكشاف
    die("فشل الاتصال بقاعدة البيانات. الخطأ هو: " . $e->getMessage());
}
?>
