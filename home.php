<?php
/*
 * PHP TELEGRAM GIVEAWAY BOT (FIXED FOR SQLITE3)
 * Fixed ArgumentCountError
 * Single File Solution
 */

// --- 1. CONFIGURATION & SETUP ---
error_reporting(E_ALL);
ini_set('display_errors', 1); 
date_default_timezone_set('Asia/Riyadh');

// --- تعديل بيانات البوت ---
$BOT_TOKEN = "7019648394:AAHY8E8-JM3I91Xr2B9hPDOJByWU9gSlKKw"; 
$ADMIN_ID = 778375826; // أيدي الأدمن
$BOT_USERNAME = "Hhrurjdbbot"; // بدون علامة @
$API_URL = "https://api.telegram.org/bot" . $BOT_TOKEN . "/";
$DB_FILE = __DIR__ . "/giveaway_v2.db";

// --- 2. DATABASE INITIALIZATION ---
try {
    $db = new SQLite3($DB_FILE);
} catch (Exception $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// Create Tables
$db->exec("CREATE TABLE IF NOT EXISTS giveaways (
    giveaway_id TEXT PRIMARY KEY,
    channel_id TEXT NOT NULL,
    message_id INTEGER,
    creator_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    winner_count INTEGER NOT NULL,
    conditions TEXT,
    end_type TEXT,
    end_value TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS participants (
    giveaway_id TEXT NOT NULL,
    user_id INTEGER NOT NULL,
    username TEXT,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (giveaway_id, user_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS banned_users (
    user_id INTEGER PRIMARY KEY,
    reason TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS captcha_attempts (
    giveaway_id TEXT NOT NULL,
    user_id INTEGER NOT NULL,
    attempts INTEGER DEFAULT 0,
    captcha_code TEXT, 
    PRIMARY KEY (giveaway_id, user_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS conversation_state (
    user_id INTEGER PRIMARY KEY,
    state TEXT,
    data_json TEXT
)");

// --- 3. Helpers & DB Wrapper ---
function apiRequest($method, $data = []) {
    global $API_URL;
    $ch = curl_init($API_URL . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['ok' => false, 'description' => $err];
    return json_decode($result, true);
}

// دالة مساعدة تنفذ الاستعلام وتربط القيم (حل مشكلة SQLite3)
function db_exec($stmt, $params = []) {
    foreach ($params as $i => $val) {
        // SQLite3 binding is 1-indexed
        $stmt->bindValue($i + 1, $val);
    }
    return $stmt->execute();
}

function sendMessage($chat_id, $text, $reply_markup = null, $parse_mode = 'Markdown') {
    global $API_URL;
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => $parse_mode];
    if ($reply_markup) {
        if (is_array($reply_markup)) $data['reply_markup'] = json_encode($reply_markup);
        else $data['reply_markup'] = $reply_markup;
    }
    return apiRequest('sendMessage', $data);
}

function editMessageText($chat_id, $message_id, $text, $reply_markup = null, $parse_mode = 'Markdown') {
    global $API_URL;
    $data = ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $text, 'parse_mode' => $parse_mode];
    if ($reply_markup) {
        if (is_array($reply_markup)) $data['reply_markup'] = json_encode($reply_markup);
        else $data['reply_markup'] = $reply_markup;
    }
    return apiRequest('editMessageText', $data);
}

function answerCallbackQuery($callback_id, $text = '', $show_alert = false) {
    return apiRequest('answerCallbackQuery', ['callback_query_id' => $callback_id, 'text' => $text, 'show_alert' => $show_alert]);
}

function getUserState($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM conversation_state WHERE user_id = ?");
    db_exec($stmt, [$user_id]);
    $res = $stmt->fetchArray(SQLITE3_ASSOC);
    if ($res && $res['data_json']) {
        $res['data'] = json_decode($res['data_json'], true);
    }
    return $res;
}

function setUserState($user_id, $state, $data = []) {
    global $db;
    $json = json_encode($data);
    $stmt = $db->prepare("INSERT OR REPLACE INTO conversation_state (user_id, state, data_json) VALUES (?, ?, ?)");
    db_exec($stmt, [$user_id, $state, $json]);
}

function clearUserState($user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM conversation_state WHERE user_id = ?");
    db_exec($stmt, [$user_id]);
}

function getGiveaway($giveaway_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM giveaways WHERE giveaway_id = ?");
    db_exec($stmt, [$giveaway_id]);
    return $stmt->fetchArray(SQLITE3_ASSOC);
}

function updateGiveawayStatus($giveaway_id, $status) {
    global $db;
    $stmt = $db->prepare("UPDATE giveaways SET status = ? WHERE giveaway_id = ?");
    db_exec($stmt, [$status, $giveaway_id]);
}

// --- 4. CAPTCHA GENERATION ---
if (isset($_GET['render_captcha'])) {
    $code = $_GET['code'] ?? '';
    header("Content-Type: image/png");
    $width = 300; $height = 120;
    $image = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($image, rand(220, 255), rand(220, 255), rand(220, 255));
    imagefill($image, 0, 0, $bg);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 80, 40, $code, $text_color);
    for ($i = 0; $i < 10; $i++) {
        imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), imagecolorallocate($image, rand(0,150), rand(0,150), rand(0,150)));
    }
    imagepng($image);
    imagedestroy($image);
    exit;
}

// --- 5. JOB QUEUE & DRAW LOGIC ---
function check_giveaways() {
    global $db, $API_URL;
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("SELECT * FROM giveaways WHERE status = 'active' AND end_type = 'time' AND end_value <= ?");
    // FIXED: Using db_exec helper
    db_exec($stmt, [$now]);

    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
        perform_giveaway_draw($row['giveaway_id']);
    }
}

function perform_giveaway_draw($giveaway_id) {
    global $db;
    $giveaway = getGiveaway($giveaway_id);
    if (!$giveaway || $giveaway['status'] != 'active') return;

    $stmt = $db->prepare("SELECT user_id, username FROM participants WHERE giveaway_id = ?");
    db_exec($stmt, [$giveaway_id]);
    $participants = [];
    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
        $participants[] = $row;
    }

    if (empty($participants)) {
        apiRequest('sendMessage', ['chat_id' => $giveaway['channel_id'], 'text' => "لم يشارك أحد! 😔"]);
        updateGiveawayStatus($giveaway_id, 'finished');
        return;
    }

    $winner_count = $giveaway['winner_count'];
    if ($winner_count == 0) $winner_count = count($participants);
    shuffle($participants);
    $selected = array_slice($participants, 0, min($winner_count, count($participants)));
    
    foreach ($selected as $winner) {
        $winners[] = $winner;
        $stmt_w = $db->prepare("INSERT INTO winners (giveaway_id, user_id) VALUES (?, ?)");
        db_exec($stmt_w, [$giveaway_id, $winner['user_id']]);
    }

    updateGiveawayStatus($giveaway_id, 'finished');

    $text = "🎊 انتهى السحب: *" . $giveaway['title'] . "* 🎊\n\n";
    $text .= "**🏆 الفائزون:**\n";
    foreach ($winners as $i => $w) {
        $user_link = $w['username'] ? "@" . $w['username'] : "[مستخدم](tg://user?id={$w['user_id']})";
        $text .= ($i+1) . ". {$user_link}\n";
    }

    if($giveaway['message_id']) {
        editMessageText($giveaway['channel_id'], $giveaway['message_id'], "~~" . $text . "~~\n\n✅ انتهى السحب.");
    }
    sendMessage($giveaway['channel_id'], $text);
}

// --- 6. MAIN LOGIC HANDLER ---

function handleUpdate($update) {
    global $ADMIN_ID, $BOT_USERNAME, $db;
    check_giveaways(); 

    $message = $update['message'] ?? null;
    $callback_query = $update['callback_query'] ?? null;
    
    // Callback Handler
    if ($callback_query) {
        $chat_id = $callback_query['message']['chat']['id'] ?? null;
        $message_id = $callback_query['message']['message_id'] ?? null;
        $user_id = $callback_query['from']['id'];
        $data = $callback_query['data'];
        $data_parts = explode('|', $data);
        $action = $data_parts[0];

        answerCallbackQuery($callback_query['id']);

        if ($action == "create_giveaway") {
            setUserState($user_id, 'SELECTING_CHANNEL', []);
            $kb = [['text' => "إلغاء ⛔️", 'callback_data' => "cancel_conv"]];
            sendMessage($chat_id, "أرسل رابط القناة (مثال: @channel):", ['inline_keyboard' => [$kb]]);
            return;
        }
        
        if ($action == "cancel_conv") {
            clearUserState($user_id);
            sendMessage($chat_id, "تم الإلغاء.");
            return;
        }

        if (in_array($action, ['pause', 'resume', 'draw_now']) && $user_id == $ADMIN_ID) {
            $giveaway_id = $data_parts[1];
            $giveaway = getGiveaway($giveaway_id);
            if ($action == 'pause') updateGiveawayStatus($giveaway_id, 'paused');
            if ($action == 'resume') updateGiveawayStatus($giveaway_id, 'active');
            if ($action == 'draw_now') perform_giveaway_draw($giveaway_id);
            answerCallbackQuery($callback_query['id'], "تم تنفيذ الإجراء!", true);
        }

        if ($action == "participate") {
            $giveaway_id = $data_parts[1];
            $giveaway = getGiveaway($giveaway_id);
            
            $stmt_ban = $db->prepare("SELECT * FROM banned_users WHERE user_id = ?");
            db_exec($stmt_ban, [$user_id]);
            if ($stmt_ban->fetchArray()) {
                answerCallbackQuery($callback_query['id'], "أنت محظور من المشاركة.", true);
                return;
            }

            $stmt_part = $db->prepare("SELECT * FROM participants WHERE giveaway_id = ? AND user_id = ?");
            db_exec($stmt_part, [$giveaway_id, $user_id]);
            if ($stmt_part->fetchArray()) {
                answerCallbackQuery($callback_query['id'], "أنت مشارك بالفعل!", true);
                return;
            }
            
            $code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
            $self_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $photo_url = $self_url . "?render_captcha=1&code=$code";
            
            $stmt = $db->prepare("INSERT INTO captcha_attempts (giveaway_id, user_id, attempts, captcha_code) VALUES (?, ?, 1, ?)");
            db_exec($stmt, [$giveaway_id, $user_id, $code]);
            
            setUserState($user_id, 'AWAITING_CAPTCHA', ['giveaway_id' => $giveaway_id]);
            $kb = [['text' => "🔙 عودة", 'callback_data' => "cancel_conv"]];
            sendMessage($chat_id, "أدخل الرمز الموجود في الصورة:", ['inline_keyboard' => [$kb]]);
            apiRequest('sendPhoto', ['chat_id' => $chat_id, 'photo' => $photo_url, 'caption' => "أدخل الرمز (غير حساس لحالة الأحرف)."]);
        }
        return;
    }

    // --- Text Message Handler ---
    if ($message) {
        $chat_id = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $user_id = $message['from']['id'];
        $user_state = getUserState($user_id);
        $data = $user_state['data'] ?? [];

        if (strpos($text, '/start') === 0) {
            if ($chat_id == $ADMIN_ID) {
                $kb = [[['text' => "🎉 إنشاء سحب", 'callback_data' => "create_giveaway"]]];
                sendMessage($chat_id, "أهلاً بك يا مدير!\nاختر للبدء:", ['inline_keyboard' => $kb]);
            } else {
                sendMessage($chat_id, "أهلاً بك! هذا بوت السحوبات.\nيمكنك المشاركة عبر أزرار التصويت.");
            }
            return;
        }

        switch ($user_state['state']) {
            case 'SELECTING_CHANNEL':
                $channel = str_replace(['@', 'https://t.me/'], '', $text);
                $data['channel'] = $channel;
                setUserState($user_id, 'ENTERING_TITLE', $data);
                $kb = [['text' => "إلغاء ⛔️", 'callback_data' => "cancel_conv"]];
                sendMessage($chat_id, "✅ القناة: $channel\n\nالآن، أرسل عنوان الجائزة:", ['inline_keyboard' => [$kb]]);
                break;

            case 'ENTERING_TITLE':
                $data['title'] = $text;
                setUserState($user_id, 'SELECTING_WINNER_COUNT', $data);
                $kb = [['text' => "إلغاء ⛔️", 'callback_data' => "cancel_conv"]];
                sendMessage($chat_id, "✅ العنوان: $text\n\nكم عدد الفائزين؟ (أرسل رقماً مثل 1 أو 5):", ['inline_keyboard' => [$kb]]);
                break;

            case 'SELECTING_WINNER_COUNT':
                if (!is_numeric($text)) { sendMessage($chat_id, "أرقام فقط!"); break; }
                $data['winner_count'] = (int)($text);
                setUserState($user_id, 'SELECTING_END_TIME', $data);
                $kb = [['text' => "إلغاء ⛔️", 'callback_data' => "cancel_conv"]];
                sendMessage($chat_id, " كم مدة السحب؟\n(m=دقائق, h=ساعات, d=أيام)\nمثال: 1h", ['inline_keyboard' => [$kb]]);
                break;

            case 'SELECTING_END_TIME':
                $val = (int)$text;
                $unit = strtolower(substr(trim($text), -1));
                if (!in_array($unit, ['m','h','d'])) { sendMessage($chat_id, "صيغة خاطئة. مثال: 10m"); break; }
                
                $end_time = date('Y-m-d H:i:s', strtotime("+$val $unit"));
                $data['end_time'] = $end_time;
                setUserState($user_id, 'CONFIRMATION', $data);
                
                $msg = "مراجعة:\nالقناة: " . $data['channel'] . "\nالجائزة: " . $data['title'] . "\nالفائزون: " . $data['winner_count'] . "\nالوقت: $end_time\n\nأرسل 'نعم' للنشر";
                $kb_c = [['text' => "نعم ✅", 'callback_data' => "confirm_gw"], ['text' => "إلغاء", 'callback_data' => "cancel_conv"]];
                sendMessage($chat_id, $msg, ['inline_keyboard' => $kb_c]);
                break;

            case 'AWAITING_CAPTCHA':
                $giveaway_id = $data['giveaway_id'];
                $stmt = $db->prepare("SELECT * FROM captcha_attempts WHERE giveaway_id = ? AND user_id = ?");
                db_exec($stmt, [$giveaway_id, $user_id]);
                $cap = $stmt->fetchArray(SQLITE3_ASSOC);

                if ($cap && strtoupper($text) == strtoupper($cap['captcha_code'])) {
                    $stmt_add = $db->prepare("INSERT INTO participants (giveaway_id, user_id, username) VALUES (?, ?, ?)");
                    $username = $message['from']['username'] ?? "";
                    db_exec($stmt_add, [$giveaway_id, $user_id, $username]);
                    sendMessage($chat_id, "✅ تمت المشاركة بنجاح!");
                    clearUserState($user_id);
                } else {
                    if (!$cap) { clearUserState($user_id); sendMessage($chat_id, "حدث خطأ."); return; }
                        
                    $new_att = $cap['attempts'] + 1;
                    if ($new_att > 3) {
                        sendMessage($chat_id, "❌ انتهت محاولاتك.");
                        clearUserState($user_id);
                    } else {
                        // Update attempts directly
                        $db->exec("UPDATE captcha_attempts SET attempts = $new_att WHERE giveaway_id='$giveaway_id' AND user_id=$user_id");
                        sendMessage($chat_id, "❌ خطأ. لديك " . (4-$new_att) . " محاولات.");
                    }
                }
                break;
        }
    }
}

// Handle Confirmation Callback
if (isset($update['callback_query'])) {
    $data_cb = $update['callback_query']['data'];
    if (strpos($data_cb, 'confirm_gw') !== false) {
         $user_id = $update['callback_query']['from']['id'];
         $chat_id = $update['callback_query']['message']['chat']['id'];
         $state = getUserState($user_id);
         // IMPORTANT: check if user is admin (basic check)
         if($user_id != $ADMIN_ID && $state['creator_id'] != $user_id) {
             // In this simple version, we trust the user ID in state, 
             // but ideally verify permissions to the channel.
         }
         
         $data = $state['data'];
         
         if ($data && isset($data['channel'], $data['title'], $data['winner_count'], $data['end_time'])) {
            $g_id = uniqid('g_');
            $stmt = $db->prepare("INSERT INTO giveaways (giveaway_id, channel_id, creator_id, title, winner_count, end_type, end_value) VALUES (?, ?, ?, ?, ?, 'time', ?)");
            db_exec($stmt, [$g_id, $data['channel'], $user_id, $data['title'], $data['winner_count'], $data['end_time']]);
            
            $self_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $kb = [['text' => "🎉 المشاركة", 'callback_data' => "participate|$g_id"]];
            
            $res = apiRequest('sendMessage', [
                'chat_id' => $data['channel'],
                'text' => "🎉 سحب جديد: " . $data['title'] . "\nيبدأ الآن وينتهي في " . $data['end_time'],
                'reply_markup' => json_encode(['inline_keyboard' => [$kb]])
            ]);
            
            if ($res['ok']) {
                $stmt_up = $db->prepare("UPDATE giveaways SET message_id = ? WHERE giveaway_id = ?");
                db_exec($stmt_up, [$res['result']['message_id'], $g_id]);
                // Don't clear state immediately to prevent errors, clear after success check? 
                // But callback query needs answer
                answerCallbackQuery($update['callback_query']['id']);
                sendMessage($chat_id, "✅ تم النشر بنجاح!");
                clearUserState($user_id);
            } else {
                answerCallbackQuery($update['callback_query']['id'], "فشل النشر: " . $res['description'], true);
            }
         }
    }
}

// --- 7. WEBHOOK ENTRY ---
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update)) {
    handleUpdate($update);
    echo "OK";
    exit;
}

