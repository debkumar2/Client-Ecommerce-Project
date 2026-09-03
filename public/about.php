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
        
        <!-- 3. HERO BANNER SECTION (Clean Modern Split Layout) -->
        <section class="about-hero">
            <div class="container">
                <div class="about-hero-grid">
                    <div class="about-hero-text">
                        <span class="eyebrow" style="color: #d4af37; font-weight: 700; letter-spacing: 0.12em; margin-bottom: 0.75rem; display: block; text-transform: uppercase;">ABOUT BISWAS ENTERPRISE</span>
                        <h1 class="about-hero-title">Global Exporter & Trader of Herbal Products & Renewable Energy Solutions</h1>
                        <p class="about-hero-lead">
                            Biswas Enterprise is a globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India.
                        </p>
                        <div class="about-hero-badges">
                            <div class="hero-badge-item">
                                <div class="badge-icon-box">
                                    <svg class="hero-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                </div>
                                <div class="badge-details">
                                    <span class="badge-label">REGISTERED EXPORTER</span>
                                    <span class="badge-value">GST: 19AGXPB1978M1ZI</span>
                                </div>
                            </div>
                            <div class="hero-badge-item">
                                <div class="badge-icon-box">
                                    <svg class="hero-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                </div>
                                <div class="badge-details">
                                    <span class="badge-label">SUPPLY NETWORK</span>
                                    <span class="badge-value">Global Export Reach</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="about-hero-image-wrapper">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1000&q=85', ['width' => 1000, 'height' => 750]) ?>" alt="Herbal Sourcing & Export" class="about-hero-img">
                        <div class="hero-img-overlay"></div>
                        <div class="about-experience-card">
                            <div class="exp-badge-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div>
                                <div class="exp-number">2023</div>
                                <div class="exp-label">ESTABLISHED IN KOLKATA</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. OFFICIAL COMPANY OVERVIEW (Luxury Redesign) -->
        <section class="about-story-section">
            <div class="container">
                <div class="story-grid">
                    <div class="story-image-wrapper">
                        <img src="<?= cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=900&q=85', ['width' => 900, 'height' => 700]) ?>" alt="Sun Dried Herbs & Arjuna Bark Sourcing" class="story-main-img">
                        <div class="story-img-backdrop"></div>
                        <div class="story-floating-stat">
                            <div class="stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-num">100% Pure</span>
                                <span class="stat-text">Ethically Sourced Herbs</span>
                            </div>
                        </div>
                    </div>

                    <div class="story-content">
                        <span class="eyebrow" style="color: #d4af37; font-weight: 700; letter-spacing: 0.12em; margin-bottom: 0.75rem; display: block; text-transform: uppercase;">OUR CORPORATE PROFILE</span>
                        <h2 class="section-title">Reliable Partner for Natural & Eco-Conscious Solutions</h2>
                        
                        <p class="story-lead-para">
                            Biswas Enterprise is a globally oriented trading and export company established in 2023 and headquartered in Kolkata, West Bengal, India. The company is engaged in the export, supply, and trading of premium-quality herbal products and renewable energy solutions, catering to diverse industries and markets worldwide. With a focus on quality, sustainability, and ethical sourcing, Biswas Enterprise serves as a reliable partner for clients seeking natural and eco-conscious product solutions.
                        </p>

                        <div class="story-feature-chips">
                            <div class="feature-chip-card">
                                <div class="chip-dot"></div>
                                <div>
                                    <strong>Ethical Sourcing</strong>
                                    <span>Direct from trusted growers</span>
                                </div>
                            </div>
                            <div class="feature-chip-card">
                                <div class="chip-dot"></div>
                                <div>
                                    <strong>Strict Quality Checks</strong>
                                    <span>International safety compliance</span>
                                </div>
                            </div>
                        </div>

                        <p class="story-body-para">
                            Our product portfolio includes Arjuna Bark, a widely valued medicinal herb, a broad range of dried herbs, finely processed herbal powders, and selected renewable energy products. All herbal products are carefully sourced from trusted growers and natural regions, then processed under controlled conditions to preserve their purity, potency, and natural properties. Stringent quality checks are conducted to ensure compliance with international safety and quality standards.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. SUSTAINABILITY & GLOBAL LOGISTICS -->
        <section class="about-values-section" style="background: linear-gradient(180deg, #f4f8f5 0%, #ffffff 100%); padding: 5.5rem 0;">
            <div class="container">
                <div class="section-header-center" style="text-align: center; max-width: 720px; margin: 0 auto 50px;">
                    <span class="eyebrow" style="color: #d4af37; font-weight: 700; letter-spacing: 0.12em; margin-bottom: 0.75rem;">SUSTAINABILITY & GLOBAL REACH</span>
                    <h2 class="section-title" style="font-family: 'Merriweather', serif; font-size: clamp(1.75rem, 3.5vw, 2.3rem); color: #1b3b2b; margin: 8px 0 0; line-height: 1.25;">Promoting Environmentally Friendly Solutions Worldwide</h2>
                </div>

                <div class="values-grid-lux">
                    <!-- Card 1 -->
                    <div class="value-card-lux">
                        <div class="value-card-header">
                            <div class="value-icon-lux">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <span class="value-pill-tag">ECO-CONSCIOUS</span>
                        </div>
                        <h3>Sustainability & Trade Practices</h3>
                        <p>
                            Biswas Enterprise places strong emphasis on sustainability and responsible trade practices. Alongside herbal offerings, our renewable energy products reflect our commitment to promoting environmentally friendly solutions that support global efforts toward cleaner and more efficient energy use.
                        </p>
                        <div class="value-card-footer">
                            <span class="footer-chip">Pharma & Wellness</span>
                            <span class="footer-chip">Clean Energy</span>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="value-card-lux">
                        <div class="value-card-header">
                            <div class="value-icon-lux">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <span class="value-pill-tag">LOGISTICS NETWORK</span>
                        </div>
                        <h3>Global Supply & Logistics</h3>
                        <p>
                            Our global supply network and efficient logistics capabilities enable us to fulfill customized and bulk requirements with timely delivery and consistent quality. We work closely with distributors, importers, manufacturers, and wholesalers to ensure transparent communication at every transaction stage.
                        </p>
                        <div class="value-card-footer">
                            <span class="footer-chip">Kolkata Maritime Hub</span>
                            <span class="footer-chip">Air Freight</span>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="value-card-lux">
                        <div class="value-card-header">
                            <div class="value-icon-lux">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <span class="value-pill-tag">QUALITY GUARANTEED</span>
                        </div>
                        <h3>Integrity & Professional Excellence</h3>
                        <p>
                            Driven by integrity, reliability, and customer satisfaction, Biswas Enterprise aims to build long-term partnerships across international markets. We continue to expand our product range and global presence while upholding the highest standards of quality and professional excellence.
                        </p>
                        <div class="value-card-footer">
                            <span class="footer-chip">100% Lab Tested</span>
                            <span class="footer-chip">Govt Registered</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Custom Styling for Sustainability Section -->
        <style>
            .values-grid-lux {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 28px;
            }
            .value-card-lux {
                background: #ffffff;
                border: 1px solid #e1ebe4;
                border-radius: 20px;
                padding: 32px 26px;
                position: relative;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(27, 59, 43, 0.04);
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .value-card-lux::before {
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
            .value-card-lux:hover {
                transform: translateY(-8px);
                border-color: #c0dbc8;
                box-shadow: 0 18px 40px rgba(27, 59, 43, 0.09);
            }
            .value-card-lux:hover::before {
                opacity: 1;
            }
            .value-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 22px;
            }
            .value-icon-lux {
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
            .value-card-lux:hover .value-icon-lux {
                background: #1b3b2b;
                color: #ffffff;
            }
            .value-pill-tag {
                font-size: 10.5px;
                font-weight: 800;
                letter-spacing: 0.08em;
                color: #1b3b2b;
                background: #f0f6f2;
                padding: 4px 10px;
                border-radius: 20px;
                border: 1px solid #d4e4d8;
            }
            .value-card-lux h3 {
                font-family: 'Merriweather', serif;
                font-size: 1.15rem;
                color: #1b3b2b;
                margin: 0 0 12px;
                line-height: 1.35;
            }
            .value-card-lux p {
                font-size: 14px;
                color: #55685c;
                line-height: 1.7;
                margin: 0 0 24px;
                flex-grow: 1;
            }
            .value-card-footer {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                padding-top: 16px;
                border-top: 1px solid #f0f5f1;
            }
            .footer-chip {
                font-size: 11px;
                color: #4a5c51;
                background: #f6faf7;
                padding: 3px 10px;
                border-radius: 6px;
                font-weight: 600;
            }

            @media (max-width: 992px) {
                .values-grid-lux {
                    grid-template-columns: 1fr;
                    gap: 20px;
                }
            }
            @media (max-width: 575px) {
                .value-card-lux {
                    padding: 24px 20px;
                }
            }
        </style>

        <!-- 6. PRODUCT PORTFOLIO OVERVIEW -->
        <section class="portfolio-overview-section">
            <div class="container">
                <div class="section-header-center">
                    <span class="eyebrow" style="color: #d4af37; font-weight: 700; letter-spacing: 0.12em; margin-bottom: 0.75rem;">FEATURED CATEGORIES</span>
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
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Core JavaScript -->
    <script type="module">
        import { initHeader } from '<?= asset('js/components/header.js') ?>';
        document.addEventListener('DOMContentLoaded', () => {
            initHeader();
        });
    </script>
    <?php include __DIR__ . '/includes/floating_enquiry.php'; ?>
</body>
</html>
