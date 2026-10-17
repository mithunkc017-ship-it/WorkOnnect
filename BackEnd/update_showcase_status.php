<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/* =========================================
   ADMIN ONLY
========================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {
    header(
        "Location: ../FrontEnd/pages/login.html?error=Unauthorized"
    );
    exit;
}

require_once __DIR__ . "/db.php";


/* =========================================
   POST ONLY
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header(
        "Location: ../FrontEnd/admin/showcase.php?error=Invalid+request"
    );
    exit;
}


$showcaseId = trim($_POST["showcase_id"] ?? "");
$status = trim($_POST["status"] ?? "");


/* =========================================
   VALIDATION
========================================= */

if ($showcaseId === "") {
    header(
        "Location: ../FrontEnd/admin/showcase.php?error=Showcase+ID+is+missing"
    );
    exit;
}

if (!in_array($status, ["approved", "rejected"], true)) {
    header(
        "Location: ../FrontEnd/admin/showcase.php?error=Invalid+status"
    );
    exit;
}


/* =========================================
   UPDATE
========================================= */

try {

    $showcases =
        $database->selectCollection("showcases");

    $result =
        $showcases->updateOne(
            [
                "_id" =>
                    new MongoDB\BSON\ObjectId(
                        $showcaseId
                    )
            ],
            [
                '$set' => [
                    "status" => $status,
                    "reviewed_at" =>
                        new MongoDB\BSON\UTCDateTime(),
                    "reviewed_by" =>
                        new MongoDB\BSON\ObjectId(
                            $_SESSION["user_id"]
                        )
                ]
            ]
        );


    if ($result->getMatchedCount() === 0) {

        header(
            "Location: ../FrontEnd/admin/showcase.php?error=Submission+not+found"
        );

        exit;
    }


    /* =========================================
       SUCCESS
    ========================================= */

    header(
        "Location: ../FrontEnd/admin/showcase.php?success=" .
        $status
    );

    exit;


} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/admin/showcase.php?error=" .
        rawurlencode(
            $e->getMessage()
        )
    );

    exit;
}