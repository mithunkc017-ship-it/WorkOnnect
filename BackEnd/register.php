
<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";


/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

function registrationError(string $message): never
{
    header(
        "Location: ../FrontEnd/pages/register.html?error=" .
        rawurlencode($message)
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    registrationError(
        "Please use the registration form."
    );
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$name = trim($_POST["name"] ?? "");

$email = trim($_POST["email"] ?? "");

$password = $_POST["password"] ?? "";

$confirmPassword = $_POST["confirmPassword"] ?? "";


/*
|--------------------------------------------------------------------------
| Validate Empty Fields
|--------------------------------------------------------------------------
*/

if (
    $name === "" ||
    $email === "" ||
    $password === "" ||
    $confirmPassword === ""
) {

    registrationError(
        "Please fill in all fields."
    );
}


/*
|--------------------------------------------------------------------------
| Validate Email
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    registrationError(
        "Please enter a valid email address."
    );
}


/*
|--------------------------------------------------------------------------
| Validate Password
|--------------------------------------------------------------------------
*/

if (strlen($password) < 6) {

    registrationError(
        "Password must be at least 6 characters."
    );
}


/*
|--------------------------------------------------------------------------
| Confirm Password
|--------------------------------------------------------------------------
*/

if ($password !== $confirmPassword) {

    registrationError(
        "Passwords do not match."
    );
}


/*
|--------------------------------------------------------------------------
| Save User
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    $existingUser = $users->findOne([
        "email" => $email
    ]);


    if ($existingUser) {

        registrationError(
            "This email is already registered."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Hash Password
    |--------------------------------------------------------------------------
    */

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
    |--------------------------------------------------------------------------
    | Insert User
    |--------------------------------------------------------------------------
    */

    $result = $users->insertOne([

        "name" => $name,

        "email" => $email,

        "password" => $hashedPassword,

        "role" => "user",

        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);


    /*
    |--------------------------------------------------------------------------
    | Check Insert
    |--------------------------------------------------------------------------
    */

    if (!$result->getInsertedId()) {

        registrationError(
            "Registration failed. Please try again."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN NOTIFICATION
    |--------------------------------------------------------------------------
    |
    | A new user has successfully registered.
    | Send a notification to every admin.
    |
    */

    try {

        $notifications =
            $database->selectCollection("notifications");


        $admins = $users->find([
            "role" => "admin"
        ]);


        foreach ($admins as $admin) {

            $notifications->insertOne([

                "user_id" => $admin["_id"],

                "type" => "user_registered",

                "title" => "New User Registered",

                "message" =>
                    $name .
                    " has created a new WorkOnnect account.",

                "read" => false,

                "created_at" =>
                    new MongoDB\BSON\UTCDateTime()
            ]);
        }

    } catch (Throwable $notificationError) {

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Do NOT stop registration if notification creation fails.
        | The user account has already been created successfully.
        |
        */

        error_log(
            "Admin notification error: " .
            $notificationError->getMessage()
        );
    }


} catch (Throwable $e) {

    registrationError(
        "Registration failed: " . $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| Registration Successful
|--------------------------------------------------------------------------
*/

header(
    "Location: ../FrontEnd/pages/login.html?registered=1"
);

exit;

