<?php
/**
 * Wishlist Page
 * Biswas Enterprise E-Commerce
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/products.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/app.php';

initDatabaseTables();

$userLoggedIn = isLoggedIn();
$currentUser  = getCurrentUser();
$wishlistItems = [];
$totalItems    = 0;

if ($userLoggedIn) {
    $pdo = Database::getConnection();
    $userId = (int)$currentUser['id'];

    try {
        $stmt = $pdo->prepare("SELECT wi.product_id, wi.created_at FROM wishlist_items wi JOIN wishlists w ON wi.wishlist_id = w.id WHERE w.user_id = ? ORDER BY wi.created_at DESC");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $pid = (int)$r['product_id'];
            $prod = getProductById($pid);
            if ($prod) {
                $prod['added_at'] = $r['created_at'];
                $wishlistItems[] = $prod;
            }
        }
        $totalItems = count($wishlistItems);
    } catch (\Throwable $e) {
        $errorMsg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Saved Wishlist | Biswas Enterprise</title>
    <meta name="description" content="View and manage your saved natural herbs, Arjuna bark, and clean energy products in your Biswas Enterprise wishlist.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/shop.css') ?>">
    <?php include __DIR__ . '/includes/toastify.php'; ?>

    <style>
        .wishlist-hero-banner {
            background: linear-gradient(135deg, #1b3b2b 0%, #0d2116 100%);
            color: #ffffff;
            padding: 50px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .wishlist-hero-title {
            font-family: 'Merriweather', serif;
            font-size: 2.2rem;
            margin-bottom: 8px;
            font-weight: 700;
            color: #ffffff !important;
        }

        .wishlist-hero-subtitle {
            color: #d1e7dd !important;
            font-size: 15px;
            max-width: 550px;
            margin: 0 auto;
        }

        .wishlist-container-padding {
            padding: 50px 0 80px 0;
        }

        .wishlist-empty-card {
            background: #ffffff;
            border: 1px dashed #d1ded6;
            border-radius: 20px;
            padding: 60px 30px;
            text-align: center;
            max-width: 580px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .wishlist-empty-icon {
            width: 72px;
            height: 72px;
            background: #f0f6f2;
            color: #1b3b2b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
        }

        .btn-wishlist-remove {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-wishlist-remove:hover {
            background: #fca5a5;
            color: #991b1b;
        }

        .wishlist-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 30px;
        }
    </style>
</head>
<body style="background-color: #f8faf8;">

    <!-- 1. ANNOUNCEMENT BAR -->
    <div class="announcement-bar" role="region" aria-label="Announcement">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">🌿 GST NO: 19AWBPB8678C1ZS</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">EXPRESS SHIPPING ACROSS INDIA</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">BULK & EXPORT ENQUIRIES WELCOME</span>
            </div>
        </div>
    </div>

    <!-- 2. HEADER & NAVIGATION -->
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

                <nav class="main-navigation" role="navigation">
                    <ul>
                        <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About Us</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <a href="<?= url('wishlist.php') ?>" class="icon-btn active" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span class="wishlist-badge" style="<?= $totalItems > 0 ? '' : 'display:none;' ?>"><?= $totalItems ?></span>
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
        <!-- 3. HERO BANNER -->
        <section class="wishlist-hero-banner">
            <div class="container">
                <h1 class="wishlist-hero-title">My Saved Wishlist</h1>
                <p class="wishlist-hero-subtitle">
                    Keep track of your favorite natural herbs, botanical powders, and clean energy products.
                </p>
            </div>
        </section>

        <!-- 4. CONTENT SECTION -->
        <section class="wishlist-container-padding">
            <div class="container">
                <?php if (!$userLoggedIn): ?>
                    <div class="wishlist-empty-card">
                        <div class="wishlist-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </div>
                        <h2 style="font-family: 'Merriweather', serif; font-size: 1.5rem; color: #1b3b2b; margin-bottom: 10px;">Please Sign In</h2>
                        <p style="color: #647569; font-size: 14px; margin-bottom: 24px;">
                            You need to log into your account to save products and view your wishlist across devices.
                        </p>
                        <a href="<?= url('login') ?>" class="btn-primary-d2c" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 50px; text-decoration: none;">
                            <span>SIGN IN TO YOUR ACCOUNT</span>
                        </a>
                    </div>
                <?php elseif ($totalItems === 0): ?>
                    <div class="wishlist-empty-card" id="emptyWishlistCard">
                        <div class="wishlist-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </div>
                        <h2 style="font-family: 'Merriweather', serif; font-size: 1.5rem; color: #1b3b2b; margin-bottom: 10px;">Your Wishlist is Empty</h2>
                        <p style="color: #647569; font-size: 14px; margin-bottom: 24px;">
                            Explore our range of Arjuna Bark, Harad powder, and solar lights to add items to your personal wishlist.
                        </p>
                        <a href="<?= url('shop') ?>" class="btn-primary-d2c" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 50px; text-decoration: none;">
                            <span>BROWSE SHOP PRODUCTS</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2ebe5; padding-bottom: 16px; margin-bottom: 20px;">
                        <h2 style="font-family: 'Merriweather', serif; font-size: 1.4rem; color: #1b3b2b; margin: 0;">
                            Saved Items (<span id="wishlistCountHeader"><?= $totalItems ?></span>)
                        </h2>
                        <a href="<?= url('shop') ?>" style="color: #1b3b2b; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <span>+ Explore More Products</span>
                        </a>
                    </div>

                    <div class="wishlist-items-grid" id="wishlistGrid">
                        <?php foreach ($wishlistItems as $item): ?>
                            <article class="product-card product-card-modern" data-id="<?= $item['id'] ?>" id="wishlist-card-<?= $item['id'] ?>">
                                <div class="product-card-image">
                                    <?php if (!empty($item['badge'])): ?>
                                        <span class="product-badge"><?= htmlspecialchars($item['badge']) ?></span>
                                    <?php endif; ?>

                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                </div>
                                <div class="product-card-body">
                                    <span class="product-category"><?= htmlspecialchars($item['category']) ?></span>
                                    <h3 class="product-title">
                                        <a href="<?= url('shop') ?>"><?= htmlspecialchars($item['name']) ?></a>
                                    </h3>
                                    <div class="product-price-wrapper">
                                        <span class="price-current">&#8377;<?= number_format($item['price']) ?></span>
                                        <?php if ($item['regular_price'] > $item['price']): ?>
                                            <span class="price-original">&#8377;<?= number_format($item['regular_price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div style="display: flex; gap: 8px; margin-top: 14px;">
                                        <button class="btn-add-cart" style="flex: 1;">
                                            ADD TO CART
                                        </button>
                                        <button class="btn-wishlist-remove" onclick="removeWishlistItem(<?= $item['id'] ?>)" title="Remove from Wishlist">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        async function removeWishlistItem(productId) {
            try {
                const response = await fetch('<?= url('api/wishlist.php') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'remove', product_id: productId })
                });

                const result = await response.json();

                if (result.success) {
                    const card = document.getElementById('wishlist-card-' + productId);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        card.style.transition = 'all 0.3s ease';
                        setTimeout(() => {
                            card.remove();
                            const grid = document.getElementById('wishlistGrid');
                            if (grid && grid.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }

                    showToastify(result.message, 'info');

                    const countHeader = document.getElementById('wishlistCountHeader');
                    if (countHeader) countHeader.textContent = result.count;

                    const badges = document.querySelectorAll('.wishlist-badge');
                    badges.forEach(b => {
                        b.textContent = result.count;
                        b.style.display = result.count > 0 ? 'inline-flex' : 'none';
                    });
                }
            } catch (err) {
                console.error('Error removing item from wishlist:', err);
            }
        }
    </script>
</body>
</html>
