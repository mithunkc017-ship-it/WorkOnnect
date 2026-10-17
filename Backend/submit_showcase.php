<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION["user_id"])) {
    header(
        "Location: ../FrontEnd/pages/login.html?error=Please+login+first"
    );
    exit;
}

require_once __DIR__ . "/db.php";


/* =========================================
   REQUEST METHOD CHECK
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header(
        "Location: ../FrontEnd/pages/showcase-submit.html?error=Invalid+request"
    );
    exit;
}


/* =========================================
   GET FORM DATA
========================================= */

$name = trim($_POST["name"] ?? "");

$instagram = trim($_POST["instagram"] ?? "");

$description = trim($_POST["description"] ?? "");


/* =========================================
   REQUIRED FIELD VALIDATION
========================================= */

if ($name === "") {
    header(
        "Location: ../FrontEnd/pages/showcase-submit.html?error=Name+is+required"
    );
    exit;
}

if ($instagram === "") {
    header(
        "Location: ../FrontEnd/pages/showcase-submit.html?error=Instagram+ID+is+required"
    );
    exit;
}


/* =========================================
   IMAGE VARIABLES
========================================= */

$filePath = null;

$imageDatabasePath = null;


/* =========================================
   IMAGE IS OPTIONAL
========================================= */

if (
    isset($_FILES["showcase_image"]) &&
    $_FILES["showcase_image"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    $image = $_FILES["showcase_image"];


    /* -----------------------------------------
       CHECK UPLOAD ERROR
    ----------------------------------------- */

    if ($image["error"] !== UPLOAD_ERR_OK) {

        header(
            "Location: ../FrontEnd/pages/showcase-submit.html?error=Image+upload+failed"
        );

        exit;
    }


    /* -----------------------------------------
       MAXIMUM FILE SIZE: 5MB
    ----------------------------------------- */

    if ($image["size"] > 5 * 1024 * 1024) {

        header(
            "Location: ../FrontEnd/pages/showcase-submit.html?error=Image+must+be+under+5MB"
        );

        exit;
    }


    /* -----------------------------------------
       CHECK IMAGE TYPE
    ----------------------------------------- */

    $allowedTypes = [
        "image/jpeg",
        "image/png"
    ];


    $imageInfo = getimagesize(
        $image["tmp_name"]
    );


    if (
        $imageInfo === false ||
        !in_array(
            $imageInfo["mime"],
            $allowedTypes,
            true
        )
    ) {

        header(
            "Location: ../FrontEnd/pages/showcase-submit.html?error=Only+JPG+and+PNG+images+are+allowed"
        );

        exit;
    }


    /* -----------------------------------------
       CREATE UPLOAD DIRECTORY
    ----------------------------------------- */

    $uploadDirectory =
        __DIR__ . "/showcase_uploads";


    if (!is_dir($uploadDirectory)) {

        mkdir(
            $uploadDirectory,
            0755,
            true
        );
    }


    /* -----------------------------------------
       GET FILE EXTENSION
    ----------------------------------------- */

    $extension =
        strtolower(
            pathinfo(
                $image["name"],
                PATHINFO_EXTENSION
            )
        );


    /* -----------------------------------------
       CREATE UNIQUE FILE NAME
    ----------------------------------------- */

    $fileName =
        uniqid(
            "showcase_",
            true
        ) . "." . $extension;


    /* -----------------------------------------
       FINAL FILE PATH
    ----------------------------------------- */

    $filePath =
        $uploadDirectory .
        "/" .
        $fileName;


    /* -----------------------------------------
       MOVE IMAGE
    ----------------------------------------- */

    if (
        !move_uploaded_file(
            $image["tmp_name"],
            $filePath
        )
    ) {

        header(
            "Location: ../FrontEnd/pages/showcase-submit.html?error=Image+upload+failed"
        );

        exit;
    }


    /* -----------------------------------------
       PATH STORED IN DATABASE
    ----------------------------------------- */

    $imageDatabasePath =
        "BackEnd/showcase_uploads/" .
        $fileName;
}


/* =========================================
   SAVE TO MONGODB
========================================= */

try {

    $showcases =
        $database->selectCollection(
            "showcases"
        );


    /* -----------------------------------------
       BASIC SHOWCASE DATA
    ----------------------------------------- */

    $showcaseData = [

        "user_id" =>
            new MongoDB\BSON\ObjectId(
                $_SESSION["user_id"]
            ),

        "name" =>
            $name,

        "instagram" =>
            $instagram,

        "description" =>
            $description,

        "status" =>
            "pending",

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ];


    /* -----------------------------------------
       ADD IMAGE ONLY IF ONE WAS UPLOADED
    ----------------------------------------- */

    if ($imageDatabasePath !== null) {

        $showcaseData["image"] =
            $imageDatabasePath;
    }


    /* -----------------------------------------
       INSERT INTO MONGODB
    ----------------------------------------- */

    $showcases->insertOne(
        $showcaseData
    );


    /* =========================================
       SUCCESS
    ========================================= */

    header(
        "Location: ../FrontEnd/pages/showcase-submit.html?success=1"
    );

    exit;


} catch (Throwable $e) {


    /* =========================================
       DELETE UPLOADED FILE IF DB FAILED
    ========================================= */

    if (
        $filePath !== null &&
        file_exists($filePath)
    ) {

        unlink($filePath);
    }


    /* =========================================
       ERROR
    ========================================= */

    header(
        "Location: ../FrontEnd/pages/showcase-submit.html?error=" .
        rawurlencode(
            $e->getMessage()
        )
    );

    exit;
}