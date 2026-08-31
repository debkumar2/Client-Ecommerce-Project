<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Helper function to return detailed specifications for each product
function getProductSpecs(array $p): array {
    $cat = strtolower($p['category'] ?? '');

    if (str_contains($cat, 'arjuna') || str_contains($cat, 'bark')) {
        return [
            'Packaging Type' => 'Plastic Bags, Jute Bags',
            'Texture' => 'Rough Bark Pieces',
            'Usage' => 'Heart Remedy, Heart Health Support',
            'Benefit' => 'Heart Health, Blood Pressure Regulation',
            'Storage' => 'Cool, Dry Place',
            'Shelf Life' => '1-2 Years',
            'Processing Method' => 'Sun-dried Natural Cut'
        ];
    } elseif (str_contains($cat, 'powder')) {
        return [
            'Packaging Type' => 'Plastic Pouch, Plastic Bags',
            'Texture' => 'Fine Micro-Powder',
            'Usage' => 'Skincare, Haircare, Ayurvedic Treatments',
            'Benefit' => 'Antibacterial, Antifungal, Antioxidant',
            'Storage' => 'Cool, Dry Place',
            'Shelf Life' => '2 Years',
            'Processing Method' => 'Sun-dried Pulverization'
        ];
    } elseif (str_contains($cat, 'dried')) {
        return [
            'Packaging Type' => 'Plastic Bags, Eco Zip-lock Pouch',
            'Texture' => 'Natural Whole Dried Leaves / Shells',
            'Usage' => 'Ayurvedic, Wellness, Haircare',
            'Benefit' => 'Pure Natural Immunity & Herbal Care',
            'Storage' => 'Cool, Dry Place',
            'Shelf Life' => '6-12 Months',
            'Processing Method' => 'Natural Sun-dried'
        ];
    } elseif (str_contains($cat, 'renewable') || str_contains($cat, 'energy')) {
        return [
            'Packaging Type' => 'Heavy-duty Corrugated Box',
            'Texture' => 'Metal, Glass & Silicon Composite',
            'Usage' => 'Residential, Commercial, Industrial Power',
            'Benefit' => '100% Eco Energy, Zero Emission',
            'Storage' => 'Dry Covered Area',
            'Shelf Life' => '5-10 Years System Life',
            'Processing Method' => 'Solar Assembly / Polycrystalline Technology'
        ];
    }

    return [
        'Packaging Type' => 'Plastic Bags, Jute Bags',
        'Texture' => 'Rough',
        'Usage' => 'Herbal Medicine, Ayurvedic Remedies',
        'Benefit' => 'Heart Health, Blood Pressure Regulation',
        'Storage' => 'Cool, Dry Place',
        'Shelf Life' => '1-2 Years',
        'Processing Method' => 'Sun-dried'
    ];
}

// Biswas Enterprise Official Product Categories (sourced from biswas-enterprise.co.in)
$categories = [
    ['id' => 1, 'name' => 'Arjuna Bark', 'slug' => 'arjuna-bark', 'count' => 3],
    ['id' => 2, 'name' => 'Dried Herbs', 'slug' => 'dried-herbs', 'count' => 3],
    ['id' => 3, 'name' => 'Herbs Powder', 'slug' => 'herbs-powder', 'count' => 4],
    ['id' => 4, 'name' => 'Renewable Energy Products', 'slug' => 'renewable-energy', 'count' => 4],
];

$brands = [
    ['id' => 1, 'name' => 'Biswas Organics', 'slug' => 'biswas-organics', 'count' => 8],
    ['id' => 2, 'name' => 'Heritage Botanicals', 'slug' => 'heritage-botanicals', 'count' => 3],
    ['id' => 3, 'name' => 'Pure Herbs Co.', 'slug' => 'pure-herbs', 'count' => 3],
    ['id' => 4, 'name' => 'Biswas Eco Tech', 'slug' => 'biswas-eco-tech', 'count' => 4],
];

