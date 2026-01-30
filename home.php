<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>القرآن الكريم - تلاوة وتفسير</title>
    
    <!-- خطوط جوجل العربية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #15803d; /* أخضر قراني */
            --secondary-color: #dcfce7; /* أخضر فاتح */
            --accent-color: #d97706; /* ذهبي */
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --sidebar-width: 280px;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        /* الوضع الليلي */
        [data-theme="dark"] {
            --primary-color: #22c55e;
            --secondary-color: #064e3b;
            --bg-color: #0f172a;
            --text-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            overflow: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        /* القائمة الجانبية */
        aside {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            transition: transform 0.3s ease;
            z-index: 100;
        }

        .logo {
            text-align: center;
            font-family: 'Amiri', serif;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 1rem;
        }

        .search-box {
            margin-bottom: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.5rem;
            border-radius: 6px;
            border: none;
            font-family: 'Tajawal', sans-serif;
        }

        .surah-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
        }

        .surah-list::-webkit-scrollbar {
            width: 6px;
        }
        .surah-list::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }

        .surah-item {
            padding: 0.8rem;
            cursor: pointer;
            border-radius: 6px;
            margin-bottom: 5px;
            transition: background 0.2s;
            display: flex;
            justify-content: space-between;
        }

        .surah-item:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .surah-item.active {
            background-color: var(--accent-color);
            font-weight: bold;
        }

        .surah-number {
            background: rgba(255,255,255,0.2);
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.8rem;
            margin-left: 10px;
        }

        /* المحتوى الرئيسي */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

        header {
            background-color: var(--bg-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .controls button {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            transition: 0.3s;
            margin-left: 5px;
        }
        [data-theme="dark"] .controls button {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .controls button:hover {
            background: var(--primary-color);
            color: white;
        }

        select {
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: 'Tajawal', sans-serif;
        }

        /* منطقة القراءة */
        #quran-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            padding-bottom: 100px; /* مساحة للمشغل */
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .basmalah {
            text-align: center;
            font-family: 'Amiri', serif;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .ayah-card {
            background-color: var(--bg-color);
            border: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: 0.3s;
        }

        .ayah-card.active {
            border-right: 5px solid var(--accent-color);
            background-color: var(--secondary-color);
            transform: scale(1.01);
        }
        [data-theme="dark"] .ayah-card.active {
            background-color: rgba(34, 197, 94, 0.1);
        }

        .ayah-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #64748b;
            position: relative;
        }

        [data-theme="dark"] .ayah-actions {
            color: #94a3b8;
        }

        .play-btn-ayah {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary-color);
        }

        .ayah-text {
            font-family: 'Amiri', serif;
            font-size: 2.5rem;
            line-height: 2.2;
            text-align: right;
        }

        .tafsir-text {
            margin-top: 1rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #475569;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding-top: 0.5rem;
            display: none; /* مخفي افتراضياً */
        }
        [data-theme="dark"] .tafsir-text {
            color: #cbd5e1;
        }

        /* شريط المشغل السفلي */
        .player-bar {
            position: fixed;
            bottom: 0;
            left: 0; /* سيتم تعديله بـ JS ليأخذ عرض الشاشة ناقص الشريط الجانبي */
            right: 0;
            background: white;
            padding: 1rem;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 200;
            border-top: 3px solid var(--accent-color);
        }
        [data-theme="dark"] .player-bar {
            background: #1e293b;
        }

        .player-info {
            flex: 1;
        }
        .player-info h4 { margin-bottom: 0.2rem; }
        .player-info p { font-size: 0.85rem; color: #64748b; }

        .player-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .ctrl-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }
        .ctrl-btn-main {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* القائمة العائمة للجوال */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            aside {
                position: fixed;
                height: 100%;
                right: -100%;
            }
            aside.open {
                right: 0;
            }
            .mobile-menu-btn {
                display: block;
            }
            .ayah-text {
                font-size: 1.8rem;
            }
            .player-bar {
                right: 0;
                bottom: 60px; /* مساحة للتنقل السفلي في الجوال إن وجد */
            }
            /* تعديل بسيط للعرض في الجوال */
            #quran-container {
                padding: 1rem;
            }
        }
        
        /* Loading Spinner */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 50px auto;
            display: none;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    </style>
</head>
<body>

    <!-- القائمة الجانبية -->
    <aside id="sidebar">
        <div class="logo">
            القرآن الكريم
        </div>
        <div class="search-box">
            <input type="text" id="search-input" placeholder="ابحث عن سورة...">
        </div>
        <ul class="surah-list" id="surah-list">
            <!-- سيتم ملؤها بالجافاسكربت -->
        </ul>
    </aside>

    <!-- المحتوى الرئيسي -->
    <main>
        <header>
            <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
            <h3 id="current-surah-name">اختر سورة للبدء</h3>
            <div class="controls">
                <select id="reciter-select" onchange="changeReciter()">
                    <option value="ar.alafasy">مشاري العفاسي</option>
                    <option value="ar.husaboratory">أحمد العجمي</option>
                    <option value="ar.abdulbasitmorattal">عبد الباسط (مرتل)</option>
                    <option value="ar.minaboratory">محمد صديق المنشاوي</option>
                </select>
                <button onclick="toggleTheme()">🌙/☀️</button>
                <button onclick="toggleTafsirMode()">تفسير/نص</button>
            </div>
        </header>

        <div id="quran-container">
            <div class="loader" id="loader"></div>
            <div id="surah-content">
                <!-- محتوى السورة يظهر هنا -->
                <div style="text-align: center; margin-top: 50px; color: #888;">
                    <p>اختر سورة من القائمة الجانبية للقراءة والاستماع</p>
                </div>
            </div>
        </div>

        <!-- مشغل الصوت -->
        <div class="player-bar">
            <div class="player-info">
                <h4 id="player-status">لا يوجد تلاوة</h4>
                <p id="player-ayah-details">--</p>
            </div>
            <div class="player-controls">
                <button class="ctrl-btn" onclick="playPrevAyah()">⏮</button>
                <button class="ctrl-btn ctrl-btn-main" id="main-play-btn" onclick="togglePlay()">▶</button>
                <button class="ctrl-btn" onclick="playNextAyah()">⏭</button>
            </div>
        </div>
    </main>

    <script>
        // --- المتغيرات العامة ---
        let surahs = [];
        let currentSurah = null;
        let currentAyahs = []; // تخزين آيات السورة الحالية
        let currentAudioIndex = -1; // index of current ayah in currentAyahs
        let audio = new Audio();
        let isTafsirVisible = false;

        // --- عنصر واجهة المستخدم ---
        const surahListEl = document.getElementById('surah-list');
        const surahContentEl = document.getElementById('surah-content');
        const loaderEl = document.getElementById('loader');
        const playerStatusEl = document.getElementById('player-status');
        const playerAyahDetailsEl = document.getElementById('player-ayah-details');
        const mainPlayBtn = document.getElementById('main-play-btn');
        
        // --- عند تحميل الصفحة ---
        document.addEventListener('DOMContentLoaded', () => {
            fetchSurahs();
            setupEvents();
        });

        function setupEvents() {
            // البحث
            document.getElementById('search-input').addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const items = document.querySelectorAll('.surah-item');
                items.forEach(item => {
                    const name = item.innerText.toLowerCase();
                    item.style.display = name.includes(query) ? 'flex' : 'none';
                });
            });

            // حدث انتهاء الصوت للتشغيل التلقائي
            audio.addEventListener('ended', () => {
                playNextAyah();
            });
            
            audio.addEventListener('timeupdate', () => {
                // يمكن إضافة شريط تقدم هنا
            });
            
            audio.addEventListener('play', () => {
                mainPlayBtn.innerText = '⏸';
            });
            
            audio.addEventListener('pause', () => {
                mainPlayBtn.innerText = '▶';
            });
        }

        // --- جلب قائمة السور ---
        async function fetchSurahs() {
            try {
                const response = await fetch('https://api.alquran.cloud/v1/surah');
                const data = await response.json();
                surahs = data.data;
                renderSurahList(surahs);
            } catch (error) {
                console.error('Error fetching surahs:', error);
                surahListEl.innerHTML = '<li style="padding:1rem">حدث خطأ في تحميل السور</li>';
            }
        }

        function renderSurahList(list) {
            surahListEl.innerHTML = '';
            list.forEach(surah => {
                const li = document.createElement('li');
                li.className = 'surah-item';
                li.innerHTML = `
                    <span>${surah.name}</span>
                    <span class="surah-number">${surah.number}</span>
                `;
                li.onclick = () => loadSurah(surah.number, li);
                surahListEl.appendChild(li);
            });
        }

        // --- جلب تفاصيل السورة ---
        async function loadSurah(number, element) {
            // تحديث واجهة القائمة
            document.querySelectorAll('.surah-item').forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            
            // في الجوال، أغلق القائمة بعد الاختيار
            if(window.innerWidth < 768) {
                document.getElementById('sidebar').classList.remove('open');
            }

            // إظهار التحميل
            surahContentEl.style.display = 'none';
            loaderEl.style.display = 'block';

            const reciter = document.getElementById('reciter-select').value;
            
            try {
                // نجلب النص والصوت معاً (Audio API for every surah)
                const response = await fetch(`https://api.alquran.cloud/v1/surah/${number}/editions/quran-uthmani,${reciter},ar.muyassar`);
                const data = await response.json();
                
                const quranData = data.data[0];
                const audioData = data.data[1];
                const tafsirData = data.data[2];

                currentSurah = quranData;
                
                // دمج البيانات (النص + الصوت + التفسير) في مصفوفة واحدة ليسهل التعامل معها
                currentAyahs = quranData.ayahs.map((ayah, index) => {
                    return {
                        number: ayah.number,
                        numberInSurah: ayah.numberInSurah,
                        text: ayah.text,
                        audio: audioData.ayahs[index].audio, // رابط الصوت
                        tafsir: tafsirData.ayahs[index].text // نص التفسير
                    };
                });

                renderSurahContent(quranData);
                
                // تحديث العنوان
                document.getElementById('current-surah-name').innerText = quranData.name;
                
                // إعادة ضبط المشغل
                currentAudioIndex = -1;
                audio.pause();
                playerStatusEl.innerText = `سورة ${quranData.name}`;
                playerAyahDetailsEl.innerText = 'اضغط على الآية لتشغيلها';

            } catch (error) {
                console.error('Error loading surah:', error);
                surahContentEl.innerHTML = '<div style="text-align:center">حدث خطأ في تحميل البيانات. تأكد من الاتصال بالإنترنت.</div>';
                loaderEl.style.display = 'none';
                surahContentEl.style.display = 'block';
            }
        }

        function renderSurahContent(surah) {
            loaderEl.style.display = 'none';
            surahContentEl.style.display = 'block';
            surahContentEl.innerHTML = '';

            // البسملة (ما عدا التوبة وبعض الحالات)
            if (surah.number !== 9) {
                const basmalah = document.createElement('div');
                basmalah.className = 'basmalah';
                basmalah.innerText = 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ';
                surahContentEl.appendChild(basmalah);
            }

            currentAyahs.forEach((ayah, index) => {
                const div = document.createElement('div');
                div.id = `ayah-${index}`;
                div.className = 'ayah-card';
                
                // ترتيب الآيات في عرض الصفحة (للمدورة)
                div.innerHTML = `
                    <div class="ayah-actions">
                        <span>آية ${ayah.numberInSurah}</span>
                        <button class="play-btn-ayah" onclick="playSpecificAyah(${index})">▶ تشغيل</button>
                    </div>
                    <div class="ayah-text">${ayah.text}</div>
                    <div class="tafsir-text">${ayah.tafsir}</div>
                `;

                // إضافة حدث عند الضغط على الآية للتشغيل
                div.addEventListener('click', (e) => {
                    if(e.target.tagName !== 'BUTTON') {
                        playSpecificAyah(index);
                    }
                });

                surahContentEl.appendChild(div);
            });
        }

        // --- منطق الصوت ---
        
        function playSpecificAyah(index) {
            if (index >= 0 && index < currentAyahs.length) {
                currentAudioIndex = index;
                highlightAyah(index);
                const url = currentAyahs[index].audio;
                audio.src = url;
                audio.play();
                
                // تحديث واجهة المشغل
                playerStatusEl.innerText = `جاري التلاوة: ${currentSurah.name}`;
                playerAyahDetailsEl.innerText = `الآية ${currentAyahs[index].numberInSurah}`;
            }
        }

        function togglePlay() {
            if (!audio.src) {
                if(currentAyahs.length > 0) playSpecificAyah(0);
                return;
            }
            if (audio.paused) {
                audio.play();
            } else {
                audio.pause();
            }
        }

        function playNextAyah() {
            if (currentAudioIndex + 1 < currentAyahs.length) {
                playSpecificAyah(currentAudioIndex + 1);
            } else {
                // انتهت السورة، إيقاف
                audio.pause();
                showToast('انتهت السورة');
            }
        }

        function playPrevAyah() {
            if (currentAudioIndex - 1 >= 0) {
                playSpecificAyah(currentAudioIndex - 1);
            }
        }

        function highlightAyah(index) {
            // إزالة التحديد القديم
            document.querySelectorAll('.ayah-card').forEach(el => el.classList.remove('active'));
            // إضافة التحديد الجديد
            const el = document.getElementById(`ayah-${index}`);
            if (el) {
                el.classList.add('active');
                // سكرول ناعم للآية
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function changeReciter() {
            // إعادة تحميل السورة الحالية بالقارئ الجديد
            if (currentSurah) {
                const activeEl = document.querySelector('.surah-item.active');
                loadSurah(currentSurah.number, activeEl);
            }
        }

        // --- أدوات واجهة المستخدم ---

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        function toggleTheme() {
            const body = document.body;
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
            } else {
                body.setAttribute('data-theme', 'dark');
            }
        }

        function toggleTafsirMode() {
            isTafsirVisible = !isTafsirVisible;
            const tafsirElements = document.querySelectorAll('.tafsir-text');
            const quranTextElements = document.querySelectorAll('.ayah-text');
            
            if (isTafsirVisible) {
                tafsirElements.forEach(el => el.style.display = 'block');
                // يمكن تقليل حجم خط القرآن قليلا لتركيز على التفسير
                quranTextElements.forEach(el => el.style.fontSize = '1.8rem'); 
            } else {
                tafsirElements.forEach(el => el.style.display = 'none');
                quranTextElements.forEach(el => el.style.fontSize = '2.5rem');
            }
        }
        
        function showToast(message) {
            // إنشاء عنصر إشعار بسيط
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
                background: rgba(0,0,0,0.8); color: white; padding: 10px 20px;
                border-radius: 20px; z-index: 1000; font-size: 0.9rem;
            `;
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

    </script>
</body>
</html>
