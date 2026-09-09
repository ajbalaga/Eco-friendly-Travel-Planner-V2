<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$mongoUri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');
$mongoDbName = $_ENV['MONGODB_DB'] ?? getenv('MONGODB_DB') ?: 'Eco-friendly-travel-planner';

if (!$mongoUri) {
    die('Database connection failed. MONGODB_URI is not set (copy .env.example to .env and fill it in).');
}

try {
    $mongoClient = new MongoDB\Client($mongoUri, [], [
        'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
    ]);
    $db = $mongoClient->selectDatabase($mongoDbName);
} catch (\Exception $e) {
    error_log('MongoDB connection failed: ' . $e->getMessage());
    die('Database connection failed. Please check your MongoDB Atlas connection string and network access settings.');
}

function isValidObjectId(?string $id): bool
{
    return is_string($id) && preg_match('/^[a-f0-9]{24}$/i', $id) === 1;
}
