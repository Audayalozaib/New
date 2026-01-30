<?php
// =============================================================================
// الجزء الأول: منطق PHP (جلب البيانات)
// في تطبيق حقيقي، هذه البيانات ستأتي من قاعدة بيانات (مثل MySQL).
// هنا، سنستخدم مصفوفة PHP لمحاكاة المقالات.
// =============================================================================

 $posts = [
    [
        'id' => 1,
        'title' => 'مقدمة إلى عالم تطوير الويب',
        'excerpt' => 'تطوير الويب هو رحلة ممتعة ومجزية. في هذا المقال، سنستعرض اللغات الأساسية مثل HTML, CSS, و JavaScript، وكيف تعمل معًا لإنشاء مواقع ويب تفاعلية وجميلة.',
        'content' => 'هذا هو المحتوى الكامل للمقال... سيكون هنا نص طويل يشرح بالتفصيل مفاهيم تطوير الويب.',
        'author' => 'أحمد علي',
        'date' => '٢٥ أكتوبر ٢٠٢٣',
        'image_url' => 'https://via.placeholder.com/800x400/3498db/ffffff?text=تطوير+الويب'
    ],
    [
        'id' => 2,
        'title' => 'أفضل ممارسات الأمان في تطبيقات PHP',
        'excerpt' => 'أمان تطبيقات الويب ليس خيارًا، بل هو ضرورة. سنناقش في هذا المقال أهم الثغرات الأمنية الشائعة مثل SQL Injection و XSS، وكيفية حماية تطبيقاتك منها باستخدام تقنيات PHP الحديثة.',
        'content' => 'المحتوى الكامل يتضمن أمثلة برمجية لفلترة المدخلات، استخدام Prepared Statements، وتشفير البيانات الحساسة.',
        'author' => 'سارة محمد',
        'date' => '٢٠ أكتوبر ٢٠٢٣',
        'image_url' => 'https://via.placeholder.com/800x400/e74c3c/ffffff?text=أمان+PHP'
    ],
    [
        'id' => 3,
        'title' => 'فن تصميم واجهات المستخدم (UI/UX)',
        'excerpt' => 'التصميم الجيد لا يقتصر على الجماليات فقط، بل يتعلق بتجربة المستخدم. تعرف على المبادئ الأساسية لتصميم واجهات سهلة الاستخدام وبديهية تجعل زوار موقعك سعداء.',
        'content' => 'هنا سنتعمق في دراسات الحالة، ونحلل تصاميم ناجحة، ونقدم نصائح عملية لتحسين واجهة موقعك.',
        'author' => 'خالد سعيد',
        'date' => '١٥ أكتوبر ٢٠٢٣',
        'image_url' => 'https://via.placeholder.com/800x400/2ecc71/ffffff?text=تصميم+واجهات'
    ]
];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدونتي البسيطة</title>

    <!-- =============================================================================
    الجزء الثاني: تنسيقات CSS (الشكل والمظهر)
    تم وضعها هنا داخل وسوم <style> لتبقى كل شيء في ملف واحد.
    ============================================================================= -->
    <style>
        /* إعداد خط عربي جميل من Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');

        /* إعدادات عامة للصفحة */
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f4f7f6; /* لون خلفية هادئ */
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }

        /* رأس الصفحة */
        .main-header {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 1.5rem 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .main-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        /* الحاوية الرئيسية للمحتوى */
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* بطاقة المقال الواحد */
        .post-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2.5rem;
            overflow: hidden; /* لجعل حواف الصورة والبطاقة مدورة */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .post-card img {
            width: 100%;
            height: auto;
            display: block;
        }

        .post-content {
            padding: 2rem;
        }

        .post-content h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .post-meta {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .post-meta span {
            margin-left: 1rem;
        }

        .post-content p {
            color: #555;
            font-size: 1.1rem;
        }

        .read-more {
            display: inline-block;
            margin-top: 1.5rem;
            background-color: #3498db;
            color: #ffffff;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .read-more:hover {
            background-color: #2980b9;
        }

        /* تذييل الصفحة */
        .main-footer {
            text-align: center;
            padding: 2rem;
            background-color: #34495e;
            color: #ecf0f1;
            margin-top: 3rem;
        }

        /* لجعل التصميم متجاوبًا مع الشاشات الصغيرة */
        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 2rem;
            }
            .post-content {
                padding: 1.5rem;
            }
            .post-content h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- =============================================================================
    الجزء الثالث: هيكل HTML وعرض البيانات باستخدام PHP
    ============================================================================= -->

    <header class="main-header">
        <h1>مدونتي البسيطة</h1>
    </header>

    <main class="container">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                <div class="post-content">
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="post-meta">
                        <span>👤 <?php echo htmlspecialchars($post['author']); ?></span>
                        <span>📅 <?php echo htmlspecialchars($post['date']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <!-- في تطبيق حقيقي، الرابط سيكون something like post.php?id=1 -->
                    <a href="#" class="read-more">اقرأ المزيد</a>
                </div>
            </article>
        <?php endforeach; ?>
    </main>

    <footer class="main-footer">
        <p>جميع الحقوق محفوظة © ٢٠٢٣ | صُمم بـ ❤️ و PHP</p>
    </footer>

</body>
</html>
