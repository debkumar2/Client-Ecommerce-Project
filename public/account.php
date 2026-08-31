<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

// Handle Logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutUser();
    header('Location: ' . url('login?logged_out=1'));
    exit;
}

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: ' . url('login'));
    exit;
}

$currentUser = getCurrentUser();
$initialLetter = strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1));
$activeTab = $_GET['tab'] ?? 'overview';

// Handle profile updates if submitted
$updateSuccess = false;
$updateError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    if (!empty($firstName) && !empty($lastName)) {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $phone, $currentUser['id']]);
            
            // Refresh session user data
            $_SESSION['user_first_name'] = $firstName;
            $_SESSION['user_last_name']  = $lastName;
            $_SESSION['user_name']       = $firstName . ' ' . $lastName;
            $_SESSION['user_phone']      = $phone;
            
            $currentUser = getCurrentUser();
            $updateSuccess = true;
        } catch (Exception $e) {
            $updateError = 'Failed to update profile details. Please try again.';
        }
    } else {
        $updateError = 'First name and last name are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="My Account Dashboard - Biswas Enterprise Premium Ayurvedic Care">
    <title>My Account - Biswas Enterprise</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/auth.css') ?>">

    <style>
        /* Account Dashboard Clean Styles */
        .account-page-bg {
            background-color: #f7faf8;
            min-height: 100vh;
        }
        
        .account-hero-banner {
            background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%);
            color: #ffffff;
            padding: 45px 0;
            position: relative;
        }

        .account-hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .account-hero-text h1 {
            font-family: 'Merriweather', serif;
            font-size: 30px;
            margin-bottom: 6px;
            color: #ffffff;
        }

        .account-hero-text p {
            color: #b8ccbf;
            font-size: 14px;
        }

        .account-hero-actions {
            display: flex;
            gap: 12px;
        }

        .hero-btn-accent {
            background: #d4af37;
            color: #1b3b2b;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .hero-btn-accent:hover {
            background: #e5c158;
            transform: translateY(-2px);
        }

        .hero-btn-outline {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }

        .hero-btn-outline:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Layout Grid */
        .account-layout-container {
            padding: 40px 0 70px;
        }

        .account-grid-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
            align-items: start;
        }

        /* Sidebar Navigation */
        .account-sidebar-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5ebe7;
            padding: 24px 16px;
        }

        .user-mini-profile {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #edf2ef;
            margin-bottom: 16px;
        }

        .user-mini-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%);
            color: #ffffff;
            font-family: 'Merriweather', serif;
            font-size: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 6px 16px rgba(27, 59, 43, 0.2);
            border: 3px solid #ffffff;
        }

        .user-mini-name {
            font-family: 'Merriweather', serif;
            font-size: 18px;
            font-weight: 700;
            color: #1a2721;
            margin-bottom: 4px;
        }

        .user-mini-email {
            font-size: 12.5px;
            color: #728277;
            word-break: break-all;
            margin-bottom: 10px;
        }

        .account-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .account-nav-item {
            margin-bottom: 4px;
        }

        .account-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #4a5c52;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .account-nav-link svg {
            width: 18px;
            height: 18px;
            color: #8fa095;
            transition: color 0.2s ease;
        }

        .account-nav-link:hover {
            background-color: #f2f7f4;
            color: #1b3b2b;
        }

        .account-nav-link:hover svg {
            color: #1b3b2b;
        }

        .account-nav-link.active {
            background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(27, 59, 43, 0.2);
        }

        .account-nav-link.active svg {
            color: #d4af37;
        }

        .account-nav-link.logout-link {
            color: #dc2626;
        }

        .account-nav-link.logout-link svg {
            color: #dc2626;
        }

        .account-nav-link.logout-link:hover {
            background-color: #fef2f2;
        }

        /* Main Content Card */
        .account-main-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5ebe7;
            padding: 32px;
            min-height: 480px;
        }

        .tab-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #edf2ef;
        }

        .tab-section-title {
            font-family: 'Merriweather', serif;
            font-size: 22px;
            color: #1a2721;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Overview Stat Widgets */
        .overview-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-widget-card {
            background: #f8faf8;
            border: 1px solid #e5ede7;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
        }

        .stat-widget-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(27, 59, 43, 0.08);
            color: #1b3b2b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-widget-icon.gold {
            background: rgba(212, 175, 55, 0.15);
            color: #92400e;
        }

        .stat-widget-val {
            font-size: 20px;
            font-weight: 700;
            color: #1a2721;
            line-height: 1.2;
        }

        .stat-widget-lbl {
            font-size: 12.5px;
            color: #64746b;
            margin-top: 2px;
        }

        /* Information Boxes */
        .info-card-box {
            background: #fafcfb;
            border: 1px solid #e6eee8;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .info-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a2721;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .details-list-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .details-item {
            background: #ffffff;
            border: 1px solid #e8eee9;
            border-radius: 12px;
            padding: 14px 18px;
        }

        .details-item label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            color: #728277;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 4px;
        }

        .details-item span {
            font-size: 14.5px;
            font-weight: 600;
            color: #1a2721;
        }

        .form-edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 900px) {
            .account-grid-wrapper {
                grid-template-columns: 1fr;
            }
            .form-edit-grid, .details-list-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="account-page-bg">

    <!-- 1. ANNOUNCEMENT BAR (IDENTICAL TO INDEX PAGE) -->
    <div class="announcement-bar" role="region" aria-label="Announcement">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">100% AUTHENTIC NATURAL SOURCING & CLEAN ENERGY</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">EXPRESS SHIPPING ACROSS INDIA</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">BULK & EXPORT ENQUIRIES WELCOME</span>
            </div>
        </div>
    </div>

    <!-- 2. HEADER & NAVIGATION (IDENTICAL TO INDEX PAGE) -->
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
                        <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>

                <!-- Header Actions (Icons) -->
                <div class="header-actions">
                    <!-- Wishlist Icon -->
                    <a href="<?= url('wishlist') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </a>

                    <!-- Account Icon -->
                    <a href="<?= url('account') ?>" class="icon-btn active" aria-label="Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>

                    <!-- Cart Button -->
                    <a href="<?= url('cart') ?>" class="icon-btn" aria-label="Open Shopping Cart">
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

    <!-- Account Hero Header -->
    <section class="account-hero-banner">
        <div class="container">
            <div class="account-hero-content">
                <div class="account-hero-text">
                    <h1>My Account Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($currentUser['first_name']) ?>! Manage your profile, view orders & track account security.</p>
                </div>
                <div class="account-hero-actions">
                    <?php if (isAdmin()): ?>
                        <a href="<?= url('admin/dashboard.php') ?>" class="hero-btn-accent">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <span>Admin Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('shop') ?>" class="hero-btn-outline">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Visit Store</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Account Dashboard Main Grid -->
    <div class="container account-layout-container">
        <div class="account-grid-wrapper">

            <!-- Sidebar Navigation -->
            <aside class="account-sidebar-card">
                <div class="user-mini-profile">
                    <div class="user-mini-avatar">
                        <?= htmlspecialchars($initialLetter) ?>
                    </div>
                    <div class="user-mini-name"><?= htmlspecialchars($currentUser['name']) ?></div>
                    <div class="user-mini-email"><?= htmlspecialchars($currentUser['email']) ?></div>
                    <span class="account-role-badge <?= $currentUser['role'] === 'admin' ? 'role-badge-admin' : 'role-badge-user' ?>">
                        <?= $currentUser['role'] === 'admin' ? '⚡ System Admin' : '🌿 Verified Customer' ?>
                    </span>
                </div>

                <ul class="account-nav-list">
                    <li class="account-nav-item">
                        <a href="<?= url('account.php?tab=overview') ?>" class="account-nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>Dashboard Overview</span>
                        </a>
                    </li>
                    <li class="account-nav-item">
                        <a href="<?= url('account.php?tab=orders') ?>" class="account-nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            <span>My Orders</span>
                        </a>
                    </li>
                    <li class="account-nav-item">
                        <a href="<?= url('account.php?tab=profile') ?>" class="account-nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Profile Details</span>
                        </a>
                    </li>
                    <li class="account-nav-item">
                        <a href="<?= url('account.php?tab=addresses') ?>" class="account-nav-link <?= $activeTab === 'addresses' ? 'active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>Saved Addresses</span>
                        </a>
                    </li>
                    <li class="account-nav-item">
                        <a href="<?= url('account.php?tab=security') ?>" class="account-nav-link <?= $activeTab === 'security' ? 'active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>Security & Password</span>
                        </a>
                    </li>
                    <li class="account-nav-item" style="margin-top: 16px; border-top: 1px solid #edf2ef; padding-top: 12px;">
                        <a href="<?= url('account.php?action=logout') ?>" class="account-nav-link logout-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <main class="account-main-card">
                <?php if ($updateSuccess): ?>
                    <div class="alert-box alert-box-success">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Your profile information has been successfully updated!</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($updateError)): ?>
                    <div class="alert-box alert-box-danger">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span><?= htmlspecialchars($updateError) ?></span>
                    </div>
                <?php endif; ?>

                <!-- TAB 1: OVERVIEW -->
                <?php if ($activeTab === 'overview'): ?>
                    <div class="tab-section-header">
                        <h2 class="tab-section-title">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>Account Summary</span>
                        </h2>
                    </div>

                    <!-- Widgets Grid -->
                    <div class="overview-stats-grid">
                        <div class="stat-widget-card">
                            <div class="stat-widget-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
                            </div>
                            <div>
                                <div class="stat-widget-val">0</div>
                                <div class="stat-widget-lbl">Total Orders</div>
                            </div>
                        </div>

                        <div class="stat-widget-card">
                            <div class="stat-widget-icon gold">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            </div>
                            <div>
                                <div class="stat-widget-val">Gold Member</div>
                                <div class="stat-widget-lbl">Loyalty Tier</div>
                            </div>
                        </div>

                        <div class="stat-widget-card">
                            <div class="stat-widget-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </div>
                            <div>
                                <div class="stat-widget-val">0 Items</div>
                                <div class="stat-widget-lbl">Saved Wishlist</div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Box -->
                    <div class="info-card-box">
                        <div class="info-card-title">
                            <span>Primary Profile Information</span>
                            <a href="<?= url('account.php?tab=profile') ?>" style="font-size: 13px; color: #1b3b2b; text-decoration: underline;">Edit Profile</a>
                        </div>
                        <div class="details-list-grid">
                            <div class="details-item">
                                <label>Full Name</label>
                                <span><?= htmlspecialchars($currentUser['name']) ?></span>
                            </div>
                            <div class="details-item">
                                <label>Email Address</label>
                                <span><?= htmlspecialchars($currentUser['email']) ?></span>
                            </div>
                            <div class="details-item">
                                <label>Phone Number</label>
                                <span><?= !empty($currentUser['phone']) ? htmlspecialchars($currentUser['phone']) : 'Not Provided' ?></span>
                            </div>
                            <div class="details-item">
                                <label>Account Status</label>
                                <span>Active & Verified</span>
                            </div>
                        </div>
                    </div>

                <!-- TAB 2: ORDERS -->
                <?php elseif ($activeTab === 'orders'): ?>
                    <div class="tab-section-header">
                        <h2 class="tab-section-title">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
                            <span>Order History</span>
                        </h2>
                    </div>

                    <div style="text-align: center; padding: 48px 20px; color: #64746b;">
                        <svg viewBox="0 0 24 24" width="54" height="54" fill="none" stroke="#c0d1c6" stroke-width="1.5" style="margin-bottom: 12px;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 20px; color: #1a2721; margin-bottom: 6px;">No Recent Orders Yet</h3>
                        <p style="font-size: 14px; margin-bottom: 20px;">You haven't placed any orders with Biswas Enterprise yet.</p>
                        <a href="<?= url('shop') ?>" class="btn-auth-submit" style="display: inline-flex; width: auto; padding: 12px 28px;">
                            <span>Explore Products</span>
                        </a>
                    </div>

                <!-- TAB 3: PROFILE EDIT -->
                <?php elseif ($activeTab === 'profile'): ?>
                    <div class="tab-section-header">
                        <h2 class="tab-section-title">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Edit Profile Information</span>
                        </h2>
                    </div>

                    <form action="<?= url('account.php?tab=profile') ?>" method="POST">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="form-edit-grid">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-input" style="padding-left: 16px !important;" value="<?= htmlspecialchars($currentUser['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-input" style="padding-left: 16px !important;" value="<?= htmlspecialchars($currentUser['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address (Read-only)</label>
                            <input type="email" class="form-input" style="padding-left: 16px !important; background-color: #f1f5f2;" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" style="padding-left: 16px !important;" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                        </div>

                        <button type="submit" class="btn-auth-submit" style="width: auto; padding: 12px 28px; margin-top: 10px;">
                            <span>Save Profile Changes</span>
                        </button>
                    </form>

                <!-- TAB 4: ADDRESSES -->
                <?php elseif ($activeTab === 'addresses'): ?>
                    <div class="tab-section-header">
                        <h2 class="tab-section-title">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path></svg>
                            <span>Saved Delivery Addresses</span>
                        </h2>
                    </div>

                    <div class="info-card-box">
                        <div class="info-card-title">
                            <span>Default Delivery Address</span>
                            <span class="account-role-badge role-badge-user">Primary</span>
                        </div>
                        <p style="font-size: 14px; color: #3a4a40; line-height: 1.6; margin-bottom: 12px;">
                            <strong><?= htmlspecialchars($currentUser['name']) ?></strong><br>
                            Biswas Enterprise Care Center<br>
                            Kolkata, West Bengal - 700001<br>
                            India<br>
                            Phone: <?= !empty($currentUser['phone']) ? htmlspecialchars($currentUser['phone']) : '+91 (Not Provided)' ?>
                        </p>
                    </div>

                <!-- TAB 5: SECURITY -->
                <?php elseif ($activeTab === 'security'): ?>
                    <div class="tab-section-header">
                        <h2 class="tab-section-title">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>Account Security & Login</span>
                        </h2>
                    </div>

                    <div class="info-card-box">
                        <div class="info-card-title">
                            <span>Database Authentication Status</span>
                        </div>
                        <p style="font-size: 14px; color: #4a5c52; margin-bottom: 14px;">
                            Your account is protected using BCRYPT hashing and PDO secure prepared statements connected to Hostinger MySQL Database.
                        </p>
                        <a href="javascript:void(0)" onclick="alert('Password reset link sent to your email.')" class="btn-auth-submit" style="display: inline-flex; width: auto; padding: 10px 22px; font-size: 13px;">
                            <span>Change Account Password</span>
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom flex-between">
                <p>&copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved. Pure Ayurvedic Solutions.</p>
                <div class="footer-social-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    <?php include __DIR__ . '/includes/floating_enquiry.php'; ?>
</body>
</html>
