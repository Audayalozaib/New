<?php
// ===================================================================
// ملف مؤقت لإنشاء جدول المستخدمين في قاعدة البيانات
// تحذير: يجب حذف هذا الملف فوراً بعد استخدامه مرة واحدة!
// ===================================================================

// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/database.php';

// أمر SQL لإنشاء جدول users
// استخدمنا "IF NOT EXISTS" كإجراء أمان إضافي
 $sql = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    // تنفيذ الأمر
    $pdo->exec($sql);
    
    // عرض رسالة نجاح
    echo "<h1>نجاح!</h1>";
    echo "<p>تم إنشاء جدول 'users' بنجاح في قاعدة البيانات.</p>";
    echo "<p><strong>مهم جداً:</strong> قم بحذف هذا الملف (setup.php) من مشروعك فوراً.</p>";

} catch (PDOException $e) {
    // في حالة حدوث خطأ، عرض رسالة مفصلة
    die("<h1>خطأ!</h1><p>فشل إنشاء الجدول: " . $e->getMessage() . "</p>");
}
?>
