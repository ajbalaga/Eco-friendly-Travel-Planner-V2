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

// 5. POST Request Validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: plan_trip.php');
    exit;
}

// 6. Capture Inputs
$tripId = (isset($_POST['trip_id']) && isValidObjectId($_POST['trip_id'])) ? $_POST['trip_id'] : null;
$destinationId = (int) ($_POST['destination_id'] ?? 0);
$travelDate = trim($_POST['travel_date'] ?? '');
$returnDate = trim($_POST['return_date'] ?? '');
$transportMode = trim($_POST['transport_mode'] ?? '');
$distanceKm = (float) ($_POST['distance_km'] ?? 0);
$travelerCount = (int) ($_POST['traveler_count'] ?? 1);
$priority = trim($_POST['priority'] ?? 'carbon');
$notes = trim($_POST['notes'] ?? '');

if ($destinationId <= 0 || $travelDate === '' || $transportMode === '' || $distanceKm <= 0) {
    header('Location: plan_trip.php');
    exit;
}

// --- OVERLAP PREVENTION LOGIC ---
$checkEnd = !empty($returnDate) ? $returnDate : $travelDate;
$overlapFilter = [
    'user_id' => (string) $userId,
    'travel_date' => ['$lte' => $checkEnd],
    'return_date' => ['$gte' => $travelDate],
];
if ($tripId) {
    $overlapFilter['_id'] = ['$ne' => new MongoDB\BSON\ObjectId($tripId)];
}

if ($db->trips->countDocuments($overlapFilter) > 0) {
    $redirectUrl = "plan_trip.php?error=overlap" . ($tripId ? "&trip_id=$tripId" : "");
    header("Location: $redirectUrl");
    exit;
}

// Fetch destination
$destination = $db->destinations->findOne(['destination_id' => $destinationId]);

if (!$destination) {
    header('Location: plan_trip.php');
    exit;
}

// 7. Emission & Scoring Logic
$emissionFactors = [
    'walking' => 0, 'bike' => 0, 'public_bus' => 0.10, 'train' => 0.05,
    'ferry' => 0.12, 'private_car' => 0.21, 'airplane' => 0.25,
];

$baseScoreMap = [
    'walking' => 100, 'bike' => 95, 'train' => 85, 'public_bus' => 75,
    'ferry' => 65, 'private_car' => 40, 'airplane' => 25,
];

$estimatedEmission = ($emissionFactors[$transportMode] ?? 0.15) * $distanceKm * $travelerCount;

// Logarithmic Penalty for distance
$distancePenalty = (int) round(8 * log10($distanceKm + 1));
$distancePenalty = min(40, $distancePenalty);

$ecoBonus = ((int) ($destination['eco_rating'] ?? 0)) * 3;
$sustainabilityScore = ($baseScoreMap[$transportMode] ?? 50) - $distancePenalty + $ecoBonus;
$sustainabilityScore = (int) max(5, min(100, $sustainabilityScore));

// 8. Save Logic
$tripDoc = [
    'destination_id' => $destinationId,
    'travel_date' => $travelDate,
    'return_date' => !empty($returnDate) ? $returnDate : null,
    'transport_mode' => $transportMode,
    'distance_km' => $distanceKm,
    'traveler_count' => $travelerCount,
    'sustainability_priority' => $priority,
    'carbon_footprint_kg' => $estimatedEmission,
    'notes' => $notes,
    'sustainability_score' => $sustainabilityScore,
];

if ($tripId) {
    $db->trips->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($tripId), 'user_id' => (string) $userId],
        ['$set' => $tripDoc]
    );
} else {
    $tripDoc['user_id'] = (string) $userId;
    $db->trips->insertOne($tripDoc);
}

require __DIR__ . '/../views/pages/trip_results.view.php';
