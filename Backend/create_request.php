<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";


/*
|--------------------------------------------------------------------------
| ONLY LOGGED-IN CUSTOMERS
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "customer"
) {

    header(
        "Location: ../FrontEnd/pages/login.html?error=Please+log+in+as+a+customer"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: ../FrontEnd/customer/customer-dashboard.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$workerId = trim($_POST["worker_id"] ?? "");
$service = trim($_POST["service"] ?? "");
$message = trim($_POST["message"] ?? "");
$preferredDate = trim($_POST["preferred_date"] ?? "");


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $workerId === "" ||
    $service === "" ||
    $message === "" ||
    $preferredDate === ""
) {

    // header(
    //     "Location: ../FrontEnd/pages/worker-profile-view.php?id="
    //     . rawurlencode($workerId)
    //     . "&error=Please+fill+all+fields"
    // );
    header(
    "Location: ../FrontEnd/customer/worker-profile-view.php?id="
    . rawurlencode($workerId)
    . "&error=Please+fill+all+fields"
);
    exit;
}


/*
|--------------------------------------------------------------------------
| LIMIT INPUT LENGTH
|--------------------------------------------------------------------------
*/

if (
    mb_strlen($service) > 100 ||
    mb_strlen($message) > 1000
) {

    // header(
    //     "Location: ../FrontEnd/pages/worker-profile-view.php?id="
    //     . rawurlencode($workerId)
    //     . "&error=Input+is+too+long"
    // );
    header(
    "Location: ../FrontEnd/customer/worker-profile-view.php?id="
    . rawurlencode($workerId)
    . "&error=Please+fill+all+fields"
);
    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER OBJECT ID
    |--------------------------------------------------------------------------
    */

    try {

        $customerId = new MongoDB\BSON\ObjectId(
            $_SESSION["user_id"]
        );

    } catch (Throwable $e) {

        session_destroy();

        header(
            "Location: ../FrontEnd/pages/login.html?error=Invalid+session"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | WORKER OBJECT ID
    |--------------------------------------------------------------------------
    */

    try {

        $workerObjectId = new MongoDB\BSON\ObjectId(
            $workerId
        );

    } catch (Throwable $e) {

        header(
            "Location: ../FrontEnd/customer/customer-dashboard.php?error=Invalid+worker"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT CUSTOMER FROM REQUESTING THEMSELVES
    |--------------------------------------------------------------------------
    */

    if ((string)$customerId === (string)$workerObjectId) {

        header(
            "Location: ../FrontEnd/customer/customer-dashboard.php?error=Invalid+worker"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET CUSTOMER
    |--------------------------------------------------------------------------
    */

    $customer = $users->findOne([
        "_id" => $customerId,
        "role" => "customer"
    ]);


    if (!$customer) {

        session_destroy();

        header(
            "Location: ../FrontEnd/pages/login.html?error=Customer+account+not+found"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET APPROVED WORKER
    |--------------------------------------------------------------------------
    */

    $worker = $users->findOne([
        "_id" => $workerObjectId,
        "role" => "worker",
        "worker_profile.approval_status" => "approved"
    ]);


    if (!$worker) {

        header(
            "Location: ../FrontEnd/customer/customer-dashboard.php?error=Worker+not+available"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | WORKER PROFILE
    |--------------------------------------------------------------------------
    */

    $workerProfile = $worker["worker_profile"] ?? [];


    $workerName =
        $workerProfile["worker_name"]
        ?? $worker["name"]
        ?? "Worker";


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER NAME
    |--------------------------------------------------------------------------
    */

    $customerName =
        $customer["name"]
        ?? "Customer";


    /*
    |--------------------------------------------------------------------------
    | CREATE REQUEST
    |--------------------------------------------------------------------------
    */

    $request = [

        "customer_id" =>
            $customerId,

        "worker_id" =>
            $workerObjectId,

        "customer_name" =>
            $customerName,

        "worker_name" =>
            $workerName,

        "service" =>
            $service,

        "message" =>
            $message,

        "preferred_date" =>
            $preferredDate,

        "status" =>
            "pending",

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ];


    /*
    |--------------------------------------------------------------------------
    | SERVICE REQUESTS COLLECTION
    |--------------------------------------------------------------------------
    */

    $requests = $database->selectCollection(
        "service_requests"
    );


    /*
    |--------------------------------------------------------------------------
    | INSERT REQUEST
    |--------------------------------------------------------------------------
    */

    $result = $requests->insertOne(
        $request
    );


    if ($result->getInsertedCount() !== 1) {

        // header(
        //     "Location: ../FrontEnd/pages/worker-profile-view.php?id="
        //     . rawurlencode($workerId)
        //     . "&error=Request+could+not+be+created"
        // );
        header(
    "Location: ../FrontEnd/customer/worker-profile-view.php?id="
    . rawurlencode($workerId)
    . "&error=Please+fill+all+fields"
);
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE WORKER NOTIFICATION
    |--------------------------------------------------------------------------
    */

    $notifications = $database->selectCollection(
        "notifications"
    );


    $notifications->insertOne([

        "user_id" =>
            $workerObjectId,

        "type" =>
            "service_request",

        "title" =>
            "New Service Request",

        "message" =>
            $customerName .
            " has requested your " .
            $service .
            " service.",

        "read" =>
            false,

        "request_id" =>
            $result->getInsertedId(),

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ]);


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../FrontEnd/customer/customer-dashboard.php?request=success"
    );

    exit;


} catch (Throwable $e) {

    error_log(
        "CREATE REQUEST ERROR: " .
        $e->getMessage()
    );

    // header(
    //     "Location: ../FrontEnd/pages/worker-profile-view.php?id="
    //     . rawurlencode($workerId)
    //     . "&error=Request+could+not+be+created"
    // );
    header(
    "Location: ../FrontEnd/customer/worker-profile-view.php?id="
    . rawurlencode($workerId)
    . "&error=Please+fill+all+fields"
);

    exit;
}

?>