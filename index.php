<?php
declare(strict_types=1);

// 1. Security Headers
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

session_start();

require_once 'config/database.php';

// 2. Authentication State
$isLoggedIn = isValidObjectId($_SESSION['user_id'] ?? null);
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';

// 3. CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 4. Profile Avatar (served from MongoDB via avatar.php)
$user_pic = $isLoggedIn
    ? "avatar.php?id=" . urlencode($_SESSION['user_id']) . "&v=" . time()
    : '';

require __DIR__ . '/views/index.view.php';
