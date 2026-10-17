<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

header("Content-Type: application/json");


/*
|--------------------------------------------------------------------------
| Only workers can view worker requests
|--------------------------------------------------------------------------
*/

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

    /*
    |--------------------------------------------------------------------------
    | Worker ID
    |--------------------------------------------------------------------------
    */

    $workerId =
        new MongoDB\BSON\ObjectId(
            $_SESSION["user_id"]
        );


    /*
    |--------------------------------------------------------------------------
    | Service requests collection
    |--------------------------------------------------------------------------
    */

    $requestsCollection =
        $database->selectCollection(
            "service_requests"
        );


    /*
    |--------------------------------------------------------------------------
    | Get requests for this worker
    |--------------------------------------------------------------------------
    */

    $requests =
        $requestsCollection->find(
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


    /*
    |--------------------------------------------------------------------------
    | Prepare requests
    |--------------------------------------------------------------------------
    */

    foreach ($requests as $request) {


        /*
        | New requests already contain customer_name.
        |
        | Older requests may not have it, so we keep
        | the database lookup as a fallback.
        */

        $customerName =
            $request["customer_name"]
            ?? null;


        if (!$customerName) {

            $customer = $users->findOne([
                "_id" => $request["customer_id"]
            ]);


            $customerName =
                $customer["name"]
                ?? "Customer";
        }


        $results[] = [

            "id" =>
                (string) $request["_id"],


            "customer_name" =>
                $customerName,


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


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

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
            "Could not load requests"

    ]);

}

?>