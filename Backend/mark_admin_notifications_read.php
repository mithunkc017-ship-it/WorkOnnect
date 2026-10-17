<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json");


/* =========================================
   ADMIN ACCESS CHECK
   ========================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Admin access required"
    ]);

    exit;
}


/* =========================================
   MARK ALL ADMIN NOTIFICATIONS AS READ
   ========================================= */

try {

    $adminId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );

    $notifications = $database->selectCollection("notifications");


    $result = $notifications->updateMany(
        [
            "user_id" => $adminId,
            "read" => ['$ne' => true]
        ],
        [
            '$set' => [
                "read" => true
            ]
        ]
    );


    echo json_encode([
        "success" => true,
        "message" => "All notifications marked as read",
        "modified_count" => $result->getModifiedCount()
    ]);

} catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid admin ID"
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Could not mark notifications as read",
        "error" => $e->getMessage()
    ]);
}