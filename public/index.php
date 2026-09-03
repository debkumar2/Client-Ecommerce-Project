<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Lightweight Front Controller Router
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$baseUrlPath = parse_url(url(), PHP_URL_PATH) ?? '';
$path = str_replace($baseUrlPath, '', $requestUri);
$path = '/' . ltrim($path, '/');

if (in_array($path, ['/shop', '/shop.php', '/shop/']) || strpos($path, '/category/') === 0) {
    if (strpos($path, '/category/') === 0) {
        $catSlug = trim(str_replace('/category/', '', $path), '/');
        if (!empty($catSlug)) {
            $_GET['category'] = $catSlug;
        }
    }
    require_once __DIR__ . '/shop.php';
    exit;
}

if (in_array($path, ['/about', '/about.php', '/about/'])) {
    require_once __DIR__ . '/about.php';
    exit;
}

if (in_array($path, ['/contact', '/contact.php', '/contact/'])) {
    require_once __DIR__ . '/contact.php';
    exit;
}

if (in_array($path, ['/blog', '/blog.php', '/blog/'])) {
    require_once __DIR__ . '/blog.php';
    exit;
}

if (in_array($path, ['/login', '/login.php', '/login/'])) {
    require_once __DIR__ . '/login.php';
    exit;
}

if (in_array($path, ['/register', '/register.php', '/signup', '/signup/'])) {
    $_GET['tab'] = 'register';
    require_once __DIR__ . '/login.php';
    exit;
}

if (in_array($path, ['/account', '/account.php', '/account/'])) {
    require_once __DIR__ . '/account.php';
    exit;
}

if (in_array($path, ['/logout', '/logout.php'])) {
    require_once __DIR__ . '/../helpers/auth.php';
    logoutUser();
    header('Location: ' . url('login?logged_out=1'));
    exit;
}

if (in_array($path, ['/404', '/404.php'])) {
    require_once __DIR__ . '/404.php';
    exit;
}

// Fallback for invalid paths ending with unknown extension or unknown routes (excluding root and home)
if (!in_array($path, ['/', '/index.php', '']) && !file_exists(__DIR__ . $path) && !str_starts_with($path, '/product/')) {
    require_once __DIR__ . '/404.php';
    exit;
}

// Dynamic Product Categories from Database Table
$categories = [];
try {
    $pdo = Database::getConnection();
    $stmtCat = $pdo->query("SELECT * FROM `categories` WHERE status IN ('approved', 'active') ORDER BY id ASC");
    $rowsCat = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rowsCat)) {
        foreach ($rowsCat as $c) {
            $subtext = !empty($c['seo_title']) ? $c['seo_title'] : 'Biswas Official Selection';
            if ($c['slug'] === 'arjuna-bark') $subtext = 'Dried, High Quality & Premium Cut';
            elseif ($c['slug'] === 'dried-herbs') $subtext = 'Neem Leaves, Tulsi & Reetha Nuts';
            elseif ($c['slug'] === 'herbs-powder') $subtext = 'Harad Powder & Neem Powder';
            elseif ($c['slug'] === 'renewable-energy') $subtext = 'Solar Street Lights, Batteries & Panels';

            $categories[] = [
                'id' => (int)$c['id'],
                'name' => $c['name'],
                'slug' => $c['slug'],
                'subtext' => $subtext,
                'description' => $c['description'] ?: 'High quality natural products supplied directly from Kolkata.',
                'image' => !empty($c['image_url']) ? $c['image_url'] : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'
            ];
        }
    }
} catch (\Throwable $e) {}

if (empty($categories)) {
    $categories = [
        [
            'id' => 1,
            'name' => 'Arjuna Bark Collection',
            'slug' => 'arjuna-bark',
            'subtext' => 'Dried, High Quality & Premium Cut',
            'description' => '99% Pure Terminalia Arjuna bark. Shade-cured for cardiac wellness & natural heart health support.',
            'image' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 2,
            'name' => 'Dried Herbs',
            'slug' => 'dried-herbs',
            'subtext' => 'Neem Leaves, Tulsi & Reetha Nuts',
            'description' => 'Medicine-grade dried Neem leaves, organic Tulsi leaves & natural Reetha soap nut shells.',
            'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 3,
            'name' => 'Herbs Powder',
            'slug' => 'herbs-powder',
            'subtext' => 'Harad Powder & Neem Powder',
            'description' => '100% fine pulverized Harad (Terminalia Chebula) powder and antibacterial Neem leaf powder.',
            'image' => 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 4,
            'name' => 'Renewable Energy Products',
            'slug' => 'renewable-energy',
            'subtext' => 'Solar Street Lights, Batteries & Panels',
            'description' => 'Commercial integrated solar LED street lights, lithium/lead-acid solar batteries & PV panels.',
            'image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=600&q=80'
        ]
    ];
}

// Official Featured Products (Derived from biswas-enterprise.co.in catalog)
$featuredProducts = [
    [
        'id' => 101,
        'name' => 'Dried Arjuna Bark (99% Pure)',
        'category' => 'Arjuna Bark',
        'price' => 710,
        'regular_price' => 775,
        'rating' => 5,
        'reviews_count' => 54,
        'badge' => 'PREMIUM EXPORT',
        'specs' => 'Purity: 99% | Origin: India | Packaging: Plastic/Jute Bags',
        'image' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 104,
        'name' => 'Pure Organic Harad Powder',
        'category' => 'Herbs Powder',
        'price' => 350,
        'regular_price' => 420,
        'rating' => 5,
        'reviews_count' => 68,
        'badge' => 'AYURVEDIC CHOICE',
        'specs' => 'Processing: Blended | Origin: India | Storage: Cool, Dry Place',
        'image' => 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 105,
        'name' => 'Antibacterial Neem Powder',
        'category' => 'Herbs Powder',
        'price' => 290,
        'regular_price' => 360,
        'rating' => 5,
        'reviews_count' => 41,
        'badge' => 'SKIN & HAIR CARE',
        'specs' => 'Form: Fine Powder | Shelf Life: 2 Years | Antibacterial & Antifungal',
        'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 108,
        'name' => 'Natural Reetha Soap Nuts (Shells)',
        'category' => 'Dried Herbs',
        'price' => 280,
        'regular_price' => 340,
        'rating' => 5,
        'reviews_count' => 39,
        'badge' => 'MEDICINE GRADE',
        'specs' => 'Purity: 90% | Moisture: 12% | Material: Reetha Shells',
        'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 109,
        'name' => 'Medicine-Grade Dried Tulsi Leaves',
        'category' => 'Dried Herbs',
        'price' => 320,
        'regular_price' => 380,
        'rating' => 5,
        'reviews_count' => 27,
        'badge' => 'HOLY BASIL',
        'specs' => 'Style: Dried | Shelf Life: 6-12 Months | Wellness & Tea',
        'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 111,
        'name' => 'Solar LED Street Light (Integrated)',
        'category' => 'Renewable Energy Products',
        'price' => 3499,
        'regular_price' => 4200,
        'rating' => 5,
        'reviews_count' => 33,
        'badge' => 'SOLAR TECH',
        'specs' => 'Material: Aluminum & Silicon | Light Source: High Output LED',
        'image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=600&q=80'
    ]
];

