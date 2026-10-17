
<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/db.php";


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


try {

    $userId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );

    $notifications =
        $database->selectCollection("notifications");


    /* =====================================================
       GET USER NOTIFICATIONS
    ===================================================== */

    $cursor = $notifications->find(
        [
            "user_id" => $userId
        ],
        [
            "sort" => [
                "created_at" => -1
            ],
            "limit" => 30
        ]
    );


    $items = [];


    foreach ($cursor as $notification) {

        $items[] = [

            "id" =>
                (string) $notification["_id"],

            "type" =>
                $notification["type"] ?? "system",

            "title" =>
                $notification["title"] ?? "Notification",

            "message" =>
                $notification["message"] ?? "",

            "read" =>
                $notification["read"] ?? false,

            "request_id" =>
                isset($notification["request_id"])
                    ? (string) $notification["request_id"]
                    : null,

            "created_at" =>
                isset($notification["created_at"])
                    ? $notification["created_at"]
                        ->toDateTime()
                        ->format("Y-m-d H:i:s")
                    : ""

        ];
    }


    /* =====================================================
       UNREAD COUNT
    ===================================================== */

    $unreadCount =
        $notifications->countDocuments([
            "user_id" => $userId,
            "read" => false
        ]);


    /* =====================================================
       RESPONSE
    ===================================================== */

    echo json_encode([

        "success" => true,

        "count" => count($items),

        "unread_count" => $unreadCount,

        "notifications" => $items

    ]);


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}
?>

