<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";


/* =========================================
   ADMIN CHECK
========================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {

    header(
        "Location: ../FrontEnd/pages/login.html?error=Admin+access+required"
    );

    exit;
}


/* =========================================
   POST CHECK
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: ../FrontEnd/admin/users.php?error=Invalid+request"
    );

    exit;
}


/* =========================================
   GET USER ID
========================================= */

$userId = trim(
    $_POST["user_id"] ?? ""
);


if ($userId === "") {

    header(
        "Location: ../FrontEnd/admin/users.php?error=User+ID+missing"
    );

    exit;
}


/* =========================================
   VALIDATE OBJECT ID
========================================= */

try {

    $objectId =
        new MongoDB\BSON\ObjectId($userId);

} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/admin/users.php?error=Invalid+user+ID"
    );

    exit;
}


/* =========================================
   PREVENT SELF DELETE
========================================= */

if (
    $userId ===
    $_SESSION["user_id"]
) {

    header(
        "Location: ../FrontEnd/admin/users.php?error=You+cannot+delete+yourself"
    );

    exit;
}


/* =========================================
   DELETE USER
========================================= */

try {

    $result = $users->deleteOne([
        "_id" => $objectId
    ]);


    if (
        $result->getDeletedCount() === 1
    ) {

        header(
            "Location: ../FrontEnd/admin/users.php?deleted=1"
        );

        exit;
    }


    header(
        "Location: ../FrontEnd/admin/users.php?error=User+not+found"
    );

    exit;


} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/admin/users.php?error=" .
        rawurlencode($e->getMessage())
    );

    exit;
}