<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Development Data Structures - Prepared for future database queries
$categories = [
    [
        'id' => 1,
        'name' => 'Herbal Products',
        'slug' => 'herbal-products',
        'description' => 'Pure herbal formulations for daily wellness and vitality.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 450])
    ],
    [
        'id' => 2,
        'name' => 'Dried Herbs',
        'slug' => 'dried-herbs',
        'description' => 'Carefully selected and dried raw natural herbs.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 450])
    ],
    [
        'id' => 3,
        'name' => 'Herbal Powders',
        'slug' => 'herbal-powders',
        'description' => 'Finely ground single-herb powders of high purity.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 450])
    ],
    [
        'id' => 4,
        'name' => 'Wellness Products',
        'slug' => 'wellness-products',
        'description' => 'Natural solutions for personal body and health care.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 450])
    ]
];

$featuredProducts = [
    [
        'id' => 101,
        'name' => 'Ashwagandha Root Powder',
        'category' => 'Herbal Powders',
        'price' => 599,
        'regular_price' => 749,
        'rating' => 5,
        'reviews_count' => 28,
        'stock_quantity' => 15,
        'badge' => 'BEST SELLER',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 102,
        'name' => 'Organic Neem Leaf Extract',
        'category' => 'Herbal Products',
        'price' => 399,
        'regular_price' => 499,
        'rating' => 5,
        'reviews_count' => 14,
        'stock_quantity' => 8,
        'badge' => 'POPULAR',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 103,
        'name' => 'Dried Tulsi Leaves',
        'category' => 'Dried Herbs',
        'price' => 299,
        'regular_price' => 349,
        'rating' => 4,
        'reviews_count' => 19,
        'stock_quantity' => 0, // OUT OF STOCK EXAMPLE
        'badge' => 'OUT OF STOCK',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ],
    [
        'id' => 104,
        'name' => 'Amla Herbal Vitality Oil',
        'category' => 'Wellness Products',
        'price' => 699,
        'regular_price' => 899,
        'rating' => 5,
        'reviews_count' => 32,
        'stock_quantity' => 22,
        'badge' => 'NEW',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
    ]
];

