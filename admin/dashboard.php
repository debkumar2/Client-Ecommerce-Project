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

    // 6. ORDER ACTIONS (Status Updates & Deletion)
    if (isset($_POST['order_action'])) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $orderAction = $_POST['order_action'];
        if ($orderId > 0) {
            try {
                $pdo = Database::getConnection();
                if ($orderAction === 'update_status') {
                    $newOrderStatus = trim($_POST['order_status'] ?? 'pending');
                    $newPayStatus   = trim($_POST['payment_status'] ?? 'pending');
                    $stmt = $pdo->prepare("UPDATE `orders` SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newOrderStatus, $newPayStatus, $orderId]);
                    $actionMessage = 'Order #' . $orderId . ' status updated successfully!';
                } elseif ($orderAction === 'accept_order') {
                    $stmt = $pdo->prepare("UPDATE `orders` SET order_status = 'processing', updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$orderId]);
                    $actionMessage = 'Order #' . $orderId . ' accepted successfully! Status updated to Processing.';
                } elseif ($orderAction === 'delete_order') {
                    $pdo->prepare("DELETE FROM `order_items` WHERE order_id = ?")->execute([$orderId]);
                    $pdo->prepare("DELETE FROM `orders` WHERE id = ?")->execute([$orderId]);
                    $actionMessage = 'Order #' . $orderId . ' deleted successfully.';
                }
            } catch (\Throwable $e) {
                $actionMessage = 'Order Action Error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Data for Admin Dashboard
$recentUsers = [];
$recentEnquiries = [];
$allEnquiries = [];
$allUsers = [];
$allOrders = [];
$recentOrders = [];
$orderItemsMap = [];
$categoriesList = [];
$productsList = [];
$totalUsersCount = 0;
$totalAdminsCount = 0;
$totalEnquiriesCount = 0;
$pendingEnquiriesCount = 0;
$totalProductsCount = 0;
$totalCategoriesCount = 0;
$totalOrdersCount = 0;
$pendingOrdersCount = 0;

try {
    $pdo = Database::getConnection();
    
    // Orders
    $stmtOrdersCount = $pdo->query("SELECT COUNT(*) FROM `orders`");
    $totalOrdersCount = (int)$stmtOrdersCount->fetchColumn();

    $stmtPendingOrders = $pdo->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'pending'");
    $pendingOrdersCount = (int)$stmtPendingOrders->fetchColumn();

    $stmtOrders = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.email as user_email, u.phone as user_phone 
        FROM `orders` o 
        LEFT JOIN `users` u ON o.user_id = u.id 
        ORDER BY o.id DESC");
    $allOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    $recentOrders = array_slice($allOrders, 0, 5);

    if (!empty($allOrders)) {
        $stmtItems = $pdo->query("SELECT * FROM `order_items` ORDER BY id ASC");
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allItems as $item) {
            $orderItemsMap[$item['order_id']][] = $item;
        }
    }

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

    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <style>
        /* SweetAlert2 Custom Enterprise Styling */
        .swal2-popup {
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            border-radius: 18px !important;
            padding: 26px !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
        }
        .swal2-title {
            font-family: 'Merriweather', serif !important;
            font-size: 20px !important;
            color: #1b3b2b !important;
            font-weight: 700 !important;
        }
        .swal2-html-container {
            font-size: 14px !important;
            color: #4a5c51 !important;
        }
        .swal2-styled.swal2-confirm {
            border-radius: 8px !important;
            padding: 10px 22px !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
        }
        .swal2-styled.swal2-cancel {
            border-radius: 8px !important;
            padding: 10px 22px !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
        }

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
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            box-sizing: border-box;
            overflow-y: auto;
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
            min-width: 0;
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

        .admin-logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #dc2626 !important;
            color: #ffffff !important;
            border: 1px solid #dc2626 !important;
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }

        .admin-logout-btn:hover {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            box-shadow: 0 6px 16px rgba(185, 28, 28, 0.4);
            transform: translateY(-1px);
        }

        /* Quick Action Bar & Layout Grids */
        .quick-actions-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .category-page-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
        }

        /* Mobile Header & Sidebar Drawer */
        .admin-mobile-header {
            display: none;
            background: #1b3b2b;
            color: #ffffff;
            padding: 14px 20px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 998;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .admin-mobile-logo {
            font-family: 'Merriweather', serif;
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
        }
        .admin-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 6px;
            padding: 7px 13px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .admin-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        /* Media Queries for Admin Responsiveness */
        @media (max-width: 992px) {
            .admin-layout {
                flex-direction: column;
            }
            .admin-mobile-header {
                display: flex;
            }
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 270px;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            .admin-sidebar-overlay.mobile-open {
                display: block;
            }
            .admin-main {
                padding: 24px 20px;
            }
            .category-page-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                padding: 16px 20px;
            }
            .admin-top-bar a {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
            .stat-card {
                padding: 18px 16px;
            }
            .stat-card-value {
                font-size: 22px;
            }
            .data-card {
                padding: 20px 16px;
            }
            .data-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 640px) {
            .quick-actions-bar {
                flex-direction: column;
                gap: 10px;
            }
            .quick-actions-bar a {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
                justify-content: center;
            }
            .form-grid-2 {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .admin-table th, .admin-table td {
                padding: 10px 10px;
                font-size: 12px;
            }
            .product-thumb-sm {
                width: 38px;
                height: 38px;
            }
            .btn-action {
                padding: 5px 8px;
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 16px 12px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .stat-card-title {
                font-size: 11px;
            }
            .stat-card-value {
                font-size: 20px;
            }
            .admin-welcome h1 {
                font-size: 20px;
            }
            .admin-welcome p {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Top Header Bar -->
    <div class="admin-mobile-header">
        <div class="admin-mobile-logo">Biswas Admin</div>
        <button type="button" class="admin-sidebar-toggle" onclick="toggleAdminSidebar()" aria-label="Toggle Navigation">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            <span>Menu</span>
        </button>
    </div>

    <!-- Backdrop Overlay for Mobile Sidebar -->
    <div class="admin-sidebar-overlay" id="admin-sidebar-overlay" onclick="closeAdminSidebar()"></div>

    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div>
                <div class="admin-logo">
                    Biswas Admin
                </div>
                <ul class="admin-nav">
                    <li><a href="#" class="active" id="nav-overview" onclick="switchView('overview', this); return false;">Overview</a></li>
                    <li><a href="#" id="nav-orders" onclick="switchView('orders', this); return false;">Orders</a></li>
                    <li><a href="#" id="nav-products" onclick="switchView('products', this); return false;">Products Catalog</a></li>
                    <li><a href="#" id="nav-add-product" onclick="switchView('add-product', this); return false;">Add New Product</a></li>
                    <li><a href="#" id="nav-categories" onclick="switchView('categories', this); return false;">Categories</a></li>
                    <li><a href="#" id="nav-enquiries" onclick="switchView('enquiries', this); return false;">Wholesale Enquiries</a></li>
                    <li><a href="#" id="nav-users" onclick="switchView('users', this); return false;">Registered Users</a></li>
                    <li><a href="<?= url('shop') ?>" target="_blank">View Live Store</a></li>
                </ul>
            </div>
            <div style="padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.12); margin-top: 20px;">
                <a href="<?= url('account.php?action=logout') ?>" class="admin-logout-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    <span>Sign Out / Log Out</span>
                </a>
            </div>
        </aside>

        <!-- Admin Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div class="admin-welcome">
                    <h1>Administrator Dashboard</h1>
                    <!-- <p>Connected Database: Hostinger MySQL (u410000684_ecommerce) | Cloudinary CDN Enabled | Admin: <strong><?= htmlspecialchars($currentUser['email']) ?></strong></p> -->
                </div>
                <a href="<?= url('shop') ?>" target="_blank" class="btn-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">View Live Store &rarr;</a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card" style="cursor: pointer;" onclick="switchView('orders', document.getElementById('nav-orders'));">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="stat-card-title">Customer Orders</div>
                            <div class="stat-card-value" style="color: #10b981;"><?= number_format($totalOrdersCount) ?></div>
                        </div>
                        <div style="background: #e6f4ea; color: #10b981; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card" style="cursor: pointer;" onclick="switchView('products', document.getElementById('nav-products'));">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="stat-card-title">Live Products</div>
                            <div class="stat-card-value"><?= number_format($totalProductsCount) ?></div>
                        </div>
                        <div style="background: #f0f6f2; color: #1b3b2b; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card" style="cursor: pointer;" onclick="switchView('categories', document.getElementById('nav-categories'));">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="stat-card-title">Categories</div>
                            <div class="stat-card-value"><?= number_format($totalCategoriesCount) ?></div>
                        </div>
                        <div style="background: #f0f6f2; color: #1b3b2b; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card" style="cursor: pointer;" onclick="switchView('enquiries', document.getElementById('nav-enquiries'));">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="stat-card-title">Wholesale Enquiries</div>
                            <div class="stat-card-value highlight-gold"><?= number_format($totalEnquiriesCount) ?></div>
                        </div>
                        <div style="background: #fefce8; color: #b8860b; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card" style="cursor: pointer;" onclick="switchView('users', document.getElementById('nav-users'));">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="stat-card-title">Registered Customers</div>
                            <div class="stat-card-value"><?= number_format($totalUsersCount) ?></div>
                        </div>
                        <div style="background: #f0f6f2; color: #1b3b2b; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 1-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== OVERVIEW SECTION ===================== -->
            <div id="section-overview">

                <!-- Recent Orders Overview Card -->
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Recent Customer Orders</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Latest store purchases awaiting approval or fulfillment</div>
                        </div>
                        <a href="#" onclick="switchView('orders', document.getElementById('nav-orders')); return false;" style="font-size: 13px; font-weight: 600; color: #1b3b2b;">View All Orders &rarr;</a>
                    </div>
                    <?php if (empty($recentOrders)): ?>
                        <p style="color: #63756a; font-size: 14px;">No customer orders placed yet.</p>
                    <?php else: ?>
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentOrders as $ord): ?>
                                <?php 
                                    $customerName = trim(($ord['first_name'] ?? '') . ' ' . ($ord['last_name'] ?? ''));
                                    if (empty($customerName)) $customerName = 'Customer #' . ($ord['user_id'] ?: 'Guest');
                                    $oStatus = strtolower($ord['order_status'] ?? 'pending');
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($ord['order_number'] ?: ('ORD-' . $ord['id'])) ?></strong></td>
                                    <td><strong><?= htmlspecialchars($customerName) ?></strong><br><small style="color:#64746b"><?= htmlspecialchars($ord['user_email'] ?? 'N/A') ?></small></td>
                                    <td><strong>₹<?= number_format((float)$ord['total_amount'], 2) ?></strong></td>
                                    <td>
                                        <?php if ($oStatus === 'pending'): ?>
                                            <span class="badge-status badge-pending">Pending</span>
                                        <?php elseif ($oStatus === 'processing'): ?>
                                            <span class="badge-status" style="background:#e0f2fe; color:#0369a1;">Processing</span>
                                        <?php elseif ($oStatus === 'shipped'): ?>
                                            <span class="badge-status" style="background:#f3e8ff; color:#6b21a8;">Shipped</span>
                                        <?php elseif ($oStatus === 'delivered'): ?>
                                            <span class="badge-status badge-quoted">Delivered</span>
                                        <?php else: ?>
                                            <span class="badge-status" style="background:#fee2e2; color:#991b1b;">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($oStatus === 'pending'): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                            <input type="hidden" name="order_action" value="accept_order">
                                            <button type="submit" class="btn-action" style="background: #10b981; color: #ffffff; border: none; font-weight: 600; padding: 4px 10px; cursor: pointer; border-radius: 4px;">✓ Accept Order</button>
                                        </form>
                                        <?php else: ?>
                                            <a href="#" onclick="switchView('orders', document.getElementById('nav-orders')); return false;" class="btn-action">Manage</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
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
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
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
                                        <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode('Hello ' . $enq['full_name'] . ', regarding your enquiry for ' . ($enq['product_name'] ?: 'Biswas Enterprise products') . '...') ?>" target="_blank" class="btn-action btn-whatsapp">Reply</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== ORDERS MANAGEMENT SECTION ===================== -->
            <div id="section-orders" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header" style="flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h2 class="data-card-title">Customer Orders Management</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Manage store transactions, shipping statuses, &amp; payment tracking</div>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span style="font-size: 12px; font-weight: 700; background: #e8f0eb; color: #1b3b2b; padding: 6px 14px; border-radius: 50px;">
                                <?= count($allOrders) ?> Total Orders
                            </span>
                            <?php if ($pendingOrdersCount > 0): ?>
                            <span style="font-size: 12px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 50px;">
                                <?= $pendingOrdersCount ?> Pending Processing
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($allOrders)): ?>
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 50px 20px; background: #fafcfb; border: 1px dashed #d1ded6; border-radius: 12px; margin-top: 10px;">
                            <div style="width: 56px; height: 56px; background: #e8f0eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1b3b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </div>
                            <h3 style="font-size: 17px; font-weight: 700; color: #1b3b2b; margin: 0 0 6px 0;">No Orders Placed Yet</h3>
                            <p style="font-size: 13.5px; color: #647569; max-width: 420px; margin: 0; line-height: 1.5;">When customers place orders in the online shop, they will automatically appear here with full item breakdowns and status controls.</p>
                        </div>
                    <?php else: ?>
                    
                    <!-- Search & Filter Controls -->
                    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                        <input type="text" id="orderSearchInput" onkeyup="filterOrdersTable()" placeholder="Search Order #, Customer, or Email..." class="form-input" style="max-width: 320px; font-size: 13px; padding: 8px 12px;">
                        <select id="orderStatusFilter" onchange="filterOrdersTable()" class="form-input" style="max-width: 200px; font-size: 13px; padding: 8px 12px;">
                            <option value="all">All Order Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="admin-table" id="adminOrdersTable">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total Items</th>
                                    <th>Total Amount</th>
                                    <th>Payment</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allOrders as $ord): ?>
                                <?php 
                                    $oItems = $orderItemsMap[$ord['id']] ?? [];
                                    $itemCount = count($oItems);
                                    $customerName = trim(($ord['first_name'] ?? '') . ' ' . ($ord['last_name'] ?? ''));
                                    if (empty($customerName)) $customerName = 'Customer #' . ($ord['user_id'] ?: 'Guest');
                                    $customerEmail = $ord['user_email'] ?? 'N/A';
                                    $customerPhone = $ord['user_phone'] ?? 'N/A';
                                    $oStatus = strtolower($ord['order_status'] ?? 'pending');
                                    $pStatus = strtolower($ord['payment_status'] ?? 'pending');
                                ?>
                                <tr data-status="<?= htmlspecialchars($oStatus) ?>">
                                    <td><strong>#<?= htmlspecialchars($ord['order_number'] ?: ('ORD-' . $ord['id'])) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($customerName) ?></strong><br>
                                        <small style="color: #64746b;"><?= htmlspecialchars($customerEmail) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-quoted"><?= $itemCount ?> <?= $itemCount === 1 ? 'Item' : 'Items' ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: #1b3b2b;">₹<?= number_format((float)$ord['total_amount'], 2) ?></strong><br>
                                        <small style="color: #728277; text-transform: uppercase;"><?= htmlspecialchars($ord['payment_method'] ?? 'COD') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($pStatus === 'paid'): ?>
                                            <span class="badge-status badge-quoted">Paid</span>
                                        <?php elseif ($pStatus === 'failed' || $pStatus === 'refunded'): ?>
                                            <span class="badge-status" style="background:#fee2e2; color:#991b1b;"><?= ucfirst($pStatus) ?></span>
                                        <?php else: ?>
                                            <span class="badge-status badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($oStatus === 'pending'): ?>
                                            <span class="badge-status badge-pending">Pending</span>
                                        <?php elseif ($oStatus === 'processing'): ?>
                                            <span class="badge-status" style="background:#e0f2fe; color:#0369a1;">Processing</span>
                                        <?php elseif ($oStatus === 'shipped'): ?>
                                            <span class="badge-status" style="background:#f3e8ff; color:#6b21a8;">Shipped</span>
                                        <?php elseif ($oStatus === 'delivered'): ?>
                                            <span class="badge-status badge-quoted">Delivered</span>
                                        <?php else: ?>
                                            <span class="badge-status" style="background:#fee2e2; color:#991b1b;">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space: nowrap; font-size: 12px; color: #64746b;">
                                        <?= date('M d, Y h:i A', strtotime($ord['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                            <?php if ($oStatus === 'pending'): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                                <input type="hidden" name="order_action" value="accept_order">
                                                <button type="submit" class="btn-action" style="background: #10b981; color: #ffffff; border: none; font-weight: 600; padding: 4px 10px; cursor: pointer; border-radius: 4px;">✓ Accept Order</button>
                                            </form>
                                            <?php endif; ?>

                                            <button type="button" class="btn-action" onclick="openOrderStatusModal(<?= $ord['id'] ?>, '<?= htmlspecialchars($oStatus) ?>', '<?= htmlspecialchars($pStatus) ?>')">Update Status</button>
                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirmAction(event, 'Delete Order #<?= $ord['id'] ?>?', 'Are you sure you want to permanently delete this customer order record?');">
                                                <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                                <input type="hidden" name="order_action" value="delete_order">
                                                <button type="submit" class="btn-action btn-delete">Delete</button>
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

            <!-- ===================== PRODUCTS CATALOG SECTION ===================== -->
            <div id="section-products" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Products Catalog</h2>
                            <div style="font-size: 12px; color: #728277; margin-top: 2px;">Manage store products, prices, stock and categories in database</div>
                        </div>
                        <a href="#" onclick="switchView('add-product', document.getElementById('nav-add-product')); return false;" class="btn-primary" style="padding: 10px 18px; text-decoration: none; font-size: 13px;">
                            Add New Product
                        </a>
                    </div>
                    <?php if (empty($productsList)): ?>
                        <p style="color:#63756a;font-size:14px;">No products in catalog yet.</p>
                    <?php else: ?>
                    <div style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
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
                                        <form method="POST" action="" onsubmit="return confirmAction(event, 'Delete Product?', 'Are you sure you want to delete \'<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>\' from catalog?');">
                                            <input type="hidden" name="action_type" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn-action btn-delete">Delete</button>
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
                            <h2 class="data-card-title">Add New Product to Respective Category</h2>
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
                                Cloudinary Image Upload
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
                            Upload Image to Cloudinary &amp; Save Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- ===================== CATEGORIES MANAGEMENT SECTION ===================== -->
            <div id="section-categories" style="display:none;">
                <div class="category-page-grid">
                    
                    <!-- Left: Categories Table -->
                    <div class="data-card" style="margin-bottom:0;">
                        <div class="data-card-header">
                            <div>
                                <h2 class="data-card-title">Respective Categories</h2>
                                <div style="font-size: 12px; color: #728277; margin-top: 2px;">Categories fetched directly from MySQL categories table</div>
                            </div>
                        </div>
                        <div style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
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
                                            <form method="POST" action="" onsubmit="return confirmAction(event, 'Delete Category?', 'Are you sure you want to delete \'<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>\' category?');">
                                                <input type="hidden" name="action_type" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn-action btn-delete">Delete</button>
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
                        <h2 class="data-card-title" style="margin-bottom:16px;">Add Category</h2>
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
                                Create Category
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
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
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
                                            <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode('Hello ' . $enq['full_name'] . ', regarding your enquiry for ' . ($enq['product_name'] ?: 'Biswas Enterprise products') . '...') ?>" target="_blank" class="btn-action btn-whatsapp">Reply</a>
                                            
                                            <?php if ($enq['status'] === 'pending'): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                <input type="hidden" name="enquiry_action" value="mark_contacted">
                                                <button type="submit" class="btn-action">Contacted</button>
                                            </form>
                                            <?php endif; ?>

                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirmAction(event, 'Delete Enquiry Record?', 'Are you sure you want to delete this wholesale enquiry record?');">
                                                <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                <input type="hidden" name="enquiry_action" value="delete">
                                                <button type="submit" class="btn-action btn-delete">Delete</button>
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
                    <div style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
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

    <!-- Order Status Update Modal -->
    <div id="orderStatusModal" class="admin-sidebar-overlay" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
        <div style="background: #ffffff; width: 90%; max-width: 440px; border-radius: 14px; padding: 26px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); position: relative;">
            <h3 style="margin-top:0; font-size: 18px; font-weight: 700; color: #1b3b2b; margin-bottom: 16px;">Update Order Status</h3>
            <form method="POST" action="">
                <input type="hidden" name="order_action" value="update_status">
                <input type="hidden" name="order_id" id="modal_order_id" value="0">
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="modal_order_status" style="font-size: 12.5px; font-weight: 600; color: #4a5c52; margin-bottom: 6px; display: block;">Order Shipping & Processing Status</label>
                    <select name="order_status" id="modal_order_status" class="form-input" style="width: 100%; padding: 10px 12px; border-radius: 8px;">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="modal_payment_status" style="font-size: 12.5px; font-weight: 600; color: #4a5c52; margin-bottom: 6px; display: block;">Payment Status</label>
                    <select name="payment_status" id="modal_payment_status" class="form-input" style="width: 100%; padding: 10px 12px; border-radius: 8px;">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeOrderStatusModal()" class="btn-action" style="padding: 10px 18px;">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding: 10px 22px; font-size: 13px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('admin-sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('mobile-open');
        }
    }

    function closeAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('admin-sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('mobile-open');
        }
    }

    function openOrderStatusModal(orderId, orderStatus, payStatus) {
        const modal = document.getElementById('orderStatusModal');
        document.getElementById('modal_order_id').value = orderId;
        document.getElementById('modal_order_status').value = orderStatus || 'pending';
        document.getElementById('modal_payment_status').value = payStatus || 'pending';
        if (modal) modal.style.display = 'flex';
    }

    function closeOrderStatusModal() {
        const modal = document.getElementById('orderStatusModal');
        if (modal) modal.style.display = 'none';
    }

    function filterOrdersTable() {
        const query = (document.getElementById('orderSearchInput')?.value || '').toLowerCase();
        const status = document.getElementById('orderStatusFilter')?.value || 'all';
        const rows = document.querySelectorAll('#adminOrdersTable tbody tr');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
            const matchesQuery = !query || text.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;

            row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
        });
    }

    function switchView(view, clickedEl) {
        // Close mobile sidebar drawer if open
        closeAdminSidebar();

        // Hide all sections
        const sections = ['overview', 'orders', 'products', 'add-product', 'categories', 'enquiries', 'users'];
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
            btn.innerHTML = '<span class="btn-spinner"></span> Uploading to Cloudinary CDN &amp; Saving... Please wait';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        }
    });

    // Loader on Category Submit
    document.getElementById('add-category-form')?.addEventListener('submit', function() {
        const btn = document.getElementById('save-category-btn');
        if (btn) {
            btn.innerHTML = '<span class="btn-spinner"></span> Uploading Image &amp; Creating...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        }
    });

    // SweetAlert2 Confirmation Dialog Helper
    function confirmAction(event, titleText, bodyText = "This action cannot be undone!", confirmBtnText = "Yes, Delete It!") {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const form = event.target.closest('form');
        if (!form) return false;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: titleText,
                text: bodyText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(titleText + '\n' + bodyText)) {
                form.submit();
            }
        }
        return false;
    }

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
        if (['orders', 'products', 'add-product', 'categories', 'enquiries', 'users'].includes(hash)) {
            switchView(hash, null);
        }
    })();
    </script>
</body>
</html>
