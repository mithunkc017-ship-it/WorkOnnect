<?php

error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");
session_start();

require_once __DIR__ . "/db.php";


/* =========================================
   LOGIN ERROR
   ========================================= */

function loginError(string $message): never
{
    header(
        "Location: ../FrontEnd/pages/login.html?error=" .
        rawurlencode($message)
    );

    exit;
}


/* =========================================
   CHECK REQUEST
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    loginError("Please use the login form.");

}


/* =========================================
   GET FORM DATA
   ========================================= */

        $email = trim($_POST["email"] ?? "");

        $password = $_POST["password"] ?? "";


        /* =========================================
        VALIDATION
        ========================================= */

        if ($email === "" || $password === "") {

            loginError(
                "Please enter your email and password."
            );

        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            loginError(
                "Please enter a valid email address."
            );

        }


        /* =========================================
        FIND USER
        ========================================= */

        $user = $users->findOne([
            "email" => $email
        ]);

     
        if (!$user) {
            error_log("WorkOnnect login diagnostic: admin email not found in selected database.");
            loginError("Invalid email or password.");
        }

        if (!isset($user["password"])) {
            error_log("WorkOnnect login diagnostic: password field missing.");
            loginError("Invalid email or password.");
        }

        if (!password_verify($password, $user["password"])) {
            error_log("WorkOnnect login diagnostic: password verification failed.");
            loginError("Invalid email or password.");
        }
 try {

//     $user = $users->findOne([
//         "email" => $email
//     ]);


//     if (!$user) {

//         loginError(
//             "Invalid email or password."
//         );

//     }


    /* =====================================
       VERIFY PASSWORD
       ===================================== */

    if (
        !isset($user["password"]) ||
        !password_verify(
            $password,
            $user["password"]
        )
    ) {

        loginError(
            "Invalid email or password."
        );

    }


    /* =====================================
       CREATE SESSION
       ===================================== */

    $_SESSION["user_id"] =
        (string) $user["_id"];

    $_SESSION["user_name"] =
        $user["name"] ?? "";

    $_SESSION["user_email"] =
        $user["email"] ?? "";

    $_SESSION["user_role"] =
        $user["role"] ?? "user";


    /* =====================================
       ROLE
       ===================================== */

    $role =
        $_SESSION["user_role"];


    /* =====================================
       ADMIN
       ===================================== */

    if ($role === "admin") {

        header(
            "Location: ../FrontEnd/admin/dash_board.php"
        );

        exit;
    }


    /* =====================================
       WORKER
       ===================================== */

    if ($role === "worker") {

        header(
            "Location: ../FrontEnd/worker/worker-dashboard.php"
        );

        exit;
    }


    /* =====================================
       CUSTOMER
       ===================================== */

    if ($role === "customer") {

        header(
            "Location: ../FrontEnd/customer/customer-dashboard.php"
        );

        exit;
    }


    /* =====================================
       NEW USER
       ===================================== */

    header(
        "Location: ../FrontEnd/pages/role.html"
    );

    exit;


} catch (Throwable $e) {

    loginError(
        "Login failed: " .
        $e->getMessage()
    );

}