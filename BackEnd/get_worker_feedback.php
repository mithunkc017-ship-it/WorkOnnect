<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

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

require_once __DIR__ . "/db.php";

try {

    $workerId =
        new MongoDB\BSON\ObjectId(
            $_SESSION["user_id"]
        );

    $feedbackCollection =
        $database->selectCollection("feedback");

    $requestsCollection =
        $database->selectCollection("service_requests");

    $feedbackList =
        $feedbackCollection->find(
            [
                "worker_id" => $workerId
            ],
            [
                "sort" => [
                    "created_at" => -1
                ]
            ]
        );

    $results = [];

    foreach ($feedbackList as $feedback) {

        /* Customer */

        $customer =
            $users->findOne([
                "_id" =>
                    $feedback["customer_id"]
            ]);

        $customerName =
            $customer["name"]
            ?? "Customer";


        /* Request */

        $request =
            $requestsCollection->findOne([
                "_id" =>
                    $feedback["request_id"]
            ]);

        $service =
            $request["service"]
            ?? "Service";


        /* Date */

        $createdAt = "";

        if (
            isset(
                $feedback["created_at"]
            )
        ) {

            $createdAt =
                $feedback["created_at"]
                    ->toDateTime()
                    ->format(
                        "d M Y, h:i A"
                    );
        }


        $results[] = [

            "id" =>
                (string)
                $feedback["_id"],

            "customer_name" =>
                $customerName,

            "service" =>
                $service,

            "rating" =>
                (int)
                ($feedback["rating"] ?? 0),

            "comment" =>
                $feedback["comment"]
                ?? "",

            "created_at" =>
                $createdAt
        ];
    }


    echo json_encode([

        "success" => true,

        "count" =>
            count($results),

        "feedback" =>
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