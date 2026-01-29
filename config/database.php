<?php
// config/database.php

// جلب بيانات الاتصال من متغيرات البيئة التي سنضعها في Railway
// إذا لم توجد المتغيرات (عند العمل المحلي)، سيستخدم القيم الافتراضية
 $host = getenv('DB_HOST') ?: 'localhost';
 $db_name = getenv('DB_NAME') ?: 'auth_system';
 $username = getenv('DB_USER') ?: 'root';
 $password = getenv('DB_PASSWORD') ?: '';
 $charset = 'utf8mb4';

// مصدر بيانات PDO (Data Source Name)
 $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

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
    // في بيئة الإنتاج، لا تعرض تفاصيل الخطأ للمستخدم
    die("لا يمكن الاتصال بقاعدة البيانات في الوقت الحالي.");
}
?>
