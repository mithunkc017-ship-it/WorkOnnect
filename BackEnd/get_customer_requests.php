<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

header("Content-Type: application/json");


/* =========================================================
   CUSTOMER AUTHENTICATION
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "customer"
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}


require_once __DIR__ . "/db.php";


/* =========================================================
   LOAD CUSTOMER REQUESTS
========================================================= */

try {

    $customerId =
        new MongoDB\BSON\ObjectId(
            $_SESSION["user_id"]
        );


    $requestsCollection =
        $database->selectCollection(
            "service_requests"
        );


    $feedbackCollection =
        $database->selectCollection(
            "feedback"
        );


    $requests =
        $requestsCollection->find(
            [
                "customer_id" => $customerId
            ],
            [
                "sort" => [
                    "created_at" => -1
                ]
            ]
        );


    $results = [];


    foreach ($requests as $request) {

        /* -------------------------------------------------
           FIND WORKER
        ------------------------------------------------- */

        $worker =
            $users->findOne([
                "_id" => $request["worker_id"]
            ]);


        $workerProfile =
            $worker["worker_profile"] ?? [];


        $workerName =
            $workerProfile["worker_name"]
            ?? $worker["name"]
            ?? "Worker";


        /* -------------------------------------------------
           CHECK WHETHER FEEDBACK EXISTS
        ------------------------------------------------- */

        $feedback =
            $feedbackCollection->findOne([
                "request_id" =>
                    $request["_id"],

                "customer_id" =>
                    $customerId
            ]);


        $feedbackSubmitted =
            $feedback !== null;


        /* -------------------------------------------------
           REQUEST DATA
        ------------------------------------------------- */

        $results[] = [

            "id" =>
                (string) $request["_id"],


            "worker_name" =>
                $workerName,


            "service" =>
                $request["service"]
                ?? "Service",


            "message" =>
                $request["message"]
                ?? "",


            "preferred_date" =>
                $request["preferred_date"]
                ?? "",


            "status" =>
                $request["status"]
                ?? "pending",


            "feedback_submitted" =>
                $feedbackSubmitted,


            "created_at" =>
                isset($request["created_at"])
                    ? $request["created_at"]
                        ->toDateTime()
                        ->format(
                            "d M Y, h:i A"
                        )
                    : ""
        ];
    }


    /* =====================================================
       RESPONSE
    ===================================================== */

    echo json_encode([

        "success" => true,

        "count" =>
            count($results),

        "requests" =>
            $results

    ]);


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            $e->getMessage()

    ]);
}

?>