$blogArticles = [
    [
        'id' => 1,
        'title' => 'Understanding Herbal Ingredients & Sourcing Purity',
        'category' => 'EDUCATION',
        'date' => 'Aug 24, 2026',
        'excerpt' => 'Discover how authentic sourcing and traditional processing preserve active botanical properties in natural products.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 400])
    ],
    [
        'id' => 2,
        'title' => 'How to Choose Quality Natural Products for Daily Use',
        'category' => 'GUIDE',
        'date' => 'Aug 18, 2026',
        'excerpt' => 'Key factors to look for when selecting raw herbs, finely milled botanical powders, and wellness items.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 400])
    ],
    [
        'id' => 3,
        'title' => 'Traditional Ingredients in Modern Wellness Routines',
        'category' => 'WELLNESS',
        'date' => 'Aug 10, 2026',
        'excerpt' => 'Integrating time-tested Indian botanical heritage into contemporary lifestyle habits effortlessly.',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 400])
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biswas Enterprise | Quality Natural & Herbal Products</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Biswas Enterprise offers thoughtfully sourced natural, herbal, and wellness products. Discover quality herbs, powders, and natural remedies.">
    <link rel="canonical" href="<?= url() ?>">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="Biswas Enterprise | Quality Natural & Herbal Products">
    <meta property="og:description" content="Discover premium natural and herbal products thoughtfully sourced for modern lifestyles.">
    <meta property="og:image" content="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1200&q=80', ['width' => 1200, 'height' => 630]) ?>">
    <meta property="og:url" content="<?= url() ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Biswas Enterprise | Quality Natural & Herbal Products">
    <meta name="twitter:description" content="Discover premium natural and herbal products thoughtfully sourced for modern lifestyles.">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Biswas Enterprise",
      "url": "<?= url() ?>",
      "logo": "<?= asset('image/logo.png') ?>",
      "description": "Exporter, supplier and trader dealing with quality herbal and natural products."
    }
    </script>

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>

    <!-- 1. ANNOUNCEMENT BAR -->
    <div class="announcement-bar" role="region" aria-label="Announcement">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">FREE SHIPPING ON SELECTED ORDERS</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">COD AVAILABLE</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">QUALITY ASSURED PRODUCTS</span>
            </div>
        </div>
    </div>

    <!-- 2. HEADER & NAVIGATION -->
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
                        <li><a href="<?= url() ?>" class="nav-link active">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('categories') ?>" class="nav-link">Categories</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <!-- Header Actions (Icons) -->
                <div class="header-actions">
                    <!-- Search Icon -->
                    <button class="icon-btn" id="search-toggle" aria-label="Open search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>

                    <!-- Wishlist Icon -->
                    <a href="<?= url('wishlist') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </a>

                    <!-- Account Icon -->
                    <a href="<?= url('account') ?>" class="icon-btn" aria-label="My Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>

                    <!-- Cart Icon with Badge -->
                    <a href="<?= url('cart') ?>" class="icon-btn" aria-label="View Cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="cart-badge">0</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Bar Modal Dropdown -->
        <div class="search-modal" id="search-modal">
            <div class="container">
                <form class="search-form" action="<?= url('shop') ?>" method="GET" role="search">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="search" name="q" placeholder="Search for natural herbs, powders, wellness products..." aria-label="Search site">
                </form>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer Overlay & Menu -->
    <div class="drawer-overlay" id="drawer-overlay"></div>
    <div class="mobile-drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
        <div class="drawer-header">
            <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
            </a>
            <button class="drawer-close" id="drawer-close" aria-label="Close menu">&times;</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="<?= url() ?>">Home</a>
            <a href="<?= url('shop') ?>">Shop</a>
            <a href="<?= url('categories') ?>">Categories</a>
            <a href="<?= url('about') ?>">About Us</a>
            <a href="<?= url('contact') ?>">Contact</a>
            <a href="<?= url('blog') ?>">Blog Journal</a>
        </nav>
    </div>

    <main id="main-content">
        <!-- 3. HERO SECTION -->
        <section class="hero-section" aria-label="Hero Introduction">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <span class="eyebrow">NATURAL & THOUGHTFULLY SOURCED</span>
                        <h1 class="hero-title">Discover Quality Natural & Herbal Products</h1>
                        <p class="hero-description">Selected with care for modern lifestyles. Powered by authentic botanical sourcing and uncompromised commitment to quality.</p>
                        <div class="hero-actions">
                            <a href="<?= url('shop') ?>" class="btn btn-primary btn-lg">SHOP PRODUCTS</a>
                            <a href="<?= url('categories') ?>" class="btn btn-outline btn-lg">EXPLORE CATEGORIES</a>
                        </div>
                    </div>
                    <div class="hero-image-wrapper">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 600]) ?>" 
                             alt="Natural herbs and botanical products arranged thoughtfully" 
                             width="800" height="600" fetchpriority="high">
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. TRUST / SERVICE BENEFITS -->
        <section class="benefits-section" aria-label="Key Service Benefits">
            <div class="container">
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="benefit-title">QUALITY ASSURED</h3>
                            <p class="benefit-desc">Carefully selected products</p>
                        </div>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="benefit-title">SECURE PAYMENT</h3>
                            <p class="benefit-desc">Safe checkout experience</p>
                        </div>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="benefit-title">COD AVAILABLE</h3>
                            <p class="benefit-desc">Convenient payment option</p>
                        </div>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="benefit-title">CUSTOMER SUPPORT</h3>
                            <p class="benefit-desc">We're here to help</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. SHOP BY CATEGORY -->
        <section class="section-padding category-section" aria-label="Categories Overview">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">CURATED SELECTION</span>
                    <h2>EXPLORE OUR COLLECTION</h2>
                    <p>Discover products across our carefully curated categories.</p>
                </div>
                <div class="category-grid">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= url('category/' . $cat['slug']) ?>" class="category-card reveal-on-scroll">
                            <img src="<?= $cat['image'] ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-image" loading="lazy" decoding="async">
                            <div class="category-content">
                                <h3 class="category-name"><?= htmlspecialchars($cat['name']) ?></h3>
                                <p class="category-desc"><?= htmlspecialchars($cat['description']) ?></p>
                                <span class="category-cta">VIEW CATEGORY &rarr;</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 6. FEATURED PRODUCTS -->
        <section class="section-padding" aria-label="Featured Products">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">HANDPICKED FOR YOU</span>
                    <h2>FEATURED PRODUCTS</h2>
                    <p>Explore some of our selected products.</p>
                </div>
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $prod): ?>
                        <article class="product-card reveal-on-scroll">
                            <div class="product-card-image">
                                <?php if ($prod['stock_quantity'] == 0): ?>
                                    <span class="product-badge out-of-stock">OUT OF STOCK</span>
                                <?php elseif (!empty($prod['badge'])): ?>
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
                                <div class="product-rating">
                                    <div class="rating-stars" aria-label="<?= $prod['rating'] ?> out of 5 stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?= $prod['reviews_count'] ?>)</span>
                                </div>
                                <div class="product-price-wrapper">
                                    <span class="price-current">&#8377;<?= number_format($prod['price']) ?></span>
                                    <?php if ($prod['regular_price'] > $prod['price']): ?>
                                        <span class="price-original">&#8377;<?= number_format($prod['regular_price']) ?></span>
                                        <span class="price-discount">
                                            Save <?= round((($prod['regular_price'] - $prod['price']) / $prod['regular_price']) * 100) ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($prod['stock_quantity'] > 0): ?>
                                    <button class="btn-add-cart">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        ADD TO CART
                                    </button>
                                <?php else: ?>
                                    <button class="btn-add-cart" disabled>
                                        OUT OF STOCK
                                    </button>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 7. WHY CHOOSE US -->
        <section class="section-padding why-us-section" aria-label="Why Choose Us">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">OUR CORE PRINCIPLES</span>
                    <h2>WHY CHOOSE BISWAS ENTERPRISE</h2>
                    <p>Building trust through quality sourcing and reliable service.</p>
                </div>
                <div class="why-us-grid">
                    <div class="why-card reveal-on-scroll">
                        <div class="why-number">01</div>
                        <h3 class="why-title">Quality First</h3>
                        <p class="why-desc">Carefully selected products with strict attention to quality and consistency.</p>
                    </div>
                    <div class="why-card reveal-on-scroll">
                        <div class="why-number">02</div>
                        <h3 class="why-title">Responsible Sourcing</h3>
                        <p class="why-desc">Products sourced with care and attention to their natural origin and botanical purity.</p>
                    </div>
                    <div class="why-card reveal-on-scroll">
                        <div class="why-number">03</div>
                        <h3 class="why-title">Customer Focused</h3>
                        <p class="why-desc">We believe in building long-term, dependable relationships with our customers.</p>
                    </div>
                    <div class="why-card reveal-on-scroll">
                        <div class="why-number">04</div>
                        <h3 class="why-title">Reliable Service</h3>
                        <p class="why-desc">A professional approach from initial product selection to ongoing customer support.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. FEATURED PRODUCT / EDITORIAL SECTION -->
        <section class="section-padding editorial-section" aria-label="Editorial Highlight">
            <div class="container">
                <div class="editorial-grid">
                    <div class="editorial-image-wrapper reveal-on-scroll">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 600]) ?>" 
                             alt="Featured Ashwagandha Herbal Formulation" loading="lazy" decoding="async">
                    </div>
                    <div class="editorial-content reveal-on-scroll">
                        <span class="eyebrow">FEATURED HIGHLIGHT</span>
                        <h2 class="editorial-title">Pure Ashwagandha Root Collection</h2>
                        <p class="editorial-desc">Discover our premium, finely ground Ashwagandha formulation. Sourced from choice botanical origins to deliver traditional vitality for modern wellness routines.</p>
                        <div class="editorial-price">&#8377;599 <span class="text-muted" style="font-size:1rem;font-weight:400;text-decoration:line-through;margin-left:8px;">&#8377;749</span></div>
                        <a href="<?= url('product/101') ?>" class="btn btn-primary btn-lg">VIEW FEATURED ITEM</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 9. COLLECTION / PROMOTIONAL BANNER -->
        <section class="promo-banner-section" style="background-image: url('<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1600&q=80', ['width' => 1600, 'height' => 800]) ?>');" aria-label="Promotional Collection Banner">
            <div class="promo-banner-overlay"></div>
            <div class="container">
                <div class="promo-banner-content reveal-on-scroll">
                    <h2>DISCOVER OUR HERBAL COLLECTION</h2>
                    <p>Explore naturally sourced products selected for quality, consistency, and daily use.</p>
                    <a href="<?= url('shop') ?>" class="btn btn-secondary btn-lg">EXPLORE SHOP</a>
                </div>
            </div>
        </section>

        <!-- 10. ABOUT BISWAS ENTERPRISE -->
        <section class="section-padding about-section" aria-label="About Biswas Enterprise">
            <div class="container">
                <div class="about-grid">
                    <div class="about-image-wrapper reveal-on-scroll">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 600]) ?>" 
                             alt="Botanical herbs laboratory and sourcing" loading="lazy" decoding="async">
                    </div>
                    <div class="about-content reveal-on-scroll">
                        <span class="eyebrow">HERITAGE & INTEGRITY</span>
                        <h2>ABOUT BISWAS ENTERPRISE</h2>
                        <p class="about-text">Biswas Enterprise is an established supplier and trader dedicated to providing high-grade natural herbs, herbal powders, and wellness essentials.</p>
                        <p class="about-text">We combine traditional botanical knowledge with modern quality management standards to ensure that every product delivers dependable purity and freshness to our valued customers.</p>
                        <a href="<?= url('about') ?>" class="btn btn-outline">LEARN MORE ABOUT US</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 11. QUALITY / TRUST SECTION -->
        <section class="section-padding quality-trust-section" aria-label="Quality Standards">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">UNCOMPROMISED STANDARDS</span>
                    <h2>QUALITY YOU CAN TRUST</h2>
                    <p>Our commitment to excellence across every stage of supply.</p>
                </div>
                <div class="quality-grid">
                    <div class="quality-card reveal-on-scroll">
                        <div class="quality-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h3>QUALITY SELECTION</h3>
                        <p>Thorough inspections of every batch before distribution.</p>
                    </div>
                    <div class="quality-card reveal-on-scroll">
                        <div class="quality-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                            </svg>
                        </div>
                        <h3>RESPONSIBLE SOURCING</h3>
                        <p>Direct relationships with trusted herbal producers.</p>
                    </div>
                    <div class="quality-card reveal-on-scroll">
                        <div class="quality-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <h3>TRANSPARENCY</h3>
                        <p>Clear product information and authentic details.</p>
                    </div>
                    <div class="quality-card reveal-on-scroll">
                        <div class="quality-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>DEDICATED SERVICE</h3>
                        <p>Responsive customer support for all inquiries.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 12. CUSTOMER REVIEWS -->
        <section class="section-padding reviews-section" aria-label="Customer Testimonials">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">CLIENT FEEDBACK</span>
                    <h2>WHAT OUR CUSTOMERS SAY</h2>
                    <p>Real feedback from satisfied buyers.</p>
                </div>
                <div class="reviews-grid">
                    <div class="review-card reveal-on-scroll">
                        <div class="review-stars">
                            ★★★★★
                        </div>
                        <p class="review-text">"Customer testimonial will appear here once verified reviews are loaded from the database."</p>
                        <div class="review-author">
                            <div class="author-avatar">R</div>
                            <div class="author-info">
                                <h4>Sample Customer</h4>
                                <span class="author-badge">&#10003; Verified Purchase</span>
                            </div>
                        </div>
                    </div>
                    <div class="review-card reveal-on-scroll">
                        <div class="review-stars">
                            ★★★★★
                        </div>
                        <p class="review-text">"Customer testimonial will appear here once verified reviews are loaded from the database."</p>
                        <div class="review-author">
                            <div class="author-avatar">A</div>
                            <div class="author-info">
                                <h4>Sample Customer</h4>
                                <span class="author-badge">&#10003; Verified Purchase</span>
                            </div>
                        </div>
                    </div>
                    <div class="review-card reveal-on-scroll">
                        <div class="review-stars">
                            ★★★★★
                        </div>
                        <p class="review-text">"Customer testimonial will appear here once verified reviews are loaded from the database."</p>
                        <div class="review-author">
                            <div class="author-avatar">S</div>
                            <div class="author-info">
                                <h4>Sample Customer</h4>
                                <span class="author-badge">&#10003; Verified Purchase</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 13. LATEST ARTICLES / BLOG -->
        <section class="section-padding blog-section" aria-label="Blog Journal">
            <div class="container">
                <div class="section-header reveal-on-scroll">
                    <span class="eyebrow">INSIGHTS & KNOWLEDGE</span>
                    <h2>FROM OUR JOURNAL</h2>
                    <p>Educational articles on herbal heritage and natural wellness.</p>
                </div>
                <div class="blog-grid">
                    <?php foreach ($blogArticles as $article): ?>
                        <article class="blog-card reveal-on-scroll">
                            <img src="<?= $article['image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="blog-image" loading="lazy" decoding="async">
                            <div class="blog-body">
                                <div class="blog-meta">
                                    <span><?= htmlspecialchars($article['category']) ?></span>
                                    <span>•</span>
                                    <span><?= htmlspecialchars($article['date']) ?></span>
                                </div>
                                <h3 class="blog-title"><?= htmlspecialchars($article['title']) ?></h3>
                                <p class="blog-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                                <a href="<?= url('blog/' . $article['id']) ?>" class="blog-link">READ ARTICLE &rarr;</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 14. NEWSLETTER / CTA -->
        <section class="newsletter-section" aria-label="Newsletter Subscription">
            <div class="container">
                <div class="newsletter-card reveal-on-scroll">
                    <span class="eyebrow">JOIN OUR COMMUNITY</span>
                    <h2>STAY CONNECTED WITH US</h2>
                    <p>Get product updates, new arrivals, and useful herbal wellness information delivered directly to your inbox.</p>
                    
                    <form class="newsletter-form" id="newsletter-form" novalidate>
                        <input type="email" class="newsletter-input" placeholder="Enter your email address..." aria-label="Email address for newsletter" required>
                        <button type="submit" class="btn btn-primary">SUBSCRIBE</button>
                    </form>
                    <div class="newsletter-feedback" id="newsletter-feedback" role="alert"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- 15. FOOTER -->
    <footer class="site-footer" role="contentinfo">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <!-- Col 1: Brand Info -->
                    <div class="footer-brand">
                        <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                            <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                        </a>
                        <p class="footer-desc">A trusted supplier and trader of quality natural herbs, botanical powders, and wellness essentials.</p>
                    </div>

                    <!-- Col 2: Shop Links -->
                    <div>
                        <h4 class="footer-heading">SHOP</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('shop') ?>">All Products</a></li>
                            <li><a href="<?= url('categories') ?>">Categories</a></li>
                            <li><a href="<?= url('shop?sort=new') ?>">New Arrivals</a></li>
                            <li><a href="<?= url('shop?sort=bestseller') ?>">Best Sellers</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Company Links -->
                    <div>
                        <h4 class="footer-heading">COMPANY</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('about') ?>">About Us</a></li>
                            <li><a href="<?= url('contact') ?>">Contact Us</a></li>
                            <li><a href="<?= url('blog') ?>">Blog Journal</a></li>
                        </ul>
                    </div>

                    <!-- Col 4: Support Links -->
                    <div>
                        <h4 class="footer-heading">SUPPORT</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('faq') ?>">FAQ</a></li>
                            <li><a href="<?= url('shipping-policy') ?>">Shipping Policy</a></li>
                            <li><a href="<?= url('return-policy') ?>">Return Policy</a></li>
                            <li><a href="<?= url('privacy-policy') ?>">Privacy Policy</a></li>
                            <li><a href="<?= url('terms') ?>">Terms & Conditions</a></li>
                        </ul>
                    </div>

                    <!-- Col 5: Contact Info -->
                    <div>
                        <h4 class="footer-heading">CONTACT</h4>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span>Contact support for inquiries</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span>info@biswasenterprise.co.in</span>
                        </div>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Instagram">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="Facebook">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                    <rect x="2" y="9" width="4" height="12"></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Core Application JavaScript -->
    <script type="module" src="<?= asset('js/core/app.js') ?>"></script>
</body>
</html>
