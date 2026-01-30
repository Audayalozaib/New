<?php
session_start();
$data_file = 'bots_data.json';

// دوال مساعدة للقراءة والكتابة
function getBots($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

function saveBots($file, $bots) {
    file_put_contents($file, json_encode($bots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// معالجة الطلبات (API) عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bots = getBots($data_file);

    if ($action === 'create_bot') {
        $newBot = [
            'id' => uniqid(),
            'name' => htmlspecialchars($_POST['bot_name']),
            'token' => htmlspecialchars($_POST['bot_token']),
            'admin_id' => htmlspecialchars($_POST['admin_id']),
            'status' => 'inactive',
            'giveaways' => [],
            'settings' => [
                'channel_links' => [],
                'required_members' => false
            ]
        ];
        $bots[] = $newBot;
        saveBots($data_file, $bots);
        header("Location: home.php");
        exit;
    }

    if ($action === 'update_settings') {
        $botId = $_POST['bot_id'];
        foreach ($bots as &$bot) {
            if ($bot['id'] === $botId) {
                $bot['settings']['channel_links'] = explode("\n", $_POST['channels']);
                $bot['settings']['required_members'] = isset($_POST['require_join']);
                $bot['admin_id'] = htmlspecialchars($_POST['admin_id']);
            }
        }
        saveBots($data_file, $bots);
        header("Location: home.php?bot={$botId}");
        exit;
    }

    if ($action === 'create_giveaway') {
        $botId = $_POST['bot_id'];
        foreach ($bots as &$bot) {
            if ($bot['id'] === $botId) {
                $newGiveaway = [
                    'id' => uniqid(),
                    'title' => htmlspecialchars($_POST['g_title']),
                    'prize' => htmlspecialchars($_POST['g_prize']),
                    'winners_count' => (int)$_POST['g_winners'],
                    'end_time' => $_POST['g_end_date'],
                    'status' => 'active', // active, ended
                    'participants' => []
                ];
                $bot['giveaways'][] = $newGiveaway;
            }
        }
        saveBots($data_file, $bots);
        header("Location: home.php?bot={$botId}&tab=giveaways");
        exit;
    }
    
    if ($action === 'delete_bot') {
        $botId = $_POST['bot_id'];
        $bots = array_filter($bots, function($b) use ($botId) { return $b['id'] !== $botId; });
        saveBots($data_file, array_values($bots));
        header("Location: home.php");
        exit;
    }
}

$active_bot_id = $_GET['bot'] ?? null;
$tab = $_GET['tab'] ?? 'dashboard';
$bots_list = getBots($data_file);
$current_bot = null;

if ($active_bot_id) {
    foreach ($bots_list as $b) {
        if ($b['id'] === $active_bot_id) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدير بوتات السحوبات - Telegram Giveaway Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0088cc;
            --primary-dark: #006699;
            --bg: #f5f7fa;
            --sidebar-bg: #1e293b;
            --card-bg: #ffffff;
            --text: #334155;
            --text-light: #94a3b8;
            --danger: #ef4444;
            --success: #22c55e;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; }
        
        body { background-color: var(--bg); color: var(--text); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        aside { width: 260px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; border-left: 1px solid rgba(255,255,255,0.1); }
        .logo { padding: 20px; font-size: 20px; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px; }
        .logo span { color: var(--primary); }
        .bots-nav { flex: 1; overflow-y: auto; padding: 10px; }
        .bots-nav h3 { font-size: 12px; color: var(--text-light); margin-bottom: 10px; margin-top: 15px; text-transform: uppercase; }
        .bot-item { padding: 10px 15px; margin-bottom: 5px; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .bot-item:hover { background-color: rgba(255,255,255,0.05); }
        .bot-item.active { background-color: var(--primary); }
        .bot-status { width: 8px; height: 8px; border-radius: 50%; }
        .status-active { background-color: var(--success); box-shadow: 0 0 5px var(--success); }
        .status-inactive { background-color: var(--text-light); }
        
        .add-bot-btn { margin: 20px; padding: 12px; background: rgba(255,255,255,0.1); border: 1px dashed rgba(255,255,255,0.3); color: white; text-align: center; border-radius: 8px; cursor: pointer; }
        .add-bot-btn:hover { background: rgba(255,255,255,0.2); }

        /* Main Content */
        main { flex: 1; overflow-y: auto; padding: 30px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { font-size: 24px; color: var(--sidebar-bg); }

        /* Dashboard Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: var(--card-bg); padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--border); }
        .card h3 { font-size: 16px; margin-bottom: 15px; color: var(--primary-dark); }
        
        .stat-value { font-size: 32px; font-weight: bold; color: var(--sidebar-bg); margin-bottom: 5px; }
        .stat-label { color: var(--text-light); font-size: 14px; }

        /* Forms */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; transition: border 0.3s; background: #fff; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); }
        
        button.btn-primary { background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        button.btn-primary:hover { background: var(--primary-dark); }
        button.btn-danger { background: var(--danger); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; }
        
        /* Giveaways Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: right; border-bottom: 1px solid var(--border); }
        th { color: var(--text-light); font-size: 13px; font-weight: 500; }
        .badge { padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-ended { background: #fee2e2; color: #991b1b; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.open { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .close-btn { cursor: pointer; font-size: 20px; }

        /* Toast */
        .toast { position: fixed; bottom: 20px; right: 20px; background: var(--sidebar-bg); color: white; padding: 12px 24px; border-radius: 8px; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 2000; }
        .toast.show { opacity: 1; }

        /* Utility */
        .hidden { display: none; }
        .empty-state { text-align: center; padding: 50px; color: var(--text-light); }
        .empty-icon { font-size: 48px; margin-bottom: 10px; display: block; }

        /* Tabs */
        .tabs { display: flex; gap: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .tab { padding: 10px 0; cursor: pointer; color: var(--text-light); border-bottom: 2px solid transparent; font-weight: 500; }
        .tab.active { color: var(--primary); border-color: var(--primary); }

        .bot-running { display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;}
        .bot-stopped { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 20px; font-size: 12px;}
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside>
        <div class="logo">
            <span>⚡</span>
            مدير البوتات
        </div>
        <div class="bots-nav">
            <h3>بوتاتي</h3>
            <?php if (empty($bots_list)): ?>
                <div style="padding: 15px; font-size: 13px; color: var(--text-light);">لا توجد بوتات حالياً</div>
            <?php else: ?>
                <?php foreach ($bots_list as $bot): ?>
                    <div class="bot-item <?= ($active_bot_id === $bot['id']) ? 'active' : '' ?>" onclick="location.href='home.php?bot=<?=$bot['id']?>'">
                        <div>
                            <div style="font-weight: bold; font-size: 14px;"><?= $bot['name'] ?></div>
                            <div style="font-size: 11px; color: var(--text-light);">@<?php echo explode(':', $bot['token'])[0]; ?></div>
                        </div>
                        <div class="bot-status <?= ($bot['status'] === 'active') ? 'status-active' : 'status-inactive' ?>"></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="add-bot-btn" onclick="openModal('addBotModal')">+ إضافة بوت جديد</div>
        </div>
    </aside>

    <!-- Main Content -->
    <main>
        <?php if ($current_bot): ?>
            <header>
                <div>
                    <h1><?= $current_bot['name'] ?></h1>
                    <div style="margin-top: 5px; color: var(--text-light); font-size: 14px;">
                        الأيدي: <code><?= $current_bot['admin_id'] ?></code> 
                        • الحالة: <?= ($current_bot['status'] == 'active') ? '<span class="bot-running">● يعمل</span>' : '<span class="bot-stopped">○ متوقف</span>' ?>
                    </div>
                </div>
                <?php if($current_bot['status'] == 'active'): ?>
                    <button class="btn-danger" onclick="toggleBot('<?= $current_bot['id'] ?>', 'inactive')">إيقاف البوت</button>
                <?php else: ?>
                    <button class="btn-primary" onclick="toggleBot('<?= $current_bot['id'] ?>', 'active')">تشغيل البوت</button>
                <?php endif; ?>
            </header>

            <div class="tabs">
                <div class="tab <?= ($tab == 'dashboard') ? 'active' : '' ?>" onclick="switchTab('dashboard')">لوحة المعلومات</div>
                <div class="tab <?= ($tab == 'giveaways') ? 'active' : '' ?>" onclick="switchTab('giveaways')">السحوبات</div>
                <div class="tab <?= ($tab == 'settings') ? 'active' : '' ?>" onclick="switchTab('settings')">الإعدادات</div>
            </div>

            <!-- Dashboard Tab -->
            <?php if ($tab == 'dashboard'): ?>
                <div class="grid">
                    <div class="card">
                        <h3>المشتركين في البوت</h3>
                        <div class="stat-value">0</div>
                        <div class="stat-label">إجمالي المستخدمين</div>
                    </div>
                    <div class="card">
                        <h3>السحوبات النشطة</h3>
                        <div class="stat-value">
                            <?php 
                            $active_g = 0;
                            foreach($current_bot['giveaways'] as $g) if($g['status'] == 'active') $active_g++;
                            echo $active_g;
                            ?>
                        </div>
                        <div class="stat-label">جارية الآن</div>
                    </div>
                    <div class="card">
                        <h3>القنوات المضافة</h3>
                        <div class="stat-value"><?= count($current_bot['settings']['channel_links']) ?></div>
                        <div class="stat-label">قنوات إلزامية</div>
                    </div>
                </div>

                <div class="card">
                    <h3>سجل النشاط</h3>
                    <div style="height: 200px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-light); font-size: 13px;">
                        <div style="text-align: center;">
                            <span style="font-size: 24px; display: block; margin-bottom: 10px;">📜</span>
                            لا توجد نشاطات مؤرشفة حالياً
                        </div>
                    </div>
                </div>

            <!-- Giveaways Tab -->
            <?php elseif ($tab == 'giveaways'): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>قائمة السحوبات</h3>
                        <button class="btn-primary" onclick="openModal('createGiveawayModal')">سحب جديد</button>
                    </div>
                    
                    <?php if (empty($current_bot['giveaways'])): ?>
                        <div class="empty-state">
                            <span class="empty-icon">🎁</span>
                            لم تقم بإنشاء أي سحوبات بعد
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>الجائزة</th>
                                        <th>عدد الفائزين</th>
                                        <th>الحالة</th>
                                        <th>المشاركين</th>
                                        <th>إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($current_bot['giveaways'] as $g): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $g['prize'] ?></strong><br>
                                            <span style="font-size: 12px; color: var(--text-light);"><?= $g['title'] ?></span>
                                        </td>
                                        <td><?= $g['winners_count'] ?></td>
                                        <td>
                                            <span class="badge <?= ($g['status'] == 'active') ? 'badge-active' : 'badge-ended' ?>">
                                                <?= ($g['status'] == 'active') ? 'جاري' : 'منتهي' ?>
                                            </span>
                                        </td>
                                        <td><?= count($g['participants']) ?></td>
                                        <td>
                                            <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="alert('سيتم اختيار فائز عشوائي للمشاركين')">اختار فائز</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- Settings Tab -->
            <?php elseif ($tab == 'settings'): ?>
                <div class="card" style="max-width: 600px;">
                    <h3>إعدادات البوت</h3>
                    <form action="home.php" method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        <input type="hidden" name="bot_id" value="<?= $current_bot['id'] ?>">
                        
                        <div class="form-group">
                            <label>أيدي المدير (Admin ID)</label>
                            <input type="text" name="admin_id" value="<?= $current_bot['admin_id'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>القنوات/المجموعات الإلزامية (رابط أو يوزر)</label>
                            <textarea name="channels" rows="5" placeholder="ضع كل رابط في سطر جديد&#10;@channel1&#10;https://t.me/channel2"><?= implode("\n", $current_bot['settings']['channel_links']) ?></textarea>
                            <small style="color: var(--text-light);">يجب على المستخدم الانضمام لهذه القنوات للمشاركة في السحب.</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="require_join" <?= $current_bot['settings']['required_members'] ? 'checked' : '' ?> style="width: auto;">
                                إلزام الاشتراك في القنوات (تحقق تلقائي)
                            </label>
                        </div>

                        <div class="form-group">
                            <label>رسالة البداية</label>
                            <textarea rows="3" placeholder="أهلاً بك في بوت السحوبات..."></textarea>
                        </div>

                        <button type="submit" class="btn-primary">حفظ الإعدادات</button>
                    </form>
                    
                    <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--border);">
                    
                    <h3 style="color: var(--danger);">منطقة الخطر</h3>
                    <form action="home.php" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا البوت؟ لا يمكن التراجع عن هذا الإجراء.');">
                        <input type="hidden" name="action" value="delete_bot">
                        <input type="hidden" name="bot_id" value="<?= $current_bot['id'] ?>">
                        <button type="submit" class="btn-danger">حذف البوت نهائياً</button>
                    </form>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Welcome State -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;">👋</div>
                <h1 style="margin-bottom: 10px;">مرحباً بك في مدير السحوبات</h1>
                <p style="color: var(--text-light); max-width: 500px; margin-bottom: 30px;">
                    قم بإضافة بوت تليجرام جديد للبدء في إدارة السحوبات والتفاعل مع أعضاء قنواتك بسهولة.
                </p>
                <button class="btn-primary" onclick="openModal('addBotModal')">إضافة بوت جديد</button>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal: Add Bot -->
    <div id="addBotModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إضافة بوت جديد</h3>
                <span class="close-btn" onclick="closeModal('addBotModal')">&times;</span>
            </div>
            <form action="home.php" method="POST">
                <input type="hidden" name="action" value="create_bot">
                <div class="form-group">
                    <label>اسم البوت (داخلي)</label>
                    <input type="text" name="bot_name" placeholder="مثال: بوت قناتي الرسمي" required>
                </div>
                <div class="form-group">
                    <label>توكن البوت (Token)</label>
                    <input type="text" name="bot_token" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11" required>
                    <small style="color: var(--text-light);">احصل عليه من @BotFather</small>
                </div>
                <div class="form-group">
                    <label>أيدي المدير (Your Admin ID)</label>
                    <input type="text" name="admin_id" placeholder="رقم الأيدي الخاص بحسابك" required>
                    <small style="color: var(--text-light);">احصل عليه من بوت @userinfobot</small>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">إضافة البوت</button>
            </form>
        </div>
    </div>

    <!-- Modal: Create Giveaway -->
    <div id="createGiveawayModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إنشاء سحب جديد</h3>
                <span class="close-btn" onclick="closeModal('createGiveawayModal')">&times;</span>
            </div>
            <form action="home.php" method="POST">
                <input type="hidden" name="action" value="create_giveaway">
                <input type="hidden" name="bot_id" value="<?= $active_bot_id ?>">
                
                <div class="form-group">
                    <label>عنوان السحب</label>
                    <input type="text" name="g_title" placeholder="سحب بمناسبة الوصول لـ 10k مشترك" required>
                </div>
                <div class="form-group">
                    <label>الجائزة</label>
                    <input type="text" name="g_prize" placeholder="iPhone 15 Pro" required>
                </div>
                <div class="form-group">
                    <label>عدد الفائزين</label>
                    <input type="number" name="g_winners" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label>تاريخ النهاية</label>
                    <input type="datetime-local" name="g_end_date" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">انشاء السحب</button>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">تم بنجاح!</div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('open');
            }
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.innerText = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function switchTab(tabName) {
            // Reload page with new tab for simplicity in PHP architecture
            // preserving bot ID
            const botId = '<?= $active_bot_id ?? "" ?>';
            window.location.href = `home.php?bot=${botId}&tab=${tabName}`;
        }

        function toggleBot(botId, newStatus) {
            // Since this is a simple file based "simulator", 
            // we use a hidden form approach or fetch to update status
            const bots = <?= json_encode($bots_list) ?>;
            const botIndex = bots.findIndex(b => b.id === botId);
            
            if(botIndex !== -1) {
                // In a real app, this would be an AJAX call
                // Here we just simulate UI feedback
                fetch('home.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=toggle_status&bot_id=${botId}&status=${newStatus}`
                }).then(() => {
                   location.reload(); 
                });
            }
        }
    </script>
</body>
</html>