// Official Company Overview Info
$companyDetails = [
    'founder' => 'Mr. Dipak Biswas',
    'gst' => '19AGXPB1978M1ZI',
    'established' => '2023',
    'business_type' => 'Exporter, Supplier, Trader',
    'employees' => 'Up to 10 Professionals',
    'location' => 'Na Kalikapur Berhampore Murshidabad, Bara Bazar, Kolkata, West Bengal - 742102',
    'email' => 'dipak_200607@yahoo.co.in',
    'whatsapp' => '+919330051702',
    'phone' => '+91 93300 51702',
    'markets' => 'Worldwide (India, US, UK, Middle East)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biswas Enterprise | Harad Powder Exporter & Arjuna Bark Supplier Kolkata</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Biswas Enterprise is a prominent exporter and supplier of Harad Powder, Arjuna Bark, dried herbs, and commercial solar energy solutions from Kolkata, West Bengal, India.">
    <link rel="canonical" href="<?= url() ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Core Stylesheets & Swiper 11 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/home.css') ?>">

    <style>
        /* Swiper Category & Featured Product Slider Styles */
        .categories-slider-wrapper {
            position: relative;
            padding: 10px 20px 60px 20px;
        }
        .categoriesSwiper {
            padding: 10px 6px 45px 6px !important;
        }
        .categoriesSwiper .swiper-slide {
            height: auto;
            display: flex;
        }
        .categoriesSwiper .category-card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .categoriesSwiper .category-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .category-swiper-prev, .category-swiper-next {
            width: 44px !important;
            height: 44px !important;
            background: #ffffff !important;
            border: 1px solid #d2ded5 !important;
            border-radius: 50% !important;
            color: #1b3b2b !important;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12) !important;
            transition: all 0.25s ease !important;
            top: 42% !important;
            z-index: 10 !important;
        }
        .category-swiper-prev { left: -14px !important; }
        .category-swiper-next { right: -14px !important; }
        .category-swiper-prev:hover, .category-swiper-next:hover {
            background: #1b3b2b !important;
            color: #ffffff !important;
            border-color: #1b3b2b !important;
            transform: scale(1.08);
        }
        .category-swiper-prev::after, .category-swiper-next::after {
            font-size: 16px !important;
            font-weight: 700 !important;
        }
        .categoriesSwiper .swiper-pagination {
            bottom: 5px !important;
        }
        .categoriesSwiper .swiper-pagination-bullet {
            background: #b0c4b6;
            opacity: 0.6;
        }
        .categoriesSwiper .swiper-pagination-bullet-active {
            background: #1b3b2b !important;
            width: 24px !important;
            border-radius: 10px !important;
            opacity: 1 !important;
        }

        /* Modernized D2C Styling Overrides */
        .btn-modern-primary {
            background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%) !important;
            color: #ffffff !important;
            padding: 14px 30px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 8px 20px rgba(27, 59, 43, 0.25) !important;
            transition: all 0.25s ease !important;
            border: none !important;
            cursor: pointer;
        }

        .btn-modern-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 12px 26px rgba(27, 59, 43, 0.35) !important;
            background: linear-gradient(135deg, #224835 0%, #346349 100%) !important;
        }

        .btn-modern-outline {
            background: #ffffff !important;
            color: #1b3b2b !important;
            border: 1.5px solid #1b3b2b !important;
            padding: 13px 26px !important;
            border-radius: 50px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.25s ease !important;
        }

        .btn-modern-outline:hover {
            background: #f0f5f2 !important;
            transform: translateY(-2px) !important;
        }

        .btn-whatsapp-direct {
            background: #25d366 !important;
            color: #ffffff !important;
            padding: 13px 26px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.3) !important;
            transition: all 0.25s ease !important;
        }

        .btn-whatsapp-direct:hover {
            background: #20bd5a !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 22px rgba(37, 211, 102, 0.4) !important;
        }

        .stats-counter-card {
            background: #ffffff;
            border: 1px solid #e5ebe7;
            border-radius: 20px;
            padding: 28px 36px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            margin-top: 40px;
        }

        .stats-grid-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            align-items: center;
        }

        .stat-item-cell {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #f0f6f2;
            color: #1b3b2b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Enhanced Glimpse of Company Section */
        .company-glimpse-wrapper {
            background: linear-gradient(135deg, #ffffff 0%, #f4f8f5 100%);
            border: 1.5px solid #e1ebd6;
            border-radius: 28px;
            padding: 44px;
            box-shadow: 0 16px 40px rgba(27, 59, 43, 0.05);
            transition: all 0.3s ease;
        }

        .glimpse-split-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 40px;
            align-items: center;
        }

        .glimpse-left-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .glimpse-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e8f5ec;
            color: #1b3b2b;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            width: fit-content;
            border: 1px solid #cce4d4;
        }

        .glimpse-right-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .glimpse-interactive-card {
            background: #ffffff;
            border: 1px solid #e3ede6;
            border-radius: 18px;
            padding: 20px 18px;
            position: relative;
            overflow: hidden;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .glimpse-interactive-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #1b3b2b;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .glimpse-interactive-card:hover {
            transform: translateY(-4px);
            border-color: #a8d5b5;
            box-shadow: 0 12px 28px rgba(27, 59, 43, 0.1);
        }

        .glimpse-interactive-card:hover::before {
            opacity: 1;
        }

        .glimpse-card-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #f0f6f2;
            color: #1b3b2b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            transition: all 0.25s ease;
        }

        .glimpse-interactive-card:hover .glimpse-card-icon-box {
            background: #1b3b2b;
            color: #ffffff;
            transform: scale(1.08);
        }

        .glimpse-card-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #647569;
            margin-bottom: 4px;
        }

        .glimpse-card-val {
            font-family: 'Merriweather', serif;
            font-size: 14px;
            font-weight: 700;
            color: #1a2721;
            line-height: 1.35;
        }

        @media (max-width: 992px) {
            .glimpse-split-grid {
                grid-template-columns: 1fr;
            }
            .glimpse-right-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Why Choose Biswas Enterprise Section */
        .why-choose-section {
            background: #ffffff;
            padding: 80px 0;
            position: relative;
        }

        .why-choose-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 48px;
        }

        .why-choose-card {
            background: #f8faf8;
            border: 1.5px solid #e2ebe4;
            border-radius: 22px;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .why-choose-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1b3b2b, #d4af37);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .why-choose-card:hover {
            transform: translateY(-6px);
            background: #ffffff;
            border-color: #a8d5b5;
            box-shadow: 0 16px 36px rgba(27, 59, 43, 0.08);
        }

        .why-choose-card:hover::before {
            opacity: 1;
        }

        .why-choose-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #e8f2eb;
            color: #1b3b2b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .why-choose-card:hover .why-choose-icon-box {
            background: #1b3b2b;
            color: #d4af37;
            transform: scale(1.08);
        }

        .why-choose-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #1b3b2b;
            background: #e1efe5;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 12px;
            width: fit-content;
        }

        .why-choose-card h3 {
            font-family: 'Merriweather', serif;
            font-size: 17px;
            color: #1a2721;
            margin: 0 0 10px;
            line-height: 1.35;
        }

        .why-choose-card p {
            font-size: 13.5px;
            color: #55685c;
            line-height: 1.6;
            margin: 0;
        }

        @media (max-width: 1024px) {
            .why-choose-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 640px) {
            .why-choose-section {
                padding: 50px 0;
            }
            .why-choose-grid {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-top: 32px;
            }
            .why-choose-card {
                padding: 24px 20px;
                border-radius: 18px;
            }
        }

        /* ============================================
           INLINE RESPONSIVE OVERRIDES
           ============================================ */

        /* Hero stat inline text sizes */
        @media (max-width: 640px) {
            .stat-item-cell > div > div:first-child {
                font-size: 1.1rem !important;
            }
            .stat-item-cell > div > div:last-child {
                font-size: 0.7rem !important;
            }

            /* Company glimpse buttons */
            .glimpse-left-content > div:last-child {
                flex-direction: column !important;
            }
            .glimpse-left-content > div:last-child .btn-modern-outline,
            .glimpse-left-content > div:last-child .btn-modern-primary {
                font-size: 12px !important;
                padding: 9px 14px !important;
                justify-content: center !important;
            }

            /* Product card action buttons */
            .product-card-body > div:last-child {
                flex-wrap: wrap !important;
            }
            .product-card-body > div:last-child .btn-modern-outline {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        @media (max-width: 400px) {
            .btn-modern-primary,
            .btn-modern-outline,
            .btn-whatsapp-direct {
                font-size: 13px !important;
                padding: 11px 18px !important;
            }
            .bulk-quote-section {
                padding: 22px 14px !important;
            }
        }

        @media (max-width: 475px) {
            .btn-modern-primary,
            .btn-modern-outline,
            .btn-whatsapp-direct {
                font-size: 11px !important;
                padding: 9px 14px !important;
                gap: 6px !important;
                border-radius: 40px !important;
            }
            /* Shrink the arrow SVG inside primary btn */
            .btn-modern-primary svg,
            .btn-modern-outline svg {
                width: 13px !important;
                height: 13px !important;
            }
            /* Tighter gap between hero buttons */
            .hero-content > div[style*="flex"] {
                gap: 10px !important;
            }
        }

        /* Quick Bulk Quote Modal Section */
        .bulk-quote-section {
            background: linear-gradient(135deg, #1b3b2b 0%, #0d2116 100%);
            color: #ffffff;
            border-radius: 28px;
            padding: 48px 40px;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }

        .bulk-quote-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .quote-form-input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 14px;
            margin-bottom: 16px;
            box-sizing: border-box;
            outline: none;
            transition: border 0.2s ease;
        }

        .quote-form-input::placeholder {
            color: #a8c0b1;
        }

        .quote-form-input:focus {
            border-color: #25d366;
            background: rgba(255, 255, 255, 0.12);
        }

        .prod-specs-pill {
            font-size: 12px;
            color: #4a6352;
            background: #f0f6f2;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body style="background-color: #f8faf8;">

    <!-- 1. OFFICIAL ANNOUNCEMENT BAR -->
    <div class="announcement-bar" role="region" aria-label="Announcement">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">GST NO: <?= $companyDetails['gst'] ?></span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">WHOLESALE EXPORTER & SUPPLIER FROM KOLKATA</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">WHATSAPP ORDER: <?= $companyDetails['phone'] ?></span>
            </div>
        </div>
    </div>

    <!-- 2. HEADER & NAVIGATION -->
    <header class="site-header" role="banner">
        <div class="container">
            <div class="header-inner">
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

                <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                    <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                </a>

                <nav class="main-navigation" role="navigation" aria-label="Main menu">
                    <ul>
                        <li><a href="<?= url() ?>" class="nav-link active">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About Us</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <a href="<?= url('wishlist.php') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span class="wishlist-badge" style="display:none;">0</span>
                    </a>
                    <a href="<?= url('account') ?>" class="icon-btn" aria-label="My Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </a>
                    <a href="<?= url('cart') ?>" class="icon-btn" aria-label="View Cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="cart-badge">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main id="main-content">
        <!-- 3. HERO SECTION -->
        <section class="hero-section" aria-label="Hero Introduction">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <div class="hero-badge-pill">
                            <span>🌿 PROMINENT HARAD POWDER & ARJUNA BARK EXPORTER</span>
                        </div>
                        <h1 class="hero-title">
                            Wholesale Natural Herbs & <br><span style="color: #1b3b2b;">Clean Solar Solutions</span>
                        </h1>
                        <p class="hero-description">
                            Biswas Enterprise is a premier exporter and wholesale supplier based in Kolkata, West Bengal. We specialize in 99% pure Harad Powder, shade-cured Arjuna Bark, dried botanical leaves, and commercial solar energy systems.
                        </p>
                        <div style="display: flex; gap: 16px; margin-top: 24px; flex-wrap: wrap;">
                            <a href="<?= url('shop') ?>" class="btn-modern-primary">
                                <span>EXPLORE PRODUCTS</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                            <a href="#quick-quote" class="btn-modern-outline">
                                <span>GET INSTANT QUOTE</span>
                            </a>
                        </div>
                    </div>

                    <div class="hero-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=800&q=80" 
                             alt="Biswas Enterprise Pure Botanical Sourcing Kolkata" 
                             width="800" height="600" fetchpriority="high">
                        
                        <div class="hero-float-card">
                            <div class="hero-float-info">
                                <div class="hero-float-icon">🌿</div>
                                <div>
                                    <div class="hero-float-title">Harad & Arjuna Bark Sourcing</div>
                                    <div class="hero-float-sub">Shade-Cured & Lab Verified</div>
                                </div>
                            </div>
                            <span class="account-role-badge role-badge-user" style="margin: 0; font-size: 11px; background: #e8f0eb; color: #1b3b2b;">⭐ 100% Organic</span>
                        </div>
                    </div>
                </div>

                <!-- 4. STATS COUNTER CARD -->
                <div class="stats-counter-card">
                    <div class="stats-grid-row">
                        <div class="stat-item-cell">
                            <div class="stat-icon-circle">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div>
                                <div style="font-family: 'Merriweather', serif; font-size: 1.3rem; font-weight: 700; color: #1a2721; line-height: 1.1;">Est. 2023</div>
                                <div style="font-size: 0.8rem; color: #64746b;">Kolkata, West Bengal</div>
                            </div>
                        </div>

                        <div class="stat-item-cell">
                            <div class="stat-icon-circle">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            </div>
                            <div>
                                <div style="font-family: 'Merriweather', serif; font-size: 1.3rem; font-weight: 700; color: #1a2721; line-height: 1.1;">Worldwide</div>
                                <div style="font-size: 0.8rem; color: #64746b;">Global Market Coverage</div>
                            </div>
                        </div>

                        <div class="stat-item-cell">
                            <div class="stat-icon-circle">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <div>
                                <div style="font-family: 'Merriweather', serif; font-size: 1.3rem; font-weight: 700; color: #1a2721; line-height: 1.1;">99% Pure</div>
                                <div style="font-size: 0.8rem; color: #64746b;">Medicine Grade Quality</div>
                            </div>
                        </div>

                        <div class="stat-item-cell">
                            <div class="stat-icon-circle">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon></svg>
                            </div>
                            <div>
                                <div style="font-family: 'Merriweather', serif; font-size: 1.3rem; font-weight: 700; color: #1a2721; line-height: 1.1;">Bulk Supply</div>
                                <div style="font-size: 0.8rem; color: #64746b;">Pan-India & Export</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. COMPANY OVERVIEW (GLIMPSE OF OUR COMPANY) -->
        <section class="section-padding" style="background-color: #ffffff;" aria-label="Company Profile">
            <div class="container">
                <div class="company-glimpse-wrapper">
                    <div class="glimpse-split-grid">
                        
                        <!-- Left Column: Company Narrative & Actions -->
                        <div class="glimpse-left-content">
                            <span class="eyebrow" style="color: #d4af37; font-weight: 700; letter-spacing: 0.12em;">VERIFIED GOVT REGISTERED EXPORTER</span>
                            <h2 style="font-family: 'Merriweather', serif; font-size: clamp(1.8rem, 4vw, 2.3rem); color: #1b3b2b; margin: 6px 0 12px; line-height: 1.2; text-transform: uppercase;">
                                ABOUT BISWAS ENTERPRISE
                            </h2>
                            
                            <p style="font-size: 14px; color: #4a5c51; line-height: 1.65; margin: 0;">
                                Biswas Enterprise is a premier Kolkata-based exporter, wholesale supplier, and trader. We specialize in 99% pure Harad Powder, shade-cured Arjuna Bark cuts, dried organic leaves, and commercial solar energy infrastructure.
                            </p>

                            <div style="display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap;">
                                <button type="button" class="btn-modern-outline" style="padding: 10px 18px !important; font-size: 13px !important;" onclick="copyGstNumber('<?= $companyDetails['gst'] ?>')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <span>Copy GST: <?= $companyDetails['gst'] ?></span>
                                </button>
                                
                                <a href="https://api.whatsapp.com/send?phone=<?= $companyDetails['whatsapp'] ?>&text=Hello+Biswas+Enterprise%2C+I+want+to+verify+your+company+credentials+and+request+a+bulk+quote." target="_blank" class="btn-modern-primary" style="padding: 10px 18px !important; font-size: 13px !important;">
                                    <span>Direct WhatsApp Note</span>
                                </a>
                            </div>
                        </div>

                        <!-- Right Column: 3x2 Interactive Cards Grid -->
                        <div class="glimpse-right-cards">
                            
                            <div class="glimpse-interactive-card" onclick="alert('Business Nature: Exporter, Supplier & Wholesale Trader based in Kolkata, India.')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    </div>
                                    <div class="glimpse-card-label">Nature of Business</div>
                                    <div class="glimpse-card-val"><?= $companyDetails['business_type'] ?></div>
                                </div>
                            </div>

                            <div class="glimpse-interactive-card" onclick="alert('Established in 2023 with 15+ years of founding expertise in herbal sourcing.')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    </div>
                                    <div class="glimpse-card-label">Established Year</div>
                                    <div class="glimpse-card-val"><?= $companyDetails['established'] ?> (Kolkata)</div>
                                </div>
                            </div>

                            <div class="glimpse-interactive-card" onclick="copyGstNumber('<?= $companyDetails['gst'] ?>')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    </div>
                                    <div class="glimpse-card-label">GST Certificate</div>
                                    <div class="glimpse-card-val"><?= $companyDetails['gst'] ?></div>
                                </div>
                            </div>

                            <div class="glimpse-interactive-card" onclick="alert('Markets Covered: Pan-India domestic wholesale supply and global exports across US, Europe & Middle East.')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                    </div>
                                    <div class="glimpse-card-label">Market Covered</div>
                                    <div class="glimpse-card-val"><?= $companyDetails['markets'] ?></div>
                                </div>
                            </div>

                            <div class="glimpse-interactive-card" onclick="alert('Founder & Director: Mr. Dipak Biswas')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="glimpse-card-label">Contact Person</div>
                                    <div class="glimpse-card-val"><?= $companyDetails['founder'] ?></div>
                                </div>
                            </div>

                            <div class="glimpse-interactive-card" onclick="alert('Headquarters: Na Kalikapur Berhampore Murshidabad, Bara Bazar, Kolkata, West Bengal - 742102')">
                                <div>
                                    <div class="glimpse-card-icon-box">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    </div>
                                    <div class="glimpse-card-label">Headquarters</div>
                                    <div class="glimpse-card-val">Kolkata, West Bengal</div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- WHY CHOOSE BISWAS ENTERPRISE SECTION (PREMIUM MODERN ELEVATED CARDS) -->
        <section class="why-choose-section" aria-label="Why Choose Biswas Enterprise" style="background: linear-gradient(180deg, #ffffff 0%, #f4f8f5 100%); padding: 5rem 0;">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">OUR UNCOMPROMISING STANDARDS</span>
                    <h2>WHY CHOOSE BISWAS ENTERPRISE</h2>
                    <p>Supplying medicine-grade botanicals and reliable solar energy solutions built on authenticity, lab verification, and swift logistics.</p>
                </div>

                <div class="why-choose-grid-modern">
                    <!-- Card 1 -->
                    <div class="why-card-lux">
                        <div class="why-card-top">
                            <div class="why-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <span class="why-pill-badge">100% TRACEABLE</span>
                        </div>
                        <h3>Direct Botanical Sourcing</h3>
                        <p>Harvested directly from certified growers & natural forest reserves across West Bengal to ensure zero chemical adulteration.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="why-card-lux">
                        <div class="why-card-top">
                            <div class="why-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                            </div>
                            <span class="why-pill-badge">HIGH POTENCY</span>
                        </div>
                        <h3>Shade-Cured Processing</h3>
                        <p>Slow shade-cured and pulverized under hygienic parameters to preserve essential active bio-nutrients & natural essential oils.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="why-card-lux">
                        <div class="why-card-top">
                            <div class="why-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            </div>
                            <span class="why-pill-badge">EXPORT READY</span>
                        </div>
                        <h3>Custom Bulk Packaging</h3>
                        <p>Moisture-proof jute bags, food-grade HDPE vacuum liners, and heavy 5-ply cartons designed for maritime & air freight safety.</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="why-card-lux">
                        <div class="why-card-top">
                            <div class="why-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            </div>
                            <span class="why-pill-badge">PAN-INDIA & EXPORT</span>
                        </div>
                        <h3>Rapid Global Dispatch</h3>
                        <p>Strategic Kolkata logistics hub enabling swift domestic freight and containerized maritime shipping with full customs clearance.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Custom Styles for Premium Why Choose Cards -->
        <style>
            .why-choose-grid-modern {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
            .why-card-lux {
                background: #ffffff;
                border: 1px solid #e1ebe4;
                border-radius: 20px;
                padding: 32px 24px;
                position: relative;
                overflow: hidden;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 4px 16px rgba(27, 59, 43, 0.03);
                display: flex;
                flex-direction: column;
            }
            .why-card-lux::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #1b3b2b 0%, #d4af37 100%);
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .why-card-lux:hover {
                transform: translateY(-8px);
                border-color: #c0dbc8;
                box-shadow: 0 16px 36px rgba(27, 59, 43, 0.09);
            }
            .why-card-lux:hover::before {
                opacity: 1;
            }
            .why-card-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 22px;
            }
            .why-icon-circle {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                background: #eaf3ed;
                color: #1b3b2b;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            .why-card-lux:hover .why-icon-circle {
                background: #1b3b2b;
                color: #ffffff;
            }
            .why-pill-badge {
                font-size: 10.5px;
                font-weight: 800;
                letter-spacing: 0.08em;
                color: #1b3b2b;
                background: #f0f6f2;
                padding: 4px 10px;
                border-radius: 20px;
                border: 1px solid #d4e4d8;
            }
            .why-card-lux h3 {
                font-family: 'Merriweather', serif;
                font-size: 1.15rem;
                color: #1b3b2b;
                margin: 0 0 10px;
                line-height: 1.35;
            }
            .why-card-lux p {
                font-size: 14px;
                color: #55685c;
                line-height: 1.65;
                margin: 0;
            }

            @media (max-width: 992px) {
                .why-choose-grid-modern {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }
            }
            @media (max-width: 575px) {
                .why-choose-grid-modern {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }
                .why-card-lux {
                    padding: 24px 20px;
                }
            }
        </style>

        <!-- 6. SHOP BY CATEGORY -->
        <section class="section-padding" style="background-color: #f8faf8;" aria-label="Categories Overview">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">OFFICIAL CATALOG</span>
                    <h2>OUR PRODUCT CATEGORIES</h2>
                    <p>Supplying natural herbal raw materials and eco-conscious solar energy systems.</p>
                </div>

                <?php if (count($categories) > 4): ?>
                    <!-- Swiper Carousel Slider for > 4 Categories -->
                    <div class="categories-slider-wrapper" style="position: relative;">
                        <div class="swiper categoriesSwiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($categories as $cat): ?>
                                    <div class="swiper-slide">
                                        <a href="<?= url('shop?category=' . $cat['slug']) ?>" class="category-card" style="height: 100%; display: flex; flex-direction: column;">
                                            <img src="<?= $cat['image'] ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-image" loading="lazy" decoding="async">
                                            <div class="category-content" style="flex: 1; display: flex; flex-direction: column;">
                                                <h3 class="category-name"><?= htmlspecialchars($cat['name']) ?></h3>
                                                <div style="font-size: 12px; color: #d4af37; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;"><?= htmlspecialchars($cat['subtext']) ?></div>
                                                <p class="category-desc" style="flex: 1;"><?= htmlspecialchars($cat['description']) ?></p>
                                                <span class="category-cta" style="margin-top: 12px;">
                                                    <span>EXPLORE CATEGORY</span>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                        <div class="swiper-button-prev category-swiper-prev"></div>
                        <div class="swiper-button-next category-swiper-next"></div>
                    </div>
                <?php else: ?>
                    <div class="category-grid">
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= url('shop?category=' . $cat['slug']) ?>" class="category-card">
                                <img src="<?= $cat['image'] ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-image" loading="lazy" decoding="async">
                                <div class="category-content">
                                    <h3 class="category-name"><?= htmlspecialchars($cat['name']) ?></h3>
                                    <div style="font-size: 12px; color: #d4af37; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;"><?= htmlspecialchars($cat['subtext']) ?></div>
                                    <p class="category-desc"><?= htmlspecialchars($cat['description']) ?></p>
                                    <span class="category-cta">
                                        <span>EXPLORE CATEGORY</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 7. FEATURED PRODUCTS & SPECS -->
        <section class="section-padding" style="background-color: #ffffff;" aria-label="Featured Products">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">WHOLESALE & EXPORT SELECTION</span>
                    <h2>FEATURED PRODUCTS</h2>
                    <p>Explore our most requested Harad powder, Arjuna bark cuts, and solar lights with detailed specs.</p>
                </div>
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $prod): ?>
                        <article class="product-card product-card-modern" data-id="<?= $prod['id'] ?>">
                            <div class="product-card-image">
                                <?php if (!empty($prod['badge'])): ?>
                                    <span class="product-badge"><?= htmlspecialchars($prod['badge']) ?></span>
                                <?php endif; ?>

                                <button class="btn-wishlist" aria-label="Add to Wishlist">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>

                                <img src="<?= $prod['image'] ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" decoding="async">
                            </div>
                            <div class="product-card-body">
                                <span class="product-category"><?= htmlspecialchars($prod['category']) ?></span>
                                <h3 class="product-title">
                                    <a href="<?= url('product/' . $prod['id']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
                                </h3>
                                <div class="prod-specs-pill"><?= htmlspecialchars($prod['specs']) ?></div>
                                <div class="product-rating">
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?= $prod['reviews_count'] ?>)</span>
                                </div>
                                <div class="product-price-wrapper">
                                    <span class="price-current">&#8377;<?= number_format($prod['price']) ?></span>
                                    <?php if ($prod['regular_price'] > $prod['price']): ?>
                                        <span class="price-original">&#8377;<?= number_format($prod['regular_price']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div style="display: flex; gap: 8px; margin-top: 12px;">
                                    <button class="btn-add-cart" style="flex: 1;">
                                        ADD TO CART
                                    </button>
                                    <a href="#quick-quote" class="btn-modern-outline" style="padding: 8px 14px !important; font-size: 12px !important;" title="Inquire Wholesale Price">
                                        QUOTE
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 8. INTERACTIVE WORKFLOW DISPLAY (CLEAN SPLIT DESIGN) -->
        <section class="section-padding" style="background-color: #f4f8f5; border-top: 1px solid #e2ebe4; border-bottom: 1px solid #e2ebe4;" aria-label="Global Export Process">
            <div class="container">
                <div class="section-header" style="text-align: center; max-width: 720px; margin: 0 auto 48px;">
                    <span class="eyebrow" style="color: #d4af37; letter-spacing: 0.1em; font-weight: 700;">HOW WE OPERATE</span>
                    <h2 style="font-family: 'Merriweather', serif; font-size: clamp(1.6rem, 4vw, 2.3rem); color: #1b3b2b; margin: 8px 0 12px;">Quality Assurance & Export Pipeline</h2>
                    <p style="color: #55685c; font-size: 15px; line-height: 1.6;">Click each phase to explore how Biswas Enterprise guarantees pure botanicals and seamless international delivery.</p>
                </div>

                <div class="workflow-split-layout">
                    <!-- Left: Interactive Vertical Step List -->
                    <div class="workflow-steps-nav">
                        <div class="workflow-step-item active" onclick="switchWorkflowStep(0, this)">
                            <div class="step-num-circle">01</div>
                            <div class="step-nav-text">
                                <h4>Ethical Sourcing & Harvesting</h4>
                                <span>Wild forest reserves & direct grower network</span>
                            </div>
                        </div>

                        <div class="workflow-step-item" onclick="switchWorkflowStep(1, this)">
                            <div class="step-num-circle">02</div>
                            <div class="step-nav-text">
                                <h4>Lab Quality Testing</h4>
                                <span>Moisture control & zero chemical purity tests</span>
                            </div>
                        </div>

                        <div class="workflow-step-item" onclick="switchWorkflowStep(2, this)">
                            <div class="step-num-circle">03</div>
                            <div class="step-nav-text">
                                <h4>Vacuum Sealed Packaging</h4>
                                <span>Moisture-barrier HDPE & heavy corrugated boxes</span>
                            </div>
                        </div>

                        <div class="workflow-step-item" onclick="switchWorkflowStep(3, this)">
                            <div class="step-num-circle">04</div>
                            <div class="step-nav-text">
                                <h4>Global Dispatch & Customs</h4>
                                <span>Sea port logistics & Pan-India freight</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Dynamic Showcase Card Panel -->
                    <div class="workflow-showcase-panel" id="workflowShowcase">
                        <div class="showcase-badge" id="wfBadge">PHASE 01 • HARVESTING</div>
                        <h3 id="wfTitle" style="font-family: 'Merriweather', serif; font-size: 1.5rem; color: #1b3b2b; margin: 12px 0 16px; line-height: 1.3;">
                            Ethical Botanical Sourcing
                        </h3>
                        <p id="wfDesc" style="font-size: 14.5px; color: #4a5c51; line-height: 1.75; margin-bottom: 24px;">
                            We collect 99% pure Harad, Arjuna Bark, and medicinal leaves directly from certified growers and natural forest reserves across West Bengal. Every batch includes complete origin tracking to prevent adulteration.
                        </p>
                        
                        <div class="wf-highlights-grid" id="wfHighlights">
                            <div class="wf-hl-tag">🌿 100% Traceable Batches</div>
                            <div class="wf-hl-tag">🛡️ Fair-Trade Sourcing</div>
                            <div class="wf-hl-tag">📍 West Bengal Sourcing Hubs</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Custom Styling & Dynamic Script for Interactive Workflow -->
        <style>
            .workflow-split-layout {
                display: grid;
                grid-template-columns: 1.1fr 1.3fr;
                gap: 36px;
                align-items: center;
            }
            .workflow-steps-nav {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }
            .workflow-step-item {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 16px 20px;
                border-radius: 16px;
                background: #ffffff;
                border: 1.5px solid #e1ebe4;
                cursor: pointer;
                transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .workflow-step-item:hover {
                border-color: #a8d5b5;
                transform: translateX(4px);
            }
            .workflow-step-item.active {
                background: #1b3b2b;
                border-color: #1b3b2b;
                color: #ffffff;
                box-shadow: 0 10px 24px rgba(27, 59, 43, 0.15);
            }
            .step-num-circle {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                background: #eaf3ed;
                color: #1b3b2b;
                font-family: 'Merriweather', serif;
                font-weight: 700;
                font-size: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: all 0.25s ease;
            }
            .workflow-step-item.active .step-num-circle {
                background: #d4af37;
                color: #1b3b2b;
            }
            .step-nav-text h4 {
                font-family: 'Merriweather', serif;
                font-size: 0.98rem;
                margin: 0 0 3px;
                color: #1a2721;
                transition: color 0.2s;
            }
            .workflow-step-item.active .step-nav-text h4 {
                color: #ffffff;
            }
            .step-nav-text span {
                font-size: 0.78rem;
                color: #647569;
                display: block;
            }
            .workflow-step-item.active .step-nav-text span {
                color: #c2d8ca;
            }
            .workflow-showcase-panel {
                background: #ffffff;
                border: 1.5px solid #dbe6dd;
                border-radius: 24px;
                padding: 40px 36px;
                position: relative;
                box-shadow: 0 14px 32px rgba(27, 59, 43, 0.05);
                transition: all 0.3s ease;
            }
            .showcase-badge {
                display: inline-block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: #1b3b2b;
                background: #e2efe6;
                padding: 5px 12px;
                border-radius: 20px;
            }
            .wf-highlights-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                padding-top: 16px;
                border-top: 1px solid #edf3ee;
            }
            .wf-hl-tag {
                font-size: 12.5px;
                font-weight: 600;
                color: #1b3b2b;
                background: #f0f6f2;
                padding: 6px 14px;
                border-radius: 20px;
                border: 1px solid #d4e4d8;
            }

            @media (max-width: 992px) {
                .workflow-split-layout {
                    grid-template-columns: 1fr;
                    gap: 24px;
                }
                .workflow-showcase-panel {
                    padding: 28px 22px;
                }
            }
            @media (max-width: 575px) {
                .workflow-step-item {
                    padding: 14px 16px;
                    gap: 12px;
                }
                .step-num-circle {
                    width: 36px;
                    height: 36px;
                    font-size: 0.9rem;
                }
                .step-nav-text h4 {
                    font-size: 0.92rem;
                }
                .step-nav-text span {
                    font-size: 0.74rem;
                }
                .workflow-showcase-panel {
                    padding: 22px 18px;
                    border-radius: 18px;
                }
                .wf-hl-tag {
                    font-size: 11.5px;
                    padding: 5px 12px;
                }
            }
        </style>

        <script>
            const workflowData = [
                {
                    badge: "PHASE 01 • HARVESTING",
                    title: "Ethical Botanical Sourcing",
                    desc: "We collect 99% pure Harad, Arjuna Bark, and medicinal leaves directly from certified growers and natural forest reserves across West Bengal. Every batch includes complete origin tracking to prevent adulteration.",
                    tags: ["🌿 100% Traceable Batches", "🛡️ Fair-Trade Sourcing", "📍 West Bengal Sourcing Hubs"]
                },
                {
                    badge: "PHASE 02 • TESTING & SHADE-CURING",
                    title: "Lab Verification & Active Potency Test",
                    desc: "Harvested botanicals undergo slow shade-curing under hygienic parameters to protect active bio-nutrients. Moisture content is lab-tested and standardized to ensure zero chemical additives or foreign impurities.",
                    tags: ["🔬 HPLC Tested Pure", "💧 Controlled Moisture (≤8%)", "☀️ Shade-Cured Processing"]
                },
                {
                    badge: "PHASE 03 • EXPORT PACKAGING",
                    title: "Moisture-Barrier & Sealed Packaging",
                    desc: "Products are packed in food-grade inner HDPE liners, vacuum-sealed bags, and heavy-duty 5-ply corrugated outer boxes to ensure zero spoilage during prolonged maritime or transit journeys.",
                    tags: ["📦 Vacuum-Sealed Liners", "🛡️ 5-Ply Corrugated Outer", "✈️ Food-Grade Packaging"]
                },
                {
                    badge: "PHASE 04 • LOGISTICS & SHIPPING",
                    title: "Global Dispatch & Pan-India Freight",
                    desc: "Direct shipping dispatch through Kolkata Sea Port and Netaji Subhash Chandra Bose International Airport. Complete customs clearance, bill of lading, and certificate of origin documentation provided.",
                    tags: ["🚢 Kolkata Sea Port Export", "✈️ Air Freight Express", "📄 Govt Phytosanitary Certificate"]
                }
            ];

            function switchWorkflowStep(index, el) {
                document.querySelectorAll('.workflow-step-item').forEach(item => item.classList.remove('active'));
                el.classList.add('active');

                const data = workflowData[index];
                const panel = document.getElementById('workflowShowcase');
                
                panel.style.opacity = '0.4';
                panel.style.transform = 'translateY(4px)';

                setTimeout(() => {
                    document.getElementById('wfBadge').innerText = data.badge;
                    document.getElementById('wfTitle').innerText = data.title;
                    document.getElementById('wfDesc').innerText = data.desc;

                    const tagsContainer = document.getElementById('wfHighlights');
                    tagsContainer.innerHTML = data.tags.map(t => `<div class="wf-hl-tag">${t}</div>`).join('');

                    panel.style.opacity = '1';
                    panel.style.transform = 'translateY(0)';
                }, 150);
            }
        </script>

        <!-- 9. INSTANT BULK ENQUIRY / QUOTE SECTION -->
        <section id="quick-quote" class="section-padding" style="background-color: #f8faf8;" aria-label="Bulk Enquiry Form">
            <div class="container">
                <div class="bulk-quote-section">
                    <div class="bulk-quote-grid">
                        <div>
                            <span class="eyebrow" style="color: #d4af37;">QUICK BULK ENQUIRY</span>
                            <h2 style="font-family: 'Merriweather', serif; font-size: clamp(1.5rem, 4vw, 2.2rem); margin-bottom: 16px; color: #ffffff;">Request Wholesale Best Price</h2>
                            <p style="color: #b8d4c1; line-height: 1.6; font-size: 15px; margin-bottom: 24px;">
                                Looking for bulk quantities of Harad Powder, Arjuna Bark, or Commercial Solar LED Lights? Send us your requirement and our export team will send you the best quote within 2 hours.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #d0e5d6;">
                                <div>📞 <strong>Direct Call / WhatsApp:</strong> <?= $companyDetails['phone'] ?></div>
                                <div>📧 <strong>Official Email:</strong> <?= $companyDetails['email'] ?></div>
                                <div>📍 <strong>Address:</strong> Na Kalikapur Berhampore Murshidabad, Bara Bazar, Kolkata - 742102</div>
                            </div>
                        </div>

                        <div>
                            <form id="bulk-quote-form" onsubmit="event.preventDefault(); alert('Thank you Mr/Ms! Your wholesale quote request has been sent successfully to Biswas Enterprise.');">
                                <input type="text" class="quote-form-input" placeholder="Your Full Name *" required>
                                <input type="email" class="quote-form-input" placeholder="Your Email Address *" required>
                                <input type="tel" class="quote-form-input" placeholder="Phone / WhatsApp Number *" required>
                                
                                <select class="quote-form-input" style="color: #ffffff; background: rgba(255,255,255,0.12);" required>
                                    <option value="" style="color: #000;">Select Product of Interest *</option>
                                    <option value="Harad Powder" style="color: #000;">Harad Powder (Terminalia Chebula)</option>
                                    <option value="Dried Arjuna Bark" style="color: #000;">Dried Arjuna Bark (99% Pure)</option>
                                    <option value="Neem Powder & Leaves" style="color: #000;">Dried Neem Leaves / Neem Powder</option>
                                    <option value="Reetha Soap Nuts" style="color: #000;">Natural Reetha Soap Nuts</option>
                                    <option value="Solar Energy Systems" style="color: #000;">Solar LED Street Light & PV Panels</option>
                                </select>

                                <textarea class="quote-form-input" rows="3" placeholder="Requirement Details (Quantity, Destination, etc.)"></textarea>

                                <button type="submit" class="btn-modern-primary" style="width: 100%; justify-content: center; background: #d4af37 !important; color: #1b3b2b !important;">
                                    <span>SUBMIT BULK ENQUIRY</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 9. FREQUENTLY ASKED QUESTIONS (FAQ ACCORDION) -->
        <section class="faq-section" aria-label="Frequently Asked Questions">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">HELP & BUYING GUIDE</span>
                    <h2>FREQUENTLY ASKED QUESTIONS</h2>
                    <p>Everything you need to know about our sourcing, delivery, and solar energy range.</p>
                </div>

                <div class="faq-container">
                    <div class="faq-item active">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>What processing type and purity is your Harad Powder?</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="faq-answer">
                            Our Harad (Terminalia Chebula) powder is processed using blended pulverized techniques, yielding fine 100% natural powder suitable for Ayurvedic formulations, skincare, and digestive wellness.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>How is Arjuna Bark packaged for domestic and export shipping?</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="faq-answer">
                            Our 99% pure Arjuna bark is shade-cured and packed in heavy-duty plastic bags or high-grade jute bags to preserve phytochemical integrity during shipping.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I order commercial solar street lights and PV panels in bulk?</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="faq-answer">
                            Yes! Biswas Enterprise supplies commercial integrated solar LED street lights, solar lithium/lead batteries, and PV panels with full technical specification sheets.
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Swiper 11 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        function toggleFaq(element) {
            const item = element.parentElement;
            item.classList.toggle('active');
        }

        function copyGstNumber(gstNo) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(gstNo).then(() => {
                    alert('GST Number ' + gstNo + ' copied to clipboard!');
                }).catch(() => {
                    alert('GST Number: ' + gstNo);
                });
            } else {
                alert('GST Number: ' + gstNo);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper !== 'undefined') {
                if (document.querySelector('.categoriesSwiper')) {
                    new Swiper('.categoriesSwiper', {
                        slidesPerView: 1,
                        spaceBetween: 20,
                        autoplay: {
                            delay: 4000,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true
                        },
                        loop: true,
                        navigation: {
                            nextEl: '.category-swiper-next',
                            prevEl: '.category-swiper-prev',
                        },
                        pagination: {
                            el: '.categoriesSwiper .swiper-pagination',
                            clickable: true,
                        },
                        breakpoints: {
                            640: { slidesPerView: 2, spaceBetween: 20 },
                            992: { slidesPerView: 3, spaceBetween: 24 },
                            1200: { slidesPerView: 4, spaceBetween: 24 },
                        },
                    });
                }

                if (document.querySelector('.featuredProductsSwiper')) {
                    new Swiper('.featuredProductsSwiper', {
                        slidesPerView: 1,
                        spaceBetween: 20,
                        autoplay: {
                            delay: 3500,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true
                        },
                        loop: true,
                        navigation: {
                            nextEl: '.featured-swiper-next',
                            prevEl: '.featured-swiper-prev',
                        },
                        pagination: {
                            el: '.featuredProductsSwiper .swiper-pagination',
                            clickable: true,
                        },
                        breakpoints: {
                            640: { slidesPerView: 2, spaceBetween: 20 },
                            992: { slidesPerView: 3, spaceBetween: 24 },
                            1200: { slidesPerView: 4, spaceBetween: 24 },
                        },
                    });
                }
            }
        });
    </script>
    <?php include __DIR__ . '/includes/floating_enquiry.php'; ?>

    <!-- Mobile Drawer Init -->
    <script type="module">
        import { initHeader } from '<?= asset('js/components/header.js') ?>';
        document.addEventListener('DOMContentLoaded', () => {
            initHeader();
        });
    </script>
</body>
</html>
