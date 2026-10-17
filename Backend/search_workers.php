<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

header("Content-Type: application/json");

require_once __DIR__ . "/db.php";

try {

    $search = trim($_GET["search"] ?? "");

    $filter = [
        "role" => "worker",
        "worker_profile.approval_status" => "approved"
    ];

    if ($search !== "") {

        $regex = new MongoDB\BSON\Regex(
            preg_quote($search, "/"),
            "i"
        );

        $filter["\$or"] = [

            [
                "worker_profile.worker_name" => $regex
            ],

            [
                "worker_profile.service_category" => $regex
            ],

            [
                "worker_profile.skills" => $regex
            ],

            [
                "worker_profile.location" => $regex
            ]

        ];
    }

    $workers = $users->find(
        $filter,
        [
            "limit" => 12,
            "sort" => [
                "created_at" => -1
            ]
        ]
    );

    $results = [];

    foreach ($workers as $worker) {

        $profile = $worker["worker_profile"] ?? [];

        $results[] = [

            "id" => (string) $worker["_id"],

            "name" =>
                $profile["worker_name"]
                ?? $worker["name"]
                ?? "Worker",

            "service" =>
                $profile["service_category"]
                ?? "Service",

            "skills" =>
                $profile["skills"]
                ?? "",

            "location" =>
                $profile["location"]
                ?? "Location not added",

            "experience" =>
                $profile["experience"]
                ?? 0,

            "availability" =>
                $profile["availability"]
                ?? "Not specified",

            "about" =>
                $profile["about"]
                ?? ""

        ];
    }

    echo json_encode([

        "success" => true,

        "count" => count($results),

        "workers" => $results

    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            "Search error: " .
            $e->getMessage()

    ]);

}