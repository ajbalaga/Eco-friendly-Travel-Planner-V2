<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$usersIndex = $db->selectCollection('users')->createIndex(['email' => 1], ['unique' => true]);
echo "users: created index {$usersIndex}\n";

$tripsUserIndex = $db->selectCollection('trips')->createIndex(['user_id' => 1]);
echo "trips: created index {$tripsUserIndex}\n";

$tripsDestIndex = $db->selectCollection('trips')->createIndex(['destination_id' => 1]);
echo "trips: created index {$tripsDestIndex}\n";

$destIndex = $db->selectCollection('destinations')->createIndex(['destination_id' => 1], ['unique' => true]);
echo "destinations: created index {$destIndex}\n";
