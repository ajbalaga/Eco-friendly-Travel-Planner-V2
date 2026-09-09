<?php
declare(strict_types=1);

// 1. Security Headers
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

session_start();
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

// 5. Recent Trips (joined with their destination via aggregation)
$recentTrips = [];
try {
    $cursor = $db->trips->aggregate([
        ['$match' => ['user_id' => (string) $userId]],
        ['$lookup' => [
            'from' => 'destinations',
            'localField' => 'destination_id',
            'foreignField' => 'destination_id',
            'as' => 'destination',
        ]],
        ['$unwind' => '$destination'],
        ['$sort' => ['travel_date' => 1]],
        ['$limit' => 10],
    ]);

    foreach ($cursor as $trip) {
        $trip['trip_id'] = (string) $trip['_id'];
        $trip['destination_name'] = $trip['destination']['name'];
        $recentTrips[] = $trip;
    }
} catch (Exception $e) {
    error_log("Dashboard Fetch Error: " . $e->getMessage());
    // Fallback: $recentTrips stays an empty array
}

// 6. Validated Helper Functions with Strict Types
function getScoreClass(int|float $score): string {
    if ($score >= 80) return 'score-high';
    if ($score >= 50) return 'score-mid';
    return 'score-low';
}

function getPriorityIcon(?string $priority): string {
    return match(strtolower(trim($priority ?? ''))) {
        'carbon'  => '🍃',
        'local'   => '🤝',
        'balance' => '⚖️',
        default   => '📍'
    };
}

function safelyFormatDate(?string $dateString): string {
    if (empty($dateString)) return 'N/A';
    $timestamp = strtotime($dateString);
    if ($timestamp === false) return 'Invalid Date';
    return date('M j, Y, g:i A', $timestamp);
}

require __DIR__ . '/../views/pages/dashboard.view.php';
