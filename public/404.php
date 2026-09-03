<?php
/**
 * CUSTOM 404 ERROR PAGE
 * Biswas Enterprise Official 404 Not Found Page
 */
http_response_code(404);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$companyPhone = '+91 93300 51702';
$companyEmail = 'dipak_200607@yahoo.co.in';
$gstNo = '19AGXPB1978M1ZI';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Biswas Enterprise</title>
    
    <!-- SEO Meta Tags -->
    <meta name="robots" content="noindex, follow">
    <meta name="description" content="The page you requested could not be found on Biswas Enterprise. Explore our herbal products and renewable energy solutions.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Core Stylesheet -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

    <style>
        .error-page-wrapper {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5rem 1.5rem;
            background: linear-gradient(180deg, #f8faf8 0%, #edf3ef 100%);
            position: relative;
            overflow: hidden;
        }

        .error-card {
            background: #ffffff;
            border: 1.5px solid #e1ebd6;
            border-radius: 28px;
            padding: 3.5rem 3rem;
            max-width: 720px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(27, 59, 43, 0.06);
            position: relative;
            z-index: 2;
        }

        .error-code-badge {
            font-family: 'Merriweather', serif;
            font-size: clamp(5rem, 12vw, 7.5rem);
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #1b3b2b 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.04em;
        }

        .error-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eaf4ed;
            color: #1b3b2b;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
            border: 1px solid #cde2d3;
        }

        .error-title {
            font-family: 'Merriweather', serif;
            font-size: clamp(1.6rem, 3.5vw, 2.2rem);
            color: #1b3b2b;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .error-desc {
            font-size: 1.05rem;
            color: #55685c;
            line-height: 1.65;
            max-width: 540px;
            margin: 0 auto 2rem;
        }

        /* Quick Search Input */
        .error-search-form {
            display: flex;
            align-items: center;
            max-width: 460px;
            margin: 0 auto 2.25rem;
            background: #f4f8f5;
            border: 1.5px solid #d4e4d8;
            border-radius: 50px;
            padding: 4px 6px 4px 18px;
            transition: all 0.25s ease;
        }

        .error-search-form:focus-within {
            border-color: #1b3b2b;
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(27, 59, 43, 0.1);
        }

        .error-search-input {
            border: none;
            background: transparent;
            width: 100%;
            font-size: 0.95rem;
            color: #1b3b2b;
            outline: none;
        }

        .error-search-btn {
            background: #1b3b2b;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .error-search-btn:hover {
            background: #255239;
        }

        /* Action Buttons */
        .error-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .btn-error-primary {
            background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%);
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(27, 59, 43, 0.2);
            transition: all 0.25s ease;
        }

        .btn-error-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(27, 59, 43, 0.3);
        }

        .btn-error-outline {
            background: #ffffff;
            color: #1b3b2b !important;
            border: 1.5px solid #1b3b2b;
            padding: 13px 26px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }

        .btn-error-outline:hover {
            background: #f0f5f2;
            transform: translateY(-2px);
        }

        /* Helpful Suggestions Links */
        .error-links-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding-top: 2rem;
            border-top: 1px solid #e5ebe7;
        }

        .error-link-card {
            background: #f8faf8;
            border: 1px solid #e3ede6;
            border-radius: 14px;
            padding: 12px 10px;
            text-decoration: none;
            color: #1b3b2b;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .error-link-card:hover {
            background: #1b3b2b;
            color: #ffffff;
            border-color: #1b3b2b;
            transform: translateY(-2px);
        }

        .error-link-card svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        @media (max-width: 640px) {
            .error-card {
                padding: 2.5rem 1.5rem;
                border-radius: 20px;
            }
            .error-links-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .error-actions {
                flex-direction: column;
                width: 100%;
            }
            .btn-error-primary, .btn-error-outline {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Official Announcement Bar -->
    <div class="announcement-bar">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">GST NO: <?= htmlspecialchars($gstNo) ?></span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">WHOLESALE EXPORTER & SUPPLIER FROM KOLKATA</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">WHATSAPP: <?= htmlspecialchars($companyPhone) ?></span>
            </div>
        </div>
    </div>

    <!-- Header Navigation -->
    <header class="site-header" role="banner">
        <div class="container">
            <div class="header-inner">
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Open menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

                <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                    <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                </a>

                <nav class="main-navigation">
                    <ul>
                        <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About Us</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main 404 Error Content -->
    <main class="error-page-wrapper">
        <div class="error-card">
            <div class="error-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>PAGE NOT FOUND</span>
            </div>
            
            <div class="error-code-badge">404</div>
            <h1 class="error-title">Looking for Herbal Sourcing or Clean Energy?</h1>
            <p class="error-desc">
                The page you are looking for doesn't exist or may have been moved. Try searching our product catalog below or navigate back to the main site.
            </p>

            <!-- Search Form -->
            <form action="<?= url('shop') ?>" method="GET" class="error-search-form">
                <input type="text" name="search" class="error-search-input" placeholder="Search Arjuna Bark, Harad Powder, Solar Lights..." required>
                <button type="submit" class="error-search-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Search</span>
                </button>
            </form>

            <!-- Action CTAs -->
            <div class="error-actions">
                <a href="<?= url() ?>" class="btn-error-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>RETURN HOME</span>
                </a>
                <a href="<?= url('shop') ?>" class="btn-error-outline">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    <span>EXPLORE CATALOG</span>
                </a>
            </div>

            <!-- Quick Category Shortcuts -->
            <div class="error-links-grid">
                <a href="<?= url('shop?category=arjuna-bark') ?>" class="error-link-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>Arjuna Bark</span>
                </a>
                <a href="<?= url('shop?category=herbs-powder') ?>" class="error-link-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    <span>Harad Powder</span>
                </a>
                <a href="<?= url('shop?category=dried-herbs') ?>" class="error-link-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
                    <span>Dried Herbs</span>
                </a>
                <a href="<?= url('shop?category=renewable-energy') ?>" class="error-link-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>
                    <span>Solar Lights</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script type="module">
        import { initHeader } from '<?= asset('js/components/header.js') ?>';
        document.addEventListener('DOMContentLoaded', () => {
            initHeader();
        });
    </script>
</body>
</html>
