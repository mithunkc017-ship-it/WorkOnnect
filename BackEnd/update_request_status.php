<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

header("Content-Type: application/json");


/*
|--------------------------------------------------------------------------
| Only workers can update requests
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


/*
|--------------------------------------------------------------------------
| Only POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get request data
|--------------------------------------------------------------------------
*/

$requestId = trim($_POST["request_id"] ?? "");
$status    = trim($_POST["status"] ?? "");


/*
|--------------------------------------------------------------------------
| Validate
|--------------------------------------------------------------------------
*/

if (
    $requestId === "" ||
    $status === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Request ID and status are required"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Allowed statuses
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    "accepted",
    "rejected",
    "completed"
];

if (!in_array($status, $allowedStatuses, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid status"
    ]);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Worker ID
    |--------------------------------------------------------------------------
    */

    $workerId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );


    /*
    |--------------------------------------------------------------------------
    | Request ID
    |--------------------------------------------------------------------------
    */

    try {

        $requestObjectId =
            new MongoDB\BSON\ObjectId($requestId);

    } catch (Throwable $e) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Invalid request ID"
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Requests collection
    |--------------------------------------------------------------------------
    */

    $requestsCollection =
        $database->selectCollection("service_requests");


    /*
    |--------------------------------------------------------------------------
    | Find request belonging to worker
    |--------------------------------------------------------------------------
    */

    $request =
        $requestsCollection->findOne([
            "_id" => $requestObjectId,
            "worker_id" => $workerId
        ]);


    if (!$request) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Request not found"
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Current status
    |--------------------------------------------------------------------------
    */

    $currentStatus =
        $request["status"] ?? "pending";


    /*
    |--------------------------------------------------------------------------
    | Validate status transition
    |--------------------------------------------------------------------------
    */

    $validTransition = false;


    if (
        $currentStatus === "pending" &&
        in_array($status, ["accepted", "rejected"], true)
    ) {

        $validTransition = true;

    } elseif (
        $currentStatus === "accepted" &&
        $status === "completed"
    ) {

        $validTransition = true;
    }


    if (!$validTransition) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Cannot change request from " .
                $currentStatus .
                " to " .
                $status
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update request
    |--------------------------------------------------------------------------
    */

    $updateFields = [

        "status" =>
            $status,

        "updated_at" =>
            new MongoDB\BSON\UTCDateTime()

    ];


    /*
    |--------------------------------------------------------------------------
    | Store completion date
    |--------------------------------------------------------------------------
    */

    if ($status === "completed") {

        $updateFields["completed_at"] =
            new MongoDB\BSON\UTCDateTime();
    }


    $result =
        $requestsCollection->updateOne(

            [
                "_id" =>
                    $requestObjectId,

                "worker_id" =>
                    $workerId,

                "status" =>
                    $currentStatus
            ],

            [
                '$set' =>
                    $updateFields
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Make sure update happened
    |--------------------------------------------------------------------------
    */

    if ($result->getMatchedCount() === 0) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Request was already updated"
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Customer notification
    |--------------------------------------------------------------------------
    */

    $customerId =
        $request["customer_id"]
        ?? null;


    if ($customerId) {

        /*
        |----------------------------------------------------------------------
        | Worker information
        |----------------------------------------------------------------------
        */

        $worker =
            $users->findOne([
                "_id" => $workerId
            ]);


        $workerProfile =
            $worker["worker_profile"]
            ?? [];


        $workerName =
            $workerProfile["worker_name"]
            ?? $worker["name"]
            ?? "Worker";


        $service =
            $request["service"]
            ?? "service";


        /*
        |----------------------------------------------------------------------
        | Notification text
        |----------------------------------------------------------------------
        */

        if ($status === "accepted") {

            $notificationTitle =
                "Request Accepted";

            $notificationMessage =
                $workerName .
                " accepted your " .
                $service .
                " service request.";


        } elseif ($status === "rejected") {

            $notificationTitle =
                "Request Rejected";

            $notificationMessage =
                $workerName .
                " rejected your " .
                $service .
                " service request.";


        } else {

            $notificationTitle =
                "Service Completed";

            $notificationMessage =
                $workerName .
                " marked your " .
                $service .
                " service request as completed.";

        }


        /*
        |----------------------------------------------------------------------
        | Notifications collection
        |----------------------------------------------------------------------
        */

        $notifications =
            $database->selectCollection(
                "notifications"
            );


        $notifications->insertOne([

            "user_id" =>
                $customerId,

            "type" =>
                "request_" . $status,

            "title" =>
                $notificationTitle,

            "message" =>
                $notificationMessage,

            "request_id" =>
                $requestObjectId,

            "read" =>
                false,

            "created_at" =>
                new MongoDB\BSON\UTCDateTime()

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        "success" => true,

        "status" =>
            $status,

        "message" =>
            "Request " .
            $status .
            " successfully"

    ]);

    exit;


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            "Could not update request"

    ]);

    exit;
}

?>