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


/* =========================================================
   ONLY POST REQUESTS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);

    exit;
}


require_once __DIR__ . "/db.php";


/* =========================================================
   GET FORM DATA
========================================================= */

$requestId =
    trim($_POST["request_id"] ?? "");


$rating =
    (int) ($_POST["rating"] ?? 0);


$comment =
    trim($_POST["comment"] ?? "");


/* =========================================================
   VALIDATE REQUEST ID
========================================================= */

if ($requestId === "") {

    echo json_encode([
        "success" => false,
        "message" => "Request ID is required"
    ]);

    exit;
}


try {

    $requestObjectId =
        new MongoDB\BSON\ObjectId(
            $requestId
        );

} catch (Throwable $e) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request ID"
    ]);

    exit;
}


/* =========================================================
   VALIDATE RATING
========================================================= */

if ($rating < 1 || $rating > 5) {

    echo json_encode([
        "success" => false,
        "message" => "Please select a rating from 1 to 5"
    ]);

    exit;
}


/* =========================================================
   COMMENT LENGTH
========================================================= */

if (strlen($comment) > 1000) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Feedback comment cannot exceed 1000 characters"
    ]);

    exit;
}


/* =========================================================
   PROCESS FEEDBACK
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


    /* -----------------------------------------------------
       FIND CUSTOMER REQUEST
    ----------------------------------------------------- */

    $request =
        $requestsCollection->findOne([

            "_id" =>
                $requestObjectId,

            "customer_id" =>
                $customerId

        ]);


    if (!$request) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Request not found or does not belong to you"
        ]);

        exit;
    }


    /* -----------------------------------------------------
       ONLY COMPLETED REQUESTS
    ----------------------------------------------------- */

    $requestStatus =
        $request["status"] ?? "";


    if ($requestStatus !== "completed") {

        echo json_encode([
            "success" => false,
            "message" =>
                "Feedback can only be submitted after the service is completed"
        ]);

        exit;
    }


    /* -----------------------------------------------------
       CHECK DUPLICATE FEEDBACK
    ----------------------------------------------------- */

    $existingFeedback =
        $feedbackCollection->findOne([

            "request_id" =>
                $requestObjectId,

            "customer_id" =>
                $customerId

        ]);


    if ($existingFeedback) {

        echo json_encode([
            "success" => false,
            "message" =>
                "You have already submitted feedback for this service"
        ]);

        exit;
    }


    /* -----------------------------------------------------
       GET WORKER ID
    ----------------------------------------------------- */

    if (!isset($request["worker_id"])) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Worker information is missing from this request"
        ]);

        exit;
    }


    $workerId =
        $request["worker_id"];


    /* -----------------------------------------------------
       CREATE FEEDBACK
    ----------------------------------------------------- */

    $feedbackDocument = [

        "request_id" =>
            $requestObjectId,

        "customer_id" =>
            $customerId,

        "worker_id" =>
            $workerId,

        "rating" =>
            $rating,

        "comment" =>
            $comment,

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ];


    $result =
        $feedbackCollection->insertOne(
            $feedbackDocument
        );



    /* ==============================
   NOTIFY WORKER ABOUT FEEDBACK
================================ */

$notificationsCollection =
    $database->selectCollection(
        "notifications"
    );

$customer =
    $users->findOne([
        "_id" => $customerId
    ]);

$customerName =
    $customer["name"]
    ?? "A customer";

$workerName = "Worker";

$worker =
    $users->findOne([
        "_id" => $workerId
    ]);

if ($worker) {

    $workerProfile =
        $worker["worker_profile"]
        ?? [];

    $workerName =
        $workerProfile["worker_name"]
        ?? $worker["name"]
        ?? "Worker";
}

$notificationsCollection->insertOne([
    "user_id" => $workerId,
    "type" => "feedback_received",
    "title" => "New Feedback Received",
    "message" =>
        $customerName .
        " submitted feedback for your " .
        ($request["service"] ?? "service") .
        " service.",
    "request_id" => $requestObjectId,
    "feedback_id" => $result->getInsertedId(),
    "is_read" => false,
    "created_at" =>
        new MongoDB\BSON\UTCDateTime()
]);
    /* -----------------------------------------------------
       RESPONSE
    ----------------------------------------------------- */

    if ($result->getInsertedCount() !== 1) {

        throw new Exception(
            "Feedback could not be saved"
        );
    }


    echo json_encode([

        "success" => true,

        "message" =>
            "Feedback submitted successfully",

        "feedback_id" =>
            (string) $result->getInsertedId()

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