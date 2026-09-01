<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$articles = [
    [
        'id' => 1, 'featured' => true,
        'category' => 'EDUCATION', 'cat_class' => 'cat-color-education',
        'title' => 'Understanding Herbal Ingredients & Sourcing Purity',
        'excerpt' => 'Learn how authentic botanical herbs are harvested, sun-dried, and laboratory tested to preserve active phytochemicals. From farm to final packaging, discover the rigorous journey that ensures every batch meets international quality standards.',
        'date' => 'May 12, 2026', 'read_time' => '4 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=900&q=80', ['width' => 900, 'height' => 600]),
    ],
    [
        'id' => 2, 'featured' => false,
        'category' => 'WELLNESS', 'cat_class' => 'cat-color-wellness',
        'title' => 'Cardiovascular Wellness with Arjuna Bark',
        'excerpt' => 'Discover traditional Ayurvedic knowledge and modern research behind Terminalia Arjuna for cardiac strength and vitality.',
        'date' => 'Apr 28, 2026', 'read_time' => '6 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
    [
        'id' => 3, 'featured' => false,
        'category' => 'SUSTAINABILITY', 'cat_class' => 'cat-color-sustain',
        'title' => 'Sustainable Off-Grid Solar Power Solutions',
        'excerpt' => 'Exploring the economic and ecological impact of solar street lighting and clean photovoltaic energy systems across rural India.',
        'date' => 'Apr 15, 2026', 'read_time' => '5 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
    [
        'id' => 4, 'featured' => false,
        'category' => 'HERBS', 'cat_class' => 'cat-color-herbs',
        'title' => 'Neem: The Ancient Tree of Wellness',
        'excerpt' => 'From dried neem leaves to cold-pressed neem powder — explore the science and tradition behind one of India\'s most powerful medicinal plants.',
        'date' => 'Apr 5, 2026', 'read_time' => '5 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
    [
        'id' => 5, 'featured' => false,
        'category' => 'WELLNESS', 'cat_class' => 'cat-color-wellness',
        'title' => 'Triphala & Harad Powder: The Digestive Duo',
        'excerpt' => 'How Harad and Triphala powders have been used for centuries in Ayurvedic medicine to support gut health, immunity, and detoxification.',
        'date' => 'Mar 20, 2026', 'read_time' => '4 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
    [
        'id' => 6, 'featured' => false,
        'category' => 'ENERGY', 'cat_class' => 'cat-color-energy',
        'title' => 'Why Solar PV Panels Are Key to India\'s Green Future',
        'excerpt' => 'India\'s renewable energy targets and how solar photovoltaic panels sourced from reliable suppliers are driving rural electrification.',
        'date' => 'Mar 8, 2026', 'read_time' => '6 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1524169113697-01d657671c25?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
    [
        'id' => 7, 'featured' => false,
        'category' => 'EDUCATION', 'cat_class' => 'cat-color-education',
        'title' => 'Reetha Soap Nuts: Nature\'s Perfect Cleanser',
        'excerpt' => 'The rise of eco-conscious consumers has revived interest in natural Reetha soap nuts — a zero-waste, plant-derived alternative to synthetic detergents.',
        'date' => 'Feb 22, 2026', 'read_time' => '3 min read',
        'author' => 'Dipak Biswas', 'author_initials' => 'DB',
        'image' => cloudinary_url('https://images.unsplash.com/photo-1512290900673-7002ddb97b09?auto=format&fit=crop&w=800&q=80', ['width' => 800, 'height' => 500]),
    ],
];

$featured = array_shift($articles); // pull first as featured
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Journal | Biswas Enterprise – Herbal Wellness & Sustainable Living</title>
    <meta name="description" content="Read expert articles on Ayurvedic herbs, natural wellness, sustainable energy, and global export insights from Biswas Enterprise, Kolkata.">
    <link rel="canonical" href="<?= url('blog') ?>">
    <meta property="og:title" content="Blog & Journal | Biswas Enterprise">
    <meta property="og:description" content="Insights on herbal wellness, sustainable energy and Ayurvedic traditions.">
    <meta property="og:url" content="<?= url('blog') ?>">
    <meta property="og:type" content="blog">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>

<!-- ANNOUNCEMENT BAR -->
<div class="announcement-bar" role="region" aria-label="Announcement">
    <div class="container">
        <div class="announcement-content">
            <span class="announcement-item">HERBAL WELLNESS INSIGHTS</span>
            <span class="announcement-separator">•</span>
            <span class="announcement-item">AYURVEDIC KNOWLEDGE</span>
            <span class="announcement-separator">•</span>
            <span class="announcement-item">SUSTAINABLE LIVING JOURNAL</span>
        </div>
    </div>
</div>

<!-- HEADER -->
<header class="site-header" role="banner">
    <div class="container">
        <div class="header-inner">
            <button class="mobile-toggle" id="mobile-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
            </a>
            <nav class="main-navigation" role="navigation" aria-label="Main menu">
                <ul>
                    <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                    <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                    <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>
                    <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                    <li><a href="<?= url('blog') ?>" class="nav-link active">Blog</a></li>
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

<div class="drawer-overlay" id="drawer-overlay"></div>
<div class="mobile-drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
    <div class="drawer-header">
        <a href="<?= url() ?>" class="site-logo"><img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img"></a>
        <button class="drawer-close" id="drawer-close" aria-label="Close menu">&times;</button>
    </div>
    <nav class="mobile-nav-links">
        <a href="<?= url() ?>">Home</a>
        <a href="<?= url('shop') ?>">Shop</a>
        <a href="<?= url('about') ?>">About Us</a>
        <a href="<?= url('contact') ?>">Contact</a>
        <a href="<?= url('blog') ?>">Blog Journal</a>
    </nav>
</div>

<main id="main-content">

    <!-- HERO -->
    <section class="blog-hero">
        <div class="container">
            <div class="blog-hero-inner">
                <span class="blog-hero-eyebrow">Knowledge & Insights</span>
                <h1>The Biswas Enterprise Journal</h1>
                <p class="blog-hero-lead">Expert articles on Ayurvedic herbs, natural wellness, sustainable energy solutions, and responsible global trade.</p>
                <div class="blog-filter-bar" id="blog-filter-bar" role="group" aria-label="Filter articles by category">
                    <span class="blog-filter-pill active" data-cat="ALL">All Articles</span>
                    <span class="blog-filter-pill" data-cat="EDUCATION">Education</span>
                    <span class="blog-filter-pill" data-cat="WELLNESS">Wellness</span>
                    <span class="blog-filter-pill" data-cat="HERBS">Herbs</span>
                    <span class="blog-filter-pill" data-cat="SUSTAINABILITY">Sustainability</span>
                    <span class="blog-filter-pill" data-cat="ENERGY">Energy</span>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN BLOG SECTION -->
    <section class="blog-main-section">
        <div class="container">

            <!-- FEATURED ARTICLE -->
            <article class="blog-featured-card" data-cat="<?= $featured['category'] ?>">
                <div class="blog-featured-img-wrap">
                    <img src="<?= $featured['image'] ?>" alt="<?= htmlspecialchars($featured['title']) ?>" loading="eager">
                    <span class="blog-featured-badge">Featured</span>
                </div>
                <div class="blog-featured-body">
                    <div class="blog-featured-meta">
                        <span class="blog-cat-tag"><?= $featured['category'] ?></span>
                        <span><?= $featured['date'] ?></span>
                        <span>•</span>
                        <span><?= $featured['read_time'] ?></span>
                    </div>
                    <h2><?= htmlspecialchars($featured['title']) ?></h2>
                    <p><?= htmlspecialchars($featured['excerpt']) ?></p>
                    <a href="<?= url('blog/' . $featured['id']) ?>" class="blog-read-link">
                        Read Full Article
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </article>

            <!-- ARTICLES GRID + SIDEBAR -->
            <div class="blog-bottom-grid">

                <!-- Grid of articles -->
                <div>
                    <div class="blog-grid-section-label">
                        <h2>Latest Articles</h2>
                    </div>
                    <div class="blog-cards-grid" id="blog-cards-grid">
                        <?php foreach ($articles as $article): ?>
                        <article class="blog-article-card reveal-on-scroll" data-cat="<?= $article['category'] ?>">
                            <div class="blog-article-img">
                                <img src="<?= $article['image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy" decoding="async">
                                <span class="blog-article-cat <?= $article['cat_class'] ?>"><?= $article['category'] ?></span>
                            </div>
                            <div class="blog-article-body">
                                <div class="blog-article-meta">
                                    <span><?= $article['date'] ?></span>
                                    <span class="dot">●</span>
                                    <span>
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:2px"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?= $article['read_time'] ?>
                                    </span>
                                </div>
                                <h3><?= htmlspecialchars($article['title']) ?></h3>
                                <p><?= htmlspecialchars($article['excerpt']) ?></p>
                                <div class="blog-article-footer">
                                    <div class="blog-author-chip">
                                        <div class="blog-author-avatar"><?= $article['author_initials'] ?></div>
                                        <span class="blog-author-name"><?= htmlspecialchars($article['author']) ?></span>
                                    </div>
                                    <a href="<?= url('blog/' . $article['id']) ?>" class="blog-read-link" aria-label="Read <?= htmlspecialchars($article['title']) ?>">
                                        Read
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="blog-load-more-wrap" id="blog-no-results" style="display:none;">
                        <p style="color:var(--muted);font-size:0.95rem;">No articles found in this category yet. Check back soon!</p>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="blog-sidebar" aria-label="Blog Sidebar">

                    <!-- Popular Posts -->
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title">Popular Articles</h3>
                        <a href="<?= url('blog/2') ?>" class="popular-post-item">
                            <img class="popular-post-thumb"
                                src="<?= cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=120&q=70', ['width'=>120,'height'=>120]) ?>"
                                alt="Arjuna Bark Article" loading="lazy">
                            <div class="popular-post-info">
                                <h4>Cardiovascular Wellness with Arjuna Bark</h4>
                                <span>Apr 28, 2026 &nbsp;·&nbsp; 6 min read</span>
                            </div>
                        </a>
                        <a href="<?= url('blog/5') ?>" class="popular-post-item">
                            <img class="popular-post-thumb"
                                src="<?= cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=120&q=70', ['width'=>120,'height'=>120]) ?>"
                                alt="Harad Powder Article" loading="lazy">
                            <div class="popular-post-info">
                                <h4>Triphala &amp; Harad Powder: The Digestive Duo</h4>
                                <span>Mar 20, 2026 &nbsp;·&nbsp; 4 min read</span>
                            </div>
                        </a>
                        <a href="<?= url('blog/3') ?>" class="popular-post-item">
                            <img class="popular-post-thumb"
                                src="<?= cloudinary_url('https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=120&q=70', ['width'=>120,'height'=>120]) ?>"
                                alt="Solar Energy Article" loading="lazy">
                            <div class="popular-post-info">
                                <h4>Sustainable Off-Grid Solar Power Solutions</h4>
                                <span>Apr 15, 2026 &nbsp;·&nbsp; 5 min read</span>
                            </div>
                        </a>
                    </div>

                    <!-- Tags -->
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title">Topics &amp; Tags</h3>
                        <div class="tags-cloud">
                            <a href="#" class="tag-pill">Arjuna Bark</a>
                            <a href="#" class="tag-pill">Ayurveda</a>
                            <a href="#" class="tag-pill">Neem Leaves</a>
                            <a href="#" class="tag-pill">Herbal Powder</a>
                            <a href="#" class="tag-pill">Solar Energy</a>
                            <a href="#" class="tag-pill">Tulsi</a>
                            <a href="#" class="tag-pill">Reetha</a>
                            <a href="#" class="tag-pill">Wellness</a>
                            <a href="#" class="tag-pill">Export</a>
                            <a href="#" class="tag-pill">Triphala</a>
                            <a href="#" class="tag-pill">Harad</a>
                            <a href="#" class="tag-pill">Sustainability</a>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="sidebar-widget" style="background:var(--primary-light);border-color:var(--border);">
                        <h3 class="sidebar-widget-title" style="border-color:var(--border);">Stay in the Loop</h3>
                        <p style="font-size:0.83rem;color:var(--muted);line-height:1.6;margin-bottom:1rem;">Get new articles and herbal product updates delivered to your inbox.</p>
                        <form class="sidebar-newsletter-form" id="sidebar-newsletter-form" novalidate>
                            <input type="email" placeholder="Your email address…" aria-label="Email for newsletter" required>
                            <button type="submit">Subscribe</button>
                        </form>
                    </div>

                    <!-- About Widget -->
                    <div class="sidebar-widget" style="text-align:center;">
                        <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" style="height:48px;margin:0 auto 1rem;display:block;">
                        <p style="font-size:0.83rem;color:var(--muted);line-height:1.6;margin-bottom:1rem;">A Kolkata-based exporter of premium herbal products and renewable energy solutions since 2023.</p>
                        <a href="<?= url('about') ?>" class="blog-read-link" style="justify-content:center;">About Us <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
                    </div>

                </aside>
            </div>

        </div>
    </section>

</main>

<!-- FOOTER -->
<?php include __DIR__ . '/includes/footer.php'; ?>

<script type="module">
import { initHeader } from '<?= asset('js/components/header.js') ?>';
document.addEventListener('DOMContentLoaded', () => {
    initHeader();

    // --- Category filter ---
    const pills   = document.querySelectorAll('.blog-filter-pill');
    const cards   = document.querySelectorAll('#blog-cards-grid .blog-article-card');
    const featured = document.querySelector('.blog-featured-card');
    const noResults = document.getElementById('blog-no-results');

    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            const cat = pill.dataset.cat;

            // Show/hide featured
            if (featured) {
                const fc = featured.dataset.cat;
                featured.style.display = (cat === 'ALL' || cat === fc) ? '' : 'none';
            }

            let visible = 0;
            cards.forEach(card => {
                const match = cat === 'ALL' || card.dataset.cat === cat;
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            noResults.style.display = (visible === 0 && (!featured || featured.style.display === 'none')) ? 'block' : 'none';
        });
    });

    // --- Sidebar newsletter ---
    const snForm = document.getElementById('sidebar-newsletter-form');
    snForm.addEventListener('submit', e => {
        e.preventDefault();
        const input = snForm.querySelector('input');
        const btn   = snForm.querySelector('button');
        if (!input.value.trim()) return;
        btn.textContent = '✓ Subscribed!';
        btn.style.background = 'var(--secondary)';
        input.value = '';
        setTimeout(() => {
            btn.textContent = 'Subscribe';
            btn.style.background = '';
        }, 3000);
    });

    // --- Scroll reveal ---
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.reveal-on-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
});
</script>
<?php include __DIR__ . '/includes/floating_enquiry.php'; ?>
</body>
</html>
