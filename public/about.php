<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Biswas Enterprise Kolkata - Exporter of Arjuna Bark & Herbal Products</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Biswas Enterprise is a globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India. Exporter of Arjuna Bark, dried herbs, herbal powders & renewable energy products.">
    <link rel="canonical" href="<?= url('about') ?>">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="About Us | Biswas Enterprise Kolkata">
    <meta property="og:description" content="Biswas Enterprise is engaged in the export, supply, and trading of premium-quality herbal products and renewable energy solutions.">
    <meta property="og:image" content="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1200&q=80', ['width' => 1200, 'height' => 630]) ?>">
    <meta property="og:url" content="<?= url('about') ?>">
    <meta property="og:type" content="website">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "mainEntity": {
        "@type": "Organization",
        "name": "Biswas Enterprise",
        "foundingDate": "2023",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "Na Kalikapur Berhampore Murshidabad, Bara Bazar",
          "addressLocality": "Kolkata",
          "addressRegion": "West Bengal",
          "postalCode": "742102",
          "addressCountry": "IN"
        },
        "taxID": "19AGXPB1978M1ZI",
        "email": "dipak_200607@yahoo.co.in",
        "url": "<?= url() ?>"
      }
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
                <span class="announcement-item">ESTABLISHED 2023 • KOLKATA, WEST BENGAL</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">GST NO: 19AGXPB1978M1ZI</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">GLOBAL EXPORT & BULK SUPPLY</span>
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
                        <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link active">About</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <!-- Header Actions (Icons) -->
                <div class="header-actions">
                    <a href="<?= url('wishlist') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </a>
                    <a href="<?= url('account') ?>" class="icon-btn" aria-label="My Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
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
    </header>

    <!-- MAIN CONTENT -->
    <main id="main-content">
        
        <!-- 3. HERO BANNER SECTION (Ayutra Style Modern Split Layout) -->
        <section class="about-hero">
            <div class="container">
                <div class="about-hero-grid">
                    <div class="about-hero-text">
                        <div class="about-hero-subtitle">ABOUT BISWAS ENTERPRISE</div>
                        <h1 class="about-hero-title">Global Exporter & Trader of Herbal Products & Renewable Energy Solutions</h1>
                        <p class="about-hero-lead">
                            Biswas Enterprise is a globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India.
                        </p>
                        <div class="about-hero-badges">
                            <div class="hero-badge-item">
                                <svg class="hero-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="hero-badge-text">GST NO: 19AGXPB1978M1ZI</span>
                            </div>
                            <div class="hero-badge-item">
                                <svg class="hero-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path></svg>
                                <span class="hero-badge-text">Global Export Network</span>
                            </div>
                        </div>
                    </div>

                    <div class="about-hero-image-wrapper">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 600]) ?>" alt="Herbal Sourcing & Export" class="about-hero-img">
                        <div class="about-experience-card">
                            <div class="exp-number">2023</div>
                            <div class="exp-label">Established in Kolkata</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. OFFICIAL COMPANY OVERVIEW (Verbatim Text from biswas-enterprise.co.in/about-us.htm) -->
        <section class="about-story-section">
            <div class="container">
                <div class="story-grid">
                    <div class="story-image-wrapper">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 650]) ?>" alt="Sun Dried Herbs & Arjuna Bark Sourcing" style="width:100%; height:520px; object-fit:cover; border-radius:var(--radius-lg); box-shadow:0 15px 35px rgba(0,0,0,0.06);">
                    </div>

                    <div class="story-content">
                        <span class="section-label">OUR CORPORATE PROFILE</span>
                        <h2 class="section-title">Reliable Partner for Natural & Eco-Conscious Solutions</h2>
                        
                        <p>
                            Biswas Enterprise is a globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India. The company is engaged in the export, supply, and trading of premium-quality herbal products and renewable energy solutions, catering to diverse industries and markets worldwide. With a focus on quality, sustainability, and ethical sourcing, Biswas Enterprise serves as a reliable partner for clients seeking natural and eco-conscious product solutions.
                        </p>
                        
                        <p>
                            Our product portfolio includes Arjuna Bark, a widely valued medicinal herb, a broad range of dried herbs, finely processed herbal powders, and selected renewable energy products. All herbal products are carefully sourced from trusted growers and natural regions, then processed under controlled conditions to preserve their purity, potency, and natural properties. Stringent quality checks are conducted to ensure compliance with international safety and quality standards.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. SUSTAINABILITY & GLOBAL LOGISTICS (Verbatim Text Section 2) -->
        <section class="about-values-section">
            <div class="container">
                <div class="section-header-center">
                    <span class="section-label">SUSTAINABILITY & GLOBAL REACH</span>
                    <h2 class="section-title">Promoting Environmentally Friendly Solutions Worldwide</h2>
                </div>

                <div class="values-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="value-card" style="text-align:left;">
                        <div class="value-icon-circle" style="margin:0 0 1.25rem 0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <h3>Sustainability & Trade Practices</h3>
                        <p>
                            Biswas Enterprise places strong emphasis on sustainability and responsible trade practices. Alongside herbal offerings, our renewable energy products reflect our commitment to promoting environmentally friendly solutions that support global efforts toward cleaner and more efficient energy use. This diversified approach allows us to serve clients across pharmaceutical, nutraceutical, wellness, energy, and industrial sectors.
                        </p>
                    </div>

                    <div class="value-card" style="text-align:left;">
                        <div class="value-icon-circle" style="margin:0 0 1.25rem 0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <h3>Global Supply & Logistics</h3>
                        <p>
                            Our global supply network and efficient logistics capabilities enable us to fulfill customized and bulk requirements with timely delivery and consistent quality. We work closely with distributors, importers, manufacturers, and wholesalers to ensure transparent communication and dependable service at every stage of the transaction.
                        </p>
                    </div>

                    <div class="value-card" style="text-align:left;">
                        <div class="value-icon-circle" style="margin:0 0 1.25rem 0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <h3>Integrity & Professional Excellence</h3>
                        <p>
                            Driven by integrity, reliability, and customer satisfaction, Biswas Enterprise aims to build long-term partnerships across international markets. We continue to expand our product range and global presence while upholding the highest standards of quality, sustainability, and professional excellence.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. PRODUCT PORTFOLIO OVERVIEW -->
        <section class="portfolio-overview-section">
            <div class="container">
                <div class="section-header-center">
                    <span class="section-label">FEATURED CATEGORIES</span>
                    <h2 class="section-title">Our Business Product Range</h2>
                </div>

                <div class="portfolio-grid">
                    <div class="portfolio-card">
                        <div class="portfolio-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        </div>
                        <div class="portfolio-card-content">
                            <h4>Arjuna Bark</h4>
                            <p>Dried Arjuna Bark, High Quality Arjuna Bark & Premium Quality Arjuna Bark sourced carefully to preserve purity and therapeutic potency.</p>
                        </div>
                    </div>

                    <div class="portfolio-card">
                        <div class="portfolio-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
                        </div>
                        <div class="portfolio-card-content">
                            <h4>Dried Herbs</h4>
                            <p>Dried Neem Leaves, Dried Tulsi Leaves, and Natural Reetha Soap Nuts selected for pharmaceutical and personal care uses.</p>
                        </div>
                    </div>

                    <div class="portfolio-card">
                        <div class="portfolio-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                        </div>
                        <div class="portfolio-card-content">
                            <h4>Herbs Powder</h4>
                            <p>Finely pulverized Harad Powder, Neem Powder, Ashwagandha Powder & Triphala Powder processed under controlled hygienic conditions.</p>
                        </div>
                    </div>

                    <div class="portfolio-card">
                        <div class="portfolio-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        </div>
                        <div class="portfolio-card-content">
                            <h4>Renewable Energy Products</h4>
                            <p>Solar LED Street Lights, Solar Power Storage Batteries, and high-efficiency Photovoltaic Solar Panels for clean energy applications.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. CTA BANNER SECTION -->
        <section class="about-cta-section">
            <div class="container">
                <div class="about-cta-content">
                    <h2 class="about-cta-title">Connect with Biswas Enterprise</h2>
                    <p class="about-cta-desc">We welcome customized and bulk requirements from importers, distributors, manufacturers, and wholesalers globally.</p>
                    <div class="cta-buttons-group">
                        <a href="<?= url('shop') ?>" class="btn btn-primary" style="background:#ffffff; color:var(--primary-dark);">Browse Products</a>
                        <a href="<?= url('contact') ?>" class="btn btn-outline" style="border-color:#ffffff; color:#ffffff;">Send Export Enquiry</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- 8. SITE FOOTER -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                
                <!-- Brand Info Column -->
                <div class="footer-col footer-brand-col">
                    <a href="<?= url() ?>" class="footer-logo">
                        <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                    </a>
                    <p class="footer-desc">
                        Globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India.
                    </p>
                    <div class="footer-gst-info">
                        <strong>GST NO:</strong> 19AGXPB1978M1ZI
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="footer-col">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?= url() ?>">Home</a></li>
                        <li><a href="<?= url('shop') ?>">Shop All Products</a></li>
                        <li><a href="<?= url('about') ?>">About Us</a></li>
                        <li><a href="<?= url('contact') ?>">Contact Us</a></li>
                        <li><a href="<?= url('blog') ?>">Latest Blog</a></li>
                    </ul>
                </div>

                <!-- Product Categories Column -->
                <div class="footer-col">
                    <h3 class="footer-title">Categories</h3>
                    <ul class="footer-links">
                        <li><a href="<?= url('shop?category=arjuna-bark') ?>">Arjuna Bark</a></li>
                        <li><a href="<?= url('shop?category=dried-herbs') ?>">Dried Herbs</a></li>
                        <li><a href="<?= url('shop?category=herbs-powder') ?>">Herbs Powder</a></li>
                        <li><a href="<?= url('shop?category=renewable-energy') ?>">Renewable Energy Products</a></li>
                    </ul>
                </div>

                <!-- Contact & Location Column -->
                <div class="footer-col">
                    <h3 class="footer-title">Headquarters</h3>
                    <p class="footer-address">
                        Na Kalikapur Berhampore Murshidabad,<br>
                        Bara Bazar, Kolkata, West Bengal - 742102, India
                    </p>
                    <p class="footer-contact-item">
                        <strong>Email:</strong> <a href="mailto:dipak_200607@yahoo.co.in">dipak_200607@yahoo.co.in</a>
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript -->
    <script type="module">
        import { initHeader } from '<?= asset('js/components/header.js') ?>';
        document.addEventListener('DOMContentLoaded', () => {
            initHeader();
        });
    </script>
</body>
</html>
