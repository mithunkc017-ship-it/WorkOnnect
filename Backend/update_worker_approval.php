<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json");


/* =========================================================
   ONLY ADMIN CAN APPROVE / REJECT WORKERS
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
   VALIDATE REQUEST
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}


$workerId = trim($_POST["worker_id"] ?? "");
$status   = trim($_POST["status"] ?? "");


if (
    $workerId === "" ||
    !in_array($status, ["approved", "rejected"], true)
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid worker or status"
    ]);

    exit;
}


/* =========================================================
   UPDATE WORKER + CREATE NOTIFICATION
========================================================= */

try {

    $objectId = new MongoDB\BSON\ObjectId($workerId);


    /* ---------------------------------------------------------
       GET WORKER
    --------------------------------------------------------- */

    $worker = $users->findOne([
        "_id" => $objectId,
        "role" => "worker"
    ]);


    if (!$worker) {

        echo json_encode([
            "success" => false,
            "message" => "Worker not found"
        ]);

        exit;
    }


    /* ---------------------------------------------------------
       UPDATE APPROVAL STATUS
    --------------------------------------------------------- */

    $result = $users->updateOne(
        [
            "_id" => $objectId,
            "role" => "worker"
        ],
        [
            '$set' => [
                "worker_profile.approval_status" => $status,

                "worker_profile.approval_updated_at" =>
                    new MongoDB\BSON\UTCDateTime(),

                "worker_profile.approved_by" =>
                    $_SESSION["user_id"]
            ]
        ]
    );


    if ($result->getModifiedCount() === 0) {

        echo json_encode([
            "success" => false,
            "message" => "Worker status was not changed"
        ]);

        exit;
    }


    /* ---------------------------------------------------------
       CREATE NOTIFICATION
    --------------------------------------------------------- */

    $notifications = $database->selectCollection("notifications");


    if ($status === "approved") {

        $title = "Profile Approved";

        $message =
            "Your worker profile has been approved. " .
            "You are now visible to customers.";

        $type = "worker_approval";

    } else {

        $title = "Profile Rejected";

        $message =
            "Your worker profile was rejected. " .
            "Please review your profile and make the necessary changes.";

        $type = "worker_rejection";
    }


    $notifications->insertOne([

        "user_id" => $worker["_id"],

        "type" => $type,

        "title" => $title,

        "message" => $message,

        "read" => false,

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ]);


    /* ---------------------------------------------------------
       SUCCESS RESPONSE
    --------------------------------------------------------- */

    echo json_encode([

        "success" => true,

        "message" =>
            $status === "approved"
                ? "Worker approved successfully"
                : "Worker rejected successfully"

    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" => "Could not update worker approval"

    ]);

}