<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= env('APP_NAME', 'E-Commerce') ?></title>
    <link rel="stylesheet" href="/assets/css/base/reset.css">
    <link rel="stylesheet" href="/assets/css/base/variables.css">
    <link rel="stylesheet" href="/assets/css/base/typography.css">
</head>
<body>
    <div class="announcement-bar">
        Welcome to our new store! Free shipping on orders over $50.
    </div>

    <header class="main-header">
        <div class="container">
            <div class="logo">E-Commerce</div>
            <nav class="main-nav">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Shop</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="#" class="btn-login">Login</a>
                <a href="#" class="btn-cart">Cart (0)</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-placeholder">
            <div class="container">
                <h1>Discover Our Amazing Products</h1>
                <p>The best quality items for your daily needs.</p>
                <a href="#" class="btn btn-primary">Shop Now</a>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> E-Commerce Management System. All rights reserved.</p>
        </div>
    </footer>

    <script type="module" src="/assets/js/core/app.js"></script>
</body>
</html>
