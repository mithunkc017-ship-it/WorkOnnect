<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

require_once __DIR__ . "/../vendor/autoload.php";

try {

    $client = new MongoDB\Client(
        "mongodb://127.0.0.1:27017"
    );

    $database = $client->selectDatabase("workonnect");

    $users = $database->selectCollection("users");

} catch (Throwable $e) {

    die(
        "MongoDB connection failed: " .
        $e->getMessage()
    );
}