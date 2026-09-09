<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

function serveDefaultAvatar(): never
{
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    readfile(__DIR__ . '/assets/images/default-avatar.png');
    exit;
}

$id = $_GET['id'] ?? '';

if (!isValidObjectId($id)) {
    serveDefaultAvatar();
}

$user = $db->users->findOne(
    ['_id' => new MongoDB\BSON\ObjectId($id)],
    ['projection' => ['profile_image_data' => 1, 'profile_image_mime' => 1]]
);

if (!$user || empty($user['profile_image_data'])) {
    serveDefaultAvatar();
}

header('Content-Type: ' . ($user['profile_image_mime'] ?? 'application/octet-stream'));
header('Cache-Control: private, max-age=3600');
echo $user['profile_image_data']->getData();
