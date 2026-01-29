<?php
// register.php
require_once 'config/database.php';

 $errors = [];
 $success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    if (empty($username)) { $errors[] = "اسم المستخدم مطلوب."; }
    if (empty($email)) { $errors[] = "البريد الإلكتروني مطلوب."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "صيغة البريد الإلكتروني غير صحيحة."; }
    if (empty($password)) { $errors[] = "كلمة المرور مطلوبة."; } elseif (strlen($password) < 6) { $errors[] = "كلمة المرور يجب أن تكون 6 أحرف على الأقل."; }
    if ($password !== $password_confirm) { $errors[] = "كلمتا المرور غير متطابقتين."; }

    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email]);
        
        if ($stmt->fetch()) {
            $errors[] = "اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password])) {
                $success_message = "تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
                $_POST = [];
            } else {
                $errors[] = "حدث خطأ ما. يرجى المحاولة مرة أخرى لاحقاً.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>إنشاء حساب جديد</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-box">
                <p><?php echo $success_message; ?></p>
                <p><a href="login.php">اذهب إلى صفحة تسجيل الدخول</a></p>
            </div>
        <?php else: ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">اسم المستخدم:</label>
                    <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">تأكيد كلمة المرور:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit">إنشاء الحساب</button>
            </form>
            <p class="switch-link">لديك حساب بالفعل؟ <a href="login.php">سجل دخولك هنا</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
