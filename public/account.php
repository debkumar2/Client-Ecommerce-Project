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

// Fetch all saved addresses, wishlist, and orders for current user
$userAddresses  = [];
$wishlistCount  = 0;
$userOrders     = [];
$ordersCount    = 0;
$ordersByStatus = [
    'pending'    => 0,
    'processing' => 0,
    'shipped'    => 0,
    'delivered'  => 0,
    'cancelled'  => 0
];

try {
    initDatabaseTables();
    $pdo = Database::getConnection();

    $stmtAddr = $pdo->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmtAddr->execute([$currentUser['id']]);
    $userAddresses = $stmtAddr->fetchAll();

    $stmtWish = $pdo->prepare("SELECT COUNT(*) FROM `wishlist_items` wi JOIN `wishlists` w ON wi.wishlist_id = w.id WHERE w.user_id = ?");
    $stmtWish->execute([$currentUser['id']]);
    $wishlistCount = (int)$stmtWish->fetchColumn();

    // Fetch user orders with address information
    $stmtOrders = $pdo->prepare("
        SELECT o.*, 
               a.full_name as ship_name, a.phone as ship_phone, a.address_line_1, a.address_line_2, a.city, a.state, a.postal_code, a.country
        FROM `orders` o 
        LEFT JOIN `addresses` a ON o.shipping_address_id = a.id
        WHERE o.user_id = ? 
        ORDER BY o.id DESC
    ");
    $stmtOrders->execute([$currentUser['id']]);
    $userOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    $ordersCount = count($userOrders);

    foreach ($userOrders as $ord) {
        $st = strtolower($ord['order_status'] ?? 'pending');
        if (isset($ordersByStatus[$st])) {
            $ordersByStatus[$st]++;
        }
    }

    // Fetch order items for each order
    if (!empty($userOrders)) {
        $orderIds = array_column($userOrders, 'id');
        $in  = str_repeat('?,', count($orderIds) - 1) . '?';
        $stmtItems = $pdo->prepare("
            SELECT oi.*, p.image as product_image, p.category_name, p.slug as product_slug 
            FROM `order_items` oi 
            LEFT JOIN `products` p ON oi.product_id = p.id 
            WHERE oi.order_id IN ($in)
            ORDER BY oi.id ASC
        ");
        $stmtItems->execute($orderIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Group items by order_id
        $itemsByOrder = [];
        foreach ($allItems as $item) {
            $itemsByOrder[$item['order_id']][] = $item;
        }

        foreach ($userOrders as &$ord) {
            $ord['items'] = $itemsByOrder[$ord['id']] ?? [];
        }
        unset($ord);
    }
} catch (\Throwable $e) {
    error_log('Account orders fetch error: ' . $e->getMessage());
}

// Handle Print Invoice request
if (isset($_GET['print_id'])) {
    $printId = (int)$_GET['print_id'];
    $printOrder = null;
    foreach ($userOrders as $o) {
        if ((int)$o['id'] === $printId) {
            $printOrder = $o;
            break;
        }
    }

    if ($printOrder) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Invoice #<?= htmlspecialchars($printOrder['order_number']) ?> - Biswas Enterprise</title>
            <style>
                body { font-family: 'Inter', -apple-system, sans-serif; color: #1a2721; padding: 40px; margin: 0; background: #ffffff; }
                .invoice-box { max-width: 800px; margin: auto; border: 1px solid #e1ebe4; border-radius: 12px; padding: 36px; box-shadow: 0 4px 14px rgba(0,0,0,0.05); }
                .inv-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid #1b3b2b; margin-bottom: 24px; }
                .brand-title { font-family: Georgia, serif; font-size: 24px; font-weight: 700; color: #1b3b2b; }
                .brand-sub { font-size: 12px; color: #64746b; margin-top: 4px; }
                .inv-meta { text-align: right; }
                .inv-title { font-size: 20px; font-weight: 700; color: #1b3b2b; text-transform: uppercase; letter-spacing: 1px; }
                .inv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; font-size: 13.5px; }
                .inv-section-title { font-weight: 700; color: #1b3b2b; margin-bottom: 8px; text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.5px; }
                .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
                .inv-table th { background: #f4f8f5; color: #1b3b2b; text-align: left; padding: 12px 14px; font-size: 12.5px; font-weight: 700; border-bottom: 1.5px solid #d4ddd7; }
                .inv-table td { padding: 12px 14px; border-bottom: 1px solid #edf2ef; font-size: 13.5px; }
                .inv-totals { width: 280px; margin-left: auto; font-size: 13.5px; }
                .inv-totals-row { display: flex; justify-content: space-between; padding: 6px 0; color: #4a5c52; }
                .inv-totals-row.grand { border-top: 2px solid #1b3b2b; font-weight: 700; font-size: 16px; color: #1b3b2b; padding-top: 10px; margin-top: 6px; }
                .inv-footer { border-top: 1px solid #e1ebe4; padding-top: 20px; text-align: center; font-size: 12px; color: #728277; margin-top: 36px; }
                @media print {
                    body { padding: 0; }
                    .invoice-box { border: none; box-shadow: none; padding: 0; }
                }
            </style>
        </head>
        <body>
            <div class="invoice-box">
                <div class="inv-header">
                    <div>
                        <div class="brand-title">Biswas Enterprise</div>
                        <div class="brand-sub">100% Authentic Ayurvedic Care & Botanical Products</div>
                        <div style="font-size: 12px; color: #4a5c52; margin-top: 8px;">
                            Kolkata, West Bengal, India<br>
                            Email: support@biswasenterprise.com | Tel: +91 98765 43210
                        </div>
                    </div>
                    <div class="inv-meta">
                        <div class="inv-title">TAX INVOICE</div>
                        <div style="font-size: 13px; color: #4a5c52; margin-top: 6px;">
                            <strong>Order #:</strong> <?= htmlspecialchars($printOrder['order_number']) ?><br>
                            <strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($printOrder['created_at'])) ?><br>
                            <strong>Status:</strong> <?= ucfirst($printOrder['order_status']) ?>
                        </div>
                    </div>
                </div>

                <div class="inv-grid">
                    <div>
                        <div class="inv-section-title">Billed & Shipped To:</div>
                        <strong><?= htmlspecialchars($printOrder['ship_name'] ?? $currentUser['name']) ?></strong><br>
                        <?= htmlspecialchars($printOrder['address_line_1'] ?? 'Default Address') ?><br>
                        <?php if (!empty($printOrder['address_line_2'])): ?><?= htmlspecialchars($printOrder['address_line_2']) ?><br><?php endif; ?>
                        <?= htmlspecialchars($printOrder['city'] ?? '') ?>, <?= htmlspecialchars($printOrder['state'] ?? '') ?> - <?= htmlspecialchars($printOrder['postal_code'] ?? '') ?><br>
                        Phone: <?= htmlspecialchars($printOrder['ship_phone'] ?? $currentUser['phone'] ?? 'N/A') ?>
                    </div>
                    <div style="text-align: right;">
                        <div class="inv-section-title">Payment Overview:</div>
                        Method: <strong><?= strtoupper(htmlspecialchars($printOrder['payment_method'] ?? 'COD')) ?></strong><br>
                        Payment Status: <strong><?= ucfirst(htmlspecialchars($printOrder['payment_status'] ?? 'pending')) ?></strong><br>
                        Currency: <strong>INR (₹)</strong>
                    </div>
                </div>

                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($printOrder['items'])): ?>
                            <?php foreach ($printOrder['items'] as $it): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                                        <?php if (!empty($it['sku'])): ?>
                                            <div style="font-size: 11.5px; color: #728277;">SKU: <?= htmlspecialchars($it['sku']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)$it['quantity'] ?></td>
                                    <td>₹<?= number_format($it['unit_price'], 2) ?></td>
                                    <td style="text-align: right;">₹<?= number_format($it['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #728277;">Order line items summarized in order record.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="inv-totals">
                    <div class="inv-totals-row">
                        <span>Subtotal:</span>
                        <span>₹<?= number_format($printOrder['subtotal'], 2) ?></span>
                    </div>
                    <div class="inv-totals-row">
                        <span>Shipping Charge:</span>
                        <span><?= $printOrder['shipping_charge'] > 0 ? '₹'.number_format($printOrder['shipping_charge'], 2) : 'FREE' ?></span>
                    </div>
                    <?php if ($printOrder['discount'] > 0 || $printOrder['coupon_discount'] > 0): ?>
                    <div class="inv-totals-row" style="color: #166534;">
                        <span>Discount:</span>
                        <span>- ₹<?= number_format($printOrder['discount'] + $printOrder['coupon_discount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="inv-totals-row grand">
                        <span>Grand Total:</span>
                        <span>₹<?= number_format($printOrder['total_amount'], 2) ?></span>
                    </div>
                </div>

                <div class="inv-footer">
                    Thank you for shopping with <strong>Biswas Enterprise</strong>. For support inquiries, please contact support@biswasenterprise.com.<br>
                    <em>This is a computer-generated tax invoice and requires no physical signature.</em>
                </div>
            </div>

            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}

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
            padding: 18px 0;
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
            font-size: 22px;
            margin-bottom: 2px;
            color: #ffffff;
        }

        .account-hero-text p {
            color: #b8ccbf;
            font-size: 13px;
            margin: 0;
        }

        .account-hero-actions {
            display: flex;
            gap: 12px;
        }

        .hero-btn-accent {
            background: #d4af37;
            color: #1b3b2b;
            padding: 7px 18px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 12.5px;
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
            padding: 7px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12.5px;
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
            padding: 24px 0 60px;
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
                    <a href="<?= url('wishlist.php') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="wishlist-badge" style="display:none;">0</span>
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
                        <a href="<?= url('account.php?tab=orders') ?>" class="stat-widget-card" style="text-decoration: none; color: inherit;">
                            <div class="stat-widget-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
                            </div>
                            <div>
                                <div class="stat-widget-val"><?= $ordersCount ?> <?= $ordersCount === 1 ? 'Order' : 'Orders' ?></div>
                                <div class="stat-widget-lbl">Total Orders</div>
                            </div>
                        </a>
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
                    <style>
                        /* Orders Tab Modern Styles */
                        .orders-header-wrap { display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #edf2ef; }
                        .orders-header-top  { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
                        .orders-page-title  { font-family: 'Merriweather', serif; font-size: 22px; color: #1a2721; display: flex; align-items: center; gap: 10px; margin: 0; }
                        .orders-search-box  { position: relative; min-width: 240px; }
                        .orders-search-input { width: 100%; padding: 10px 14px 10px 36px; border: 1.5px solid #dce8e0; border-radius: 10px; font-size: 13px; color: #1a2721; outline: none; transition: all 0.2s ease; background: #ffffff; }
                        .orders-search-input:focus { border-color: #1b3b2b; box-shadow: 0 0 0 3px rgba(27, 59, 43, 0.08); }
                        .orders-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #8fa095; pointer-events: none; }
                        .orders-filter-pills { display: flex; align-items: center; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
                        .orders-filter-pill  { background: #f1f6f3; color: #4a5c52; border: 1px solid #e1ebe4; padding: 7px 16px; border-radius: 30px; font-size: 12.5px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px; user-select: none; }
                        .orders-filter-pill:hover { background: #e4eee7; color: #1b3b2b; }
                        .orders-filter-pill.active { background: #1b3b2b; color: #ffffff; border-color: #1b3b2b; box-shadow: 0 4px 12px rgba(27, 59, 43, 0.18); }
                        .orders-filter-count { background: rgba(255, 255, 255, 0.25); color: inherit; font-size: 11px; padding: 2px 7px; border-radius: 12px; font-weight: 700; }
                        .orders-filter-pill:not(.active) .orders-filter-count { background: #e0eae3; color: #2b3d33; }

                        /* Order Cards */
                        .orders-list-wrapper { display: flex; flex-direction: column; gap: 20px; }
                        .order-card { background: #ffffff; border: 1.5px solid #e2ece5; border-radius: 18px; overflow: hidden; transition: all 0.25s ease; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02); }
                        .order-card:hover { border-color: #b8ccbf; box-shadow: 0 8px 24px rgba(27, 59, 43, 0.08); }
                        .order-card-header { background: #f8faf8; padding: 16px 20px; border-bottom: 1px solid #edf2ef; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
                        .order-meta-info { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
                        .order-number-badge { font-family: 'Merriweather', serif; font-size: 15px; font-weight: 700; color: #1a2721; display: inline-flex; align-items: center; gap: 6px; }
                        .order-copy-btn { border: none; background: #eef4f0; color: #1b3b2b; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.18s ease; display: inline-flex; align-items: center; gap: 4px; }
                        .order-copy-btn:hover { background: #1b3b2b; color: #ffffff; }
                        .order-date-text { font-size: 12.5px; color: #64746b; display: flex; align-items: center; gap: 5px; }
                        .order-header-right { display: flex; align-items: center; gap: 14px; }
                        .order-total-price { font-size: 16px; font-weight: 700; color: #1b3b2b; }

                        /* Status Badges */
                        .order-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 0.3px; text-transform: capitalize; }
                        .order-status-badge.status-pending    { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
                        .order-status-badge.status-processing { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
                        .order-status-badge.status-shipped    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
                        .order-status-badge.status-delivered  { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
                        .order-status-badge.status-cancelled  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
                        .status-pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; display: inline-block; animation: pulse 1.8s infinite; }
                        @keyframes pulse { 0% { opacity: 0.4; } 50% { opacity: 1; } 100% { opacity: 0.4; } }

                        /* Order Progress Stepper */
                        .order-tracker-bar { padding: 18px 24px 14px; background: #fafcfb; border-bottom: 1px solid #edf2ef; overflow-x: auto; }
                        .tracker-steps { display: flex; align-items: center; justify-content: space-between; position: relative; min-width: 320px; }
                        .tracker-steps-line-bg { position: absolute; top: 14px; left: 30px; right: 30px; height: 3px; background: #e2ece5; z-index: 1; }
                        .tracker-steps-line-fill { position: absolute; top: 14px; left: 30px; height: 3px; background: linear-gradient(90deg, #1b3b2b 0%, #2a523c 100%); z-index: 2; transition: width 0.4s ease; }
                        .tracker-step { position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; gap: 6px; }
                        .tracker-step-icon { width: 28px; height: 28px; border-radius: 50%; background: #ffffff; border: 2px solid #cbd7cf; color: #8fa095; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; transition: all 0.25s ease; }
                        .tracker-step.completed .tracker-step-icon { background: #1b3b2b; border-color: #1b3b2b; color: #ffffff; }
                        .tracker-step.active .tracker-step-icon { background: #ffffff; border-color: #d4af37; color: #1b3b2b; box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.2); }
                        .tracker-step-label { font-size: 11px; font-weight: 600; color: #728277; text-align: center; }
                        .tracker-step.completed .tracker-step-label, .tracker-step.active .tracker-step-label { color: #1a2721; font-weight: 700; }

                        /* Items List */
                        .order-items-list { padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
                        .order-item-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 10px 14px; border-radius: 12px; background: #ffffff; border: 1px solid #f0f5f2; transition: background 0.2s ease; }
                        .order-item-row:hover { background: #f8faf8; border-color: #e2ece5; }
                        .order-item-left { display: flex; align-items: center; gap: 14px; }
                        .order-item-thumb { width: 52px; height: 52px; border-radius: 10px; background: #f0f5f2; object-fit: cover; border: 1px solid #e1ebe4; display: flex; align-items: center; justify-content: center; color: #1b3b2b; flex-shrink: 0; font-size: 20px; }
                        .order-item-title { font-size: 14px; font-weight: 700; color: #1a2721; margin-bottom: 2px; text-decoration: none; display: inline-block; }
                        .order-item-title:hover { color: #1b3b2b; }
                        .order-item-sub { font-size: 12px; color: #728277; display: flex; align-items: center; gap: 8px; }
                        .order-item-qty { background: #eef4f0; color: #1b3b2b; font-weight: 700; font-size: 11.5px; padding: 2px 8px; border-radius: 6px; }
                        .order-item-price { font-size: 14px; font-weight: 700; color: #1a2721; text-align: right; }

                        /* Order Footer Actions */
                        .order-card-footer { padding: 14px 20px; background: #f8faf8; border-top: 1px solid #edf2ef; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
                        .btn-order-action { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; border: 1.5px solid #d4ddd7; background: #ffffff; color: #2c3e35; text-decoration: none; }
                        .btn-order-action:hover { border-color: #1b3b2b; color: #1b3b2b; background: #f0f6f2; }
                        .btn-order-action.primary { background: #1b3b2b; color: #ffffff; border-color: #1b3b2b; }
                        .btn-order-action.primary:hover { background: #2a523c; border-color: #2a523c; }

                        /* Expandable Drawer Panel */
                        .order-details-drawer { display: none; padding: 20px; background: #ffffff; border-top: 1px dashed #dce8e0; animation: fadeSlideDown 0.25s ease; }
                        .drawer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                        .drawer-box { background: #f8faf8; border: 1px solid #e5ede7; border-radius: 12px; padding: 16px; }
                        .drawer-box-title { font-size: 12.5px; font-weight: 700; color: #1a2721; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
                        .drawer-price-row { display: flex; justify-content: space-between; font-size: 13px; color: #4a5c52; margin-bottom: 6px; }
                        .drawer-price-row.total { border-top: 1px solid #e1ebe4; padding-top: 8px; margin-top: 8px; font-weight: 700; font-size: 15px; color: #1b3b2b; }

                        /* Refined Empty State UI */
                        .orders-empty-card { background: linear-gradient(145deg, #ffffff 0%, #f7faf8 100%); border: 1.5px solid #e2ece5; border-radius: 20px; padding: 50px 24px; text-align: center; box-shadow: 0 8px 30px rgba(27, 59, 43, 0.03); position: relative; overflow: hidden; }
                        .orders-empty-card::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 200px; height: 4px; background: linear-gradient(90deg, #1b3b2b 0%, #d4af37 50%, #2a523c 100%); border-radius: 0 0 4px 4px; }
                        .orders-empty-icon-wrap { width: 84px; height: 84px; border-radius: 50%; background: linear-gradient(135deg, rgba(27,59,43,0.08) 0%, rgba(212,175,55,0.15) 100%); border: 2px solid #e1ebe4; color: #1b3b2b; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04); }
                        .orders-empty-title { font-family: 'Merriweather', serif; font-size: 22px; font-weight: 700; color: #1a2721; margin-bottom: 8px; }
                        .orders-empty-desc { font-size: 14px; color: #64746b; max-width: 460px; margin: 0 auto 24px; line-height: 1.6; }
                        .orders-empty-actions { display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap; margin-bottom: 28px; }
                        .btn-orders-primary { background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%); color: #ffffff; padding: 12px 28px; border-radius: 50px; font-weight: 700; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 18px rgba(27, 59, 43, 0.22); transition: all 0.25s ease; }
                        .btn-orders-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(27, 59, 43, 0.3); color: #ffffff; }
                        .btn-orders-secondary { background: #ffffff; color: #1b3b2b; border: 1.5px solid #d4ddd7; padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.25s ease; }
                        .btn-orders-secondary:hover { border-color: #1b3b2b; background: #f4f8f5; color: #1b3b2b; }
                        .orders-empty-categories { border-top: 1px solid #edf2ef; padding-top: 22px; }
                        .empty-cat-title { font-size: 11.5px; font-weight: 700; color: #8fa095; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 12px; }
                        .empty-cat-pills { display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; }
                        .empty-cat-pill { background: #ffffff; border: 1px solid #e1ebe4; padding: 7px 16px; border-radius: 20px; font-size: 12.5px; font-weight: 600; color: #2c3e35; text-decoration: none; transition: all 0.2s ease; }
                        .empty-cat-pill:hover { border-color: #1b3b2b; color: #1b3b2b; background: #f0f6f2; }

                        @media (max-width: 640px) {
                            .drawer-grid { grid-template-columns: 1fr; }
                            .order-card-header { flex-direction: column; align-items: flex-start; }
                            .order-header-right { width: 100%; justify-content: space-between; }
                        }
                    </style>

                    <!-- Header & Filter Bar -->
                    <div class="orders-header-wrap">
                        <div class="orders-header-top">
                            <h2 class="orders-page-title">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                                <span>My Orders & Purchases</span>
                            </h2>

                            <?php if ($ordersCount > 0): ?>
                            <div class="orders-search-box">
                                <svg class="orders-search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" id="orderSearchInput" class="orders-search-input" placeholder="Search order # or product name..." onkeyup="searchOrders(this.value)">
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($ordersCount > 0): ?>
                        <!-- Status Filter Pills -->
                        <div class="orders-filter-pills">
                            <button type="button" class="orders-filter-pill active" onclick="filterOrders('all', this)">
                                <span>All Orders</span>
                                <span class="orders-filter-count"><?= $ordersCount ?></span>
                            </button>
                            <button type="button" class="orders-filter-pill" onclick="filterOrders('pending', this)">
                                <span>Pending</span>
                                <span class="orders-filter-count"><?= $ordersByStatus['pending'] ?></span>
                            </button>
                            <button type="button" class="orders-filter-pill" onclick="filterOrders('processing', this)">
                                <span>Processing</span>
                                <span class="orders-filter-count"><?= $ordersByStatus['processing'] ?></span>
                            </button>
                            <button type="button" class="orders-filter-pill" onclick="filterOrders('shipped', this)">
                                <span>Shipped</span>
                                <span class="orders-filter-count"><?= $ordersByStatus['shipped'] ?></span>
                            </button>
                            <button type="button" class="orders-filter-pill" onclick="filterOrders('delivered', this)">
                                <span>Delivered</span>
                                <span class="orders-filter-count"><?= $ordersByStatus['delivered'] ?></span>
                            </button>
                            <?php if ($ordersByStatus['cancelled'] > 0): ?>
                            <button type="button" class="orders-filter-pill" onclick="filterOrders('cancelled', this)">
                                <span>Cancelled</span>
                                <span class="orders-filter-count"><?= $ordersByStatus['cancelled'] ?></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($ordersCount === 0): ?>
                        <!-- REFINED EMPTY STATE -->
                        <div class="orders-empty-card">
                            <div class="orders-empty-icon-wrap">
                                <svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="#1b3b2b" stroke-width="1.6">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </div>
                            <h3 class="orders-empty-title">No Orders Placed Yet</h3>
                            <p class="orders-empty-desc">You haven't placed any orders with Biswas Enterprise yet. Explore our authentic range of Ayurvedic care, Arjuna bark remedies, and organic formulations.</p>
                            
                            <div class="orders-empty-actions">
                                <a href="<?= url('shop') ?>" class="btn-orders-primary">
                                    <span>Explore All Products</span>
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                </a>
                                <a href="<?= url('about') ?>" class="btn-orders-secondary">
                                    <span>Learn About Us</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- ORDERS CARDS LIST -->
                        <div class="orders-list-wrapper" id="ordersContainer">
                            <?php foreach ($userOrders as $order): ?>
                                <?php 
                                    $st = strtolower($order['order_status'] ?? 'pending');
                                    $paySt = ucfirst($order['payment_status'] ?? 'pending');
                                    $orderDate = date('M d, Y \a\t h:i A', strtotime($order['created_at']));
                                    
                                    // Progress calculations for tracker stepper
                                    $progressPercent = 25;
                                    $stepCompleted = [1 => true, 2 => false, 3 => false, 4 => false];
                                    if ($st === 'processing') {
                                        $progressPercent = 50;
                                        $stepCompleted[2] = true;
                                    } elseif ($st === 'shipped') {
                                        $progressPercent = 75;
                                        $stepCompleted[2] = true;
                                        $stepCompleted[3] = true;
                                    } elseif ($st === 'delivered') {
                                        $progressPercent = 100;
                                        $stepCompleted[2] = true;
                                        $stepCompleted[3] = true;
                                        $stepCompleted[4] = true;
                                    }
                                ?>
                                <div class="order-card" data-status="<?= $st ?>" data-search="<?= strtolower(htmlspecialchars($order['order_number'])) ?> <?= strtolower(htmlspecialchars(implode(' ', array_column($order['items'], 'product_name')))) ?>">
                                    <!-- Card Header -->
                                    <div class="order-card-header">
                                        <div class="order-meta-info">
                                            <span class="order-number-badge">
                                                #<?= htmlspecialchars($order['order_number']) ?>
                                                <button type="button" class="order-copy-btn" onclick="copyOrderNumber('<?= htmlspecialchars($order['order_number']) ?>')" title="Copy Order Number">
                                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                    <span>Copy</span>
                                                </button>
                                            </span>
                                            <span class="order-date-text">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                                <?= $orderDate ?>
                                            </span>
                                        </div>

                                        <div class="order-header-right">
                                            <span class="order-status-badge status-<?= $st ?>">
                                                <span class="status-pulse-dot"></span>
                                                <?= ucfirst($st) ?>
                                            </span>
                                            <span class="order-total-price">₹<?= number_format($order['total_amount'], 2) ?></span>
                                        </div>
                                    </div>

                                    <?php if ($st !== 'cancelled'): ?>
                                    <!-- Order Progress Stepper Bar -->
                                    <div class="order-tracker-bar">
                                        <div class="tracker-steps">
                                            <div class="tracker-steps-line-bg"></div>
                                            <div class="tracker-steps-line-fill" style="width: calc(<?= $progressPercent ?>% - 30px);"></div>

                                            <div class="tracker-step <?= $stepCompleted[1] ? 'completed' : '' ?>">
                                                <div class="tracker-step-icon">✓</div>
                                                <div class="tracker-step-label">Placed</div>
                                            </div>
                                            <div class="tracker-step <?= $stepCompleted[2] ? 'completed' : ($st==='pending'?'active':'') ?>">
                                                <div class="tracker-step-icon"><?= $stepCompleted[2] ? '✓' : '2' ?></div>
                                                <div class="tracker-step-label">Processing</div>
                                            </div>
                                            <div class="tracker-step <?= $stepCompleted[3] ? 'completed' : ($st==='processing'?'active':'') ?>">
                                                <div class="tracker-step-icon"><?= $stepCompleted[3] ? '✓' : '3' ?></div>
                                                <div class="tracker-step-label">Dispatched</div>
                                            </div>
                                            <div class="tracker-step <?= $stepCompleted[4] ? 'completed' : ($st==='shipped'?'active':'') ?>">
                                                <div class="tracker-step-icon"><?= $stepCompleted[4] ? '✓' : '4' ?></div>
                                                <div class="tracker-step-label">Delivered</div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Order Items Showcase -->
                                    <div class="order-items-list">
                                        <?php if (!empty($order['items'])): ?>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <div class="order-item-row">
                                                    <div class="order-item-left">
                                                        <?php if (!empty($item['product_image'])): ?>
                                                            <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="order-item-thumb">
                                                        <?php else: ?>
                                                            <div class="order-item-thumb">📦</div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <span class="order-item-title"><?= htmlspecialchars($item['product_name']) ?></span>
                                                            <div class="order-item-sub">
                                                                <span class="order-item-qty">Qty: <?= (int)$item['quantity'] ?></span>
                                                                <span>× ₹<?= number_format($item['unit_price'], 2) ?></span>
                                                                <?php if (!empty($item['sku'])): ?>
                                                                    <span>• SKU: <?= htmlspecialchars($item['sku']) ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="order-item-price">
                                                        ₹<?= number_format($item['total'], 2) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div style="font-size: 13px; color: #728277; padding: 8px 10px;">Order items record available in receipt summary.</div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Order Actions & Accordion Toggle -->
                                    <div class="order-card-footer">
                                        <button type="button" class="btn-order-action" onclick="toggleOrderDrawer(<?= $order['id'] ?>, this)">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            <span>View Details</span>
                                        </button>

                                        <div style="display: flex; gap: 8px;">
                                            <button type="button" class="btn-order-action" onclick="printOrderInvoice(<?= $order['id'] ?>)">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                                <span>Print Receipt</span>
                                            </button>
                                            <a href="<?= url('contact') ?>" class="btn-order-action primary">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                <span>Need Help?</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Expandable Drawer Content -->
                                    <div id="orderDrawer-<?= $order['id'] ?>" class="order-details-drawer">
                                        <div class="drawer-grid">
                                            <!-- Shipping Details -->
                                            <div class="drawer-box">
                                                <div class="drawer-box-title">
                                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#1b3b2b" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                                    Delivery Address
                                                </div>
                                                <div style="font-size: 13.5px; color: #3a4a40; line-height: 1.6;">
                                                    <strong><?= htmlspecialchars($order['ship_name'] ?? $currentUser['name']) ?></strong><br>
                                                    <?= htmlspecialchars($order['address_line_1'] ?? 'Default Customer Address') ?><br>
                                                    <?php if (!empty($order['address_line_2'])): ?><?= htmlspecialchars($order['address_line_2']) ?><br><?php endif; ?>
                                                    <?= htmlspecialchars($order['city'] ?? '') ?><?= !empty($order['state']) ? ', ' . htmlspecialchars($order['state']) : '' ?> <?= htmlspecialchars($order['postal_code'] ?? '') ?><br>
                                                    <?= htmlspecialchars($order['country'] ?? 'India') ?>
                                                    <?php if (!empty($order['ship_phone'])): ?>
                                                        <div style="margin-top: 4px; font-size: 12.5px; color: #728277;">📞 Phone: <?= htmlspecialchars($order['ship_phone']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Payment & Price Summary -->
                                            <div class="drawer-box">
                                                <div class="drawer-box-title">
                                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#1b3b2b" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                    Payment & Summary
                                                </div>
                                                <div class="drawer-price-row">
                                                    <span>Payment Method:</span>
                                                    <span style="font-weight:700; color:#1a2721; text-transform:uppercase;"><?= htmlspecialchars($order['payment_method'] ?? 'COD') ?></span>
                                                </div>
                                                <div class="drawer-price-row">
                                                    <span>Payment Status:</span>
                                                    <span style="font-weight:700; color:<?= strtolower($paySt)==='paid'?'#166534':'#92400e' ?>;"><?= $paySt ?></span>
                                                </div>
                                                <div class="drawer-price-row" style="margin-top: 8px;">
                                                    <span>Subtotal:</span>
                                                    <span>₹<?= number_format($order['subtotal'], 2) ?></span>
                                                </div>
                                                <div class="drawer-price-row">
                                                    <span>Shipping Charge:</span>
                                                    <span><?= $order['shipping_charge'] > 0 ? '₹'.number_format($order['shipping_charge'], 2) : 'FREE' ?></span>
                                                </div>
                                                <?php if ($order['discount'] > 0 || $order['coupon_discount'] > 0): ?>
                                                <div class="drawer-price-row" style="color: #166534;">
                                                    <span>Discount:</span>
                                                    <span>- ₹<?= number_format($order['discount'] + $order['coupon_discount'], 2) ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <div class="drawer-price-row total">
                                                    <span>Total Amount:</span>
                                                    <span>₹<?= number_format($order['total_amount'], 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <script>
                    function filterOrders(status, pillBtn) {
                        const pills = document.querySelectorAll('.orders-filter-pill');
                        pills.forEach(p => p.classList.remove('active'));
                        if (pillBtn) pillBtn.classList.add('active');

                        const cards = document.querySelectorAll('.order-card');
                        cards.forEach(card => {
                            const cardStatus = card.getAttribute('data-status');
                            if (status === 'all' || cardStatus === status) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }

                    function searchOrders(query) {
                        const q = query.toLowerCase().trim();
                        const cards = document.querySelectorAll('.order-card');
                        cards.forEach(card => {
                            const searchData = card.getAttribute('data-search') || '';
                            if (searchData.includes(q)) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }

                    function copyOrderNumber(ordNum) {
                        navigator.clipboard.writeText(ordNum).then(() => {
                            if (typeof showToastify === 'function') {
                                showToastify('Order #' + ordNum + ' copied to clipboard!', 'success');
                            } else {
                                alert('Order #' + ordNum + ' copied to clipboard!');
                            }
                        }).catch(() => {
                            alert('Order #' + ordNum);
                        });
                    }

                    function toggleOrderDrawer(orderId, btn) {
                        const drawer = document.getElementById('orderDrawer-' + orderId);
                        if (!drawer) return;
                        const isHidden = getComputedStyle(drawer).display === 'none';
                        drawer.style.display = isHidden ? 'block' : 'none';
                        btn.querySelector('span').textContent = isHidden ? 'Hide Details' : 'View Details';
                        btn.querySelector('svg').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                    }

                    function printOrderInvoice(orderId) {
                        window.open('<?= url("account.php?tab=orders") ?>&print_id=' + orderId, '_blank');
                    }
                    </script>

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
                        .sec-card { background:#fff; border:1.5px solid #e4ede8; border-radius:16px; padding:28px; width:100%; margin-bottom:24px; box-shadow:0 4px 16px rgba(27,59,43,0.03); }
                        .sec-card-title { font-size:16px; font-weight:700; color:#1a2721; margin:0 0 8px; display:flex; align-items:center; gap:8px; }
                        .sec-card-sub { font-size:13px; color:#6b7c72; margin-bottom:22px; line-height:1.5; }
                        .sec-form-grid { display:flex; flex-direction:column; gap:18px; }
                        .sec-fg { display:flex; flex-direction:column; gap:6px; }
                        .sec-fg label { font-size:11.5px; font-weight:700; color:#6b7c72; text-transform:uppercase; letter-spacing:0.6px; }
                        .sec-fg input { padding:12px 14px; border:1.5px solid #dce8e0; border-radius:9px; font-size:14px; color:#1a2721; background:#fff; outline:none; transition:border-color 0.2s,box-shadow 0.2s; font-family:inherit; }
                        .sec-fg input:focus { border-color:#1b3b2b; box-shadow:0 0 0 3px rgba(27,59,43,0.08); }
                        .sec-submit-btn { background:#1b3b2b; color:#fff; border:none; padding:12px 26px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; transition:background 0.2s,transform 0.15s; width:fit-content; display:inline-flex; align-items:center; gap:8px; margin-top:6px; }
                        .sec-submit-btn:hover { background:#2a523c; transform:translateY(-1px); }
                        .sec-flash { display:flex; align-items:center; gap:10px; padding:13px 18px; border-radius:10px; font-size:13.5px; font-weight:500; margin-bottom:20px; width:100%; }
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
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <?php include __DIR__ . '/includes/floating_enquiry.php'; ?>
</body>
</html>
