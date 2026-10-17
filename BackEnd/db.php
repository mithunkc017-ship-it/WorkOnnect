<?php
require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

try {
    $uri = $_ENV["MONGODB_URI"] ?? "";
    $databaseName = $_ENV["MONGODB_DATABASE"] ?? "workonnect";

    if ($uri === "") {
        throw new RuntimeException("MONGODB_URI is missing from .env");
    }

    $client = new MongoDB\Client($uri);
    $database = $client->selectDatabase($databaseName);
    $users = $database->selectCollection("users");

} catch (Throwable $e) {
    error_log("MongoDB connection failed: " . $e->getMessage());
    die("MongoDB connection failed. Please check the database configuration.");
}
