<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

// Initialize database tables & seed default admin if needed
initDatabaseTables();

$errorMessage = '';
$successMessage = '';
$activeTab = $_GET['tab'] ?? ($_GET['action'] ?? 'login');

// Redirect if already logged in (unless logging out)
if (isLoggedIn() && !isset($_GET['action'])) {
    if (isAdmin()) {
        header('Location: ' . url('admin/dashboard.php'));
        exit;
    } else {
        header('Location: ' . url('account'));
        exit;
    }
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $activeTab = $action;

    if ($action === 'login' || $action === 'admin_login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = loginUser($email, $password);
        if ($result['success']) {
            if ($result['role'] === 'admin') {
                header('Location: ' . url('admin/dashboard.php'));
                exit;
            } else {
                if ($action === 'admin_login') {
                    $errorMessage = 'Access Denied: This account does not have administrator privileges.';
                } else {
                    header('Location: ' . url('account'));
                    exit;
                }
            }
        } else {
            $errorMessage = $result['message'];
        }
    } elseif ($action === 'register') {
        $firstName       = trim($_POST['first_name'] ?? '');
        $lastName        = trim($_POST['last_name'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = registerUser($firstName, $lastName, $email, $phone, $password, $confirmPassword);
        if ($result['success']) {
            header('Location: ' . url('account'));
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in or create an account with Biswas Enterprise - Premium Ayurvedic & Herbal Care.">
    <title>Account Access - Biswas Enterprise</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/auth.css') ?>">
</head>
<body class="clean-auth-body">

    <!-- Clean Standalone Auth Main Screen -->
    <main class="auth-page-section clean-standalone-auth">
        <!-- Floating Back to Store Link -->
        <a href="<?= url() ?>" class="auth-back-store-btn" title="Back to Biswas Enterprise Store">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Store</span>
        </a>

        <div class="auth-container">
            <div class="auth-card">
                <!-- Auth Card Header -->
                <div class="auth-card-header">
                    <a href="<?= url() ?>" class="auth-card-logo-link" aria-label="Biswas Enterprise Home">
                        <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise Logo" class="auth-card-logo-img">
                    </a>
                    <h1 class="auth-card-title" id="form-title">Welcome Back</h1>
                    <p class="auth-card-subtitle" id="form-subtitle">Sign in to manage your orders, wishlist, and account settings.</p>
                </div>

                <!-- Auth Tabs -->
                <div class="auth-tabs">
                    <button type="button" class="auth-tab-btn <?= (!in_array($activeTab, ['register', 'admin_login', 'admin'])) ? 'active' : '' ?>" onclick="switchAuthTab('login')">Sign In</button>
                    <button type="button" class="auth-tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" onclick="switchAuthTab('register')">Create Account</button>
                    <button type="button" class="auth-tab-btn tab-admin-btn <?= in_array($activeTab, ['admin_login', 'admin']) ? 'active' : '' ?>" onclick="switchAuthTab('admin')">
                        <svg class="tab-shield-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Admin Login</span>
                    </button>
                </div>

                <!-- Auth Form Body -->
                <div class="auth-card-body">
                    <!-- Alert Error Box -->
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert-box alert-box-danger">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span><?= htmlspecialchars($errorMessage) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- 1. CUSTOMER LOGIN FORM PANE -->
                    <div id="pane-login" class="auth-form-pane <?= (!in_array($activeTab, ['register', 'admin_login', 'admin'])) ? 'active' : '' ?>">
                        <form action="<?= url('login') ?>" method="POST" autocomplete="on">
                            <input type="hidden" name="action" value="login">

                            <div class="form-group">
                                <label class="form-label" for="login-email">Email Address <span class="required">*</span></label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input type="email" id="login-email" name="email" class="form-input" placeholder="name@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="login-password">Password <span class="required">*</span></label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <input type="password" id="login-password" name="password" class="form-input" placeholder="Enter your password" required>
                                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('login-password')">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-extra-row">
                                <label class="remember-checkbox">
                                    <input type="checkbox" name="remember" checked>
                                    <span>Remember me</span>
                                </label>
                                <a href="#" onclick="alert('Please contact admin support to reset your password.')" class="forgot-password-link">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn-auth-submit">
                                <span>Sign In to Account</span>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </button>
                        </form>

                        <p class="auth-footer-text">
                            Don't have an account yet? <a href="javascript:void(0)" onclick="switchAuthTab('register')">Create a new account</a>
                        </p>
                    </div>

                    <!-- 2. SIGN UP / REGISTER FORM PANE -->
                    <div id="pane-register" class="auth-form-pane <?= $activeTab === 'register' ? 'active' : '' ?>">
                        <form action="<?= url('login') ?>" method="POST" autocomplete="on">
                            <input type="hidden" name="action" value="register">

                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label class="form-label" for="reg-firstname">First Name <span class="required">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <input type="text" id="reg-firstname" name="first_name" class="form-input" placeholder="John" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="reg-lastname">Last Name <span class="required">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <input type="text" id="reg-lastname" name="last_name" class="form-input" placeholder="Doe" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reg-email">Email Address <span class="required">*</span></label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input type="email" id="reg-email" name="email" class="form-input" placeholder="john.doe@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reg-phone">Phone Number</label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <input type="tel" id="reg-phone" name="phone" class="form-input" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label class="form-label" for="reg-password">Password <span class="required">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        <input type="password" id="reg-password" name="password" class="form-input" placeholder="At least 6 chars" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="reg-confirm">Confirm Password <span class="required">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        <input type="password" id="reg-confirm" name="confirm_password" class="form-input" placeholder="Repeat password" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-auth-submit">
                                <span>Create My Account</span>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </button>
                        </form>

                        <p class="auth-footer-text">
                            Already have an account? <a href="javascript:void(0)" onclick="switchAuthTab('login')">Sign in here</a>
                        </p>
                    </div>

                    <!-- 3. SEPARATE ADMIN LOGIN FORM PANE -->
                    <div id="pane-admin" class="auth-form-pane <?= in_array($activeTab, ['admin_login', 'admin']) ? 'active' : '' ?>">
                        <form action="<?= url('login') ?>" method="POST" autocomplete="on">
                            <input type="hidden" name="action" value="admin_login">

                            <div class="form-group">
                                <label class="form-label" for="admin-email">Admin Email Address <span class="required">*</span></label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input type="email" id="admin-email" name="email" class="form-input" placeholder="Enter admin email address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="admin-password">Admin Password <span class="required">*</span></label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <input type="password" id="admin-password" name="password" class="form-input" placeholder="••••••••" required>
                                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('admin-password')">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn-auth-submit btn-admin-submit">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span>Login to Admin Control Panel</span>
                            </button>
                        </form>

                        <p class="auth-footer-text">
                            Are you a customer? <a href="javascript:void(0)" onclick="switchAuthTab('login')">Return to customer sign in</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="clean-auth-bottom-copy">
                &copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved. Pure Ayurvedic Formulations.
            </div>
        </div>
    </main>

    <!-- Interactive Tab Script -->
    <script>
        function switchAuthTab(tabName) {
            const loginPane = document.getElementById('pane-login');
            const registerPane = document.getElementById('pane-register');
            const adminPane = document.getElementById('pane-admin');
            const tabBtns = document.querySelectorAll('.auth-tab-btn');
            const titleEl = document.getElementById('form-title');
            const subtitleEl = document.getElementById('form-subtitle');

            // Hide all panes
            loginPane.classList.remove('active');
            registerPane.classList.remove('active');
            adminPane.classList.remove('active');
            tabBtns.forEach(btn => btn.classList.remove('active'));

            if (tabName === 'register') {
                registerPane.classList.add('active');
                tabBtns[1].classList.add('active');
                titleEl.textContent = "Create Account";
                subtitleEl.textContent = "Join Biswas Enterprise to unlock member benefits & track your orders.";
            } else if (tabName === 'admin') {
                adminPane.classList.add('active');
                tabBtns[2].classList.add('active');
                titleEl.textContent = "Administrator Portal";
                subtitleEl.textContent = "Secure management & control access for Biswas Enterprise store.";
            } else {
                loginPane.classList.add('active');
                tabBtns[0].classList.add('active');
                titleEl.textContent = "Welcome Back";
                subtitleEl.textContent = "Sign in to manage your orders, wishlist, and account settings.";
            }
        }

        function fillAdminPreset() {
            document.getElementById('admin-email').value = 'admin@123gmail.com';
            document.getElementById('admin-password').value = 'Admin@2026';
            document.getElementById('admin-password').focus();
        }

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>
</body>
</html>
