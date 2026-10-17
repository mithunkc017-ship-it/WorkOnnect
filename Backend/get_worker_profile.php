<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json");

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "worker"
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

try {

    $userId = $_SESSION["user_id"];

    $worker = $users->findOne([
        "_id" => new MongoDB\BSON\ObjectId($userId)
    ]);

    if (!$worker) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Worker not found"
        ]);

        exit;
    }

    // IMPORTANT:
    // Worker profile is stored inside worker_profile
    $profile = $worker["worker_profile"] ?? [];

    echo json_encode([
        "success" => true,

        "profile" => [

            "worker_name" =>
                $profile["worker_name"] ?? "",

            "contact_number" =>
                $profile["contact_number"] ?? "",

            "experience" =>
                $profile["experience"] ?? "",

            "service_category" =>
                $profile["service_category"] ?? "",

            "skills" =>
                $profile["skills"] ?? "",

            "location" =>
                $profile["location"] ?? "",

            "availability" =>
                $profile["availability"] ?? "",

            "about" =>
                $profile["about"] ?? "",

            "profile_image" =>
                $profile["profile_image"] ?? "",

            "approval_status" => 
            $profile["approval_status"] ?? "pending",

            "email" =>
                $worker["email"] ?? ""
        ]
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

    exit;
}