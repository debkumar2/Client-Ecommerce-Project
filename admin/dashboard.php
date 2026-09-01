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
$recentEnquiries = [];
$totalUsersCount = 0;
$totalAdminsCount = 0;
$totalEnquiriesCount = 0;
$pendingEnquiriesCount = 0;
$actionMessage = '';

// Handle POST actions for updating status or deleting enquiries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_action'])) {
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

try {
    $pdo = Database::getConnection();
    
    // Count users
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM `users`");
    $totalUsersCount = (int)$stmtCount->fetchColumn();

    // Count admins
    $stmtAdminCount = $pdo->query("SELECT COUNT(*) FROM `admins`");
    $totalAdminsCount = (int)$stmtAdminCount->fetchColumn();

    // Count total enquiries
    $stmtEnqCount = $pdo->query("SELECT COUNT(*) FROM `enquiries`");
    $totalEnquiriesCount = (int)$stmtEnqCount->fetchColumn();

    // Count pending enquiries
    $stmtPendingEnq = $pdo->query("SELECT COUNT(*) FROM `enquiries` WHERE status = 'pending'");
    $pendingEnquiriesCount = (int)$stmtPendingEnq->fetchColumn();

    // Fetch ALL enquiries
    $stmtEnquiries = $pdo->query("SELECT * FROM `enquiries` ORDER BY id DESC");
    $allEnquiries = $stmtEnquiries->fetchAll();
    $recentEnquiries = array_slice($allEnquiries, 0, 5);

    // Fetch ALL users (exclude any admins)
    $stmtUsers = $pdo->query("SELECT id, first_name, last_name, email, phone, status, created_at FROM `users` WHERE email NOT IN (SELECT email FROM `admins`) ORDER BY id DESC");
    $allUsers = $stmtUsers->fetchAll();
    $recentUsers = array_slice($allUsers, 0, 5);
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
            font-size: 12px;
            font-weight: 700;
            color: #728277;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card-value {
            font-family: 'Merriweather', serif;
            font-size: 30px;
            font-weight: 700;
            color: #1b3b2b;
        }

        .stat-card-value.highlight-gold {
            color: #d4af37;
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
            margin-bottom: 20px;
        }

        .data-card-title {
            font-family: 'Merriweather', serif;
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
            font-size: 13.5px;
            vertical-align: top;
        }

        .admin-table th {
            font-weight: 600;
            color: #55665c;
            background-color: #f8faf8;
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
            padding: 5px 10px;
            font-size: 11px;
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

        .btn-action.btn-whatsapp {
            background: #25d366;
            color: #ffffff;
            border-color: #25d366;
        }

        .btn-action.btn-whatsapp:hover {
            background: #1da851;
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

        .alert-toast {
            padding: 12px 18px;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
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
                    <li><a href="#" class="active" onclick="switchView('overview', this); return false;">📊 Overview</a></li>
                    <li><a href="#" onclick="switchView('enquiries', this); return false;">📩 Wholesale Enquiries</a></li>
                    <li><a href="#" onclick="switchView('users', this); return false;">👥 Registered Users</a></li>
                    <li><a href="<?= url('shop') ?>">🛒 View Live Store</a></li>
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

            <?php if (!empty($actionMessage)): ?>
                <div class="alert-toast">
                    ✅ <?= htmlspecialchars($actionMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Total Enquiries</div>
                    <div class="stat-card-value highlight-gold"><?= number_format($totalEnquiriesCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Pending Enquiries</div>
                    <div class="stat-card-value"><?= number_format($pendingEnquiriesCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Registered Customers</div>
                    <div class="stat-card-value"><?= number_format($totalUsersCount) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">System Administrators</div>
                    <div class="stat-card-value"><?= number_format($totalAdminsCount) ?></div>
                </div>
            </div>

            <!-- ===================== OVERVIEW SECTION ===================== -->
            <div id="section-overview">

            <!-- Wholesale & Export Enquiries Data Table (Overview: 5 records) -->
            <div class="data-card">
                <div class="data-card-header">
                    <div>
                        <h2 class="data-card-title">Wholesale &amp; Product Enquiries Database</h2>
                        <div style="font-size: 12px; color: #728277; margin-top: 2px;">Real-time SQL Enquiry Submissions from Floating Popup &amp; Contact Page</div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-size: 12px; font-weight: 700; background: #e8f0eb; color: #1b3b2b; padding: 4px 10px; border-radius: 50px;">
                            Showing 5 of <?= count($allEnquiries) ?>
                        </span>
                        <a href="#" onclick="switchView('enquiries', null); return false;" style="font-size:12px;color:#1b3b2b;font-weight:600;text-decoration:underline;">View All →</a>
                    </div>
                </div>

                <?php if (empty($recentEnquiries)): ?>
                    <p style="color: #63756a; font-size: 14px; padding: 20px 0;">No wholesale or export enquiries received yet in database.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Details</th>
                                    <th>Product / Subject</th>
                                    <th>Requirement Details</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEnquiries as $enq): ?>
                                    <tr>
                                        <td>#<?= $enq['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($enq['full_name']) ?></strong><br>
                                            <span style="font-size: 12px; color: #555;">✉️ <?= htmlspecialchars($enq['email']) ?></span><br>
                                            <span style="font-size: 12px; color: #555;">📞 <?= htmlspecialchars($enq['phone']) ?></span>
                                        </td>
                                        <td>
                                            <strong style="color: #1b3b2b;"><?= htmlspecialchars($enq['product_name'] ?: 'General') ?></strong>
                                            <?php if (!empty($enq['quantity'])): ?>
                                                <br><span style="font-size: 11px; color: #728277;">Qty: <?= htmlspecialchars($enq['quantity']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 250px;">
                                            <div style="font-size: 13px; color: #333; line-height: 1.4;"><?= nl2br(htmlspecialchars($enq['requirement_details'] ?: 'No details specified')) ?></div>
                                            <?php if (!empty($enq['destination'])): ?>
                                                <span style="font-size: 11px; color: #15803d; font-weight: 600;">Destination: <?= htmlspecialchars($enq['destination']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $st = strtolower($enq['status'] ?? 'pending');
                                            $badgeClass = $st === 'contacted' ? 'badge-contacted' : ($st === 'quoted' ? 'badge-quoted' : 'badge-pending');
                                            ?>
                                            <span class="badge-status <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($st)) ?></span>
                                        </td>
                                        <td style="font-size: 12px; color: #666;">
                                            <?= date('M d, Y', strtotime($enq['created_at'])) ?><br>
                                            <span style="color: #888;"><?= date('h:i A', strtotime($enq['created_at'])) ?></span>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                                <?php 
                                                $phoneClean = preg_replace('/[^0-9]/', '', $enq['phone']);
                                                if (!empty($phoneClean)):
                                                    $waMsg = rawurlencode("Hello " . $enq['full_name'] . ", regarding your inquiry for " . $enq['product_name'] . " on Biswas Enterprise:");
                                                ?>
                                                    <a href="https://api.whatsapp.com/send?phone=<?= $phoneClean ?>&text=<?= $waMsg ?>" target="_blank" class="btn-action btn-whatsapp" title="Chat on WhatsApp">
                                                        💬 WhatsApp
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($st === 'pending'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                        <input type="hidden" name="enquiry_action" value="mark_contacted">
                                                        <button type="submit" class="btn-action" title="Mark as Contacted">✓ Contacted</button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete Enquiry #<?= $enq['id'] ?>?');">
                                                    <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                    <input type="hidden" name="enquiry_action" value="delete">
                                                    <button type="submit" class="btn-action btn-delete" title="Delete Record">🗑 Delete</button>
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

            <!-- Recent Users Data Table (Overview: 5 records) -->
            <div class="data-card">
                <div class="data-card-header">
                    <h2 class="data-card-title">Registered Users Database</h2>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-size:13px;color:#728277;">Showing 5 of <?= count($allUsers) ?></span>
                        <a href="#" onclick="switchView('users', null); return false;" style="font-size:12px;color:#1b3b2b;font-weight:600;text-decoration:underline;">View All →</a>
                    </div>
                </div>

                <?php if (empty($recentUsers)): ?>
                    <p style="color: #63756a; font-size: 14px;">No customer accounts registered yet in database.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
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
                                        <td><span class="badge-status badge-quoted"><?= htmlspecialchars(ucfirst($u['status'])) ?></span></td>
                                        <td><?= date('M d, Y h:i A', strtotime($u['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            </div><!-- end #section-overview -->

            <!-- ===================== ENQUIRIES FULL SECTION ===================== -->
            <div id="section-enquiries" style="display:none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="data-card-title">Wholesale &amp; Product Enquiries</h2>
                            <div style="font-size:12px;color:#728277;margin-top:2px;">All enquiry submissions from Floating Popup &amp; Contact Page</div>
                        </div>
                        <span style="font-size:12px;font-weight:700;background:#e8f0eb;color:#1b3b2b;padding:4px 10px;border-radius:50px;">
                            <?= count($allEnquiries) ?> Total Records
                        </span>
                    </div>
                    <?php if (empty($allEnquiries)): ?>
                        <p style="color:#63756a;font-size:14px;padding:20px 0;">No enquiries received yet.</p>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead><tr>
                                <th>ID</th><th>Client Details</th><th>Product / Subject</th>
                                <th>Requirement Details</th><th>Status</th><th>Submitted At</th>
                                <th style="text-align:right;">Actions</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($allEnquiries as $enq): ?>
                                <tr>
                                    <td>#<?= $enq['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($enq['full_name']) ?></strong><br>
                                        <span style="font-size:12px;color:#555;">✉️ <?= htmlspecialchars($enq['email']) ?></span><br>
                                        <span style="font-size:12px;color:#555;">📞 <?= htmlspecialchars($enq['phone']) ?></span>
                                    </td>
                                    <td><strong style="color:#1b3b2b;"><?= htmlspecialchars($enq['product_name'] ?: 'General') ?></strong>
                                        <?php if (!empty($enq['quantity'])): ?><br><span style="font-size:11px;color:#728277;">Qty: <?= htmlspecialchars($enq['quantity']) ?></span><?php endif; ?>
                                    </td>
                                    <td style="max-width:250px;"><div style="font-size:13px;color:#333;line-height:1.4;"><?= nl2br(htmlspecialchars($enq['requirement_details'] ?: 'No details')) ?></div>
                                        <?php if (!empty($enq['destination'])): ?><span style="font-size:11px;color:#15803d;font-weight:600;">Destination: <?= htmlspecialchars($enq['destination']) ?></span><?php endif; ?>
                                    </td>
                                    <td><?php $st=strtolower($enq['status']??'pending'); $bc=$st==='contacted'?'badge-contacted':($st==='quoted'?'badge-quoted':'badge-pending'); ?>
                                        <span class="badge-status <?= $bc ?>"><?= ucfirst($st) ?></span>
                                    </td>
                                    <td style="font-size:12px;color:#666;"><?= date('M d, Y', strtotime($enq['created_at'])) ?><br><span style="color:#888;"><?= date('h:i A', strtotime($enq['created_at'])) ?></span></td>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                            <?php $phoneClean=preg_replace('/[^0-9]/','', $enq['phone']); if(!empty($phoneClean)): $waMsg=rawurlencode("Hello ".$enq['full_name'].", regarding your inquiry for ".$enq['product_name']." on Biswas Enterprise:"); ?>
                                                <a href="https://api.whatsapp.com/send?phone=<?= $phoneClean ?>&text=<?= $waMsg ?>" target="_blank" class="btn-action btn-whatsapp">💬 WhatsApp</a>
                                            <?php endif; ?>
                                            <?php if($st==='pending'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
                                                    <input type="hidden" name="enquiry_action" value="mark_contacted">
                                                    <button type="submit" class="btn-action">✓ Contacted</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete Enquiry #<?= $enq['id'] ?>?');">
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

            <!-- ===================== USERS FULL SECTION ===================== -->
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
                            <thead><tr>
                                <th>ID</th><th>Name</th><th>Email Address</th>
                                <th>Phone</th><th>Status</th><th>Registered At</th>
                            </tr></thead>
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
        document.getElementById('section-overview').style.display = 'none';
        document.getElementById('section-enquiries').style.display = 'none';
        document.getElementById('section-users').style.display = 'none';

        // Show target
        document.getElementById('section-' + view).style.display = 'block';

        // Update active nav link
        document.querySelectorAll('.admin-nav a').forEach(a => a.classList.remove('active'));
        if (clickedEl) {
            clickedEl.classList.add('active');
        } else {
            // Called from "View All" links — activate matching sidebar link
            const map = { enquiries: 1, users: 2 };
            const navLinks = document.querySelectorAll('.admin-nav a');
            if (map[view] !== undefined) navLinks[map[view]].classList.add('active');
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // On page load, check hash
    (function() {
        const hash = window.location.hash;
        if (hash === '#enquiries') switchView('enquiries', null);
        else if (hash === '#users') switchView('users', null);
    })();
    </script>
</body>
</html>
