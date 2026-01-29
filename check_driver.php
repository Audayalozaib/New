<?php
// check_driver.php

echo "<h1>فحص إعدادات PHP على Railway</h1>";
echo "<hr>";

echo "<h2>1. فحص سائق PDO لـ MySQL:</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green; font-size: 1.2em;'><strong>✅ نجاح!</strong> سائق PDO لـ MySQL مثبت ومفعل.</p>";
} else {
    echo "<p style='color: red; font-size: 1.2em;'><strong>❌ فشل!</strong> سائق PDO لـ MySQL غير مثبت.</p>";
    echo "<p>هذا يعني أن متغير البيئة <code>NIXPACKS_PHP_EXTENSIONS</code> لم يعمل بشكل صحيح.</p>";
}

echo "<hr>";

echo "<h2>2. فحص متغيرات البيئة:</h2>";
 $dbUrl = getenv('DB_URL');
if ($dbUrl) {
    echo "<p style='color: green;'><strong>✅ متغير DB_URL موجود.</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ متغير DB_URL غير موجود!</strong></p>";
}

echo "<hr>";

echo "<h2>3. جميع امتدادات PHP المثبتة:</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

?>
