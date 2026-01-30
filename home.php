<?php
// =============================================================================
// منطق PHP: بيانات وهمية لمعرض الأعمال (Portfolio)
// =============================================================================

 $portfolio_items = [
    [
        'id' => 1,
        'title' => 'تطبيق "تواصل" للهواتف الذكية',
        'category' => 'تطبيقات الجوال',
        'image_url' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 2,
        'title' => 'منصة "تعلم" للتعليم الإلكتروني',
        'category' => 'تطوير الويب',
        'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 3,
        'title' => 'هوية بصرية لمقاهي "أصيلة"',
        'category' => 'هوية بصرية',
        'image_url' => 'https://images.unsplash.com/photo-1544966503-7e3c4c857b9c?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 4,
        'title' => 'حملة "نواة" للتسويق الرقمي',
        'category' => 'تسويق رقمي',
        'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2015&auto=format&fit=crop'
    ],
    [
        'id' => 5,
        'title' => 'موقع "فنادق الراحة" الحصري',
        'category' => 'تطوير الويب',
        'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=2070&auto=format&fit=crop'
    ],
    [
        'id' => 6,
        'title' => 'واجهة متجر "إلكترو" الإلكتروني',
        'category' => 'تصميم واجهات',
        'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=2070&auto=format&fit=crop'
    ]
];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نواة الرقمية | وكالة إبداعية</title>
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- =============================================================================
    تنسيقات CSS: تصميم حديث ونظيف
    ============================================================================= -->
    <style>
        /* خطوط عربية وإنجليزية */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Tajawal:wght@400;500;700&display=swap');

        /* متغيرات CSS للسهولة */
        :root {
            --primary-color: #007BFF;
            --secondary-color: #6c757d;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --accent-color: #ffc107;
        }

        /* إعدادات عامة */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }

        h1, h2, h3 {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* شريط التنقل */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            font-family: 'Cairo', sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-menu a {
            color: var(--dark-color);
            text-decoration: none;
            margin-right: 1.5rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: var(--primary-color);
        }

        /* قسم البطل (Hero Section) */
        .hero {
            background: linear-gradient(rgba(0, 123, 255, 0.8), rgba(0, 123, 255, 0.8)), url('https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            text-align: center;
            color: #fff;
            padding-top: 80px; /* مساحة للشريط الثابت */
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .hero-content p {
            font-size: 1.3rem;
            max-width: 600px;
            margin: 0 auto 2rem auto;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: var(--dark-color);
        }

        .btn-primary:hover {
            background-color: #e0a800;
            transform: translateY(-3px);
        }

        /* قسم الخدمات */
        .services {
            padding: 5rem 0;
            background-color: var(--light-color);
        }
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: var(--dark-color);
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        .service-card {
            background: #fff;
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .service-card .icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        /* قسم معرض الأعمال */
        .portfolio {
            padding: 5rem 0;
        }
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        .portfolio-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .portfolio-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .portfolio-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .portfolio-item:hover .portfolio-overlay {
            opacity: 1;
        }
        .portfolio-item:hover img {
            transform: scale(1.1);
        }
        .portfolio-overlay h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .portfolio-overlay p {
            font-size: 1rem;
            color: var(--accent-color);
        }

        /* قسم آراء العملاء */
        .testimonials {
            padding: 5rem 0;
            background-color: var(--light-color);
            text-align: center;
        }
        .testimonial-quote {
            font-size: 1.5rem;
            font-style: italic;
            color: var(--secondary-color);
            max-width: 800px;
            margin: 0 auto 2rem auto;
        }
        .testimonial-author {
            font-weight: bold;
            color: var(--dark-color);
        }

        /* قسم الدعوة لاتخاذ إجراء (CTA) */
        .cta {
            background-color: var(--primary-color);
            color: #fff;
            padding: 4rem 0;
            text-align: center;
        }
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .btn-light {
            background-color: #fff;
            color: var(--primary-color);
        }
        .btn-light:hover {
            background-color: var(--light-color);
        }

        /* التذييل (Footer) */
        .main-footer {
            background-color: var(--dark-color);
            color: #fff;
            padding: 3rem 0 1rem 0;
        }
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .footer-section h3 {
            margin-bottom: 1rem;
            color: var(--accent-color);
        }
        .footer-section p, .footer-section ul {
            color: #ccc;
        }
        .footer-section ul {
            list-style: none;
        }
        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-section ul li a:hover {
            color: var(--accent-color);
        }
        .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            margin-left: 1rem;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: var(--accent-color);
        }
        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #555;
            color: #999;
        }

        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.5rem; }
            .hero-content p { font-size: 1.1rem; }
            .navbar .container { flex-direction: column; }
            .nav-menu { margin-top: 1rem; }
            .nav-menu a { margin: 0 0.5rem; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <!-- =============================================================================
    هيكل HTML: أقسام الموقع المختلفة
    ============================================================================= -->

    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">نواة الرقمية</a>
            <div class="nav-menu">
                <a href="#services">خدماتنا</a>
                <a href="#portfolio">أعمالنا</a>
                <a href="#testimonials">آراء العملاء</a>
                <a href="#contact">تواصل معنا</a>
            </div>
        </div>
    </nav>

    <!-- قسم البطل -->
    <header class="hero">
        <div class="hero-content">
            <h1>نحول أفكارك إلى واقع رقمي مذهل</h1>
            <p>نحن وكالة إبداعية متخصصة في تصميم وتطوير حلول رقمية مبتكرة تساعد علامتك التجارية على النمو والازدهار.</p>
            <a href="#contact" class="btn btn-primary">ابدأ مشروعك الآن</a>
        </div>
    </header>

    <main>
        <!-- قسم الخدمات -->
        <section id="services" class="services">
            <div class="container">
                <h2 class="section-title">خدماتنا</h2>
                <div class="services-grid">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-code"></i></div>
                        <h3>تطوير الويب</h3>
                        <p>مواقع ويب سريعة، آمنة، ومتجاوبة باستخدام أحدث التقنيات.</p>
                    </div>
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                        <h3>تطبيقات الجوال</h3>
                        <p>تطبيقات ذكية وأنيقة لنظامي iOS و Android توفر تجربة مستخدم فريدة.</p>
                    </div>
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-paint-brush"></i></div>
                        <h3>تصميم UI/UX</h3>
                        <p>واجهات جذابة وسهلة الاستخدام تضع المستخدم في مقدمة الأولويات.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- قسم معرض الأعمال -->
        <section id="portfolio" class="portfolio">
            <div class="container">
                <h2 class="section-title">أحدث أعمالنا</h2>
                <div class="portfolio-grid">
                    <?php foreach ($portfolio_items as $item): ?>
                        <div class="portfolio-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="portfolio-overlay">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p><?php echo htmlspecialchars($item['category']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- قسم آراء العملاء -->
        <section id="testimonials" class="testimonials">
            <div class="container">
                <h2 class="section-title">ماذا يقول عملاؤنا</h2>
                <p class="testimonial-quote">"فريق نواة الرقمية احترافي للغاية. لقد حولوا رؤيتنا إلى منصة إلكترونية تفوقت على توقعاتنا. ننصح بهم بشدة!"</p>
                <p class="testimonial-author">- سارة أحمد، مديرة مشروع في شركة "الأفق"</p>
            </div>
        </section>

        <!-- قسم الدعوة لاتخاذ إجراء -->
        <section id="contact" class="cta">
            <div class="container">
                <h2>هل أنت مستعد لبدء مشروعك؟</h2>
                <p>تواصل معنا اليوم واحصل على استشارة مجانية</p>
                <a href="mailto:info@nawaa.com" class="btn btn-light">تواصل معنا</a>
            </div>
        </section>
    </main>

    <!-- التذييل -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>نواة الرقمية</h3>
                    <p>شريكك الموثوق في رحلة التحول الرقمي. نحن نؤمن بقوة الأفكار الإبداعية والتقنية المتقدمة.</p>
                </div>
                <div class="footer-section">
                    <h3>روابط سريعة</h3>
                    <ul>
                        <li><a href="#services">خدماتنا</a></li>
                        <li><a href="#portfolio">أعمالنا</a></li>
                        <li><a href="#testimonials">آراء العملاء</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>تابعنا</h3>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> نواة الرقمية. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

</body>
</html>
