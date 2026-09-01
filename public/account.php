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

// ---- Address CRUD Handlers ----
$addressMessage = '';
$addressError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_action'])) {
    $addrAction = $_POST['address_action'];
    $uid = $currentUser['id'];
    try {
        $pdo = Database::getConnection();
        if ($addrAction === 'add_address' || $addrAction === 'edit_address') {
            $fn  = trim($_POST['full_name']      ?? '');
            $ph  = trim($_POST['phone']           ?? '');
            $al1 = trim($_POST['address_line_1'] ?? '');
            $al2 = trim($_POST['address_line_2'] ?? '');
            $cy  = trim($_POST['city']           ?? '');
            $st  = trim($_POST['state']          ?? '');
            $pc  = trim($_POST['postal_code']    ?? '');
            $co  = trim($_POST['country']        ?? 'India');
            $at  = trim($_POST['address_type']   ?? 'home');
            $isd = isset($_POST['is_default']) ? 1 : 0;
            if (empty($fn) || empty($al1) || empty($cy) || empty($st) || empty($pc)) {
                $addressError = 'Please fill all required fields (Name, Address, City, State, Postal Code).';
            } else {
                if ($isd) {
                    $pdo->prepare("UPDATE `addresses` SET is_default = 0 WHERE user_id = ?")->execute([$uid]);
                }
                if ($addrAction === 'add_address') {
                    $pdo->prepare("INSERT INTO `addresses` (user_id, full_name, phone, address_line_1, address_line_2, city, state, postal_code, country, address_type, is_default, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())")
                        ->execute([$uid,$fn,$ph,$al1,$al2,$cy,$st,$pc,$co,$at,$isd]);
                    $addressMessage = 'New address added successfully!';
                } else {
                    $aid = (int)($_POST['address_id'] ?? 0);
                    $pdo->prepare("UPDATE `addresses` SET full_name=?, phone=?, address_line_1=?, address_line_2=?, city=?, state=?, postal_code=?, country=?, address_type=?, is_default=?, updated_at=NOW() WHERE id=? AND user_id=?")
                        ->execute([$fn,$ph,$al1,$al2,$cy,$st,$pc,$co,$at,$isd,$aid,$uid]);
                    $addressMessage = 'Address updated successfully!';
                    header('Location: ' . url('account.php?tab=addresses&saved=1')); exit;
                }
            }
        } elseif ($addrAction === 'set_default') {
            $aid = (int)($_POST['address_id'] ?? 0);
            $pdo->prepare("UPDATE `addresses` SET is_default = 0 WHERE user_id = ?")->execute([$uid]);
            $pdo->prepare("UPDATE `addresses` SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$aid,$uid]);
            $addressMessage = 'Default address updated.';
        } elseif ($addrAction === 'delete_address') {
            $aid = (int)($_POST['address_id'] ?? 0);
            $pdo->prepare("DELETE FROM `addresses` WHERE id = ? AND user_id = ?")->execute([$aid,$uid]);
            $addressMessage = 'Address deleted successfully.';
        }
    } catch (\Throwable $e) {
        $addressError = 'Error: ' . $e->getMessage();
    }
}

// ---- Password Change Handler ----
$passwordSuccess = '';
$passwordError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword     = $_POST['old_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $uid             = $currentUser['id'];
    $userRole        = $currentUser['role'] ?? 'user';
    $table           = ($userRole === 'admin') ? 'admins' : 'users';

    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = 'All password fields are required.';
    } elseif (strlen($newPassword) < 6) {
        $passwordError = 'New password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = 'New password and confirm password do not match.';
    } else {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT password FROM `{$table}` WHERE id = ?");
            $stmt->execute([$uid]);
            $userDb = $stmt->fetch();

            if ($userDb && (password_verify($oldPassword, $userDb['password']) || $oldPassword === $userDb['password'])) {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE `{$table}` SET password = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$newHash, $uid]);
                $passwordSuccess = 'Your password has been changed successfully!';
            } else {
                $passwordError = 'Current password is incorrect. Please try again.';
            }
        } catch (\Throwable $e) {
            $passwordError = 'Failed to update password: ' . $e->getMessage();
        }
    }
}

// Fetch all saved addresses for current user
$userAddresses = [];
try {
    $pdo = Database::getConnection();
    $stmtAddr = $pdo->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmtAddr->execute([$currentUser['id']]);
    $userAddresses = $stmtAddr->fetchAll();
} catch (\Throwable $e) { /* silent */ }

