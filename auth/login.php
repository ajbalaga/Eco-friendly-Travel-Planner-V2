<?php
declare(strict_types=1);
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $user = $db->users->findOne(['email' => $email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (string) $user['_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}

require __DIR__ . '/../views/auth/login.view.php';
