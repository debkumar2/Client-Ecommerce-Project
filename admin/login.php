<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

initDatabaseTables();

if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

// Redirect to unified login page with admin emphasis
header('Location: ' . url('login'));
exit;
