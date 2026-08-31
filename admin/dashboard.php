<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

initDatabaseTables();

if (!isAdmin()) {
    header('Location: ' . url('login'));
    exit;
}

$currentUser = getCurrentUser();
$recentUsers = [];
$totalUsersCount = 0;
$totalAdminsCount = 0;

try {
    $pdo = Database::getConnection();
    
    // Count users
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM `users`");
    $totalUsersCount = (int)$stmtCount->fetchColumn();

    // Count admins
    $stmtAdminCount = $pdo->query("SELECT COUNT(*) FROM `admins`");
    $totalAdminsCount = (int)$stmtAdminCount->fetchColumn();

    // Fetch latest users
    $stmtUsers = $pdo->query("SELECT id, first_name, last_name, email, phone, status, created_at FROM `users` ORDER BY id DESC LIMIT 10");
    $recentUsers = $stmtUsers->fetchAll();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

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
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 40px;
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
            font-family: 'Playfair Display', serif;
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            font-weight: 600;
            color: #728277;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card-value {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: #1b3b2b;
        }

        .data-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #e5ebe7;
        }

        .data-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .data-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
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
            font-size: 14px;
        }

        .admin-table th {
            font-weight: 600;
            color: #55665c;
            background-color: #f8faf8;
        }

        .user-status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            background-color: #dcfce7;
            color: #166534;
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
                    <li><a href="#" class="active">📊 Overview</a></li>
                    <li><a href="<?= url('shop') ?>">🛒 View Website</a></li>
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
                    <p>Connected Database: Hostinger MySQL | Signed in as <strong><?= htmlspecialchars($currentUser['email']) ?></strong></p>
                </div>
                <a href="<?= url('shop') ?>" class="btn-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">View Live Store &rarr;</a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Registered Customers</div>
                    <div class="stat-card-value"><?= number_format($totalUsersCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">System Administrators</div>
                    <div class="stat-card-value"><?= number_format($totalAdminsCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Admin Account Email</div>
                    <div class="stat-card-value" style="font-size: 16px; font-family: 'Inter', sans-serif; color: #1b3b2b;">admin@123gmail.com</div>
                </div>
            </div>

            <!-- Recent Users Data Table -->
            <div class="data-card">
                <div class="data-card-header">
                    <h2 class="data-card-title">Registered Users Database</h2>
                    <span style="font-size: 13px; color: #728277;">Real-time SQL Records</span>
                </div>

                <?php if (empty($recentUsers)): ?>
                    <p style="color: #63756a; font-size: 14px;">No customer accounts registered yet in database.</p>
                <?php else: ?>
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
                            <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></td>
                                    <td><span class="user-status-badge"><?= htmlspecialchars(ucfirst($u['status'])) ?></span></td>
                                    <td><?= date('M d, Y h:i A', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
