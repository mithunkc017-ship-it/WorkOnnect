<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";


/* =========================================================
   CHECK WORKER LOGIN
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "worker"
) {
    header(
        "Location: ../FrontEnd/pages/login.html?error=Please+login+as+a+worker"
    );
    exit;
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html"
    );
    exit;
}


/* =========================================================
   USER ID
========================================================= */

$userId = $_SESSION["user_id"];


/* =========================================================
   FORM DATA
========================================================= */

$workerName =
    trim($_POST["worker_name"] ?? "");

$contactNumber =
    trim($_POST["contact_number"] ?? "");

$experience =
    trim($_POST["experience"] ?? "");

$serviceCategory =
    trim($_POST["service_category"] ?? "");

$skills =
    trim($_POST["skills"] ?? "");

$location =
    trim($_POST["location"] ?? "");

$availability =
    trim($_POST["availability"] ?? "");

$about =
    trim($_POST["about"] ?? "");


/* =========================================================
   VALIDATION
========================================================= */

if ($workerName === "") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=Please+enter+your+name"
    );
    exit;
}

if ($contactNumber === "") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=Please+enter+contact+number"
    );
    exit;
}

if ($experience === "") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=Please+enter+experience"
    );
    exit;
}

if ($serviceCategory === "") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=Please+select+a+service"
    );
    exit;
}

if ($location === "") {
    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=Please+enter+location"
    );
    exit;
}


/* =========================================================
   GET EXISTING WORKER PROFILE
========================================================= */

try {

    $existingWorker = $users->findOne([
        "_id" => new MongoDB\BSON\ObjectId($userId)
    ]);

    $existingProfile =
        $existingWorker["worker_profile"] ?? [];

} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=" .
        rawurlencode("Could not load existing profile")
    );

    exit;
}


/* =========================================================
   PROFILE DATA
========================================================= */

/* =========================================================
   KEEP EXISTING APPROVAL STATUS
========================================================= */

$approvalStatus =
    $existingProfile["approval_status"] ?? "pending";


/* =========================================================
   PROFILE DATA
========================================================= */

$profileData = [

    "worker_name" =>
        $workerName,

    "contact_number" =>
        $contactNumber,

    "experience" =>
        $experience,

    "service_category" =>
        $serviceCategory,

    "skills" =>
        $skills,

    "location" =>
        $location,

    "availability" =>
        $availability,

    "about" =>
        $about,

    "profile_completed" =>
        true,

    "approval_status" =>
        $approvalStatus,

    "updated_at" =>
        new MongoDB\BSON\UTCDateTime()
];

/* =========================================================
   KEEP EXISTING PROFILE IMAGE
========================================================= */

if (
    !empty($existingProfile["profile_image"])
) {

    $profileData["profile_image"] =
        $existingProfile["profile_image"];
}


/* =========================================================
   NEW PROFILE IMAGE
========================================================= */

if (
    isset($_FILES["profile_image"]) &&
    $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES["profile_image"]["error"]
        !== UPLOAD_ERR_OK
    ) {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html?error=Image+upload+failed"
        );

        exit;
    }


    /* Maximum 5 MB */

    if (
        $_FILES["profile_image"]["size"]
        > 5 * 1024 * 1024
    ) {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html?error=Image+must+be+less+than+5MB"
        );

        exit;
    }


    $tmpName =
        $_FILES["profile_image"]["tmp_name"];


    $imageInfo =
        getimagesize($tmpName);


    if ($imageInfo === false) {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html?error=Invalid+image"
        );

        exit;
    }


    $allowedTypes = [

        "image/jpeg" => "jpg",

        "image/png" => "png",

        "image/webp" => "webp"

    ];


    $mimeType =
        $imageInfo["mime"];


    if (
        !isset($allowedTypes[$mimeType])
    ) {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html?error=Only+JPG,+PNG+or+WEBP+allowed"
        );

        exit;
    }


    /* Create upload folder */

    $uploadDirectory =
        __DIR__ . "/uploads/worker_profiles/";


    if (
        !is_dir($uploadDirectory)
    ) {

        mkdir(
            $uploadDirectory,
            0777,
            true
        );
    }


    /* Unique filename */

    $extension =
        $allowedTypes[$mimeType];


    $fileName =
        "worker_" .
        preg_replace(
            "/[^a-zA-Z0-9]/",
            "",
            $userId
        ) .
        "_" .
        time() .
        "." .
        $extension;


    $destination =
        $uploadDirectory . $fileName;


    if (
        !move_uploaded_file(
            $tmpName,
            $destination
        )
    ) {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html?error=Could+not+save+image"
        );

        exit;
    }


    /* Replace old image path with new image */

    $profileData["profile_image"] =
        "BackEnd/uploads/worker_profiles/" .
        $fileName;
}


/* =========================================================
   UPDATE MONGODB
========================================================= */

try {

    $result = $users->updateOne(

        [
            "_id" =>
                new MongoDB\BSON\ObjectId($userId)
        ],

        [
            '$set' => [

                "worker_profile" =>
                    $profileData

            ]
        ]

    );


    /* =====================================================
       SUCCESS
    ===================================================== */

    header(
        "Location: ../FrontEnd/worker/worker-dashboard.php?success=Profile+saved+successfully"
    );

    exit;


} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/pages/worker-profile.html?error=" .
        rawurlencode(
            "Could not save profile: " .
            $e->getMessage()
        )
    );

    exit;
}