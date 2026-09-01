<?php
/**
 * Wishlist API Endpoint
 * Handles Adding, Removing, Toggling, and Fetching Wishlist Items
 * Biswas Enterprise E-Commerce
 */

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

initDatabaseTables();

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'require_login' => true,
        'message' => 'Please sign in to save items to your wishlist.'
    ]);
    exit;
}

$user = getCurrentUser();
$userId = (int)$user['id'];
$pdo = Database::getConnection();

// Get or create wishlist record for user
try {
    $stmt = $pdo->prepare("SELECT id FROM `wishlists` WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $wishlist = $stmt->fetch();

    if (!$wishlist) {
        $insertStmt = $pdo->prepare("INSERT INTO `wishlists` (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
        $insertStmt->execute([$userId]);
        $wishlistId = (int)$pdo->lastInsertId();
    } else {
        $wishlistId = (int)$wishlist['id'];
    }
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error initializing wishlist: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET request - return user's wishlist item product IDs
if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT product_id FROM `wishlist_items` WHERE wishlist_id = ? ORDER BY created_at DESC");
        $stmt->execute([$wishlistId]);
        $items = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'count' => count($items),
            'items' => array_map('strval', $items)
        ]);
        exit;
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle POST request - toggle or remove items
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'toggle';
    $productId = trim((string)($input['product_id'] ?? ''));

    if (empty($productId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }

    try {
        // Check if item is already in wishlist
        $stmt = $pdo->prepare("SELECT id FROM `wishlist_items` WHERE wishlist_id = ? AND product_id = ? LIMIT 1");
        $stmt->execute([$wishlistId, $productId]);
        $existingItem = $stmt->fetch();

        $isInWishlist = false;

        if ($action === 'remove' || ($action === 'toggle' && $existingItem)) {
            // Remove item
            if ($existingItem) {
                $del = $pdo->prepare("DELETE FROM `wishlist_items` WHERE id = ?");
                $del->execute([$existingItem['id']]);
            }
            $isInWishlist = false;
            $msg = 'Product removed from wishlist.';
        } else {
            // Add item
            if (!$existingItem) {
                $add = $pdo->prepare("INSERT INTO `wishlist_items` (wishlist_id, product_id, created_at) VALUES (?, ?, NOW())");
                $add->execute([$wishlistId, $productId]);
            }
            $isInWishlist = true;
            $msg = 'Product added to your wishlist!';
        }

        // Update wishlist updated_at timestamp
        $up = $pdo->prepare("UPDATE `wishlists` SET updated_at = NOW() WHERE id = ?");
        $up->execute([$wishlistId]);

        // Get total count
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist_items` WHERE wishlist_id = ?");
        $cntStmt->execute([$wishlistId]);
        $totalCount = (int)$cntStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'is_in_wishlist' => $isInWishlist,
            'count' => $totalCount,
            'message' => $msg
        ]);
        exit;
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating wishlist: ' . $e->getMessage()]);
        exit;
    }
}
