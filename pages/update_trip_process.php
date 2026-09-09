<?php
declare(strict_types=1);
session_start();
require_once '../config/database.php';

// Security: Ensure user is logged in
if (!isValidObjectId($_SESSION['user_id'] ?? null)) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userId = $_SESSION['user_id'];
$trip_id = $_POST['trip_id'] ?? null;

$csrfOk = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);

if (!$csrfOk || !isValidObjectId($trip_id)) {
    header('Location: dashboard.php?error=update_failed');
    exit;
}

// Collect and validate inputs
$travel_date = trim($_POST['travel_date'] ?? '');
$return_date = trim($_POST['return_date'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$priority = in_array($_POST['priority'] ?? '', ['carbon', 'balance', 'local'], true)
    ? $_POST['priority']
    : 'carbon';
$traveler_count = max(1, (int)($_POST['traveler_count'] ?? 1));

if ($travel_date === '' || $return_date === '' || $return_date < $travel_date) {
    header('Location: dashboard.php?error=update_failed');
    exit;
}

$result = $db->trips->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($trip_id), 'user_id' => $userId],
    ['$set' => [
        'travel_date' => $travel_date,
        'return_date' => $return_date,
        'notes' => $notes,
        'sustainability_priority' => $priority,
        'traveler_count' => $traveler_count,
    ]]
);

if ($result->getMatchedCount() === 0) {
    header('Location: dashboard.php?error=update_failed');
    exit;
}

header('Location: dashboard.php?msg=updated');
exit;
