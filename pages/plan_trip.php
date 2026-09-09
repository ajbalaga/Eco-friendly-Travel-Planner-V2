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

// 5. Trip Planning Logic
function formatForInput($dateString, $default) {
    if (empty($dateString)) return $default;
    return date('Y-m-d\TH:i', strtotime($dateString));
}

$date = new DateTime('tomorrow');
$date->modify('+1 day');
$min_datetime = $date->format('Y-m-d\TH:i');

// EDIT LOGIC: Check if we are editing an existing trip
$trip_id = $_GET['trip_id'] ?? null;
$edit_trip = null;

if ($trip_id && isValidObjectId($trip_id)) {
    $edit_trip = $db->trips->findOne([
        '_id' => new MongoDB\BSON\ObjectId($trip_id),
        'user_id' => (string) $userId,
    ]);
    if ($edit_trip) {
        $edit_trip['trip_id'] = (string) $edit_trip['_id'];
    }
}

// Fetch Destinations for the dropdown
$destinations = $db->destinations->find([], ['sort' => ['eco_rating' => -1, 'name' => 1]])->toArray();

require __DIR__ . '/../views/pages/plan_trip.view.php';
