<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");


/* =========================================================
   ONLY ADMIN CAN VIEW PENDING WORKERS
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}


/* =========================================================
   GET PENDING WORKERS
========================================================= */

try {

    /*
     * A worker must satisfy BOTH conditions:
     *
     * role = worker
     * worker_profile.approval_status = pending
     */

    $workers = $users->find(
        [
            "role" => "worker",
            "worker_profile.approval_status" => "pending"
        ],
        [
            "sort" => [
                "worker_profile.updated_at" => -1
            ]
        ]
    );


    $result = [];


    /* =====================================================
       LOOP THROUGH WORKERS
    ===================================================== */

    foreach ($workers as $worker) {

        $profile = $worker["worker_profile"] ?? [];


        /* =================================================
           SAFE UPDATED DATE
        ================================================= */

        $updatedAt = "";

        if (isset($profile["updated_at"])) {

            if (
                $profile["updated_at"]
                instanceof MongoDB\BSON\UTCDateTime
            ) {

                $updatedAt =
                    $profile["updated_at"]
                    ->toDateTime()
                    ->format("Y-m-d H:i:s");

            } else {

                $updatedAt =
                    (string) $profile["updated_at"];
            }
        }


        /* =================================================
           WORKER ID
        ================================================= */

        $workerId = "";

        if (isset($worker["_id"])) {

            $workerId = (string) $worker["_id"];
        }


        /* =================================================
           WORKER DATA
        ================================================= */

        $result[] = [

            "id" => $workerId,

            "name" =>
                $profile["worker_name"]
                ?? $worker["name"]
                ?? "Worker",

            "email" =>
                $worker["email"]
                ?? "",

            "contact_number" =>
                $profile["contact_number"]
                ?? "",

            "service_category" =>
                $profile["service_category"]
                ?? "",

            "skills" =>
                $profile["skills"]
                ?? "",

            "location" =>
                $profile["location"]
                ?? "",

            "experience" =>
                $profile["experience"]
                ?? "",

            "availability" =>
                $profile["availability"]
                ?? "",

            "about" =>
                $profile["about"]
                ?? "",

            "profile_image" =>
                $profile["profile_image"]
                ?? "",

            "approval_status" =>
                $profile["approval_status"]
                ?? "pending",

            "updated_at" =>
                $updatedAt
        ];
    }


    /* =====================================================
       SUCCESS RESPONSE
    ===================================================== */

    echo json_encode(
        [
            "success" => true,

            "workers" => $result,

            "count" => count($result)
        ],
        JSON_UNESCAPED_UNICODE
    );


} catch (Throwable $e) {

    /* =====================================================
       DATABASE / SERVER ERROR
    ===================================================== */

    http_response_code(500);

    echo json_encode([
        "success" => false,

        "message" => "Could not load pending workers",

        "error" => $e->getMessage()
    ]);
}
?>