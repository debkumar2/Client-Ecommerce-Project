<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cloudinary.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/products.php';

initDatabaseTables();

if (!isAdmin()) {
    header('Location: ' . url('login'));
    exit;
}

$currentUser = getCurrentUser();
$actionMessage = '';

// Handle POST actions (Enquiries, Categories, Products)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. ADD CATEGORY WITH CLOUDINARY UPLOAD
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_category') {
        $catName = trim($_POST['category_name'] ?? '');
        $catSlug = trim($_POST['category_slug'] ?? '');
        $catDesc = trim($_POST['category_description'] ?? '');
        $catImageUrlInput = trim($_POST['category_image_url'] ?? '');

        if (empty($catSlug)) {
            $catSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $catName), '-'));
        }

        $finalCatImageUrl = '';

        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['category_image']['tmp_name'])) {
            $uploadRes = uploadToCloudinary($_FILES['category_image']['tmp_name'], 'categories');
            if ($uploadRes['success']) {
                $finalCatImageUrl = $uploadRes['url'];
            }
        } elseif (!empty($catImageUrlInput)) {
            $uploadRes = uploadToCloudinary($catImageUrlInput, 'categories');
            if ($uploadRes['success']) {
                $finalCatImageUrl = $uploadRes['url'];
            } else {
                $finalCatImageUrl = $catImageUrlInput;
            }
        }

        if (empty($finalCatImageUrl)) {
            $finalCatImageUrl = 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80';
        }

        if (!empty($catName) && !empty($catSlug)) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("INSERT INTO `categories` (name, slug, description, image_url, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'approved', NOW(), NOW())");
                $stmt->execute([$catName, $catSlug, $catDesc, $finalCatImageUrl]);
                $actionMessage = 'Category "' . htmlspecialchars($catName) . '" created successfully!';
            } catch (\Throwable $e) {
                $actionMessage = 'Category Error: ' . $e->getMessage();
            }
        }
    }

    // 2. DELETE CATEGORY
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_category') {
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($catId > 0) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("DELETE FROM `categories` WHERE id = ?");
                $stmt->execute([$catId]);
                $actionMessage = 'Category deleted successfully.';
            } catch (\Throwable $e) {
                $actionMessage = 'Error deleting category: ' . $e->getMessage();
            }
        }
    }

    // 3. ADD PRODUCT WITH CLOUDINARY UPLOAD
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_product') {
        $prodName      = trim($_POST['product_name'] ?? '');
        $catSlug       = trim($_POST['category_slug'] ?? '');
        $price         = (float)($_POST['price'] ?? 0);
        $regularPrice  = (float)($_POST['regular_price'] ?? 0);
        $stock         = (int)($_POST['stock'] ?? 50);
        $brand         = trim($_POST['brand'] ?? 'Biswas Enterprise');
        $badge         = trim($_POST['badge'] ?? '');
        $shortDesc     = trim($_POST['short_desc'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $imageUrlInput = trim($_POST['image_url'] ?? '');

        // Resolve Category ID & Name
        $categoryId = 1;
        $catName = 'General';
        try {
            $pdo = Database::getConnection();
            $stmtC = $pdo->prepare("SELECT id, name FROM `categories` WHERE slug = ? LIMIT 1");
            $stmtC->execute([$catSlug]);
            if ($rowC = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                $categoryId = (int)$rowC['id'];
                $catName = $rowC['name'];
            }
        } catch (\Throwable $e) {}

        $finalImageUrl = '';

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['product_image']['tmp_name'])) {
            $uploadRes = uploadToCloudinary($_FILES['product_image']['tmp_name'], 'products');
            if ($uploadRes['success']) {
                $finalImageUrl = $uploadRes['url'];
            } else {
                $actionMessage = 'Cloudinary Upload Failed: ' . ($uploadRes['error'] ?? 'Unknown error');
            }
        } elseif (!empty($imageUrlInput)) {
            $uploadRes = uploadToCloudinary($imageUrlInput, 'products');
            if ($uploadRes['success']) {
                $finalImageUrl = $uploadRes['url'];
            } else {
                $finalImageUrl = $imageUrlInput;
            }
        }

        if (empty($finalImageUrl) && empty($actionMessage)) {
            $actionMessage = 'Please select a product image file to upload or enter an Image URL.';
        } elseif (!empty($prodName) && $price > 0 && !empty($finalImageUrl)) {
            if ($regularPrice <= 0) $regularPrice = round($price * 1.15);
            $prodSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $prodName), '-'));

            try {
                $pdo = Database::getConnection();
                $stmtP = $pdo->prepare("INSERT INTO `products` 
                    (category_id, name, slug, brand, selling_price, regular_price, stock_quantity, short_description, description, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW(), NOW())");
                $stmtP->execute([
                    $categoryId, $prodName, $prodSlug, $brand, $price, $regularPrice, $stock, $shortDesc, $description
                ]);
                $newProdId = (int)$pdo->lastInsertId();

                if ($newProdId > 0) {
                    $stmtImg = $pdo->prepare("INSERT INTO `product_images` (product_id, image_url, is_primary, created_at) VALUES (?, ?, 1, NOW())");
                    $stmtImg->execute([$newProdId, $finalImageUrl]);
                }

                $actionMessage = '🎉 Product "' . htmlspecialchars($prodName) . '" added successfully to category "' . htmlspecialchars($catName) . '" via Cloudinary CDN!';
            } catch (\Throwable $e) {
                $actionMessage = 'Database Error: ' . $e->getMessage();
            }
        }
    }

    // 4. DELETE PRODUCT
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_product') {
        $prodId = (int)($_POST['product_id'] ?? 0);
        if ($prodId > 0) {
            try {
                $pdo = Database::getConnection();
                $pdo->prepare("DELETE FROM `product_images` WHERE product_id = ?")->execute([$prodId]);
                $pdo->prepare("DELETE FROM `products` WHERE id = ?")->execute([$prodId]);
                $actionMessage = 'Product #' . $prodId . ' deleted successfully.';
            } catch (\Throwable $e) {
                $actionMessage = 'Error deleting product: ' . $e->getMessage();
            }
        }
    }

    // 5. ENQUIRY ACTIONS
    if (isset($_POST['enquiry_action'])) {
        $enquiryId = (int)($_POST['enquiry_id'] ?? 0);
        $enquiryAction = $_POST['enquiry_action'];
        if ($enquiryId > 0) {
            try {
                $pdo = Database::getConnection();
                if ($enquiryAction === 'mark_contacted') {
                    $stmt = $pdo->prepare("UPDATE `enquiries` SET status = 'contacted', contacted_at = NOW() WHERE id = ?");
                    $stmt->execute([$enquiryId]);
                    $actionMessage = 'Enquiry #' . $enquiryId . ' marked as Contacted.';
                } elseif ($enquiryAction === 'mark_quoted') {
                    $stmt = $pdo->prepare("UPDATE `enquiries` SET status = 'quoted', quoted_at = NOW() WHERE id = ?");
                    $stmt->execute([$enquiryId]);
                    $actionMessage = 'Enquiry #' . $enquiryId . ' marked as Quoted.';
                } elseif ($enquiryAction === 'delete') {
                    $stmt = $pdo->prepare("DELETE FROM `enquiries` WHERE id = ?");
                    $stmt->execute([$enquiryId]);
                    $actionMessage = 'Enquiry #' . $enquiryId . ' deleted successfully.';
                }
            } catch (\Throwable $e) {
                $actionMessage = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Data for Admin Dashboard
$recentUsers = [];
$recentEnquiries = [];
$allEnquiries = [];
$allUsers = [];
$categoriesList = [];
$productsList = [];
$totalUsersCount = 0;
$totalAdminsCount = 0;
$totalEnquiriesCount = 0;
$pendingEnquiriesCount = 0;
$totalProductsCount = 0;
$totalCategoriesCount = 0;

try {
    $pdo = Database::getConnection();
    
    // Users count
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM `users`");
    $totalUsersCount = (int)$stmtCount->fetchColumn();

    // Admins count
    $stmtAdminCount = $pdo->query("SELECT COUNT(*) FROM `admins`");
    $totalAdminsCount = (int)$stmtAdminCount->fetchColumn();

    // Enquiries
    $stmtEnqCount = $pdo->query("SELECT COUNT(*) FROM `enquiries`");
    $totalEnquiriesCount = (int)$stmtEnqCount->fetchColumn();

    $stmtPendingEnq = $pdo->query("SELECT COUNT(*) FROM `enquiries` WHERE status = 'pending'");
    $pendingEnquiriesCount = (int)$stmtPendingEnq->fetchColumn();

    $stmtEnquiries = $pdo->query("SELECT * FROM `enquiries` ORDER BY id DESC");
    $allEnquiries = $stmtEnquiries->fetchAll(PDO::FETCH_ASSOC);
    $recentEnquiries = array_slice($allEnquiries, 0, 5);

    // Registered Users
    $stmtUsers = $pdo->query("SELECT id, first_name, last_name, email, phone, status, created_at FROM `users` WHERE email NOT IN (SELECT email FROM `admins`) ORDER BY id DESC");
    $allUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
    $recentUsers = array_slice($allUsers, 0, 5);

    // Categories
    $stmtCats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM `products` p WHERE p.category_id = c.id) as product_count FROM `categories` c ORDER BY c.id ASC");
    $categoriesList = $stmtCats->fetchAll(PDO::FETCH_ASSOC);
    $totalCategoriesCount = count($categoriesList);

    // Products
    $productsList = getAllProducts();
    $totalProductsCount = count($productsList);
} catch (\Throwable $e) {
    error_log('Admin Dashboard Query Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Biswas Enterprise</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">

    <!-- Toastify CSS & JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
            background-color: #f4f7f4;
        }

        .admin-sidebar {
            width: 260px;
            background: #1b3b2b;
            color: #ffffff;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .admin-logo {
            font-family: 'Merriweather', serif;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-logo span {
            color: #d4af37;
        }

        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .admin-nav li {
            margin-bottom: 8px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #c0d1c7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .admin-nav a.active, .admin-nav a:hover {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        .admin-main {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .admin-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            background: #ffffff;
            padding: 20px 28px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .admin-welcome h1 {
            font-family: 'Merriweather', serif;
            font-size: 24px;
            color: #1a2721;
            margin: 0;
        }

        .admin-welcome p {
            font-size: 13px;
            color: #63756a;
            margin: 4px 0 0 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #e5ebe7;
        }

        .stat-card-title {
            font-size: 13px;
            color: #63756a;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            color: #1b3b2b;
            margin-top: 8px;
        }

        .stat-card-value.highlight-gold {
            color: #b8860b;
        }

        .data-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #e5ebe7;
            margin-bottom: 32px;
        }

        .data-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .data-card-title {
            font-family: 'Merriweather', serif;
            font-size: 18px;
            color: #1a2721;
            margin: 0;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th, .admin-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #edf2ee;
            font-size: 13.5px;
            vertical-align: middle;
        }

        .admin-table th {
            font-weight: 600;
            color: #55665c;
            background-color: #f8faf8;
        }

        .product-thumb-sm {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e2ebe5;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-contacted {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-quoted {
            background-color: #dcfce7;
            color: #166534;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #d0d7d3;
            background: #ffffff;
            color: #2c3e35;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-action:hover {
            background: #1b3b2b;
            color: #ffffff;
            border-color: #1b3b2b;
        }

        .btn-action.btn-delete {
            color: #dc2626;
            border-color: #fca5a5;
        }

        .btn-action.btn-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e35;
            margin-bottom: 6px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1ded6;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            color: #1b3b2b;
            transition: border-color 0.2s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #1b3b2b;
        }

        .cloudinary-box {
            background: #f0f6f2;
            border: 2px dashed #b8d4c1;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Submit Button Loading Spinner */
        .btn-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div>
                <div class="admin-logo">
                    <span>🌿</span> Biswas Admin
                </div>
                <ul class="admin-nav">
                    <li><a href="#" class="active" id="nav-overview" onclick="switchView('overview', this); return false;">📊 Overview</a></li>
                    <li><a href="#" id="nav-products" onclick="switchView('products', this); return false;">📦 Products Catalog</a></li>
                    <li><a href="#" id="nav-add-product" onclick="switchView('add-product', this); return false;">➕ Add New Product</a></li>
                    <li><a href="#" id="nav-categories" onclick="switchView('categories', this); return false;">🏷️ Categories</a></li>
                    <li><a href="#" id="nav-enquiries" onclick="switchView('enquiries', this); return false;">📩 Wholesale Enquiries</a></li>
                    <li><a href="#" id="nav-users" onclick="switchView('users', this); return false;">👥 Registered Users</a></li>
                    <li><a href="<?= url('shop') ?>" target="_blank">🛒 View Live Store</a></li>
                </ul>
            </div>
            <div>
                <a href="<?= url('account.php?action=logout') ?>" style="display: flex; align-items: center; gap: 8px; color: #f87171; text-decoration: none; font-size: 14px; font-weight: 600;">
                    <span>🚪 Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Admin Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div class="admin-welcome">
                    <h1>Administrator Dashboard</h1>
                    <p>Connected Database: Hostinger MySQL (u410000684_ecommerce) | Cloudinary CDN Enabled | Admin: <strong><?= htmlspecialchars($currentUser['email']) ?></strong></p>
                </div>
                <a href="<?= url('shop') ?>" target="_blank" class="btn-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">View Live Store &rarr;</a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Total Products</div>
                    <div class="stat-card-value"><?= number_format($totalProductsCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Product Categories</div>
                    <div class="stat-card-value"><?= number_format($totalCategoriesCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Total Enquiries</div>
                    <div class="stat-card-value highlight-gold"><?= number_format($totalEnquiriesCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Registered Customers</div>
                    <div class="stat-card-value"><?= number_format($totalUsersCount) ?></div>
                </div>
            </div>

            <!-- ===================== OVERVIEW SECTION ===================== -->
            <div id="section-overview">
                <!-- Quick Action Bar -->
                <div style="display:flex; gap: 12px; margin-bottom: 24px;">
                    <a href="#" onclick="switchView('add-product', document.getElementById('nav-add-product')); return false;" class="btn-primary" style="padding: 12px 20px; text-decoration: none; font-size: 13.5px;">
                        ➕ Add New Product (Cloudinary)
                    </a>
                    <a href="#" onclick="switchView('categories', document.getElementById('nav-categories')); return false;" class="btn-action" style="padding: 12px 20px; font-size: 13.5px;">
                        🏷️ Manage Categories
                    </a>
                </div>

                <!-- Recent Enquiries -->
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Recent Wholesale &amp; Product Enquiries</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Submissions from floating enquiry form &amp; contact page</div>
                        </div>
                        <a href="#" onclick="switchView('enquiries', document.getElementById('nav-enquiries')); return false;" style="font-size: 13px; font-weight: 600; color: #1b3b2b;">View All &rarr;</a>
                    </div>
                    <?php if (empty($recentEnquiries)): ?>
                        <p style="color: #63756a; font-size: 14px;">No enquiries received yet.</p>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Product / Requirement</th>
                                    <th>Quantity</th>
                                    <th>Phone / Destination</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentEnquiries as $enq): ?>
                                <tr>
                                    <td>#<?= $enq['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($enq['full_name']) ?></strong><br><small style="color:#647569"><?= htmlspecialchars($enq['email']) ?></small></td>
                                    <td><strong><?= htmlspecialchars($enq['product_name'] ?: 'General Wholesale') ?></strong><br><span style="font-size:11px;color:#718096;"><?= htmlspecialchars(substr($enq['requirement_details'] ?? '', 0, 50)) ?>...</span></td>
                                    <td><?= htmlspecialchars($enq['quantity'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($enq['phone']) ?><br><small style="color:#647569;"><?= htmlspecialchars($enq['destination'] ?: 'India') ?></small></td>
                                    <td>
                                        <?php if ($enq['status'] === 'pending'): ?>
                                            <span class="badge-status badge-pending">Pending</span>
                                        <?php elseif ($enq['status'] === 'contacted'): ?>
                                            <span class="badge-status badge-contacted">Contacted</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-quoted">Quoted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $waPhone = preg_replace('/[^0-9]/', '', $enq['phone']); ?>
                                        <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode('Hello ' . $enq['full_name'] . ', regarding your enquiry for ' . ($enq['product_name'] ?: 'Biswas Enterprise products') . '...') ?>" target="_blank" class="btn-action btn-whatsapp">💬 Reply</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Products Overview -->
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Live Products Catalog</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Recently created products in store</div>
                        </div>
                        <a href="#" onclick="switchView('products', document.getElementById('nav-products')); return false;" style="font-size: 13px; font-weight: 600; color: #1b3b2b;">View All Products &rarr;</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Respective Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Badge</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (array_slice($productsList, 0, 5) as $p): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-thumb-sm"></td>
                                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                    <td><span style="background:#e8f0eb;color:#1b3b2b;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:600;"><?= htmlspecialchars($p['category']) ?></span></td>
                                    <td><strong>&#8377;<?= number_format($p['price']) ?></strong> <small style="text-decoration:line-through;color:#999">&#8377;<?= number_format($p['regular_price']) ?></small></td>
                                    <td><?= (int)$p['stock_quantity'] ?> units</td>
                                    <td><?= !empty($p['badge']) ? '<span class="badge-status badge-pending">'.htmlspecialchars($p['badge']).'</span>' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===================== PRODUCTS CATALOG SECTION ===================== -->
            <div id="section-products" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Products Catalog</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Manage store products, prices, stock and categories in database</div>
                        </div>
                        <a href="#" onclick="switchView('add-product', document.getElementById('nav-add-product')); return false;" class="btn-primary" style="padding: 10px 18px; text-decoration: none; font-size: 13px;">
                            ➕ Add New Product
                        </a>
                    </div>
                    <?php if (empty($productsList)): ?>
                        <p style="color:#63756a;font-size:14px;">No products in catalog yet.</p>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Respective Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Badge</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($productsList as $p): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-thumb-sm"></td>
                                    <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small style="color:#647569"><?= htmlspecialchars($p['brand']) ?></small></td>
                                    <td><span style="background:#e8f0eb;color:#1b3b2b;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;"><?= htmlspecialchars($p['category']) ?></span></td>
                                    <td><strong>&#8377;<?= number_format($p['price']) ?></strong> <small style="text-decoration:line-through;color:#999">&#8377;<?= number_format($p['regular_price']) ?></small></td>
                                    <td><span class="badge-status badge-quoted"><?= (int)$p['stock_quantity'] ?> In Stock</span></td>
                                    <td><?= !empty($p['badge']) ? '<span class="badge-status badge-pending">'.htmlspecialchars($p['badge']).'</span>' : '-' ?></td>
                                    <td>
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="action_type" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn-action btn-delete">🗑 Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== ADD NEW PRODUCT SECTION ===================== -->
            <div id="section-add-product" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">➕ Add New Product to Respective Category</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Image will be uploaded directly to Cloudinary CDN via API (.env credentials)</div>
                        </div>
                    </div>

                    <form id="add-product-form" method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action_type" value="add_product">

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="product_name">Product Title / Name *</label>
                                <input type="text" id="product_name" name="product_name" class="form-input" placeholder="e.g. Organic Harad Powder 500g" required>
                            </div>

                            <div class="form-group">
                                <label for="category_slug">Respective Category *</label>
                                <select id="category_slug" name="category_slug" class="form-select" required>
                                    <option value="">-- Select Respective Category --</option>
                                    <?php foreach ($categoriesList as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['slug']) ?>">
                                            <?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['slug']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="price">Selling Price (₹) *</label>
                                <input type="number" step="0.01" id="price" name="price" class="form-input" placeholder="e.g. 450" required>
                            </div>

                            <div class="form-group">
                                <label for="regular_price">Regular / MRP Price (₹)</label>
                                <input type="number" step="0.01" id="regular_price" name="regular_price" class="form-input" placeholder="e.g. 550 (Optional)">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="stock">Initial Stock Quantity *</label>
                                <input type="number" id="stock" name="stock" class="form-input" value="50" required>
                            </div>

                            <div class="form-group">
                                <label for="badge">Product Badge / Tag (Optional)</label>
                                <input type="text" id="badge" name="badge" class="form-input" placeholder="e.g. BEST SELLER, POPULAR, PREMIUM, NEW">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="brand">Brand Name</label>
                            <input type="text" id="brand" name="brand" class="form-input" value="Biswas Enterprise">
                        </div>

                        <!-- Cloudinary Image Upload Box -->
                        <div class="cloudinary-box">
                            <h3 style="font-size: 15px; color: #1b3b2b; margin: 0 0 10px 0; font-family: 'Merriweather', serif;">
                                ☁️ Cloudinary Image Upload
                            </h3>
                            <p style="font-size: 12.5px; color: #55665c; margin: 0 0 14px 0;">
                                Select an image from your computer to upload directly to Cloudinary CDN, or enter a direct image URL.
                            </p>

                            <div class="form-group">
                                <label for="product_image">Option 1: Upload Local Image File to Cloudinary *</label>
                                <input type="file" id="product_image" name="product_image" accept="image/*" class="form-input">
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label for="image_url">Option 2: Or Provide Remote Image URL</label>
                                <input type="url" id="image_url" name="image_url" class="form-input" placeholder="https://images.unsplash.com/photo-...">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="short_desc">Short Summary</label>
                            <input type="text" id="short_desc" name="short_desc" class="form-input" placeholder="Brief 1-line product description...">
                        </div>

                        <div class="form-group">
                            <label for="description">Full Product Description</label>
                            <textarea id="description" name="description" class="form-textarea" rows="4" placeholder="Detailed product specifications, benefits, usage instructions..."></textarea>
                        </div>

                        <button type="submit" id="save-product-btn" class="btn-primary" style="background:#1b3b2b; color:#ffffff; padding: 14px 30px; font-size: 14px; font-weight: 600; border-radius: 8px; border:none; cursor:pointer; display:inline-flex; align-items:center;">
                            🚀 Upload Image to Cloudinary &amp; Save Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- ===================== CATEGORIES MANAGEMENT SECTION ===================== -->
            <div id="section-categories" style="display:none;">
                <div style="display:grid; grid-template-columns: 1fr 340px; gap: 24px;">
                    
                    <!-- Left: Categories Table -->
                    <div class="data-card" style="margin-bottom:0;">
                        <div class="data-card-header">
                            <div>
                                <h2 class="data-card-title">Respective Categories</h2>
                                <div style="font-size: 12px; color: #728277; margin-top: 2px;">Categories fetched directly from MySQL categories table</div>
                            </div>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Category Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($categoriesList as $cat): ?>
                                    <tr>
                                        <td>#<?= $cat['id'] ?></td>
                                        <td>
                                            <?php if (!empty($cat['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="product-thumb-sm">
                                            <?php else: ?>
                                                <span style="color:#aaa;">No img</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                                        <td><small style="color:#647569"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 60)) ?></small></td>
                                        <td><span class="badge-status badge-quoted"><?= (int)($cat['product_count'] ?? 0) ?> Products</span></td>
                                        <td>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                <input type="hidden" name="action_type" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn-action btn-delete">🗑 Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Add Category Form -->
                    <div class="data-card" style="margin-bottom:0; align-self: start;">
                        <h2 class="data-card-title" style="margin-bottom:16px;">🏷️ Add Category</h2>
                        <form id="add-category-form" method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="add_category">

                            <div class="form-group">
                                <label for="category_name">Category Name *</label>
                                <input type="text" id="category_name" name="category_name" class="form-input" placeholder="e.g. Organic Seeds" required>
                            </div>

                            <div class="form-group">
                                <label for="category_slug">Category Slug (URL Identifier)</label>
                                <input type="text" id="category_slug" name="category_slug" class="form-input" placeholder="e.g. organic-seeds (Optional)">
                            </div>

                            <div class="form-group">
                                <label for="category_image">Category Image File (Cloudinary)</label>
                                <input type="file" id="category_image" name="category_image" accept="image/*" class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="category_image_url">Or Category Image URL</label>
                                <input type="url" id="category_image_url" name="category_image_url" class="form-input" placeholder="https://images.unsplash.com/photo-...">
                            </div>

                            <div class="form-group">
                                <label for="category_description">Description</label>
                                <textarea id="category_description" name="category_description" class="form-textarea" rows="3" placeholder="Brief category description..."></textarea>
                            </div>

                            <button type="submit" id="save-category-btn" class="btn-primary" style="background:#1b3b2b; color:#ffffff; width:100%; padding: 12px; font-size: 13.5px; border-radius: 8px; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                                ➕ Create Category
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- ===================== ENQUIRIES SECTION ===================== -->
            <div id="section-enquiries" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Wholesale &amp; Product Enquiries Database</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Real-time Enquiry Submissions</div>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; background: #e8f0eb; color: #1b3b2b; padding: 4px 10px; border-radius: 50px;">
                            <?= count($allEnquiries) ?> Total Enquiries
                        </span>
                    </div>
                    <?php if (empty($allEnquiries)): ?>
                        <p style="color: #63756a; font-size: 14px;">No wholesale enquiries submitted yet.</p>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Phone</th>
                                    <th>Product Interested</th>
                                    <th>Qty &amp; Destination</th>
                                    <th>Requirement Details</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allEnquiries as $enq): ?>
                                <tr>
                                    <td>#<?= $enq['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($enq['full_name']) ?></strong></td>
                                    <td><a href="mailto:<?= htmlspecialchars($enq['email']) ?>"><?= htmlspecialchars($enq['email']) ?></a></td>
                                    <td><?= htmlspecialchars($enq['phone']) ?></td>
                                    <td><strong><?= htmlspecialchars($enq['product_name'] ?: 'General Wholesale') ?></strong></td>
                                    <td><?= htmlspecialchars($enq['quantity'] ?: 'N/A') ?><br><small style="color:#647569;"><?= htmlspecialchars($enq['destination'] ?: 'India') ?></small></td>
                                    <td style="max-width: 200px; font-size: 12px; color: #4a5568;"><?= htmlspecialchars($enq['requirement_details'] ?: 'No notes') ?></td>
                                    <td>
                                        <?php if ($enq['status'] === 'pending'): ?>
                                            <span class="badge-status badge-pending">Pending</span>
                                        <?php elseif ($enq['status'] === 'contacted'): ?>
                                            <span class="badge-status badge-contacted">Contacted</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-quoted">Quoted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $waPhone = preg_replace('/[^0-9]/', '', $enq['phone']); ?>
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                            <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode('Hello ' . $enq['full_name'] . ', regarding your enquiry for ' . ($enq['product_name'] ?: 'Biswas Enterprise products') . '...') ?>" target="_blank" class="btn-action btn-whatsapp">💬 Reply</a>
                                            
                                            <?php if ($enq['status'] === 'pending'): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                <input type="hidden" name="enquiry_action" value="mark_contacted">
                                                <button type="submit" class="btn-action">✓ Contacted</button>
                                            </form>
                                            <?php endif; ?>

                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this enquiry record?');">
                                                <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                <input type="hidden" name="enquiry_action" value="delete">
                                                <button type="submit" class="btn-action btn-delete">🗑 Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== USERS SECTION ===================== -->
            <div id="section-users" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Registered Users Database</h2>
                            <div style="font-size:12px;color:#728277;margin-top:2px;">All registered customer accounts</div>
                        </div>
                        <span style="font-size:12px;font-weight:700;background:#e8f0eb;color:#1b3b2b;padding:4px 10px;border-radius:50px;">
                            <?= count($allUsers) ?> Total Records
                        </span>
                    </div>
                    <?php if (empty($allUsers)): ?>
                        <p style="color:#63756a;font-size:14px;">No users registered yet.</p>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email Address</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></td>
                                    <td><span class="badge-status badge-quoted"><?= ucfirst($u['status']) ?></span></td>
                                    <td><?= date('M d, Y h:i A', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script>
    function switchView(view, clickedEl) {
        // Hide all sections
        const sections = ['overview', 'products', 'add-product', 'categories', 'enquiries', 'users'];
        sections.forEach(s => {
            const el = document.getElementById('section-' + s);
            if (el) el.style.display = 'none';
        });

        // Show target
        const target = document.getElementById('section-' + view);
        if (target) target.style.display = 'block';

        // Update active nav link
        document.querySelectorAll('.admin-nav a').forEach(a => a.classList.remove('active'));
        if (clickedEl) {
            clickedEl.classList.add('active');
        } else {
            const navEl = document.getElementById('nav-' + view);
            if (navEl) navEl.classList.add('active');
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Loader on Product Submit
    document.getElementById('add-product-form')?.addEventListener('submit', function() {
        const btn = document.getElementById('save-product-btn');
        if (btn) {
            btn.innerHTML = '<span class="btn-spinner"></span> ⏳ Uploading to Cloudinary CDN &amp; Saving... Please wait';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        }
    });

    // Loader on Category Submit
    document.getElementById('add-category-form')?.addEventListener('submit', function() {
        const btn = document.getElementById('save-category-btn');
        if (btn) {
            btn.innerHTML = '<span class="btn-spinner"></span> ⏳ Uploading Image &amp; Creating...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        }
    });

    // Toastify Notification Pop-up
    <?php if (!empty($actionMessage)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Toastify({
            text: <?= json_encode($actionMessage) ?>,
            duration: 4500,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            style: {
                background: "linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%)",
                boxShadow: "0 10px 25px rgba(0,0,0,0.25)",
                borderRadius: "10px",
                borderLeft: "5px solid #d4af37",
                color: "#ffffff",
                fontFamily: "Inter, sans-serif",
                fontSize: "14px",
                fontWeight: "600",
                padding: "14px 22px"
            }
        }).showToast();
    });
    <?php endif; ?>

    // On page load, check URL hash
    (function() {
        const hash = window.location.hash.replace('#', '');
        if (['products', 'add-product', 'categories', 'enquiries', 'users'].includes(hash)) {
            switchView(hash, null);
        }
    })();
    </script>
</body>
</html>
