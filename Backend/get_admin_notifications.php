<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json");


/* =========================================================
   ADMIN MUST BE LOGGED IN
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Admin access required"
    ]);

    exit;
}


try {

    /* =====================================================
       ADMIN ID
    ===================================================== */

    $adminId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );


    /* =====================================================
       NOTIFICATIONS COLLECTION
    ===================================================== */

    $notifications =
        $database->selectCollection(
            "notifications"
        );


    /* =====================================================
       GET NOTIFICATIONS
    ===================================================== */

    $cursor =
        $notifications->find(

            [
                "user_id" => $adminId
            ],

            [
                "sort" => [
                    "created_at" => -1
                ],

                "limit" => 20
            ]

        );


    $notificationList = [];


    foreach ($cursor as $notification) {

        $notificationList[] = [

            "id" =>
                (string) $notification["_id"],

            "type" =>
                $notification["type"] ?? "general",

            "title" =>
                $notification["title"] ?? "Notification",

            "message" =>
                $notification["message"] ?? "",

            "read" =>
                (bool) ($notification["read"] ?? false),

            "created_at" =>
                isset($notification["created_at"])
                    ? $notification["created_at"]->toDateTime()->format(
                        "c"
                    )
                    : null

        ];
    }


    /* =====================================================
       COUNT UNREAD
    ===================================================== */

    $unreadCount =
        $notifications->countDocuments(

            [
                "user_id" => $adminId,

                "read" => [
                    '$ne' => true
                ]
            ]

        );


    /* =====================================================
       RESPONSE
    ===================================================== */

    echo json_encode([

        "success" => true,

        "notifications" =>
            $notificationList,

        "unread_count" =>
            $unreadCount

    ]);


} catch (
    MongoDB\Driver\Exception\InvalidArgumentException $e
) {

    http_response_code(400);

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid admin ID"

    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            "Could not load notifications",

        "error" =>
            $e->getMessage()

    ]);

}