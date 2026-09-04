<?php
/**
 * API Endpoint: Submit Wholesale / Product Enquiry
 * Saves enquiry details into MySQL `enquiries` table.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/response.php';

header('Content-Type: application/json');

initDatabaseTables();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Invalid request method. Only POST is allowed.'
    ], 405);
    exit;
}

// Read input (support JSON body or standard POST data)
$input = $_POST;
if (empty($input)) {
    $rawInput = file_get_contents('php://input');
    if ($rawInput) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
}

$fullName           = trim($input['full_name'] ?? $input['name'] ?? '');
$email              = trim($input['email'] ?? '');
$phone              = trim($input['phone'] ?? '');
$productName        = trim($input['product_name'] ?? $input['product'] ?? $input['subject'] ?? 'General Enquiry');
$requirementDetails = trim($input['requirement_details'] ?? $input['details'] ?? $input['message'] ?? '');
$quantity           = trim($input['quantity'] ?? '');
$destination        = trim($input['destination'] ?? $input['country'] ?? '');
$productId          = !empty($input['product_id']) ? (int)$input['product_id'] : null;

// Basic validation
if (empty($fullName) || empty($email) || empty($phone)) {
    jsonResponse([
        'success' => false,
        'message' => 'Please provide your full name, email address, and phone number.'
    ], 400);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ], 400);
    exit;
}

try {
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("INSERT INTO `enquiries` 
        (`full_name`, `email`, `phone`, `product_id`, `product_name`, `requirement_details`, `quantity`, `destination`, `status`, `created_at`, `updated_at`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");

    $stmt->execute([
        $fullName,
        $email,
        $phone,
        $productId,
        $productName,
        $requirementDetails,
        $quantity,
        $destination
    ]);

    $enquiryId = $pdo->lastInsertId();

    // Send Admin Notification Email
    require_once __DIR__ . '/../helpers/mail.php';
    sendEnquiryAdminNotification([
        'enquiry_id'          => $enquiryId,
        'full_name'           => $fullName,
        'email'               => $email,
        'phone'               => $phone,
        'product_name'        => $productName,
        'quantity'            => $quantity,
        'destination'         => $destination,
        'requirement_details' => $requirementDetails
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Thank you! Your enquiry has been received and stored in our database.',
        'enquiry_id' => $enquiryId
    ]);

} catch (\Throwable $e) {
    error_log('Enquiry Insertion Error: ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Database error while saving enquiry: ' . $e->getMessage()
    ], 500);
}