$products = [
    [
        'id' => 101,
        'name' => 'Dried Arjuna Bark',
        'category' => 'Arjuna Bark',
        'category_slug' => 'arjuna-bark',
        'brand' => 'Biswas Organics',
        'price' => 710,
        'regular_price' => 775,
        'rating' => 5,
        'reviews_count' => 32,
        'stock_quantity' => 50,
        'stock_status' => 'in-stock',
        'badge' => 'BEST SELLER',
        'badge_type' => 'sale',
        'description' => 'Pure 99% high-purity Dried Arjuna Bark sourced directly from West Bengal for traditional heart remedies and cardio support.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 102,
        'name' => 'High Quality Arjuna Bark',
        'category' => 'Arjuna Bark',
        'category_slug' => 'arjuna-bark',
        'brand' => 'Biswas Organics',
        'price' => 750,
        'regular_price' => 820,
        'rating' => 5,
        'reviews_count' => 24,
        'stock_quantity' => 35,
        'stock_status' => 'in-stock',
        'badge' => 'POPULAR',
        'badge_type' => 'sale',
        'description' => 'Selected thick cut medicinal-grade Arjuna bark rich in tannins and flavonoids for natural cardiovascular health.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1563865436874-9aef32095fad?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 103,
        'name' => 'Premium Quality Arjuna Bark',
        'category' => 'Arjuna Bark',
        'category_slug' => 'arjuna-bark',
        'brand' => 'Biswas Organics',
        'price' => 790,
        'regular_price' => 890,
        'rating' => 5,
        'reviews_count' => 18,
        'stock_quantity' => 20,
        'stock_status' => 'in-stock',
        'badge' => 'PREMIUM',
        'badge_type' => 'sale',
        'description' => 'Export-quality sun-dried Terminalia Arjuna tree bark strips carefully cleaned and sorted for pharmaceutical and herbal use.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 104,
        'name' => 'Harad Powder',
        'category' => 'Herbs Powder',
        'category_slug' => 'herbs-powder',
        'brand' => 'Biswas Organics',
        'price' => 350,
        'regular_price' => 420,
        'rating' => 5,
        'reviews_count' => 41,
        'stock_quantity' => 40,
        'stock_status' => 'in-stock',
        'badge' => 'BEST SELLER',
        'badge_type' => 'sale',
        'description' => 'Pure blended Harad (Haritaki) powder from Kolkata. Promotes digestive wellness, detoxification, and natural rejuvenation.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 105,
        'name' => 'Neem Powder',
        'category' => 'Herbs Powder',
        'category_slug' => 'herbs-powder',
        'brand' => 'Heritage Botanicals',
        'price' => 299,
        'regular_price' => 380,
        'rating' => 5,
        'reviews_count' => 29,
        'stock_quantity' => 25,
        'stock_status' => 'in-stock',
        'badge' => 'POPULAR',
        'badge_type' => 'sale',
        'description' => 'Fine micro-powdered organic Neem leaves. Antibacterial, antifungal & antioxidant for skincare, haircare, and Ayurvedic remedies.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 106,
        'name' => 'Ashwagandha Root Powder',
        'category' => 'Herbs Powder',
        'category_slug' => 'herbs-powder',
        'brand' => 'Biswas Organics',
        'price' => 599,
        'regular_price' => 749,
        'rating' => 5,
        'reviews_count' => 28,
        'stock_quantity' => 15,
        'stock_status' => 'in-stock',
        'badge' => 'BEST SELLER',
        'badge_type' => 'sale',
        'description' => 'Pure premium Ashwagandha (Withania Somnifera) root powder, traditional revitalizing herb for energy, stamina, and stress management.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 107,
        'name' => 'Organic Triphala Powder',
        'category' => 'Herbs Powder',
        'category_slug' => 'herbs-powder',
        'brand' => 'Biswas Organics',
        'price' => 349,
        'regular_price' => 429,
        'rating' => 5,
        'reviews_count' => 38,
        'stock_quantity' => 25,
        'stock_status' => 'in-stock',
        'badge' => 'POPULAR',
        'badge_type' => 'sale',
        'description' => 'Balanced classic formulation of Amla, Haritaki, and Bibhitaki for gentle gut cleansing and daily digestive harmony.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 108,
        'name' => 'Natural Reetha Soap Nuts',
        'category' => 'Dried Herbs',
        'category_slug' => 'dried-herbs',
        'brand' => 'Pure Herbs Co.',
        'price' => 280,
        'regular_price' => 340,
        'rating' => 4,
        'reviews_count' => 22,
        'stock_quantity' => 30,
        'stock_status' => 'in-stock',
        'badge' => 'ECO CHOICE',
        'badge_type' => 'sale',
        'description' => '90% pure medicine grade Reetha (Soapnut) shells. 100% natural chemical-free organic cleanser for hair washing and delicate fabrics.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1512290900673-7002ddb97b09?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 109,
        'name' => 'Dried Tulsi Leaves',
        'category' => 'Dried Herbs',
        'category_slug' => 'dried-herbs',
        'brand' => 'Pure Herbs Co.',
        'price' => 299,
        'regular_price' => 349,
        'rating' => 4,
        'reviews_count' => 19,
        'stock_quantity' => 12,
        'stock_status' => 'in-stock',
        'badge' => 'FRESH HARVEST',
        'badge_type' => 'sale',
        'description' => 'Handpicked shade-dried sacred Rama & Krishna Tulsi leaves for natural immunity teas and respiratory wellness.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 110,
        'name' => 'Dried Neem Leaves',
        'category' => 'Dried Herbs',
        'category_slug' => 'dried-herbs',
        'brand' => 'Heritage Botanicals',
        'price' => 249,
        'regular_price' => 299,
        'rating' => 5,
        'reviews_count' => 31,
        'stock_quantity' => 18,
        'stock_status' => 'in-stock',
        'badge' => 'POPULAR',
        'badge_type' => 'sale',
        'description' => '99% pure sun-dried green Neem leaves. Essential for therapeutic herbal baths, skin detox, and botanical infusions.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 111,
        'name' => 'Solar LED Street Light',
        'category' => 'Renewable Energy Products',
        'category_slug' => 'renewable-energy',
        'brand' => 'Biswas Eco Tech',
        'price' => 3499,
        'regular_price' => 4200,
        'rating' => 5,
        'reviews_count' => 42,
        'stock_quantity' => 15,
        'stock_status' => 'in-stock',
        'badge' => 'BEST SELLER',
        'badge_type' => 'sale',
        'description' => 'Integrated aluminum & polycrystalline silicon solar LED street light. High lumen output, dusk-to-dawn sensor, and weather resistance.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1545259741-2ea3ebf61fa3?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 112,
        'name' => 'Solar Power Battery',
        'category' => 'Renewable Energy Products',
        'category_slug' => 'renewable-energy',
        'brand' => 'Biswas Eco Tech',
        'price' => 5899,
        'regular_price' => 6999,
        'rating' => 5,
        'reviews_count' => 27,
        'stock_quantity' => 8,
        'stock_status' => 'in-stock',
        'badge' => 'HIGH CYCLE',
        'badge_type' => 'sale',
        'description' => 'Heavy-duty Lithium-ion & deep-cycle lead-acid solar storage battery (12V/24V/48V) for reliable off-grid power storage.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 113,
        'name' => 'Solar PV Panel',
        'category' => 'Renewable Energy Products',
        'category_slug' => 'renewable-energy',
        'brand' => 'Biswas Eco Tech',
        'price' => 4200,
        'regular_price' => 4999,
        'rating' => 5,
        'reviews_count' => 35,
        'stock_quantity' => 20,
        'stock_status' => 'in-stock',
        'badge' => 'ECO POWER',
        'badge_type' => 'sale',
        'description' => 'High efficiency silicon & tempered glass solar photovoltaic panel for residential, commercial & industrial power generation.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 114,
        'name' => 'Solar Emergency LED Lantern',
        'category' => 'Renewable Energy Products',
        'category_slug' => 'renewable-energy',
        'brand' => 'Biswas Eco Tech',
        'price' => 1299,
        'regular_price' => 1599,
        'rating' => 5,
        'reviews_count' => 19,
        'stock_quantity' => 18,
        'stock_status' => 'in-stock',
        'badge' => 'PORTABLE',
        'badge_type' => 'sale',
        'description' => 'High-efficiency solar rechargeable lantern with dual USB emergency phone charging and 12-hour illumination.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Natural & Herbal Products | Biswas Enterprise</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Browse authentic Indian natural herbs, Arjuna bark, dried herbs, herbs powder, and renewable energy solutions at Biswas Enterprise. Quality guaranteed.">
    <link rel="canonical" href="<?= url('shop') ?>">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="Shop Natural & Herbal Products | Biswas Enterprise">
    <meta property="og:description" content="Discover authentic natural herbs, Arjuna bark, pure powders, and clean energy products.">
    <meta property="og:image" content="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1200&q=80', ['width' => 1200, 'height' => 630]) ?>">
    <meta property="og:url" content="<?= url('shop') ?>">
    <meta property="og:type" content="website">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>

    <!-- Announcement Bar -->
    <div class="announcement-bar">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    100% Authentic Natural Sourcing & Clean Energy Solutions
                </span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">Free Express Shipping across India on orders above ₹999</span>
            </div>
        </div>
    </div>

    <!-- Header Navigation -->
    <header class="site-header" role="banner">
        <div class="container">
            <div class="header-inner">
                
                <!-- Mobile Menu Toggle Button -->
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

                <!-- Brand Logo -->
                <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                    <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="main-navigation" role="navigation" aria-label="Main menu">
                    <ul>
                        <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link active">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <!-- Header Actions (Icons) -->
                <div class="header-actions">
                    <a href="<?= url('wishlist') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
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

    <!-- 2. Shop Hero -->
    <section class="shop-hero">
        <div class="container">
            <div class="shop-hero-content">
                <span class="shop-hero-eyebrow">Curated Botanical Catalog</span>
                <h1 class="shop-hero-title">Explore Our Collection</h1>
                <p class="shop-hero-description">Browse authentic natural herbs, finely ground powders, and traditional wellness formulations carefully sourced for purity.</p>
            </div>
        </div>
    </section>

    <!-- Main Shop Section -->
    <section class="shop-page-section">
        <div class="container">
            
            <!-- 3. Shop Toolbar -->
            <div class="shop-toolbar">
                <div class="toolbar-left">
                    <button id="openMobileFilter" class="btn-mobile-filter" aria-label="Filter Products">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        <span>Filter</span>
                    </button>

                    <?php 
                    $requestedCatSlug = strtolower($_GET['category'] ?? '');
                    $filteredInitialCount = 0;
                    if (!empty($requestedCatSlug)) {
                        foreach ($products as $p) {
                            $pCatLower = strtolower($p['category']);
                            $pCatSlugLower = strtolower($p['category_slug'] ?? str_replace(' ', '-', $pCatLower));
                            $reqFormatted = str_replace('-', ' ', $requestedCatSlug);
                            if ($requestedCatSlug === $pCatSlugLower || $requestedCatSlug === $pCatLower || $reqFormatted === $pCatLower || str_contains($pCatLower, $reqFormatted) || str_contains($pCatSlugLower, $requestedCatSlug)) {
                                $filteredInitialCount++;
                            }
                        }
                    } else {
                        $filteredInitialCount = count($products);
                    }
                    ?>
                    <div class="results-count">
                        Showing <strong id="showingCount"><?= $filteredInitialCount ?></strong> of <strong id="totalCount"><?= count($products) ?></strong> products
                    </div>
                </div>

                <!-- 4. Search Component -->
                <div class="toolbar-search">
                    <div class="toolbar-search-input-wrapper">
                        <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="shopSearchInput" class="toolbar-search-input" placeholder="Search products, herbs, powders..." aria-label="Search products">
                        <button type="button" id="shopSearchClear" class="toolbar-search-clear" aria-label="Clear search">&times;</button>
                        <div id="shopSearchSpinner" class="toolbar-search-spinner"></div>
                    </div>
                </div>

                <!-- Sort & View Switcher -->
                <div class="toolbar-right">
                    <div class="sort-wrapper">
                        <label for="shopSortSelect" class="sort-label">Sort By:</label>
                        <div class="sort-select-wrapper">
                            <select id="shopSortSelect" class="sort-select" aria-label="Sort products">
                                <option value="featured">Featured</option>
                                <option value="newest">Newest Arrivals</option>
                                <option value="price-low">Price: Low &rarr; High</option>
                                <option value="price-high">Price: High &rarr; Low</option>
                                <option value="rating">Highest Rated</option>
                            </select>
                            <svg class="sort-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>

                    <div class="view-mode-toggle">
                        <button id="viewGridBtn" class="view-btn active" title="Grid View" aria-label="Grid View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </button>
                        <button id="viewListBtn" class="view-btn" title="List View" aria-label="List View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="shop-layout">

                <!-- 5. Desktop Filter Sidebar -->
                <aside class="filter-sidebar desktop-only">
                    <div class="filter-sidebar-header">
                        <h2 class="filter-sidebar-title">Filters</h2>
                        <button class="btn-clear-filters">Clear All</button>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">Categories</h3>
                        <div class="filter-list">
                            <?php 
                            $requestedCategory = strtolower($_GET['category'] ?? '');
                            foreach ($categories as $cat): 
                                $isCatChecked = false;
                                if (!empty($requestedCategory)) {
                                    $catNameLower = strtolower($cat['name']);
                                    $catSlugLower = strtolower($cat['slug']);
                                    $reqFormatted = str_replace('-', ' ', $requestedCategory);
                                    if ($requestedCategory === $catSlugLower || $requestedCategory === $catNameLower || $reqFormatted === $catNameLower || str_contains($catNameLower, $reqFormatted) || str_contains($requestedCategory, $catSlugLower)) {
                                        $isCatChecked = true;
                                    }
                                }
                            ?>
                            <label class="filter-item">
                                <input type="checkbox" class="filter-category-input" value="<?= htmlspecialchars($cat['name']) ?>" data-slug="<?= htmlspecialchars($cat['slug']) ?>" <?= $isCatChecked ? 'checked' : '' ?>>
                                <span class="filter-checkbox-label">
                                    <span class="custom-checkbox"></span>
                                    <span class="filter-item-name"><?= htmlspecialchars($cat['name']) ?></span>
                                </span>
                                <span class="filter-count"><?= $cat['count'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Range Slider -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">Price Range</h3>
                        <div class="price-slider-wrapper">
                            <div class="price-inputs">
                                <div class="price-input-box">
                                    <span>₹</span>
                                    <input type="number" value="0" min="0" max="7000" readonly>
                                </div>
                                <span style="color:var(--muted)">to</span>
                                <div class="price-input-box">
                                    <span>₹</span>
                                    <input type="number" class="price-max-input" value="7000" min="100" max="7000">
                                </div>
                            </div>
                            <input type="range" class="price-range-slider" min="100" max="7000" step="100" value="7000" aria-label="Max Price Filter">
                        </div>
                    </div>

                    <!-- Availability Filter -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">Availability</h3>
                        <div class="filter-list">
                            <label class="filter-item">
                                <input type="checkbox" id="filterInStock" value="in-stock">
                                <span class="filter-checkbox-label">
                                    <span class="custom-checkbox"></span>
                                    <span class="filter-item-name">In Stock Only</span>
                                </span>
                            </label>
                            <label class="filter-item">
                                <input type="checkbox" id="filterOutOfStock" value="out-of-stock">
                                <span class="filter-checkbox-label">
                                    <span class="custom-checkbox"></span>
                                    <span class="filter-item-name">Include Out of Stock</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">Rating</h3>
                        <div class="filter-list rating-stars-list">
                            <label class="filter-item">
                                <input type="radio" name="rating-filter" value="5">
                                <span class="filter-checkbox-label">
                                    <span class="custom-radio"></span>
                                    <span class="rating-stars-item">
                                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <span class="filter-item-name">5 Stars Only</span>
                                    </span>
                                </span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="rating-filter" value="4">
                                <span class="filter-checkbox-label">
                                    <span class="custom-radio"></span>
                                    <span class="rating-stars-item">
                                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <span class="filter-item-name">4 Stars &amp; Above</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">Brand / Line</h3>
                        <div class="filter-list">
                            <?php foreach ($brands as $brand): ?>
                            <label class="filter-item">
                                <input type="checkbox" class="filter-brand-input" value="<?= htmlspecialchars($brand['name']) ?>">
                                <span class="filter-checkbox-label">
                                    <span class="custom-checkbox"></span>
                                    <span class="filter-item-name"><?= htmlspecialchars($brand['name']) ?></span>
                                </span>
                                <span class="filter-count"><?= $brand['count'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>

                <!-- 6. Mobile Filter Drawer -->
                <div id="mobileFilterDrawer" class="mobile-filter-drawer" aria-hidden="true">
                    <div class="drawer-filter-header">
                        <h3>Filter Products</h3>
                        <button id="closeMobileFilter" class="drawer-close-btn" aria-label="Close Filter">&times;</button>
                    </div>
                    <div class="drawer-filter-body">
                        <!-- Same Filter Groups inside Drawer -->
                        <div class="filter-group">
                            <h4 class="filter-group-title">Categories</h4>
                            <div class="filter-list">
                                <?php foreach ($categories as $cat): ?>
                                <label class="filter-item">
                                    <input type="checkbox" class="filter-category-input" value="<?= htmlspecialchars($cat['name']) ?>">
                                    <span class="filter-checkbox-label">
                                        <span class="custom-checkbox"></span>
                                        <span class="filter-item-name"><?= htmlspecialchars($cat['name']) ?></span>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-group-title">Price Range</h4>
                            <div class="price-slider-wrapper">
                                <div class="price-inputs">
                                    <div class="price-input-box">
                                        <span>₹</span>
                                        <span class="price-max-val">₹7000</span>
                                    </div>
                                </div>
                                <input type="range" class="price-range-slider" min="100" max="7000" step="100" value="7000">
                            </div>
                        </div>
                    </div>
                    <div class="drawer-filter-footer">
                        <button class="btn btn-outline btn-clear-filters">Clear All</button>
                        <button id="applyMobileFilter" class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>

                <!-- Product Listing Main Column -->
                <div class="shop-products-column">
                            <!-- 7 & 8. Product Grid & Cards -->
                    <div id="productsGrid" class="products-grid">
                        <?php 
                        $requestedCatSlug = strtolower($_GET['category'] ?? '');
                        foreach ($products as $p): 
                            $isInitialHide = false;
                            if (!empty($requestedCatSlug)) {
                                $pCatLower = strtolower($p['category']);
                                $pCatSlugLower = strtolower($p['category_slug'] ?? str_replace(' ', '-', $pCatLower));
                                $reqFormatted = str_replace('-', ' ', $requestedCatSlug);
                                if ($requestedCatSlug !== $pCatSlugLower && $requestedCatSlug !== $pCatLower && $reqFormatted !== $pCatLower && !str_contains($pCatLower, $reqFormatted) && !str_contains($pCatSlugLower, $requestedCatSlug)) {
                                    $isInitialHide = true;
                                }
                            }
                        ?>
                        <div class="product-card <?= $p['stock_status'] === 'out-of-stock' ? 'is-out-of-stock' : '' ?>"
                             style="<?= $isInitialHide ? 'display: none !important;' : '' ?>"
                             data-id="<?= $p['id'] ?>"
                             data-title="<?= htmlspecialchars($p['name']) ?>"
                             data-category="<?= htmlspecialchars($p['category']) ?>"
                             data-brand="<?= htmlspecialchars($p['brand']) ?>"
                             data-price="₹<?= number_format($p['price']) ?>"
                             data-raw-price="<?= $p['price'] ?>"
                             data-original-price="₹<?= number_format($p['regular_price']) ?>"
                             data-rating="<?= $p['rating'] ?>"
                             data-description="<?= htmlspecialchars($p['description']) ?>"
                             data-image="<?= htmlspecialchars($p['image']) ?>"
                             data-stock="<?= $p['stock_status'] === 'out-of-stock' ? 'Out of Stock' : ($p['stock_status'] === 'low-stock' ? 'Only ' . $p['stock_quantity'] . ' Left' : 'In Stock') ?>"
                             data-specs="<?= htmlspecialchars(json_encode(getProductSpecs($p)), ENT_QUOTES, 'UTF-8') ?>">
                            
                            <div class="product-card-image">
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                                
                                <span class="product-badge <?= $p['badge_type'] ?>">
                                    <?= htmlspecialchars($p['badge']) ?>
                                </span>

                                <button class="btn-wishlist" title="Add to Wishlist" aria-label="Add to Wishlist">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>

                                <button class="btn-quick-view">Quick View</button>
                            </div>

                            <div class="product-card-body">
                                <span class="product-category"><?= htmlspecialchars($p['category']) ?></span>
                                
                                <h3 class="product-title">
                                    <a href="<?= url('product/' . $p['id']) ?>"><?= htmlspecialchars($p['name']) ?></a>
                                </h3>

                                <div class="product-rating">
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <svg viewBox="0 0 24 24" fill="<?= $i < $p['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?= $p['reviews_count'] ?>)</span>
                                </div>

                                <div class="product-price-wrapper">
                                    <span class="price-current">₹<?= number_format($p['price']) ?></span>
                                    <span class="price-original">₹<?= number_format($p['regular_price']) ?></span>
                                    <?php 
                                    $discount = round((($p['regular_price'] - $p['price']) / $p['regular_price']) * 100);
                                    if ($discount > 0): 
                                    ?>
                                        <span class="price-discount"><?= $discount ?>% OFF</span>
                                    <?php endif; ?>
                                </div>

                                <!-- 9. Product Purchase States -->
                                <?php if ($p['stock_status'] === 'out-of-stock'): ?>
                                    <button class="btn btn-add-cart" disabled>OUT OF STOCK</button>
                                <?php elseif ($p['stock_status'] === 'low-stock'): ?>
                                    <button class="btn btn-add-cart">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
                                        <span>Add to Cart</span>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-add-cart">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
                                        <span>Add to Cart</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- 12. Empty State Placeholder -->
                    <div id="emptyState" class="empty-state" style="display: none;">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                        </div>
                        <h3 class="empty-state-title">No products found</h3>
                        <p class="empty-state-desc">We couldn't find any products matching your selected search or filter criteria. Try clearing some filters.</p>
                        <button class="btn btn-primary btn-clear-filters">Reset Filters</button>
                    </div>

                    <!-- 13. Pagination Component -->
                    <div class="pagination-wrapper">
                        <nav class="pagination" aria-label="Page navigation">
                            <a href="#" class="page-link prev-next disabled" aria-disabled="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                <span>Previous</span>
                            </a>
                            <a href="#" class="page-link active" aria-current="page">1</a>
                            <a href="#" class="page-link">2</a>
                            <a href="#" class="page-link">3</a>
                            <a href="#" class="page-link prev-next">
                                <span>Next</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- 11. Quick View Modal Component -->
    <div id="quickViewModal" class="modal-backdrop" aria-hidden="true">
        <div class="quick-view-modal">
            <button id="closeQuickView" class="modal-close-btn" aria-label="Close Quick View">&times;</button>
            <div class="quick-view-grid">
                <div class="quick-view-image-wrapper">
                    <img id="quickViewImg" src="" alt="Product Quick View">
                </div>
                <div class="quick-view-details">
                    <span id="quickViewCategory" class="quick-view-category">HERBAL</span>
                    <h2 id="quickViewTitle" class="quick-view-title">Product Title</h2>
                    <div class="quick-view-price">
                        <span id="quickViewPrice">₹0</span>
                        <span id="quickViewOrigPrice" class="quick-view-price-original">₹0</span>
                    </div>
                    <p id="quickViewDesc" class="quick-view-description">Short description goes here...</p>
                    <div class="quick-view-meta">
                        <div><strong>Availability:</strong> <span id="quickViewStock" style="color:var(--primary)">In Stock</span></div>
                        <div><strong>Guaranteed:</strong> 100% Authentic Biswas Enterprise Quality</div>
                    </div>
                    <div class="quick-view-actions">
                        <button class="btn btn-primary btn-add-cart" style="flex:1;">Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Specifications Table Card -->
            <div class="product-details-specs-card">
                <h3 class="specs-title">Product Details</h3>
                <div id="quickViewSpecsGrid" class="specs-grid">
                    <!-- Key-Value spec pairs will be populated dynamically -->
                </div>
                <div class="specs-interested-wrapper">
                    <button type="button" class="btn-interested" id="btnInterested">Yes! I am interested</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 12. Quick Quote / Enquiry Modal Component -->
    <div id="enquiryModal" class="modal-backdrop" aria-hidden="true">
        <div class="enquiry-modal-card">
            <button id="closeEnquiryModal" class="modal-close-btn" aria-label="Close Enquiry Modal">&times;</button>
            <div class="enquiry-modal-grid">
                <!-- Left Column: Product Showcase -->
                <div class="enquiry-product-side">
                    <div class="enquiry-product-header">
                        <h3 id="enquiryProductTitle">Product Title</h3>
                    </div>
                    <div class="enquiry-image-container">
                        <img id="enquiryProductImg" src="" alt="Enquiry Product">
                    </div>
                    <div class="enquiry-price-info">
                        <p class="enquiry-price-line"><strong>Price :</strong> <span id="enquiryPriceDisplay">₹ 710.00 - 775.00 / Kilogram</span></p>
                        <p class="enquiry-moq-line"><strong>MOQ :</strong> <span id="enquiryMoqDisplay">50 Kilogram</span></p>
                    </div>
                </div>

                <!-- Right Column: Get a Quick Quote Form -->
                <div class="enquiry-form-side">
                    <div class="enquiry-form-banner">
                        <h2>Get a Quick Quote</h2>
                    </div>
                    <form id="quickQuoteForm" class="enquiry-form-body">
                        <div class="form-row-dual">
                            <div class="form-group">
                                <label for="enquiryQty">Quantity</label>
                                <input type="number" id="enquiryQty" name="quantity" placeholder="Quantity" min="1" value="50" required>
                            </div>
                            <div class="form-group">
                                <label for="enquiryUnit">Measurement Units</label>
                                <div class="unit-input-wrapper">
                                    <input type="text" id="enquiryUnit" name="unit" value="Kilogram" required>
                                    <button type="button" class="btn-unit-edit" id="btnEditUnit">Edit</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group margin-top-md">
                            <label for="enquiryMobile">Mobile No.</label>
                            <div class="phone-input-group">
                                <div class="country-code-box">
                                    <span class="flag-icon">🇮🇳</span>
                                    <span class="code">+91</span>
                                </div>
                                <input type="tel" id="enquiryMobile" name="mobile" placeholder="Enter Mobile No." pattern="[0-9]{10}" maxlength="10" required>
                            </div>
                        </div>

                        <div class="form-group submit-group">
                            <button type="submit" class="btn btn-send-enquiry">Send Enquiry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 14. Newsletter CTA -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-card">
                <div class="newsletter-content">
                    <span class="eyebrow-tag">STAY INFORMED</span>
                    <h2 class="section-title">Subscribe for Natural Wellness Tips</h2>
                    <p class="section-desc">Get early notification on new harvest arrivals, herbal guides, and exclusive wellness offers.</p>
                    
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing to Biswas Enterprise!');">
                        <input type="email" placeholder="Enter your email address" required aria-label="Email address">
                        <button type="submit" class="btn btn-secondary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <a href="<?= url() ?>" class="site-logo">
                            <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise Logo" class="brand-logo-img">
                        </a>
                        <p class="footer-desc">Biswas Enterprise is dedicated to delivering genuine natural herbs, pure botanical powders, and holistic wellness products.</p>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                            <a href="#" class="social-link" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                            <a href="#" class="social-link" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                        </div>
                    </div>

                    <div class="footer-column">
                        <h4 class="footer-heading">Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url() ?>">Home</a></li>
                            <li><a href="<?= url('shop') ?>">Shop All Products</a></li>
                            <li><a href="<?= url('#categories') ?>">Product Categories</a></li>
                            <li><a href="<?= url('#about') ?>">About Us</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h4 class="footer-heading">Categories</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('shop') ?>">Herbal Products</a></li>
                            <li><a href="<?= url('shop') ?>">Dried Herbs</a></li>
                            <li><a href="<?= url('shop') ?>">Herbal Powders</a></li>
                            <li><a href="<?= url('shop') ?>">Wellness Items</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h4 class="footer-heading">Customer Care</h4>
                        <ul class="footer-links">
                            <li><a href="#">Shipping &amp; Delivery</a></li>
                            <li><a href="#">Returns &amp; Refunds</a></li>
                            <li><a href="#">Quality Sourcing Guarantee</a></li>
                            <li><a href="#">Contact Support</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h4 class="footer-heading">Contact Us</h4>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>West Bengal, India</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span>info@biswasenterprise.com</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <span>+91 98765 43210</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved. Crafted for Natural Living.</p>
            </div>
        </div>
    </footer>

    <!-- Modular Page JavaScript -->
    <script type="module" src="<?= asset('js/pages/shop.js') ?>"></script>
    <?php include __DIR__ . '/includes/floating_enquiry.php'; ?>
</body>
</html>