// Flash message from redirect
if (isset($_GET['saved']) && empty($addressMessage)) $addressMessage = 'Address updated successfully!';
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
    <?php include __DIR__ . '/includes/toastify.php'; ?>

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
                    <script>document.addEventListener('DOMContentLoaded', () => showToastify('Your profile information has been successfully updated!', 'success'));</script>
                <?php endif; ?>

                <?php if (!empty($updateError)): ?>
                    <script>document.addEventListener('DOMContentLoaded', () => showToastify('<?= addslashes(htmlspecialchars($updateError)) ?>', 'error'));</script>
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

                    <style>
                        .addr-page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid #edf2ef}
                        .addr-page-title{font-family:'Merriweather',serif;font-size:20px;color:#1a2721;display:flex;align-items:center;gap:10px;margin:0}
                        .addr-add-btn{display:inline-flex;align-items:center;gap:7px;background:#1b3b2b;color:#fff;border:none;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;transition:background .2s,transform .15s}
                        .addr-add-btn:hover{background:#2a523c;transform:translateY(-1px)}
                        .addr-flash{display:flex;align-items:center;gap:10px;padding:13px 18px;border-radius:10px;font-size:13.5px;font-weight:500;margin-bottom:20px}
                        .addr-flash.ok{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
                        .addr-flash.err{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
                        .addr-form-panel{background:#f8faf9;border:1.5px solid #d9e8de;border-radius:16px;padding:28px 28px 24px;margin-bottom:24px;animation:fadeSlideDown .25s ease}
                        .addr-form-panel-title{font-size:15px;font-weight:700;color:#1a2721;margin:0 0 20px;display:flex;align-items:center;gap:8px}
                        .addr-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
                        .addr-fg{display:flex;flex-direction:column;gap:6px}
                        .addr-fg.full{grid-column:1/-1}
                        .addr-fg label{font-size:11.5px;font-weight:700;color:#6b7c72;text-transform:uppercase;letter-spacing:.6px}
                        .addr-fg input,.addr-fg select{padding:11px 14px;border:1.5px solid #dce8e0;border-radius:9px;font-size:13.5px;color:#1a2721;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit}
                        .addr-fg input:focus,.addr-fg select:focus{border-color:#1b3b2b;box-shadow:0 0 0 3px rgba(27,59,43,.08)}
                        .addr-fg input::placeholder{color:#b0bdb6}
                        .addr-checkbox-row{display:flex;align-items:center;gap:9px;font-size:13px;color:#4a5c52;cursor:pointer;margin-top:4px}
                        .addr-checkbox-row input[type=checkbox]{width:16px;height:16px;accent-color:#1b3b2b;cursor:pointer}
                        .addr-form-actions{display:flex;gap:10px;margin-top:22px}
                        .addr-form-save{background:#1b3b2b;color:#fff;border:none;padding:11px 26px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s}
                        .addr-form-save:hover{background:#2a523c}
                        .addr-form-cancel{background:#fff;color:#4a5c52;border:1.5px solid #d4ddd7;padding:11px 20px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center}
                        .addr-form-cancel:hover{border-color:#1b3b2b;color:#1b3b2b}
                        .addr-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
                        .addr-card{background:#fff;border:1.5px solid #e4ede8;border-radius:16px;padding:22px 22px 18px;display:flex;flex-direction:column;gap:14px;transition:border-color .2s,box-shadow .2s;position:relative}
                        .addr-card:hover{border-color:#c5d9ca;box-shadow:0 6px 20px rgba(27,59,43,.06)}
                        .addr-card.is-default{border-color:#1b3b2b;box-shadow:0 0 0 1px #1b3b2b,0 6px 20px rgba(27,59,43,.09)}
                        .addr-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
                        .addr-badges{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
                        .badge-type{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:3px 10px;border-radius:20px;background:#eef4f0;color:#2a6040}
                        .badge-default{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:3px 10px;border-radius:20px;background:#1b3b2b;color:#fff}
                        .addr-info{font-size:13.5px;color:#3a4a40;line-height:1.75}
                        .addr-info strong{color:#1a2721;font-size:14.5px;display:block;margin-bottom:2px}
                        .addr-phone-line{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#728277;margin-top:4px}
                        .addr-card-actions{display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid #f0f5f2}
                        .aBtn{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:all .18s;border:1.5px solid transparent;text-decoration:none}
                        .aBtn-edit{border-color:#d4ddd7;background:#fff;color:#2c3e35}
                        .aBtn-edit:hover{border-color:#1b3b2b;background:#1b3b2b;color:#fff}
                        .aBtn-star{border-color:#d4ddd7;background:#fff;color:#2c3e35}
                        .aBtn-star:hover{border-color:#d4af37;background:#fefce8;color:#92400e}
                        .aBtn-del{border-color:#fecaca;background:#fff;color:#dc2626;margin-left:auto}
                        .aBtn-del:hover{background:#dc2626;border-color:#dc2626;color:#fff}
                        .addr-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;gap:12px}
                        .addr-empty-icon{width:72px;height:72px;border-radius:50%;background:#f0f5f2;display:flex;align-items:center;justify-content:center}
                        .addr-empty h3{font-family:'Merriweather',serif;font-size:18px;color:#1a2721;margin:0}
                        .addr-empty p{font-size:13.5px;color:#728277;margin:0;text-align:center}
                        @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
                        @media(max-width:640px){.addr-form-grid,.addr-cards-grid{grid-template-columns:1fr}}
                    </style>

                    <!-- Header -->
                    <div class="addr-page-header">
                        <h2 class="addr-page-title">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Saved Addresses
                        </h2>
                        <button type="button" id="btnAddAddress" class="addr-add-btn" onclick="toggleAddressForm()">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add New Address
                        </button>
                    </div>

                    <?php if (!empty($addressMessage)): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToastify('<?= addslashes(htmlspecialchars($addressMessage)) ?>', 'success'));</script>
                    <?php endif; ?>
                    <?php if (!empty($addressError)): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToastify('<?= addslashes(htmlspecialchars($addressError)) ?>', 'error'));</script>
                    <?php endif; ?>

                    <!-- Add New Address Form Panel -->
                    <div id="addAddressForm" style="display:none;">
                        <div class="addr-form-panel">
                            <p class="addr-form-panel-title">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                New Delivery Address
                            </p>
                            <form method="POST" action="<?= url('account.php?tab=addresses') ?>">
                                <input type="hidden" name="address_action" value="add_address">
                                <div class="addr-form-grid">
                                    <div class="addr-fg">
                                        <label>Full Name <span style="color:#dc2626">*</span></label>
                                        <input type="text" name="full_name" placeholder="Recipient name" required value="<?= htmlspecialchars($currentUser['name']) ?>">
                                    </div>
                                    <div class="addr-fg">
                                        <label>Phone Number</label>
                                        <input type="tel" name="phone" placeholder="+91 98765 43210" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                                    </div>
                                    <div class="addr-fg full">
                                        <label>Address Line 1 <span style="color:#dc2626">*</span></label>
                                        <input type="text" name="address_line_1" placeholder="House / Flat No., Street, Area" required>
                                    </div>
                                    <div class="addr-fg full">
                                        <label>Address Line 2 <span style="color:#b0bdb6;font-weight:400;text-transform:none;">— optional</span></label>
                                        <input type="text" name="address_line_2" placeholder="Landmark, Colony (optional)">
                                    </div>
                                    <div class="addr-fg">
                                        <label>City <span style="color:#dc2626">*</span></label>
                                        <input type="text" name="city" placeholder="City" required>
                                    </div>
                                    <div class="addr-fg">
                                        <label>State <span style="color:#dc2626">*</span></label>
                                        <input type="text" name="state" placeholder="State" required>
                                    </div>
                                    <div class="addr-fg">
                                        <label>Postal Code <span style="color:#dc2626">*</span></label>
                                        <input type="text" name="postal_code" placeholder="700001" required>
                                    </div>
                                    <div class="addr-fg">
                                        <label>Address Type</label>
                                        <select name="address_type">
                                            <option value="home">🏠 Home</option>
                                            <option value="work">🏢 Work</option>
                                            <option value="other">📦 Other</option>
                                        </select>
                                    </div>
                                    <div class="addr-fg full">
                                        <label class="addr-checkbox-row">
                                            <input type="checkbox" name="is_default" <?= empty($userAddresses) ? 'checked' : '' ?>>
                                            Set as my default delivery address
                                        </label>
                                    </div>
                                </div>
                                <div class="addr-form-actions">
                                    <button type="submit" class="addr-form-save">Save Address</button>
                                    <button type="button" class="addr-form-cancel" onclick="toggleAddressForm()">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Address Cards / Empty State -->
                    <?php if (empty($userAddresses)): ?>
                        <div class="addr-empty">
                            <div class="addr-empty-icon">
                                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#9ab4a2" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <h3>No Saved Addresses</h3>
                            <p>Click "Add New Address" above to save<br>your first delivery address.</p>
                        </div>
                    <?php else: ?>
                        <div class="addr-cards-grid">
                        <?php foreach ($userAddresses as $addr): ?>
                            <?php $editMode = isset($_GET['edit_id']) && (int)$_GET['edit_id'] === (int)$addr['id']; ?>

                            <?php if ($editMode): ?>
                                <div style="grid-column:1/-1;">
                                <div class="addr-form-panel" style="border-color:#1b3b2b;">
                                    <p class="addr-form-panel-title">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit Address
                                    </p>
                                    <form method="POST" action="<?= url('account.php?tab=addresses') ?>">
                                        <input type="hidden" name="address_action" value="edit_address">
                                        <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                        <div class="addr-form-grid">
                                            <div class="addr-fg">
                                                <label>Full Name <span style="color:#dc2626">*</span></label>
                                                <input type="text" name="full_name" required value="<?= htmlspecialchars($addr['full_name']) ?>">
                                            </div>
                                            <div class="addr-fg">
                                                <label>Phone Number</label>
                                                <input type="tel" name="phone" value="<?= htmlspecialchars($addr['phone'] ?? '') ?>">
                                            </div>
                                            <div class="addr-fg full">
                                                <label>Address Line 1 <span style="color:#dc2626">*</span></label>
                                                <input type="text" name="address_line_1" required value="<?= htmlspecialchars($addr['address_line_1']) ?>">
                                            </div>
                                            <div class="addr-fg full">
                                                <label>Address Line 2</label>
                                                <input type="text" name="address_line_2" value="<?= htmlspecialchars($addr['address_line_2'] ?? '') ?>">
                                            </div>
                                            <div class="addr-fg">
                                                <label>City <span style="color:#dc2626">*</span></label>
                                                <input type="text" name="city" required value="<?= htmlspecialchars($addr['city']) ?>">
                                            </div>
                                            <div class="addr-fg">
                                                <label>State <span style="color:#dc2626">*</span></label>
                                                <input type="text" name="state" required value="<?= htmlspecialchars($addr['state']) ?>">
                                            </div>
                                            <div class="addr-fg">
                                                <label>Postal Code <span style="color:#dc2626">*</span></label>
                                                <input type="text" name="postal_code" required value="<?= htmlspecialchars($addr['postal_code']) ?>">
                                            </div>
                                            <div class="addr-fg">
                                                <label>Address Type</label>
                                                <select name="address_type">
                                                    <option value="home" <?= $addr['address_type']==='home'?'selected':'' ?>>🏠 Home</option>
                                                    <option value="work" <?= $addr['address_type']==='work'?'selected':'' ?>>🏢 Work</option>
                                                    <option value="other" <?= $addr['address_type']==='other'?'selected':'' ?>>📦 Other</option>
                                                </select>
                                            </div>
                                            <div class="addr-fg full">
                                                <label class="addr-checkbox-row">
                                                    <input type="checkbox" name="is_default" <?= $addr['is_default']?'checked':'' ?>>
                                                    Set as my default delivery address
                                                </label>
                                            </div>
                                        </div>
                                        <div class="addr-form-actions">
                                            <button type="submit" class="addr-form-save">Save Changes</button>
                                            <a href="<?= url('account.php?tab=addresses') ?>" class="addr-form-cancel">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                                </div>
                            <?php else: ?>
                                <!-- ADDRESS CARD -->
                                <div class="addr-card <?= $addr['is_default'] ? 'is-default' : '' ?>">
                                    <div class="addr-card-top">
                                        <div class="addr-badges">
                                            <span class="badge-type"><?= ucfirst(htmlspecialchars($addr['address_type'])) ?></span>
                                            <?php if ($addr['is_default']): ?><span class="badge-default">✓ Default</span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="addr-info">
                                        <strong><?= htmlspecialchars($addr['full_name']) ?></strong>
                                        <?= htmlspecialchars($addr['address_line_1']) ?>
                                        <?php if (!empty($addr['address_line_2'])): ?>, <?= htmlspecialchars($addr['address_line_2']) ?><?php endif; ?><br>
                                        <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> – <?= htmlspecialchars($addr['postal_code']) ?><br>
                                        <?= htmlspecialchars($addr['country'] ?? 'India') ?>
                                        <?php if (!empty($addr['phone'])): ?>
                                        <div class="addr-phone-line">
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.11 11.9 19.79 19.79 0 0 1 1.04 3.27 2 2 0 0 1 3 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                            <?= htmlspecialchars($addr['phone']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="addr-card-actions">
                                        <a href="<?= url('account.php?tab=addresses&edit_id='.$addr['id']) ?>" class="aBtn aBtn-edit">
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </a>
                                        <?php if (!$addr['is_default']): ?>
                                        <form method="POST" style="display:contents;">
                                            <input type="hidden" name="address_action" value="set_default">
                                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                            <button type="submit" class="aBtn aBtn-star">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                Set Default
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:contents;" onsubmit="return confirm('Remove this address?');">
                                            <input type="hidden" name="address_action" value="delete_address">
                                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                            <button type="submit" class="aBtn aBtn-del">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <script>
                    function toggleAddressForm() {
                        const form = document.getElementById('addAddressForm');
                        const btn  = document.getElementById('btnAddAddress');
                        const open = form.style.display === 'none';
                        form.style.display = open ? 'block' : 'none';
                        btn.innerHTML = open
                            ? '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Cancel'
                            : '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add New Address';
                        if (open) form.scrollIntoView({ behavior:'smooth', block:'start' });
                    }
                    <?php if (!empty($addressError)): ?>
                    document.addEventListener('DOMContentLoaded', () => toggleAddressForm());
                    <?php endif; ?>
                    </script>


                <!-- TAB 5: SECURITY -->
                <?php elseif ($activeTab === 'security'): ?>
                    <style>
                        .sec-page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid #edf2ef; }
                        .sec-page-title  { font-family:'Merriweather',serif; font-size:20px; color:#1a2721; display:flex; align-items:center; gap:10px; margin:0; }
                        .sec-card { background:#fff; border:1.5px solid #e4ede8; border-radius:16px; padding:28px; max-width:540px; margin-bottom:24px; box-shadow:0 4px 16px rgba(27,59,43,0.03); }
                        .sec-card-title { font-size:16px; font-weight:700; color:#1a2721; margin:0 0 8px; display:flex; align-items:center; gap:8px; }
                        .sec-card-sub { font-size:13px; color:#6b7c72; margin-bottom:22px; line-height:1.5; }
                        .sec-form-grid { display:flex; flex-direction:column; gap:18px; }
                        .sec-fg { display:flex; flex-direction:column; gap:6px; }
                        .sec-fg label { font-size:11.5px; font-weight:700; color:#6b7c72; text-transform:uppercase; letter-spacing:0.6px; }
                        .sec-fg input { padding:12px 14px; border:1.5px solid #dce8e0; border-radius:9px; font-size:14px; color:#1a2721; background:#fff; outline:none; transition:border-color 0.2s,box-shadow 0.2s; font-family:inherit; }
                        .sec-fg input:focus { border-color:#1b3b2b; box-shadow:0 0 0 3px rgba(27,59,43,0.08); }
                        .sec-submit-btn { background:#1b3b2b; color:#fff; border:none; padding:12px 26px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; transition:background 0.2s,transform 0.15s; width:fit-content; display:inline-flex; align-items:center; gap:8px; margin-top:6px; }
                        .sec-submit-btn:hover { background:#2a523c; transform:translateY(-1px); }
                        .sec-flash { display:flex; align-items:center; gap:10px; padding:13px 18px; border-radius:10px; font-size:13.5px; font-weight:500; margin-bottom:20px; max-width:540px; }
                        .sec-flash.ok { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
                        .sec-flash.err { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
                    </style>

                    <div class="sec-page-header">
                        <h2 class="sec-page-title">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Account Security
                        </h2>
                    </div>

                    <?php if (!empty($passwordSuccess)): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToastify('<?= addslashes(htmlspecialchars($passwordSuccess)) ?>', 'success'));</script>
                    <?php endif; ?>
                    <?php if (!empty($passwordError)): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToastify('<?= addslashes(htmlspecialchars($passwordError)) ?>', 'error'));</script>
                    <?php endif; ?>

                    <div class="sec-card">
                        <h3 class="sec-card-title">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.778-7.778zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                            Change Password
                        </h3>
                        <p class="sec-card-sub">Enter your current password and choose a strong new password to update your account security in the database.</p>

                        <form method="POST" action="<?= url('account.php?tab=security') ?>">
                            <input type="hidden" name="change_password" value="1">
                            <div class="sec-form-grid">
                                <div class="sec-fg">
                                    <label for="old_password">Current Password <span style="color:#dc2626">*</span></label>
                                    <input type="password" id="old_password" name="old_password" placeholder="Enter current password" required>
                                </div>
                                <div class="sec-fg">
                                    <label for="new_password">New Password <span style="color:#dc2626">*</span></label>
                                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 6 characters)" required minlength="6">
                                </div>
                                <div class="sec-fg">
                                    <label for="confirm_password">Confirm New Password <span style="color:#dc2626">*</span></label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="6">
                                </div>
                                <div>
                                    <button type="submit" class="sec-submit-btn">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
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
