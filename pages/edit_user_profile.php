<?php
declare(strict_types=1);

// 1. Security Headers
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

session_start();
require_once '../config/database.php';

if (!isValidObjectId($_SESSION['user_id'] ?? null)) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
$success = '';
$info = '';
$errors = [];

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. FETCH CURRENT DATA
$user = $db->users->findOne(['_id' => $userId], [
    'projection' => ['name' => 1, 'email' => 1],
]);

if (!$user) {
    die("User not found.");
}

// 3. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security token mismatch.");
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $image = $_FILES['profile_image'] ?? null;

    $image_uploaded = ($image && $image['error'] === UPLOAD_ERR_OK);
    $password_provided = !empty($new_password);

    // Basic Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    // CHANGE DETECTION
    if ($name === $user['name'] &&
        $email === $user['email'] &&
        !$password_provided &&
        !$image_uploaded) {
        $info = "Everything looks up to date! No changes were made.";
    } else {
        $update = ['name' => $name, 'email' => $email];

        if ($password_provided) {
            if (strlen($new_password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Passwords do not match.";
            } else {
                $update['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        if ($image_uploaded && empty($errors)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($image['tmp_name']);
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

            if (in_array($mime_type, $allowed_mimes)) {
                if ($image['size'] > 2000000) {
                    $errors[] = "Image is too heavy. Max limit is 2MB.";
                } else {
                    $update['profile_image_data'] = new MongoDB\BSON\Binary(
                        file_get_contents($image['tmp_name']),
                        MongoDB\BSON\Binary::TYPE_GENERIC
                    );
                    $update['profile_image_mime'] = $mime_type;
                }
            } else {
                $errors[] = "Please upload a JPG, PNG, or WebP image.";
            }
        }

        if (empty($errors)) {
            try {
                $db->users->updateOne(['_id' => $userId], ['$set' => $update]);

                $_SESSION['user_name'] = $name;
                $success = "Profile updated successfully! ✨";

                $user['name'] = $name;
                $user['email'] = $email;
            } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                $errors[] = ($e->getCode() === 11000) ? "Email already exists." : "Update failed.";
            }
        }
    }
}

$display_img = "../avatar.php?id=" . urlencode((string) $userId) . "&v=" . time();

require __DIR__ . '/../views/pages/edit_user_profile.view.php';
