<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * ==========================================
 * TELEGRAM BOT MANAGER - PRO VERSION
 * Single File Solution (PHP + JS + CSS)
 * ==========================================
 */

// --- 1. CONFIGURATION & SETUP ---
$data_file = 'bots_data_v2.json';
$current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// --- 2. CORE FUNCTIONS ---

// Helper: Make Telegram API Request
function apiRequest($token, $method, $data = []) {
    $url = "https://api.telegram.org/bot$token/$method";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// Helper: Load/Save Data
function getDB($file) {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveDB($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Helper: Check if user is member of channels
function checkSubscriptions($token, $user_id, $channels) {
    foreach ($channels as $channel) {
        // Clean channel link (remove https://t.me/ or @)
        $chat_id = str_replace(['https://t.me/', '@'], '', $channel);
        // Add @ if not username and not numeric ID
        if (!is_numeric($chat_id)) $chat_id = '@' . $chat_id;
        
        $res = apiRequest($token, 'getChatMember', ['chat_id' => $chat_id, 'user_id' => $user_id]);
        
        if (!$res['ok'] || !in_array($res['result']['status'], ['member', 'administrator', 'creator'])) {
            return false; // Not a member
        }
    }
    return true;
}

// --- 3. WEBHOOK HANDLER (The "Brain" of the Bot) ---
// This part runs when Telegram sends an update
if (isset($_GET['webhook_token'])) {
    $webhook_token = $_GET['webhook_token'];
    $db = getDB($data_file);
    
    // Find the bot matching the webhook token
    $bot_key = null;
    $bot_data = null;
    
    // Simple security check: map token to bot data
    foreach($db as $key => $bot) {
        // We verify by matching the token passed in URL with stored token
        if ($bot['token'] === $webhook_token) {
            $bot_key = $key;
            $bot_data = $bot;
            break;
        }
    }

    if ($bot_data) {
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);
        
        if (!$update) {
            echo "No updates";
            exit;
        }
        
        // Process Update
        $message = $update['message'] ?? $update['callback_query']['message'] ?? null;
        $callback_query = $update['callback_query'] ?? null;
        
        $user_id = $message['from']['id'] ?? $callback_query['from']['id'];
        $chat_id = $message['chat']['id'] ?? $callback_query['message']['chat']['id'];
        $username = $message['from']['username'] ?? 'بدون يوزر';
        $first_name = $message['from']['first_name'] ?? 'مستخدم';

        // Handle Callback (Button Clicks)
        if ($callback_query) {
            $data_cb = $callback_query['data'];
            
            if (strpos($data_cb, 'join_') === 0) {
                $g_id = str_replace('join_', '', $data_cb);
                
                // Reload DB to be safe
                $db = getDB($data_file);
                $bot = $db[$bot_key];
                $giveaway = null;
                foreach($bot['giveaways'] as &$g) {
                    if ($g['id'] == $g_id) { $giveaway = &$g; break; }
                }

                if ($giveaway && $giveaway['status'] == 'active') {
                    // Check Subscriptions
                    $req_channels = $giveaway['conditions']['channels'] ?? [];
                    
                    if (!empty($req_channels) && !checkSubscriptions($bot['token'], $user_id, $req_channels)) {
                        $channels_text = implode("\n", $req_channels);
                        apiRequest($bot['token'], 'answerCallbackQuery', [
                            'callback_query_id' => $callback_query['id'],
                            'text' => "يجب الاشتراك في القنوات أولاً!",
                            'show_alert' => true
                        ]);
                        apiRequest($bot['token'], 'sendMessage', [
                            'chat_id' => $chat_id,
                            'text' => "عذراً $first_name، يجب عليك الاشتراك في القنوات التالية للمشاركة:\n\n" . $channels_text . "\n\nاضغط على 'تحديث' بعد الاشتراك."
                        ]);
                        exit;
                    }

                    // Add Participant
                    $exists = false;
                    foreach($giveaway['participants'] as $p) {
                        if ($p['id'] == $user_id) { $exists = true; break; }
                    }
                    
                    if (!$exists) {
                        $giveaway['participants'][] = [
                            'id' => $user_id,
                            'name' => $first_name,
                            'username' => $username,
                            'date' => date('Y-m-d H:i:s')
                        ];
                        saveDB($data_file, $db);
                        
                        apiRequest($bot['token'], 'answerCallbackQuery', [
                            'callback_query_id' => $callback_query['id'],
                            'text' => "تم التسجيل بنجاح! 🎉",
                            'show_alert' => true
                        ]);
                        
                        // Update message text (optional, keeping it simple for now)
                    } else {
                        apiRequest($bot['token'], 'answerCallbackQuery', [
                            'callback_query_id' => $callback_query['id'],
                            'text' => "أنت مسجل بالفعل!",
                            'show_alert' => true
                        ]);
                    }
                }
            }
            // Admin commands to pick winner
            elseif (strpos($data_cb, 'pick_') === 0) {
                $g_id = str_replace('pick_', '', $data_cb);
                // Verify admin
                if ($user_id != $bot['admin_id']) exit;
                
                $db = getDB($data_file);
                $bot = $db[$bot_key];
                $giveaway = null;
                foreach($bot['giveaways'] as &$g) {
                    if ($g['id'] == $g_id) { $giveaway = &$g; break; }
                }

                if ($giveaway && !empty($giveaway['participants'])) {
                    $winner = $giveaway['participants'][array_rand($giveaway['participants'])];
                    $giveaway['winner_selected'] = $winner;
                    $giveaway['status'] = 'ended'; // Stop joining
                    saveDB($data_file, $db);

                    $msg = "🏆 **تم اختيار الفائز** 🏆\n\n";
                    $msg .= "السحب: " . $giveaway['title'] . "\n";
                    $msg .= "الجائزة: " . $giveaway['prize'] . "\n\n";
                    $msg .= "الفائز هو: [{$winner['name']}](tg://user?id={$winner['id']})";
                    
                    apiRequest($bot['token'], 'sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => $msg,
                        'parse_mode' => 'Markdown'
                    ]);
                }
            }
        }
    }
    echo "Webhook Handled";
    exit;
}

// --- 4. ADMIN PANEL LOGIC ---
$db = getDB($data_file);

// Handle POST requests from the Dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add New Bot
    if ($action === 'add_bot') {
        $token = trim($_POST['bot_token']);
        // Validate Token
        $check = apiRequest($token, 'getMe');
        if ($check && $check['ok']) {
            $botInfo = $check['result'];
            $newBot = [
                'id' => uniqid(),
                'name' => $botInfo['first_name'],
                'username' => $botInfo['username'],
                'token' => $token,
                'admin_id' => $_POST['admin_id'],
                'webhook_url' => '', 
                'giveaways' => []
            ];
            $db[] = $newBot;
            saveDB($data_file, $db);
            header("Location: home.php?new=true");
            exit;
        } else {
            $error = "التوكن غير صالح!";
        }
    }

    // Set Webhook
    if ($action === 'set_webhook') {
        $idx = $_POST['bot_index'];
        $url = trim($_POST['webhook_url']);
        if ($url) {
            $res = apiRequest($db[$idx]['token'], 'setWebhook', ['url' => $url]);
            if ($res['ok']) {
                $db[$idx]['webhook_url'] = $url;
                saveDB($data_file, $db);
                header("Location: home.php?bot_id=".$db[$idx]['id']."&msg=webhook_set");
                exit;
            }
        }
    }

    // Create Giveaway
    if ($action === 'create_giveaway') {
        $idx = $_POST['bot_index'];
        $channels = array_filter(explode("\n", $_POST['g_channels']));
        
        $newG = [
            'id' => uniqid(),
            'title' => $_POST['g_title'],
            'prize' => $_POST['g_prize'],
            'chat_id' => $_POST['g_target_chat'], // Where to post the message
            'status' => 'draft',
            'participants' => [],
            'conditions' => ['channels' => $channels],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Send initial message to channel
        $msg_text = "🎁 **سحب جديد!** 🎁\n\n";
        $msg_text .= "الجائزة: " . $newG['prize'] . "\n";
        $msg_text .= "التفاصيل: " . $newG['title'] . "\n\n";
        if (!empty($channels)) {
            $msg_text .= "شروط المشاركة: الاشتراك في القنوات أدناه 👇\n" . implode("\n", $channels);
        }
        
        $kb = json_encode([
            'inline_keyboard' => [[['text' => "🚀 اشترك الآن", 'callback_data' => "join_" . $newG['id']]]]
        ]);

        $sent = apiRequest($db[$idx]['token'], 'sendMessage', [
            'chat_id' => $newG['chat_id'],
            'text' => $msg_text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $kb
        ]);

        if ($sent && $sent['ok']) {
            $newG['message_id'] = $sent['result']['message_id'];
            $newG['status'] = 'active';
            $db[$idx]['giveaways'][] = $newG;
            saveDB($data_file, $db);
            header("Location: home.php?bot_id=".$db[$idx]['id']."&tab=giveaways&msg=g_created");
            exit;
        } else {
            $error = "فشل إرسال الرسالة. تأكد من أن البوت admins في القناة/المجموعة وأن أيدي القناة صحيح.";
        }
    }

    // Delete Bot
    if ($action === 'delete_bot') {
        $idx = $_POST['bot_index'];
        unset($db[$idx]);
        $db = array_values($db); // re-index
        saveDB($data_file, $db);
        header("Location: home.php");
        exit;
    }
}

$active_bot_id = $_GET['bot_id'] ?? null;
$tab = $_GET['tab'] ?? 'dashboard';
$current_bot_index = -1;
$current_bot = null;

if ($active_bot_id) {
    foreach($db as $k => $b) {
        if ($b['id'] === $active_bot_id) {
            $current_bot_index = $k;
            $current_bot = $b;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مدير السحوبات المتطور - TurboGiveaway</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <!-- Using Material Icons via Google Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        :root {
            --primary: #24A1DE; /* Telegram Blue */
            --primary-dark: #1b8bbf;
            --secondary: #2b5278;
            --bg: #f0f2f5;
            --surface: #ffffff;
            --text: #1f2937;
            --text-light: #6b7280;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --border: #e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --sidebar-width: 280px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; -webkit-tap-highlight-color: transparent; }
        
        body { background-color: var(--bg); color: var(--text); display: flex; height: 100vh; overflow: hidden; }

        /* --- Layout --- */
        .app-container { display: flex; width: 100%; height: 100%; }

        /* Sidebar */
        aside {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1c2e4a 0%, #151d2e 100%);
            color: white;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            border-left: 1px solid rgba(255,255,255,0.1);
        }
        
        .brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand-icon { background: var(--primary); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-text { font-weight: 800; font-size: 18px; letter-spacing: 0.5px; }

        .bot-list { flex: 1; overflow-y: auto; padding: 20px 10px; }
        .bot-list-header { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 10px; padding: 0 10px; text-transform: uppercase; letter-spacing: 1px; }

        .bot-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 6px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .bot-item:hover { background: rgba(255,255,255,0.08); }
        .bot-item.active { background: rgba(36, 161, 222, 0.2); border-color: rgba(36, 161, 222, 0.4); }
        .bot-avatar { width: 40px; height: 40px; border-radius: 50%; background: #374151; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #9ca3af; margin-left: 12px; }
        .bot-info { flex: 1; }
        .bot-name { font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 2px; }
        .bot-status { font-size: 11px; color: rgba(255,255,255,0.6); display: flex; align-items: center; gap: 4px; }
        .dot { width: 6px; height: 6px; border-radius: 50%; background: #6b7280; }
        .dot.online { background: var(--success); box-shadow: 0 0 8px var(--success); }

        .add-btn-container { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .btn-add-bot {
            width: 100%;
            padding: 14px;
            border: 2px dashed rgba(255,255,255,0.3);
            background: transparent;
            color: white;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-add-bot:hover { background: rgba(255,255,255,0.1); border-color: white; }

        /* Main Content */
        main { flex: 1; overflow-y: auto; position: relative; background: var(--bg); }
        
        .mobile-header {
            display: none;
            padding: 15px 20px;
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .header-bar {
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .page-title h1 { font-size: 24px; font-weight: 800; color: var(--secondary); }
        .page-title p { color: var(--text-light); font-size: 14px; margin-top: 4px; }

        /* Cards & Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            padding: 0 40px 40px;
        }
        
        .card {
            background: var(--surface);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-2px); }
        
        .stat-card { display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .stat-info h3 { font-size: 28px; font-weight: 800; color: var(--text); line-height: 1; margin-bottom: 5px; }
        .stat-info span { font-size: 13px; color: var(--text-light); font-weight: 500; }

        /* Tabs */
        .content-area { padding: 0 40px 40px; max-width: 1200px; margin: 0 auto; width: 100%; }
        .tabs-header { display: flex; gap: 20px; border-bottom: 1px solid var(--border); margin-bottom: 30px; }
        .tab-btn {
            padding: 12px 0;
            background: none;
            border: none;
            color: var(--text-light);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }
        .tab-btn.active { color: var(--primary); }
        .tab-btn.active::after {
            content: ''; position: absolute; bottom: -1px; right: 0; width: 100%; height: 3px;
            background: var(--primary); border-radius: 3px 3px 0 0;
        }

        /* Forms & Tables */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--secondary); }
        input, textarea, select {
            width: 100%;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            background: #f9fafb;
            transition: all 0.2s;
        }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); background: white; }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(36, 161, 222, 0.3); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-danger { background: #fee2e2; color: var(--danger); }
        .btn-danger:hover { background: #fecaca; }
        
        .table-wrap { overflow-x: auto; background: white; border-radius: 12px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th { text-align: right; padding: 16px; border-bottom: 1px solid var(--border); color: var(--text-light); font-size: 12px; font-weight: 700; text-transform: uppercase; background: #f9fafb; }
        td { padding: 16px; border-bottom: 1px solid var(--border); color: var(--text); font-size: 14px; }
        tr:last-child td { border-bottom: none; }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; animation: fadeIn 0.2s; }
        .modal-box {
            background: white; width: 100%; max-width: 500px; border-radius: 20px;
            overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: scale(0.95); transition: transform 0.2s;
        }
        .modal-overlay.open .modal-box { transform: scale(1); }
        .modal-header { padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 24px; }

        /* Responsive */
        @media (max-width: 768px) {
            aside {
                position: fixed; width: 100%; height: 100%;
                transform: translateX(100%); /* Hide by default on mobile RTL */
            }
            aside.open { transform: translateX(0); }
            .header-bar { padding: 20px; flex-direction: column; align-items: flex-start; }
            .dashboard-grid { padding: 0 20px 20px; }
            .content-area { padding: 0 20px 20px; }
            .mobile-header { display: flex; }
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-active { background: #d1fae5; color: #059669; }
        .badge-ended { background: #f3f4f6; color: #6b7280; }
        
        .overlay-dark { background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 40; display: none;}
        .overlay-dark.active { display: block; }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="overlay-dark" id="mobileOverlay" onclick="toggleSidebar()"></div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="brand">
                <div class="brand-icon"><span class="material-icons" style="color:white; font-size:20px;">bolt</span></div>
                <div class="brand-text">TurboGiveaway</div>
            </div>
            
            <div class="bot-list">
                <div class="bot-list-header">بوتاتي النشطة</div>
                <?php if(empty($db)): ?>
                    <div style="padding:10px; text-align:center; color:rgba(255,255,255,0.4); font-size:13px;">
                        لا توجد بوتات. أضف واحداً للبدء.
                    </div>
                <?php else: ?>
                    <?php foreach($db as $k => $bot): ?>
                        <div class="bot-item <?= ($active_bot_id == $bot['id']) ? 'active' : '' ?>" onclick="window.location.href='home.php?bot_id=<?=$bot['id']?>'">
                            <div class="bot-avatar"><?= substr($bot['name'], 0, 1) ?></div>
                            <div class="bot-info">
                                <div class="bot-name"><?= htmlspecialchars($bot['name']) ?></div>
                                <div class="bot-status">
                                    <div class="dot <?= (!empty($bot['webhook_url'])) ? 'online' : '' ?>"></div>
                                    <?= (!empty($bot['webhook_url'])) ? 'يعمل' : 'متوقف' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-btn-container">
                <button class="btn-add-bot" onclick="document.getElementById('addBotModal').classList.add('open')">
                    <span class="material-icons">add</span> إضافة بوت جديد
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <!-- Mobile Header -->
            <div class="mobile-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="material-icons" onclick="toggleSidebar()" style="cursor:pointer;">menu</span>
                    <strong style="color:var(--primary);">TurboGiveaway</strong>
                </div>
                <?php if($current_bot): ?>
                    <div class="dot <?= (!empty($current_bot['webhook_url'])) ? 'online' : '' ?>" style="border: 1px solid #ddd;"></div>
                <?php endif; ?>
            </div>

            <?php if($current_bot): ?>
                <div class="header-bar">
                    <div class="page-title">
                        <h1><?= htmlspecialchars($current_bot['name']) ?></h1>
                        <p>إدارة السحوبات والإعدادات</p>
                    </div>
                    <div>
                        <?php if(empty($current_bot['webhook_url'])): ?>
                            <button class="btn btn-primary" onclick="openWebhookModal()">
                                <span class="material-icons">link</span> تفعيل البوت (Webhook)
                            </button>
                        <?php else: ?>
                            <button class="btn" style="background:#e0f2fe; color:#0284c7;">
                                <span class="material-icons">check_circle</span> البوت مفعل
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tabs-header">
                    <button class="tab-btn <?= $tab == 'dashboard' ? 'active' : '' ?>" onclick="changeTab('dashboard')">لوحة التحكم</button>
                    <button class="tab-btn <?= $tab == 'giveaways' ? 'active' : '' ?>" onclick="changeTab('giveaways')">إدارة السحوبات</button>
                    <button class="tab-btn <?= $tab == 'settings' ? 'active' : '' ?>" onclick="changeTab('settings')">الإعدادات</button>
                </div>

                <!-- TAB: DASHBOARD -->
                <?php if($tab == 'dashboard'): ?>
                    <div class="dashboard-grid">
                        <div class="card stat-card">
                            <div class="stat-icon" style="background:#3b82f6;">
                                <span class="material-icons">people</span>
                            </div>
                            <div class="stat-info">
                                <h3>
                                    <?php 
                                    $total_users = 0;
                                    foreach($current_bot['giveaways'] as $g) $total_users += count($g['participants']);
                                    echo $total_users;
                                    ?>
                                </h3>
                                <span>مشارك إجمالي</span>
                            </div>
                        </div>
                        <div class="card stat-card">
                            <div class="stat-icon" style="background:#f59e0b;">
                                <span class="material-icons">card_giftcard</span>
                            </div>
                            <div class="stat-info">
                                <h3><?= count($current_bot['giveaways']) ?></h3>
                                <span>سحب نشط</span>
                            </div>
                        </div>
                        <div class="card stat-card">
                            <div class="stat-icon" style="background:#ef4444;">
                                <span class="material-icons">admin_panel_settings</span>
                            </div>
                            <div class="stat-info">
                                <h3><?= $current_bot['admin_id'] ?></h3>
                                <span>أيدي الأدمن</span>
                            </div>
                        </div>
                    </div>

                    <div class="content-area">
                        <div class="card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom:15px; font-size:16px; color:var(--secondary);">سحب سريع</h3>
                            <form action="home.php" method="POST" onsubmit="return validateQuickGiveaway()">
                                <input type="hidden" name="action" value="create_giveaway">
                                <input type="hidden" name="bot_index" value="<?= $current_bot_index ?>">
                                
                                <div class="dashboard-grid" style="padding:0; gap:15px; margin-bottom:15px;">
                                    <input type="text" name="g_title" placeholder="عنوان السحب (مثلاً: وصول 10k)" required>
                                    <input type="text" name="g_prize" placeholder="الجائزة" required>
                                    <input type="text" name="g_target_chat" placeholder="أيدي القناة/المجموعة (مثال: -100) " required>
                                </div>
                                
                                <div class="form-group">
                                    <label>القنوات الإلزامية (رابط واحد في السطر)</label>
                                    <textarea name="g_channels" rows="3" placeholder="@channel1&#10;https://t.me/channel2"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                                    <span class="material-icons">send</span> إنشاء وإرسال السحب الآن
                                </button>
                            </form>
                        </div>
                    </div>

                <!-- TAB: GIVEAWAYS -->
                <?php elseif($tab == 'giveaways'): ?>
                    <div class="content-area">
                        <?php if(empty($current_bot['giveaways'])): ?>
                            <div class="empty-state">
                                <span class="material-icons" style="font-size:48px; display:block; margin-bottom:10px;">inbox</span>
                                لا توجد سحوبات حالياً
                            </div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>الجائزة</th>
                                            <th>الحالة</th>
                                            <th>المشاركين</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($current_bot['giveaways'] as $g): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:bold;"><?= htmlspecialchars($g['prize']) ?></div>
                                                <div style="font-size:12px; color:var(--text-light);"><?= htmlspecialchars($g['title']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($g['status']=='active')?'badge-active':'badge-ended' ?>">
                                                    <?= ($g['status']=='active')?'جاري':'منتهي' ?>
                                                </span>
                                            </td>
                                            <td><?= count($g['participants']) ?></td>
                                            <td>
                                                <?php if($g['status'] == 'active'): ?>
                                                    <button class="btn btn-primary" style="padding:6px 12px; font-size:12px;" onclick="simulatePick('<?= $g['id'] ?>')">محاكاة اختيار (للأدمن)</button>
                                                <?php else: ?>
                                                    <?php if(isset($g['winner_selected'])): ?>
                                                        <div style="font-size:12px; color:var(--success);">فائز: <?= $g['winner_selected']['name'] ?></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                <!-- TAB: SETTINGS -->
                <?php elseif($tab == 'settings'): ?>
                    <div class="content-area">
                        <div class="card">
                            <h3 style="margin-bottom:20px;">معلومات البوت</h3>
                            <div style="margin-bottom:10px; color:var(--text-light);">Username: <strong>@<?= $current_bot['username'] ?></strong></div>
                            <div style="margin-bottom:10px; color:var(--text-light);">Token: <code><?= substr($current_bot['token'], 0, 10) ?>...</code></div>
                            <div style="margin-bottom:20px; color:var(--text-light);">Webhook URL: <code><?= $current_bot['webhook_url'] ?: 'غير مفعل' ?></code></div>
                            
                            <hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">
                            
                            <h3 style="color:var(--danger); margin-bottom:10px;">منطقة الخطر</h3>
                            <form action="home.php" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف البوت؟')">
                                <input type="hidden" name="action" value="delete_bot">
                                <input type="hidden" name="bot_index" value="<?= $current_bot_index ?>">
                                <button type="submit" class="btn btn-danger">حذف البوت نهائياً</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Welcome Page -->
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; text-align:center; padding:20px;">
                    <div style="background:white; padding:40px; border-radius:20px; box-shadow:var(--shadow); max-width:400px;">
                        <span class="material-icons" style="font-size:60px; color:var(--primary); margin-bottom:20px;">rocket_launch</span>
                        <h2 style="margin-bottom:10px; color:var(--secondary);">مرحباً بك!</h2>
                        <p style="color:var(--text-light); margin-bottom:30px;">ابدأ بإدارة بوتات السحوبات الخاصة بك باحترافية.</p>
                        <button class="btn btn-primary" onclick="document.getElementById('addBotModal').classList.add('open')" style="width:100%; justify-content:center;">
                            أضف بوتك الأول
                        </button>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Modal: Add Bot -->
    <div id="addBotModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>إضافة بوت جديد</h3>
                <span class="material-icons" style="cursor:pointer;" onclick="document.getElementById('addBotModal').classList.remove('open')">close</span>
            </div>
            <form action="home.php" method="POST" class="modal-body">
                <input type="hidden" name="action" value="add_bot">
                <div class="form-group">
                    <label>توكن البوت (Token)</label>
                    <input type="text" name="bot_token" placeholder="123456:ABC..." required>
                    <small style="size:12px; color:var(--text-light);">احصل عليه من @BotFather</small>
                </div>
                <div class="form-group">
                    <label>أيدي الأدمن (Admin ID)</label>
                    <input type="number" name="admin_id" placeholder="رقم الأيدي" required>
                </div>
                <?php if(isset($error)): ?>
                    <div style="color:red; margin-bottom:10px; font-size:13px; background:#fee2e2; padding:10px; border-radius:8px;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">تسجيل الدخول</button>
            </form>
        </div>
    </div>

    <!-- Modal: Set Webhook -->
    <div id="webhookModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>تفعيل البوت (Webhook)</h3>
                <span class="material-icons" style="cursor:pointer;" onclick="document.getElementById('webhookModal').classList.remove('open')">close</span>
            </div>
            <form action="home.php" method="POST" class="modal-body">
                <input type="hidden" name="action" value="set_webhook">
                <input type="hidden" name="bot_index" value="<?= $current_bot_index ?>">
                
                <p style="font-size:13px; color:var(--text-light); margin-bottom:15px;">
                    لكي يعمل البوت تلقائياً، يجب عليك ربطه برابط ملفك الحالي. يجب أن يكون الرابط بروتوكول <b>HTTPS</b>.
                </p>
                
                <div class="form-group">
                    <label>رابط ملف home.php</label>
                    <input type="url" name="webhook_url" value="<?= $current_url ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    تفعيل الاتصال
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('mobileOverlay').classList.toggle('active');
        }

        function changeTab(tabName) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.location.href = url.toString();
        }

        function openWebhookModal() {
            document.getElementById('webhookModal').classList.add('open');
        }

        function validateQuickGiveaway() {
            const chatId = document.querySelector('input[name="g_target_chat"]').value;
            if(!chatId.startsWith('-') && !chatId.startsWith('@') && isNaN(chatId)) {
                alert('يرجى إدخال أيدي القناة/المجموعة بشكل صحيح (مثال: -100123456789 أو @channelname)');
                return false;
            }
            return true;
        }

        // Admin pick winner simulation (Sends button to admin to click pick)
        function simulatePick(giveawayId) {
            if(confirm('سيتم إرسال زر "اختيار الفائز" للمشرف في الدردشة الخاصة للاختيار الآمن. هل تريد المتابعة؟')) {
                // Since we can't easily send to admin without knowing their private chat ID (unless stored),
                // We can simulate by sending to the last active chat or just alert for now.
                // In a full app, we would store admin_user_id and send the button to him.
                alert('تمت المحاكاة! في النسخة القادمة سيتم إرسال زر للأدمن.');
            }
        }
        
        // Auto close modals on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('open');
            }
        }
    </script>
</body>
</html>
