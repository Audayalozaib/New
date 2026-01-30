<?php
// =============================================================================
// منطق PHP: بيانات وهمية لمحاكاة مقالات المجلة
// =============================================================================

 $magazine_posts = [
    [
        'id' => 1,
        'title' => 'مستقبل الذكاء الاصطناعي: كيف سيغير عالمنا في العقد المقبل؟',
        'category' => 'تقنية',
        'excerpt' => 'من السيارات ذاتية القيادة إلى التشخيص الطبي الدقيق، يغزو الذكاء الاصطناعي كل جوانب حياتنا. دعنا نستكشف الفرص والتحديات التي تنتظرنا في هذا العصر الجديد.',
        'author' => 'نور الدين',
        'image_url' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 2,
        'title' => 'فن الطبخ البطيء: وصفات تستحق الانتظار',
        'category' => 'مطبخ',
        'excerpt' => 'الطبخ البطيء ليس مجرد طريقة لإعداد الطعام، بل هو فن يركز على النكهات العميقة والقوام المثالي. تعلم أسرار الطبخ على نار هادئة لتحصل على وجبات لا تُنسى.',
        'author' => 'مروة',
        'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 3,
        'title' => 'أفضل 10 وجهات سفرية لمحبي المغامرة في ٢٠٢٤',
        'category' => 'سفر',
        'excerpt' => 'هل أنت مستعد لتخطي الحدود واكتشاف عوالم جديدة؟ قدمنا لك قائمة بأروع الوجهات التي تنتظر المستكشفين الباحثين عن الأدرينالين والطبيعة الخلابة.',
        'author' => 'ياسر',
        'image_url' => 'https://images.unsplash.com/photo-1554528259-9a8b3b5c1e0c?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 4,
        'title' => 'التصميم minimalism: أقل هو الأكثر فعلاً',
        'category' => 'تصميم',
        'excerpt' => 'في عالم مليء بالضوضاء البصرية، يقدم التصميم البسيط هدوءًا ووضوحًا. اكتشف كيف يمكن للمبادئ البسيطة أن تخلق تصاميم قوية ومؤثرة.',
        'author' => 'لينا',
        'image_url' => 'https://images.unsplash.com/photo-1586432815325-59bf106bf0c4?q=80&w=2070&auto=format&fit=crop'
    ]
];

// نأخذ أول مقال ليكون المقال الرئيسي (Featured)
 $featured_post = $magazine_posts[0];

// ونأخذ باقي المقالات لعرضها في الشبكة
 $recent_posts = array_slice($magazine_posts, 1);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجلة ذوق | ملهمك اليومي</title>

    <!-- =============================================================================
    تنسيقات CSS: تصميم عصري وداكن مع لمسات من الألوان
    ============================================================================= -->
    <style>
        /* خطوط عربية من Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Tajawal:wght@400;700&display=swap');

        /* إعدادات عامة */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #1a1a1a; /* خلفية داكنة جداً */
            color: #e0e0e0; /* نص رمادي فاتح */
            margin: 0;
            line-height: 1.7;
        }

        /* رأس الصفحة */
        .site-header {
            background-color: #000;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #ff4757; /* خط ملون بالأحمر */
        }

        .logo {
            font-family: 'Cairo', sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            color: #fff;
            text-decoration: none;
            letter-spacing: -1px;
        }

        .nav-menu a {
            color: #b0b0b0;
            text-decoration: none;
            margin-right: 1.5rem;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: #ff4757;
        }

        /* الحاوية الرئيسية */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* قسم المقال الرئيسي (Featured) */
        .featured-post {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
        }
        .featured-post:hover {
            transform: scale(1.01);
        }

        .featured-post img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* لجعل الصورة تغطي المساحة بالكامل دون تشوه */
        }

        .featured-content {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .category-tag {
            display: inline-block;
            background-color: #ff4757;
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .featured-content h1 {
            font-family: 'Cairo', sans-serif;
            font-size: 2.5rem;
            margin: 0 0 1rem 0;
            color: #ffffff;
            line-height: 1.3;
        }

        .featured-content p {
            font-size: 1.1rem;
            color: #cccccc;
            margin-bottom: 1.5rem;
        }

        .author-name {
            font-size: 0.9rem;
            color: #999;
        }

        /* شبكة المقالات الأخيرة */
        .recent-posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .grid-post-card {
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .grid-post-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(255, 71, 87, 0.2); /* ظل ملون */
        }

        .grid-post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .grid-post-content {
            padding: 1.5rem;
        }

        .grid-post-content h3 {
            font-family: 'Cairo', sans-serif;
            font-size: 1.3rem;
            margin: 0 0 0.5rem 0;
            color: #fff;
        }

        .grid-post-content p {
            font-size: 0.95rem;
            color: #b0b0b0;
            margin: 0;
        }

        /* تصميم متجاوب */
        @media (max-width: 900px) {
            .featured-post {
                grid-template-columns: 1fr;
            }
            .featured-post img {
                height: 250px;
            }
            .featured-content h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 600px) {
            .site-header {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-menu a {
                margin: 0 0.5rem;
            }
            .featured-content {
                padding: 2rem 1.5rem;
            }
            .featured-content h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>

    <!-- =============================================================================
    هيكل HTML: عرض المقال الرئيسي والمقالات الأخرى
    ============================================================================= -->

    <header class="site-header">
        <a href="#" class="logo">مجلة ذوق</a>
        <nav class="nav-menu">
            <a href="#">تقنية</a>
            <a href="#">مطبخ</a>
            <a href="#">سفر</a>
            <a href="#">تصميم</a>
        </nav>
    </header>

    <main class="main-container">

        <!-- المقال الرئيسي -->
        <article class="featured-post">
            <img src="<?php echo htmlspecialchars($featured_post['image_url']); ?>" alt="<?php echo htmlspecialchars($featured_post['title']); ?>">
            <div class="featured-content">
                <span class="category-tag"><?php echo htmlspecialchars($featured_post['category']); ?></span>
                <h1><?php echo htmlspecialchars($featured_post['title']); ?></h1>
                <p><?php echo htmlspecialchars($featured_post['excerpt']); ?></p>
                <p class="author-name">✍️ <?php echo htmlspecialchars($featured_post['author']); ?></p>
            </div>
        </article>

        <!-- المقالات الأخيرة -->
        <section class="recent-posts-grid">
            <?php foreach ($recent_posts as $post): ?>
                <article class="grid-post-card">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <div class="grid-post-content">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

    </main>

</body>
</html>
