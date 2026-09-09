<?php
declare(strict_types=1);
session_start();
require_once '../config/database.php';

// Check if user is logged in and trip_id is provided
if (isValidObjectId($_SESSION['user_id'] ?? null) && isValidObjectId($_POST['trip_id'] ?? null)) {
    $db->trips->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($_POST['trip_id']),
        'user_id' => $_SESSION['user_id'],
    ]);
}

// Redirect back to dashboard
header('Location: dashboard.php');
exit;
