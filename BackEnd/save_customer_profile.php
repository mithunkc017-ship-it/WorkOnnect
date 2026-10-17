<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/* Check whether the user is logged in */
if (!isset($_SESSION["user_id"])) {
    header(
        "Location: ../FrontEnd/pages/login.html?error=Please+login+first"
    );
    exit;
}

if (($_SESSION["user_role"] ?? "") !== "customer") {
    header(
        "Location: ../FrontEnd/pages/role.html?error=Please+choose+the+customer+role"
    );
    exit;
}

require_once __DIR__ . "/db.php";

/* Only accept POST requests */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header(
        "Location: ../FrontEnd/pages/customer-profile.html"
    );
    exit;
}

/* Get form values */
$address = trim($_POST["address"] ?? "");
$location = trim($_POST["location"] ?? "");
$requiredService = trim($_POST["required_service"] ?? "");

/* Check required fields */
if (
    $address === "" ||
    $location === "" ||
    $requiredService === ""
) {
    header(
        "Location: ../FrontEnd/pages/customer-profile.html?error=Please+fill+all+required+fields"
    );
    exit;
}

try {

    /* Convert session ID into MongoDB ObjectId */
    $objectIdClass = "MongoDB\\BSON\\ObjectId";
    $userId = new $objectIdClass(
        $_SESSION["user_id"]
    );

    /* Save customer information */
    $result = $users->updateOne(
        [
            "_id" => $userId
        ],
        [
            '$set' => [
                "customer_profile" => [
                    "address" => $address,
                    "location" => $location,
                    "required_service" => $requiredService
                ],
                "profile_completed" => true,
                "role" => "customer"
            ]
        ]
    );

    /* Check if user was found */
    if ($result->getMatchedCount() > 0) {

        $_SESSION["user_role"] = "customer";

        header(
            "Location: ../FrontEnd/customer/customer-dashboard.php"
        );
        exit;
    }

    header(
        "Location: ../FrontEnd/pages/customer-profile.html?error=User+not+found"
    );
    exit;

} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/pages/customer-profile.html?error=Unable+to+save+your+profile"
    );

    exit;
}