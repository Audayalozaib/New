<?php
// =============================================================================
// لوحة تحكم بوت تليجرام الاحترافية - ملف واحد متكامل
// =============================================================================

// تعطيل عرض الأخطاء للمستخدم (لأسباب أمنية)
error_reporting(0);
ini_set('display_errors', 0);

// ملفات التكوين والتخزين
define('CONFIG_FILE', 'config.json');
define('USERS_FILE', 'users.json');

// =============================================================================
// دوال مساعدة للتعامل مع تليجرام
// =============================================================================

function sendTelegramRequest($method, $params = []) {
    $config = getConfig();
    if (!$config || !isset($config['token'])) {
        return false;
    }
    $url = "https://api.telegram.org/bot" . $config['token'] . "/" . $method;
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($params),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
}

function getConfig() {
    return file_exists(CONFIG_FILE) ? json_decode(file_get_contents(CONFIG_FILE), true) : null;
}

function saveConfig($data) {
    file_put_contents(CONFIG_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function getUsers() {
    return file_exists(USERS_FILE) ? json_decode(file_get_contents(USERS_FILE), true) : [];
}

function saveUser($userId, $username) {
    $users = getUsers();
    if (!in_array($userId, array_column($users, 'id'))) {
        $users[] = ['id' => $userId, 'username' => $username];
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    }
}

// =============================================================================
// معالج الويب هوك (Webhook Handler) - قلب البوت النابض
// يتم تنفيذ هذا الجزء عند تلقي رسالة من تليجرام
// =============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && file_exists(CONFIG_FILE)) {
    $update = json_decode(file_get_contents('php://input'), true);
    $config = getConfig();
    $adminId = $config['admin_id'];

    if (isset($update['message'])) {
        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = trim($message['text']);
        $fromId = $message['from']['id'];
        $username = $message['from']['username'] ?? 'Unknown';

        // حفظ معلومات المستخدم
        saveUser($fromId, $username);

        // أوامر البوت
        if (strpos($text, '/start') === 0) {
            $response = "👋 أهلاً بك في البوت!\n\nاستخدم /help لرؤية قائمة الأوامر.";
        } elseif (strpos($text, '/help') === 0) {
            $response = "🤖 قائمة الأوامر:\n/start - بدء المحادثة\n/help - عرض هذه المساعدة\n/about - معرفة المزيد";
        } elseif (strpos($text, '/about') === 0) {
            $response = "✨ هذا بوت تم إنشاؤه وإدارته بواسطة لوحة تحكم PHP مخصصة.";
        } elseif ($fromId == $adminId) { // أوامر الأدمن فقط
            if (strpos($text, '/broadcast') === 0) {
                $broadcastMessage = substr($text, 11);
                if (!empty($broadcastMessage)) {
                    $users = getUsers();
                    $successCount = 0;
                    foreach ($users as $user) {
                        if (sendTelegramRequest('sendMessage', ['chat_id' => $user['id'], 'text' => "📢 رسالة من الأدمن:\n\n" . $broadcastMessage])) {
                            $successCount++;
                        }
                    }
                    $response = "✅ تم إرسال الرسالة بنجاح إلى $successCount مستخدم.";
                } else {
                    $response = "❌ الرجاء كتابة الرسالة بعد الأمر.\nمثال: /broadcast مرحباً جميعاً";
                }
            } else {
                $response = "رسالة من الأدمن تم استلامها.";
            }
        } else {
            $response = "لم أفهم هذا الأمر. أرسل /help للمساعدة.";
        }

        sendTelegramRequest('sendMessage', ['chat_id' => $chatId, 'text' => $response]);
    }
    exit; // إنهاء التنفيذ بعد معالجة الويب هوك
}

// =============================================================================
// منطق لوحة التحكم (Admin Panel)
// =============================================================================

 $message = '';
 $config = getConfig();

// معالجة طلبات POST من لوحة التحكم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_config'])) {
        $token = trim($_POST['token']);
        $adminId = trim($_POST['admin_id']);
        if ($token && $adminId) {
            saveConfig(['token' => $token, 'admin_id' => $adminId, 'webhook_set' => false]);
            $message = "✅ تم حفظ الإعدادات بنجاح! الآن اضغط على 'تفعيل البوت'.";
            $config = getConfig();
        } else {
            $message = "❌ الرجاء ملء جميع الحقول.";
        }
    } elseif (isset($_POST['set_webhook']) && $config) {
        $webhook_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $result = sendTelegramRequest('setWebhook', ['url' => $webhook_url]);
        if ($result && $result['ok']) {
            $config['webhook_set'] = true;
            saveConfig($config);
            $message = "✅ تم تفعيل البوت بنجاح! أصبح الآن جاهزًا للاستخدام.";
        } else {
            $message = "❌ فشل تفعيل البوت: " . ($result['description'] ?? 'خطأ غير معروف');
        }
    } elseif (isset($_POST['unset_webhook']) && $config) {
        $result = sendTelegramRequest('deleteWebhook');
        if ($result && $result['ok']) {
            $config['webhook_set'] = false;
            saveConfig($config);
            $message = "✅ تم إيقاف البوت بنجاح.";
        } else {
            $message = "❌ فشل إيقاف البوت.";
        }
    } elseif (isset($_POST['delete_bot'])) {
        if (file_exists(CONFIG_FILE)) unlink(CONFIG_FILE);
        if (file_exists(USERS_FILE)) unlink(USERS_FILE);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم البوت</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        :root { --primary: #0088cc; --dark: #2c3e50; --light: #ecf0f1; --danger: #e74c3c; --success: #2ecc71; }
        * { box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--dark); color: var(--light); margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; background-color: #34495e; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        h1, h2 { text-align: center; color: var(--light); }
        h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-left: 10px; }
        .status-active { background-color: var(--success); box-shadow: 0 0 10px var(--success); }
        .status-inactive { background-color: var(--danger); }
        .alert { padding: 15px; background-color: rgba(255,255,255,0.1); border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert.success { border-right: 5px solid var(--success); }
        .alert.error { border-right: 5px solid var(--danger); }
        .card { background-color: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 12px; border: 1px solid #555; background-color: var(--dark); color: var(--light); border-radius: 6px; font-size: 1rem; }
        .btn { display: inline-block; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: bold; text-decoration: none; transition: all 0.3s ease; margin: 5px; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-success { background-color: var(--success); color: white; }
        .btn:hover { opacity: 0.8; transform: translateY(-2px); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .user-list { max-height: 200px; overflow-y: auto; background: var(--dark); padding: 10px; border-radius: 6px; }
        .user-list p { margin: 0; padding: 5px; border-bottom: 1px solid #555; }
    </style>
</head>
<body>

<div class="container">
    <h1>لوحة تحكم البوت <span class="status-indicator <?php echo ($config && $config['webhook_set']) ? 'status-active' : 'status-inactive'; ?>"></span></h1>
    <p style="text-align:center; opacity:0.7;">أنشئ وأدر بوت تليجرام الخاص بك من هنا</p>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!$config): ?>
        <!-- شاشة الإعداد الأولي -->
        <div class="card">
            <h2><i class="fas fa-robot"></i> إعداد بوت جديد</h2>
            <p>للبدء،你需要从 <a href="https://t.me/BotFather" target="_blank">@BotFather</a> على تليجرام الحصول على التوكن وآيدي الأدمن.</p>
            <form method="POST">
                <div class="form-group">
                    <label for="token">توكن البوت (Bot Token)</label>
                    <input type="text" id="token" name="token" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11" required>
                </div>
                <div class="form-group">
                    <label for="admin_id">آيدي الأدمن (Admin ID)</label>
                    <input type="number" id="admin_id" name="admin_id" placeholder="123456789" required>
                    <small>للحصول على آيديك، أرسل رسالة لبوت <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>.</small>
                </div>
                <button type="submit" name="save_config" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
            </form>
        </div>
    <?php else: ?>
        <!-- لوحة التحكم الرئيسية -->
        <div class="card">
            <h2><i class="fas fa-cogs"></i> إدارة البوت</h2>
            <div class="grid">
                <div>
                    <strong>حالة البوت:</strong>
                    <p><?php echo $config['webhook_set'] ? '<span style="color: var(--success);">🟢 نشط</span>' : '<span style="color: var(--danger);">🔴 غير نشط</span>'; ?></p>
                </div>
                <div>
                    <strong>آيدي الأدمن:</strong>
                    <p><?php echo htmlspecialchars($config['admin_id']); ?></p>
                </div>
            </div>
            <hr style="margin: 20px 0; border: 1px solid #555;">
            <form method="POST" style="display:inline;">
                <?php if ($config['webhook_set']): ?>
                    <button type="submit" name="unset_webhook" class="btn btn-danger"><i class="fas fa-stop"></i> إيقاف البوت</button>
                <?php else: ?>
                    <button type="submit" name="set_webhook" class="btn btn-success"><i class="fas fa-play"></i> تفعيل البوت</button>
                <?php endif; ?>
            </form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف البوت وكل بياناته؟');">
                <button type="submit" name="delete_bot" class="btn btn-danger"><i class="fas fa-trash"></i> حذف البوت</button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-users"></i> المستخدمون (<?php echo count(getUsers()); ?>)</h2>
            <div class="user-list">
                <?php
                $users = getUsers();
                if (empty($users)) {
                    echo "<p>لا يوجد مستخدمون بعد.</p>";
                } else {
                    foreach ($users as $user) {
                        echo "<p>ID: " . htmlspecialchars($user['id']) . " - @" . htmlspecialchars($user['username']) . "</p>";
                    }
                }
                ?>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-paper-plane"></i> إرسال رسالة جماعية (للأدمن)</h2>
            <p>لإرسال رسالة لجميع المستخدمين، اذهب إلى البوت وأرسل الأمر:</p>
            <code style="background: var(--dark); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-top: 10px;">/broadcast نص الرسالة هنا</code>
        </div>

    <?php endif; ?>
</div>

</body>
</html>
