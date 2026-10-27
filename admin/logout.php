<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';

logoutUser();
header('Location: ' . url('login?logged_out=1'));
exit;
