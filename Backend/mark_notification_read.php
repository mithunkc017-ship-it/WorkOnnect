<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json");


/* =========================================================
   USER MUST BE LOGGED IN
========================================================= */

if (!isset($_SESSION["user_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}


/* =========================================================
   REQUEST MUST CONTAIN NOTIFICATION ID
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    empty($_POST["notification_id"])
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}


try {

    $userId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );


    $notificationId =
        new MongoDB\BSON\ObjectId(
            $_POST["notification_id"]
        );


    $notifications =
        $database->selectCollection(
            "notifications"
        );


    /* =====================================================
       MARK ONLY THIS USER'S NOTIFICATION AS READ
    ===================================================== */

    $result =
        $notifications->updateOne(

            [
                "_id" => $notificationId,

                "user_id" => $userId
            ],

            [
                '$set' => [
                    "read" => true
                ]
            ]

        );


    /* =====================================================
       CHECK RESULT
    ===================================================== */

    if (
        $result->getMatchedCount() === 0
    ) {

        http_response_code(404);

        echo json_encode([

            "success" => false,

            "message" =>
                "Notification not found"

        ]);

        exit;
    }


    echo json_encode([

        "success" => true,

        "message" =>
            "Notification marked as read"

    ]);


} catch (
    MongoDB\Driver\Exception\InvalidArgumentException $e
) {

    http_response_code(400);

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid notification ID"

    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            "Could not update notification"

    ]);

}