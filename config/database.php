<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");

    $db = $client->community_hall_db;

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>