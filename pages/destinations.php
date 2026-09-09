<?php
declare(strict_types=1);
session_start();

// 1. Security Headers
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

require_once '../config/database.php';

// 2. Strict Authentication Validation
if (!isValidObjectId($_SESSION['user_id'] ?? null)) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// 3. CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = md5(uniqid((string)mt_rand(), true));
    }
}

// 4. Profile Avatar (served from MongoDB via avatar.php)
$user_pic = "../avatar.php?id=" . urlencode($_SESSION['user_id']) . "&v=" . time();

$search = trim($_GET['search'] ?? '');

/**
 * HELPER FUNCTIONS
 */
function ecoBadge(int $rating): string {
    return str_repeat('🍃', max(1, min(5, $rating)));
}

function getRegionName(string $location): string {
    if (preg_match('/Batanes|Baguio|Manila|Union|Province/i', $location)) return "Luzon, Philippines";
    if (preg_match('/Palawan|Bohol|Cebu|Iloilo|Negros/i', $location)) return "Visayas & Palawan, Philippines";
    if (preg_match('/Norte|Davao|Camiguin/i', $location)) return "Mindanao, Philippines";
    if (preg_match('/Japan|Bhutan|Cambodia|Laos|Indonesia|India|New Zealand|Australia/i', $location)) return "Asia & Oceania";
    if (preg_match('/Norway|Iceland|Portugal|Slovenia|Switzerland|Rwanda|Seychelles/i', $location)) return "Europe & Africa";
    return "The Americas";
}

// 5. Destination Query Logic
$filter = [];
if ($search !== '') {
    $pattern = preg_quote($search, '/');
    $filter = ['$or' => [
        ['name' => ['$regex' => $pattern, '$options' => 'i']],
        ['location' => ['$regex' => $pattern, '$options' => 'i']],
        ['description' => ['$regex' => $pattern, '$options' => 'i']],
    ]];
}

$destinations = $db->destinations->find($filter)->toArray();

// Geographic Grouping: same region order as the original SQL CASE, then eco_rating desc, name asc
$regionOrder = [
    'Luzon, Philippines' => 1,
    'Visayas & Palawan, Philippines' => 2,
    'Mindanao, Philippines' => 3,
    'Asia & Oceania' => 4,
    'Europe & Africa' => 5,
    'The Americas' => 6,
];

usort($destinations, function ($a, $b) use ($regionOrder) {
    $rankA = $regionOrder[getRegionName($a['location'])];
    $rankB = $regionOrder[getRegionName($b['location'])];
    return $rankA <=> $rankB
        ?: $b['eco_rating'] <=> $a['eco_rating']
        ?: $a['name'] <=> $b['name'];
});

require __DIR__ . '/../views/pages/destinations.view.php';
