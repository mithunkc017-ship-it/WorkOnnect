<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../FrontEnd/pages/login.html?error=Please+login+first");
    exit;
}

require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FrontEnd/pages/role.html");
    exit;
}

$role = $_POST["role"] ?? "";

if (!in_array($role, ["customer", "worker"], true)) {
    header(
        "Location: ../FrontEnd/pages/role.html?error=Invalid+role"
    );
    exit;
}

try {

    $users->updateOne(
        [
            "_id" => new MongoDB\BSON\ObjectId(
                $_SESSION["user_id"]
            )
        ],
        [
            '$set' => [
                "role" => $role
            ]
        ]
    );

    $_SESSION["user_role"] = $role;

    if ($role === "customer") {

        header(
            "Location: ../FrontEnd/pages/customer-profile.html"
        );

    } else {

        header(
            "Location: ../FrontEnd/pages/worker-profile.html"
        );

    }

    exit;

} catch (Throwable $e) {

    header(
        "Location: ../FrontEnd/pages/role.html?error=" .
        rawurlencode($e->getMessage())
    );

    exit;
}