<?php
/**
 * Authentication & Database User Helpers
 * Biswas Enterprise E-Commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/security.php';

/**
 * Ensure database tables exist and default Admin user is present.
 */
function initDatabaseTables(): void {
    static $initialized = false;
    if ($initialized) return;
    $initialized = true;

    try {
        $pdo = Database::getConnection();

        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `first_name` VARCHAR(100) NOT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(191) NOT NULL UNIQUE,
            `phone` VARCHAR(30) NULL,
            `password` VARCHAR(255) NOT NULL,
            `status` VARCHAR(20) DEFAULT 'active',
            `email_verified_at` TIMESTAMP NULL,
            `last_login_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Create admins table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `first_name` VARCHAR(100) NOT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(191) NOT NULL UNIQUE,
            `phone` VARCHAR(30) NULL,
            `password` VARCHAR(255) NOT NULL,
            `status` VARCHAR(20) DEFAULT 'active',
            `last_login_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Create enquiries table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `enquiries` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(191) NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `phone` VARCHAR(50) NOT NULL,
            `product_id` BIGINT UNSIGNED NULL,
            `product_name` VARCHAR(191) NULL,
            `requirement_details` TEXT NULL,
            `quantity` VARCHAR(100) NULL,
            `destination` VARCHAR(191) NULL,
            `status` VARCHAR(30) DEFAULT 'pending',
            `admin_notes` TEXT NULL,
            `contacted_at` TIMESTAMP NULL,
            `quoted_at` TIMESTAMP NULL,
            `closed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Create addresses table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `addresses` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `full_name` VARCHAR(191) NOT NULL,
            `phone` VARCHAR(30) NULL,
            `address_line_1` VARCHAR(255) NOT NULL,
            `address_line_2` VARCHAR(255) NULL,
            `city` VARCHAR(100) NOT NULL,
            `state` VARCHAR(100) NOT NULL,
            `postal_code` VARCHAR(20) NOT NULL,
            `country` VARCHAR(100) DEFAULT 'India',
            `address_type` VARCHAR(20) DEFAULT 'home',
            `is_default` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Create wishlists table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `wishlists` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Create wishlist_items table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `wishlist_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `wishlist_id` BIGINT UNSIGNED NOT NULL,
            `product_id` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_wishlist_item` (`wishlist_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Safely drop any legacy strict foreign key constraint on wishlist_items if present
        try {
            $pdo->exec("ALTER TABLE `wishlist_items` DROP FOREIGN KEY `fk_wishlist_item_product`");
        } catch (\Throwable $e) {}

        // Seed default Admin user if not existing in admins table
        $adminEmail = 'admin@123gmail.com';
        $adminPassRaw = 'Admin@2026';
        $hashedPass = password_hash($adminPassRaw, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("SELECT id, password FROM `admins` WHERE email = ? LIMIT 1");
        $stmt->execute([$adminEmail]);
        $existingAdmin = $stmt->fetch();

        if (!$existingAdmin) {
            $insertStmt = $pdo->prepare("INSERT INTO `admins` 
                (first_name, last_name, email, phone, password, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
            $insertStmt->execute(['System', 'Admin', $adminEmail, '9876543210', $hashedPass]);
        }

        // NOTE: Admins are stored ONLY in the `admins` table, not in `users`.

    } catch (\Throwable $e) {
        // Table initialization fail-safe error logging
        error_log('Database init warning: ' . $e->getMessage());
    }
}

/**
 * Check if a user/admin is currently logged in.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Check if logged in user is an Administrator.
 */
function isAdmin(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'admin');
}

/**
 * Get current session user data.
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? 'User',
        'last_name' => $_SESSION['last_name'] ?? '',
        'name' => trim(($_SESSION['first_name'] ?? 'User') . ' ' . ($_SESSION['last_name'] ?? '')),
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'phone' => $_SESSION['user_phone'] ?? ''
    ];
}

/**
 * Attempt to authenticate user or admin.
 */
function loginUser(string $email, string $password): array {
    initDatabaseTables();

    $email = strtolower(trim($email));

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please enter both email address and password.'];
    }

    try {
        $pdo = Database::getConnection();

        // 1. Check in admins table first
        $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            $passValid = password_verify($password, $admin['password']) || ($password === $admin['password']);
            
            if ($passValid) {
                // Update hashed password if it was plain text
                if (!password_verify($password, $admin['password'])) {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $updatePass = $pdo->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
                    $updatePass->execute([$newHash, $admin['id']]);
                }

                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['first_name'] = $admin['first_name'];
                $_SESSION['last_name'] = $admin['last_name'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_phone'] = $admin['phone'] ?? '';
                $_SESSION['user_role'] = 'admin';

                // Update last login
                $up = $pdo->prepare("UPDATE `admins` SET last_login_at = NOW() WHERE id = ?");
                $up->execute([$admin['id']]);

                return ['success' => true, 'role' => 'admin', 'message' => 'Admin login successful! Welcome back.'];
            }
        }

        // 2. Check in users table
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $passValid = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($passValid) {
                $role = ($user['email'] === 'admin@123gmail.com') ? 'admin' : 'user';

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['user_role'] = $role;

                // Update last login
                $up = $pdo->prepare("UPDATE `users` SET last_login_at = NOW() WHERE id = ?");
                $up->execute([$user['id']]);

                return ['success' => true, 'role' => $role, 'message' => 'Login successful! Welcome back, ' . htmlspecialchars($user['first_name']) . '.'];
            }
        }

        return ['success' => false, 'message' => 'Invalid email address or password. Please check your credentials.'];

    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()];
    }
}

/**
 * Register a new user into the database.
 */
function registerUser(string $firstName, string $lastName, string $email, string $phone, string $password, string $confirmPassword): array {
    initDatabaseTables();

    $firstName = trim($firstName);
    $lastName  = trim($lastName);
    $email     = strtolower(trim($email));
    $phone     = trim($phone);

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please fill in all required fields.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Password and Confirm Password do not match.'];
    }

    try {
        $pdo = Database::getConnection();

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM `users` WHERE email = ? UNION SELECT id FROM `admins` WHERE email = ? LIMIT 1");
        $stmt->execute([$email, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'An account with this email address already exists. Please login instead.'];
        }

        // Hash password
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $insert = $pdo->prepare("INSERT INTO `users` 
            (first_name, last_name, email, phone, password, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
        $insert->execute([$firstName, $lastName, $email, $phone, $hashedPass]);
        $pdo->lastInsertId();

        // Do NOT auto-login — user must login manually with their credentials.
        return ['success' => true, 'message' => 'Account created successfully! Please login with your credentials.'];

    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Log out user/admin session.
 */
function logoutUser(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