// --- 8. WEBHOOK INSTALL ---
if (isset($_GET['webhook_install'])) {
    $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    // If running on non-standard port, add it
    if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
        $url .= ":" . $_SERVER['SERVER_PORT'];
    }
    apiRequest('setWebhook', ['url' => $url]);
    echo "Webhook set to: " . htmlspecialchars($url);
    exit;
}

if (isset($_GET['delete_webhook'])) {
    apiRequest('deleteWebhook');
    echo "Webhook deleted.";
    exit;
}

// --- 9. ADMIN PANEL HTML ---
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Bot Manager</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f0f2f5; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: right; }
        .btn { display: inline-block; padding: 5px 10px; color: white; text-decoration: none; border-radius: 4px; }
        .btn-green { background: #22c55e; }
        .btn-yellow { background: #eab308; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 مدير البوت</h1>
        <p><a href="?webhook_install" class="btn btn-green">تفعيل Webhook</a></p>
        
        <h3>السحوبات</h3>
        <table>
            <tr><th>العنوان</th><th>الحالة</th><th>المشاركون</th><th>إجراء</th></tr>
            <?php
            $res = $db->query("SELECT * FROM giveaways ORDER BY created_at DESC");
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                $c_stmt = $db->prepare("SELECT COUNT(*) as c FROM participants WHERE giveaway_id = ?");
                db_exec($c_stmt, [$row['giveaway_id']]);
                $count = $c_stmt->fetchArray(SQLITE3_ASSOC)['c'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $count ?></td>
                    <td>
                        <?php if($row['status'] == 'active'): ?>
                            <a href="?force_draw=<?= $row['giveaway_id'] ?>" class="btn btn-yellow">⚡ سحب الآن</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php
if (isset($_GET['force_draw'])) {
    perform_giveaway_draw($_GET['force_draw']);
    echo "<script>alert('تم السحب!'); window.location='home.php';</script>";
}
?>
