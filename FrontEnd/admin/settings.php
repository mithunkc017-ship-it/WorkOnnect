<?php 

error_reporting(E_ALL);
ini_set("display_errors", "1");
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {
    header("Location: ../pages/login.html?error=Admin+access+required");
    exit;
}

require_once __DIR__ . "/../../BackEnd/db.php";

use MongoDB\BSON\ObjectId;

$adminName = $_SESSION["user_name"] ?? "Admin";
$adminEmail = "";
$adminSettings = [];

/*
|--------------------------------------------------------------------------
| FIND CURRENT ADMIN
|--------------------------------------------------------------------------
*/

try {

    $adminId = $_SESSION["user_id"];

    $adminObjectId = null;

    try {
        $adminObjectId = new ObjectId($adminId);
    } catch (Throwable $e) {
        $adminObjectId = null;
    }

    $admin = null;

    if ($adminObjectId) {

        $admin = $users->findOne([
            "_id" => $adminObjectId,
            "role" => "admin"
        ]);

    }

    if (!$admin) {

        $admin = $users->findOne([
            "_id" => $adminId,
            "role" => "admin"
        ]);

    }

    if ($admin) {

    $adminName =
        $admin["name"] ??
        $adminName;

    $adminEmail =
        $admin["email"] ??
        "";

    $adminSettings =
        $admin["admin_settings"] ??
        [];

    /*
    |--------------------------------------------------------------------------
    | CONVERT MONGODB BSON DOCUMENT TO PHP ARRAY
    |--------------------------------------------------------------------------
    */

    if ($adminSettings instanceof \MongoDB\Model\BSONDocument) {

        $adminSettings =
            $adminSettings->getArrayCopy();
    }

    /*
    |--------------------------------------------------------------------------
    | SAFETY CHECK
    |--------------------------------------------------------------------------
    */

    if (!is_array($adminSettings)) {

        $adminSettings = [];
    }
}

} catch (Throwable $e) {

    $adminEmail = "";
    $adminSettings = [];
}


/*
|--------------------------------------------------------------------------
| DEFAULT SETTINGS
|--------------------------------------------------------------------------
*/

$defaults = [

    "platform_name" => "WorkOnnect",

    "registration_enabled" => true,

    "maintenance_mode" => false,

    "worker_approval_required" => true,

    "worker_visible_after_approval" => true,

    "worker_approval_notification" => true,

    "customer_connect_enabled" => true,

    "customer_complaints_enabled" => true,

    "customer_feedback_enabled" => true,

    "complaints_enabled" => true,

    "warning_system_enabled" => true,

    "auto_suspend_enabled" => false,

    "warning_limit" => 3,

    "notify_new_users" => true,

    "notify_new_workers" => true,

    "notify_complaints" => true,

    "notify_feedback" => true,

    "notify_worker_approval" => true,

    "session_timeout" => 60,

    "strong_passwords" => true,

    "login_protection" => true,

    "audit_logging" => true,

    "audit_logging" => true,

/*
|--------------------------------------------------------------------------
| SMART MODERATION AUTOMATION
|--------------------------------------------------------------------------
*/

"moderation_automation_enabled" => true,

"auto_complaint_acknowledgement" => true,

"auto_warning_enabled" => false,

"auto_suspend_enabled" => false,

"auto_close_complaints" => false,

"complaint_review_required" => true,

"complaint_warning_threshold" => 3,

/*
|--------------------------------------------------------------------------
| MODERATION MESSAGE TEMPLATES
|--------------------------------------------------------------------------
*/

"complaint_received_message" =>
    "Hello {customer_name}, your complaint has been received successfully. Our administration team will review it shortly.",

"worker_warning_message" =>
    "Hello {worker_name}, a complaint has been raised regarding your service. Please review the issue and maintain professional service standards. Warning {warning_count} of {warning_limit}.",

"worker_suspension_message" =>
    "Hello {worker_name}, your WorkOnnect worker account has been temporarily suspended because the allowed warning limit was reached. Please contact administration for further assistance.",

"complaint_resolved_message" =>
    "Hello {customer_name}, your complaint has been reviewed by the WorkOnnect administration team and has been marked as resolved.",

"worker_approval_message" =>
    "Hello {worker_name}, your WorkOnnect worker profile has been approved. You are now visible to customers.",

"worker_rejection_message" =>
    "Hello {worker_name}, your worker profile could not be approved at this time. Please review your profile information and contact administration if necessary."
];


foreach ($defaults as $key => $value) {

    if (!array_key_exists($key, $adminSettings)) {

        $adminSettings[$key] = $value;
    }
}


/*
|--------------------------------------------------------------------------
| HANDLE SETTINGS SAVE
|--------------------------------------------------------------------------
*/

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT UPDATE
    |--------------------------------------------------------------------------
    */

    if ($action === "update_account") {

        $currentPassword =
            $_POST["current_password"] ?? "";

        $newName =
            trim($_POST["admin_name"] ?? "");

        $newEmail =
            trim($_POST["admin_email"] ?? "");

        $newPassword =
            $_POST["new_password"] ?? "";

        $confirmPassword =
            $_POST["confirm_password"] ?? "";

        try {

            $adminId =
                $_SESSION["user_id"];

            $adminObjectId =
                null;

            try {

                $adminObjectId =
                    new ObjectId($adminId);

            } catch (Throwable $e) {}

            $admin = null;

            if ($adminObjectId) {

                $admin =
                    $users->findOne([
                        "_id" => $adminObjectId,
                        "role" => "admin"
                    ]);
            }

            if (!$admin) {

                $admin =
                    $users->findOne([
                        "_id" => $adminId,
                        "role" => "admin"
                    ]);
            }

            if (!$admin) {

                throw new Exception(
                    "Admin account could not be found."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BASIC VALIDATION
            |--------------------------------------------------------------------------
            */

            if ($newName === "") {

                throw new Exception(
                    "Admin name cannot be empty."
                );
            }

            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {

                throw new Exception(
                    "Please enter a valid email address."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK IF EMAIL CHANGED
            |--------------------------------------------------------------------------
            */

            $emailChanged =
                strtolower($newEmail) !==
                strtolower($admin["email"] ?? "");


            $passwordChanging =
                $newPassword !== "";


            /*
            |--------------------------------------------------------------------------
            | CURRENT PASSWORD REQUIRED
            |--------------------------------------------------------------------------
            */

            if ($emailChanged || $passwordChanging) {

                if ($currentPassword === "") {

                    throw new Exception(
                        "Enter your current password to change account security details."
                    );
                }


                $storedPassword =
                    $admin["password"] ?? "";

                if (
                    !$storedPassword ||
                    !password_verify(
                        $currentPassword,
                        $storedPassword
                    )
                ) {

                    throw new Exception(
                        "Current password is incorrect."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK EMAIL DUPLICATE
            |--------------------------------------------------------------------------
            */

            if ($emailChanged) {

                $existingUser =
                    $users->findOne([
                        "email" => $newEmail,
                        "_id" => [
                            '$ne' =>
                                $adminObjectId
                                ?? $adminId
                        ]
                    ]);

                if ($existingUser) {

                    throw new Exception(
                        "This email address is already being used."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE DATA
            |--------------------------------------------------------------------------
            */

            $updateData = [

                "name" =>
                    $newName,

                "email" =>
                    $newEmail,

                "updated_at" =>
                    new MongoDB\BSON\UTCDateTime()
            ];


            /*
            |--------------------------------------------------------------------------
            | PASSWORD
            |--------------------------------------------------------------------------
            */

            if ($passwordChanging) {

                if (strlen($newPassword) < 8) {

                    throw new Exception(
                        "New password must contain at least 8 characters."
                    );
                }

                if ($newPassword !== $confirmPassword) {

                    throw new Exception(
                        "New passwords do not match."
                    );
                }

                $updateData["password"] =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE
            |--------------------------------------------------------------------------
            */

            if ($adminObjectId) {

                $users->updateOne(
                    [
                        "_id" =>
                            $adminObjectId
                    ],
                    [
                        '$set' =>
                            $updateData
                    ]
                );

            } else {

                $users->updateOne(
                    [
                        "_id" =>
                            $adminId
                    ],
                    [
                        '$set' =>
                            $updateData
                    ]
                );
            }


            $_SESSION["user_name"] =
                $newName;

            $_SESSION["user_email"] =
                $newEmail;


            $adminName =
                $newName;

            $adminEmail =
                $newEmail;


            $successMessage =
                "Admin account updated successfully.";

        } catch (Throwable $e) {

            $errorMessage =
                $e->getMessage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PLATFORM SETTINGS
    |--------------------------------------------------------------------------
    */

    if ($action === "save_settings") {

        try {

            $settings = [

                "platform_name" =>
                    trim(
                        $_POST["platform_name"]
                        ?? "WorkOnnect"
                    ),

                "registration_enabled" =>
                    isset(
                        $_POST["registration_enabled"]
                    ),

                "maintenance_mode" =>
                    isset(
                        $_POST["maintenance_mode"]
                    ),

                "worker_approval_required" =>
                    isset(
                        $_POST["worker_approval_required"]
                    ),

                "worker_visible_after_approval" =>
                    isset(
                        $_POST["worker_visible_after_approval"]
                    ),

                "worker_approval_notification" =>
                    isset(
                        $_POST["worker_approval_notification"]
                    ),

                "customer_connect_enabled" =>
                    isset(
                        $_POST["customer_connect_enabled"]
                    ),

                "customer_complaints_enabled" =>
                    isset(
                        $_POST["customer_complaints_enabled"]
                    ),

                "customer_feedback_enabled" =>
                    isset(
                        $_POST["customer_feedback_enabled"]
                    ),

                "complaints_enabled" =>
                    isset(
                        $_POST["complaints_enabled"]
                    ),

                "warning_system_enabled" =>
                    isset(
                        $_POST["warning_system_enabled"]
                    ),

                "auto_suspend_enabled" =>
                    isset(
                        $_POST["auto_suspend_enabled"]
                    ),

                "warning_limit" =>
                    max(
                        1,
                        min(
                            20,
                            (int)(
                                $_POST["warning_limit"]
                                ?? 3
                            )
                        )
                    ),

                "notify_new_users" =>
                    isset(
                        $_POST["notify_new_users"]
                    ),

                "notify_new_workers" =>
                    isset(
                        $_POST["notify_new_workers"]
                    ),

                "notify_complaints" =>
                    isset(
                        $_POST["notify_complaints"]
                    ),

                "notify_feedback" =>
                    isset(
                        $_POST["notify_feedback"]
                    ),

                "notify_worker_approval" =>
                    isset(
                        $_POST["notify_worker_approval"]
                    ),

                "session_timeout" =>
                    max(
                        15,
                        min(
                            480,
                            (int)(
                                $_POST["session_timeout"]
                                ?? 60
                            )
                        )
                    ),

                "strong_passwords" =>
                    isset(
                        $_POST["strong_passwords"]
                    ),

                "login_protection" =>
                    isset(
                        $_POST["login_protection"]
                    ),

                "audit_logging" =>
                    isset(
                        $_POST["audit_logging"]
                    ),

                "audit_logging" =>
    isset(
        $_POST["audit_logging"]
    ),

/*
|--------------------------------------------------------------------------
| SMART MODERATION AUTOMATION
|--------------------------------------------------------------------------
*/

"moderation_automation_enabled" =>
    isset(
        $_POST["moderation_automation_enabled"]
    ),

"auto_complaint_acknowledgement" =>
    isset(
        $_POST["auto_complaint_acknowledgement"]
    ),

"auto_warning_enabled" =>
    isset(
        $_POST["auto_warning_enabled"]
    ),

"auto_suspend_enabled" =>
    isset(
        $_POST["auto_suspend_enabled"]
    ),

"auto_close_complaints" =>
    isset(
        $_POST["auto_close_complaints"]
    ),

"complaint_review_required" =>
    isset(
        $_POST["complaint_review_required"]
    ),

"complaint_warning_threshold" =>
    max(
        1,
        min(
            20,
            (int)(
                $_POST["complaint_warning_threshold"]
                ?? 3
            )
        )
    ),

/*
|--------------------------------------------------------------------------
| MESSAGE TEMPLATES
|--------------------------------------------------------------------------
*/

"complaint_received_message" =>
    trim(
        $_POST["complaint_received_message"]
        ?? ""
    ),

"worker_warning_message" =>
    trim(
        $_POST["worker_warning_message"]
        ?? ""
    ),

"worker_suspension_message" =>
    trim(
        $_POST["worker_suspension_message"]
        ?? ""
    ),

"complaint_resolved_message" =>
    trim(
        $_POST["complaint_resolved_message"]
        ?? ""
    ),

"worker_approval_message" =>
    trim(
        $_POST["worker_approval_message"]
        ?? ""
    ),

"worker_rejection_message" =>
    trim(
        $_POST["worker_rejection_message"]
        ?? ""
    )
            ];


            $adminId =
                $_SESSION["user_id"];

            $adminObjectId =
                null;

            try {

                $adminObjectId =
                    new ObjectId($adminId);

            } catch (Throwable $e) {}


            $filter =
                $adminObjectId
                    ? ["_id" => $adminObjectId]
                    : ["_id" => $adminId];


            $users->updateOne(
                $filter,
                [
                    '$set' => [

                        "admin_settings" =>
                            $settings,

                        "updated_at" =>
                            new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );


            $adminSettings =
                array_merge(
                    $adminSettings,
                    $settings
                );


            $successMessage =
                "WorkOnnect settings saved successfully.";

        } catch (Throwable $e) {

            $errorMessage =
                "Could not save settings: " .
                $e->getMessage();
        }
    }
}


$avatarLetter =
    strtoupper(
        substr(
            $adminName ?: "A",
            0,
            1
        )
    );

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Settings | WorkOnnect Admin</title>

<link
    rel="stylesheet"
    href="../assets/css/admin.css?v=2"
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
    rel="stylesheet"
>


<style>

/* =========================================================
   WORKONNECT SETTINGS
========================================================= */

.settings-page {

    padding: 4px 0 60px;

    font-family:
        "DM Sans",
        sans-serif;

    color: #17252b;
}


/* HEADER */

.settings-intro {

    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    gap: 30px;

    margin-bottom: 28px;
}

.settings-intro-left {

    max-width: 700px;
}

.settings-eyebrow {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    color: #197487;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 1.5px;

    text-transform: uppercase;

    margin-bottom: 8px;
}

.settings-eyebrow::before {

    content: "";

    width: 22px;

    height: 2px;

    border-radius: 10px;

    background: #f4c95d;
}

.settings-intro h2 {

    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 32px;

    letter-spacing: -0.8px;
}

.settings-intro p {

    margin: 8px 0 0;

    color: #66767a;

    font-size: 14px;

    line-height: 1.6;
}

.settings-status {

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 11px 15px;

    border: 1px solid #dce6e5;

    border-radius: 14px;

    background: #fff;

    font-size: 12px;

    color: #66767a;

    white-space: nowrap;
}

.settings-status-dot {

    width: 8px;

    height: 8px;

    border-radius: 50%;

    background: #65a875;

    box-shadow: 0 0 0 4px #cde5c2;
}


/* LAYOUT */

.settings-layout {

    display: grid;

    grid-template-columns: 230px minmax(0, 1fr);

    gap: 22px;

    align-items: start;
}


/* SETTINGS NAV */

.settings-navigation {

    position: sticky;

    top: 24px;

    padding: 9px;

    border: 1px solid #dce6e5;

    border-radius: 20px;

    background: rgba(255,255,255,.92);

    box-shadow:
        0 12px 35px rgba(23,37,43,.05);

    backdrop-filter: blur(12px);
}

.settings-nav-button {

    width: 100%;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px 13px;

    border: 0;

    border-radius: 13px;

    background: transparent;

    color: #66767a;

    font: inherit;

    font-size: 13px;

    font-weight: 600;

    text-align: left;

    cursor: pointer;

    transition:
        .2s ease;
}

.settings-nav-button:hover {

    color: #17252b;

    background: #f7faf9;

    transform: translateX(2px);
}

.settings-nav-button.active {

    color: #197487;

    background: #d9eff2;
}

.settings-nav-icon {

    width: 30px;

    height: 30px;

    display: grid;

    place-items: center;

    border-radius: 9px;

    background: #f5f8f7;

    font-size: 14px;

    flex-shrink: 0;
}

.settings-nav-button.active
.settings-nav-icon {

    background: rgba(255,255,255,.75);
}


/* CONTENT */

.settings-content {

    min-width: 0;
}

.settings-section {

    display: none;

    animation:
        settingsFade .25s ease;
}

.settings-section.active {

    display: block;
}

@keyframes settingsFade {

    from {

        opacity: 0;

        transform: translateY(6px);
    }

    to {

        opacity: 1;

        transform: translateY(0);
    }
}


/* SECTION TITLE */

.section-heading {

    margin-bottom: 17px;
}

.section-heading h3 {

    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 21px;
}

.section-heading p {

    margin: 5px 0 0;

    color: #66767a;

    font-size: 13px;
}


/* CARD */

.settings-card {

    padding: 23px;

    margin-bottom: 18px;

    border: 1px solid #dce6e5;

    border-radius: 20px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23,37,43,.035);

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

.settings-card:hover {

    border-color: #cbdcda;

    box-shadow:
        0 14px 35px rgba(23,37,43,.055);
}

.card-top {

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 20px;
}

.card-icon {

    width: 42px;

    height: 42px;

    display: grid;

    place-items: center;

    border-radius: 13px;

    background: #d9eff2;

    color: #197487;

    font-size: 18px;
}

.card-top h4 {

    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 16px;
}

.card-top span {

    display: block;

    margin-top: 3px;

    color: #66767a;

    font-size: 11px;
}


/* FORM */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0,1fr));

    gap: 17px;
}

.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;
}

.form-group.full {

    grid-column: 1 / -1;
}

.form-group label {

    color: #405257;

    font-size: 12px;

    font-weight: 700;
}

.form-control {

    width: 100%;

    box-sizing: border-box;

    padding: 12px 13px;

    border: 1px solid #dce6e5;

    border-radius: 12px;

    outline: none;

    background: #fafcfb;

    color: #17252b;

    font: inherit;

    font-size: 13px;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.form-control:focus {

    border-color: #197487;

    background: #fff;

    box-shadow:
        0 0 0 4px rgba(25,116,135,.09);
}

.form-help {

    color: #8a999c;

    font-size: 11px;

    line-height: 1.5;
}


/* PASSWORD */

.password-wrap {

    position: relative;
}

.password-wrap .form-control {

    padding-right: 48px;
}

.password-toggle {

    position: absolute;

    right: 8px;

    top: 50%;

    transform: translateY(-50%);

    width: 34px;

    height: 34px;

    border: 0;

    border-radius: 9px;

    background: transparent;

    color: #66767a;

    cursor: pointer;

    font-size: 14px;
}

.password-toggle:hover {

    background: #eef6f5;

    color: #197487;
}


/* SETTING ROW */

.setting-list {

    display: flex;

    flex-direction: column;
}

.setting-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 16px 0;

    border-bottom: 1px solid #edf2f1;
}

.setting-row:first-child {

    padding-top: 0;
}

.setting-row:last-child {

    border-bottom: 0;

    padding-bottom: 0;
}

.setting-info {

    min-width: 0;
}

.setting-info strong {

    display: block;

    margin-bottom: 4px;

    color: #17252b;

    font-size: 13px;
}

.setting-info span {

    display: block;

    color: #66767a;

    font-size: 11px;

    line-height: 1.5;
}


/* SWITCH */

.switch {

    position: relative;

    width: 45px;

    height: 25px;

    flex-shrink: 0;
}

.switch input {

    opacity: 0;

    width: 0;

    height: 0;
}

.slider {

    position: absolute;

    inset: 0;

    cursor: pointer;

    border-radius: 50px;

    background: #dce6e5;

    transition: .25s;
}

.slider::before {

    content: "";

    position: absolute;

    width: 19px;

    height: 19px;

    left: 3px;

    top: 3px;

    border-radius: 50%;

    background: #fff;

    box-shadow:
        0 2px 5px rgba(0,0,0,.15);

    transition: .25s;
}

.switch input:checked + .slider {

    background: #197487;
}

.switch input:checked + .slider::before {

    transform: translateX(20px);
}


/* SELECT */

.setting-select {

    min-width: 145px;

    padding: 9px 11px;

    border: 1px solid #dce6e5;

    border-radius: 10px;

    background: #fafcfb;

    color: #17252b;

    font: inherit;

    font-size: 12px;

    outline: none;
}


/* WARNING */

.warning-box {

    padding: 15px 16px;

    margin-top: 18px;

    border: 1px solid #f0dfb3;

    border-radius: 14px;

    background: #fffaf0;
}

.warning-box strong {

    display: block;

    color: #80631e;

    font-size: 12px;

    margin-bottom: 4px;
}

.warning-box span {

    color: #877a59;

    font-size: 11px;

    line-height: 1.5;
}


/* DANGER */

.danger-card {

    border-color: #f0d6d2;
}

.danger-card .card-icon {

    background: #fff0ed;

    color: #c45c4c;
}

.danger-button {

    padding: 10px 14px;

    border: 1px solid #efc9c3;

    border-radius: 11px;

    background: #fff7f5;

    color: #b64e40;

    font: inherit;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    transition: .2s;
}

.danger-button:hover {

    background: #fff0ed;

    transform: translateY(-1px);
}


/* SAVE BAR */

.settings-savebar {

    position: sticky;

    bottom: 18px;

    z-index: 20;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-top: 22px;

    padding: 14px 16px;

    border: 1px solid #dce6e5;

    border-radius: 16px;

    background: rgba(255,255,255,.94);

    box-shadow:
        0 12px 35px rgba(23,37,43,.09);

    backdrop-filter: blur(15px);
}

.savebar-text {

    color: #66767a;

    font-size: 12px;
}

.savebar-text strong {

    display: block;

    color: #17252b;

    margin-bottom: 2px;
}

.save-button {

    border: 0;

    padding: 11px 19px;

    border-radius: 11px;

    background: #197487;

    color: #fff;

    font: inherit;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 7px 18px rgba(25,116,135,.2);

    transition:
        .2s ease;
}

.save-button:hover {

    background: #146476;

    transform: translateY(-1px);
}


/* ACCOUNT BADGE */

.account-badge {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 18px;

    padding: 7px 10px;

    border-radius: 9px;

    background: #eef8f0;

    color: #4d8657;

    font-size: 11px;

    font-weight: 700;
}

.account-badge::before {

    content: "";

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #65a875;
}


/* TOAST */

.settings-toast {

    position: fixed;

    right: 25px;

    bottom: 25px;

    z-index: 1000;

    min-width: 280px;

    max-width: 380px;

    padding: 14px 16px;

    border-radius: 14px;

    background: #17252b;

    color: #fff;

    box-shadow:
        0 15px 40px rgba(23,37,43,.2);

    transform:
        translateY(20px);

    opacity: 0;

    pointer-events: none;

    transition: .25s ease;
}

.settings-toast.show {

    transform:
        translateY(0);

    opacity: 1;
}

.settings-toast.success {

    border-left:
        4px solid #8abf91;
}

.settings-toast.error {

    border-left:
        4px solid #f28573;
}



/* =========================================================
   SMART AUTOMATION
========================================================= */

.automation-panel {

    position: relative;

    overflow: hidden;

    margin-top: 20px;

    padding: 22px;

    border: 1px solid #dce6e5;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #f7fbfa 55%,
            #f1fafb 100%
        );

    box-shadow:
        0 12px 35px rgba(23,37,43,.045);
}

.automation-panel::before {

    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    right: -80px;
    top: -90px;

    border-radius: 50%;

    background:
        rgba(217,239,242,.45);

    filter: blur(2px);
}

.automation-header {

    position: relative;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    margin-bottom: 20px;
}

.automation-title {

    display: flex;

    align-items: center;

    gap: 13px;
}

.automation-orb {

    width: 43px;
    height: 43px;

    display: grid;

    place-items: center;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #d9eff2,
            #cde5c2
        );

    color: #197487;

    font-size: 19px;

    box-shadow:
        0 7px 20px rgba(25,116,135,.12);
}

.automation-title h4 {

    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 17px;
}

.automation-title span {

    display: block;

    margin-top: 3px;

    color: #66767a;

    font-size: 11px;
}

.automation-grid {

    position: relative;

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0,1fr));

    gap: 12px;
}

.automation-feature {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 14px;

    border: 1px solid #e3ecea;

    border-radius: 14px;

    background: rgba(255,255,255,.82);

    transition:
        .2s ease;
}

.automation-feature:hover {

    border-color: #c5ddda;

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px rgba(23,37,43,.04);
}

.automation-feature strong {

    display: block;

    font-size: 12px;

    margin-bottom: 3px;
}

.automation-feature span {

    color: #7a898c;

    font-size: 10px;

    line-height: 1.45;
}


/* =========================================================
   MESSAGE CENTER
========================================================= */

.message-center {

    margin-top: 20px;

    padding: 22px;

    border: 1px solid #dce6e5;

    border-radius: 20px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23,37,43,.035);
}

.message-center-header {

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 20px;
}

.message-center-icon {

    width: 42px;
    height: 42px;

    display: grid;

    place-items: center;

    border-radius: 13px;

    background: #fff5d9;

    color: #8a6b20;

    font-size: 18px;
}

.message-center-header h4 {

    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 16px;
}

.message-center-header span {

    display: block;

    margin-top: 3px;

    color: #66767a;

    font-size: 11px;
}

.message-template {

    padding: 16px 0;

    border-bottom: 1px solid #edf2f1;
}

.message-template:last-child {

    border-bottom: 0;

    padding-bottom: 0;
}

.message-template-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 12px;

    margin-bottom: 8px;
}

.message-template-title {

    color: #17252b;

    font-size: 12px;

    font-weight: 700;
}

.message-template-tag {

    padding: 4px 8px;

    border-radius: 7px;

    background: #eef8f0;

    color: #54805c;

    font-size: 9px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.message-textarea {

    width: 100%;

    min-height: 88px;

    box-sizing: border-box;

    resize: vertical;

    padding: 12px;

    border: 1px solid #dce6e5;

    border-radius: 12px;

    outline: none;

    background: #fafcfb;

    color: #17252b;

    font-family: "DM Sans", sans-serif;

    font-size: 12px;

    line-height: 1.55;

    transition:
        .2s ease;
}

.message-textarea:focus {

    border-color: #197487;

    background: #fff;

    box-shadow:
        0 0 0 4px rgba(25,116,135,.08);
}

.message-hint {

    margin-top: 6px;

    color: #8a999c;

    font-size: 10px;
}


/* =========================================================
   LIVE MESSAGE PREVIEW
========================================================= */

.message-preview {

    margin-top: 20px;

    padding: 17px;

    border: 1px dashed #bcd2d0;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #f8fcfb,
            #f4fafb
        );
}

.message-preview-label {

    display: flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 8px;

    color: #197487;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 1px;

    text-transform: uppercase;
}

.message-preview-content {

    color: #405257;

    font-size: 12px;

    line-height: 1.6;
}

.placeholder-chip {

    display: inline-flex;

    padding: 3px 6px;

    margin: 2px;

    border-radius: 5px;

    background: #d9eff2;

    color: #197487;

    font-size: 9px;

    font-weight: 700;
}


/* =========================================================
   AUTOMATION STATUS
========================================================= */

.automation-status {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 6px 9px;

    border-radius: 8px;

    background: #eef8f0;

    color: #54805c;

    font-size: 10px;

    font-weight: 700;
}

.automation-status-dot {

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: #65a875;

    box-shadow:
        0 0 0 3px #cde5c2;
}

@media (max-width: 650px) {

    .automation-grid {

        grid-template-columns: 1fr;
    }

    .automation-header {

        align-items: flex-start;

        flex-direction: column;
    }

}


/* RESPONSIVE */

@media (max-width: 950px) {

    .settings-layout {

        grid-template-columns: 1fr;
    }

    .settings-navigation {

        position: static;

        display: flex;

        overflow-x: auto;

        gap: 5px;
    }

    .settings-nav-button {

        min-width: max-content;

        width: auto;
    }
}

@media (max-width: 650px) {

    .settings-intro {

        align-items: flex-start;

        flex-direction: column;
    }

    .settings-intro h2 {

        font-size: 27px;
    }

    .form-grid {

        grid-template-columns: 1fr;
    }

    .form-group.full {

        grid-column: auto;
    }

    .settings-card {

        padding: 18px;
    }

    .setting-row {

        align-items: flex-start;
    }

    .settings-savebar {

        align-items: flex-start;

        flex-direction: column;
    }

    .save-button {

        width: 100%;
    }
}

</style>

</head>


<body>

<div class="dashboard">


<!-- =====================================================
     EXISTING SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="brand">

        <div class="brand-logo">
            W
        </div>

        <div class="brand-text">
            Work<span>onnect</span>
        </div>

    </div>


    <nav class="navigation">

        <a href="dash_board.php" class="nav-item">

            <span class="nav-icon">⌂</span>

            <span>Dashboard</span>

        </a>


        <a href="users.php" class="nav-item">

            <span class="nav-icon">♙</span>

            <span>Users</span>

        </a>


        <a href="showcase.php" class="nav-item">

            <span class="nav-icon">▣</span>

            <span>Showcase</span>

        </a>


        <a href="settings.php" class="nav-item active">

            <span class="nav-icon">⚙</span>

            <span>Settings</span>

        </a>

    </nav>


    <div class="sidebar-bottom">

        <a
            href="../../BackEnd/logout.php"
            class="logout"
        >

            <span class="logout-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M10 5H6.5C5.67 5 5 5.67 5 6.5V17.5C5 18.33 5.67 19 6.5 19H10"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />

                    <path
                        d="M13 8L17 12L13 16"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M17 12H9"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />

                </svg>

            </span>

            <span class="logout-text">
                Logout
            </span>

            <span class="logout-arrow">
                →
            </span>

        </a>

    </div>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main-content">


<!-- =====================================================
     TOPBAR
===================================================== -->

<header class="topbar">

    <div>

        <p class="small-title">
            ADMIN PANEL
        </p>

        <h1>
            Settings
        </h1>

    </div>


    <div class="top-actions">


        <!-- NOTIFICATION -->

        <div class="admin-notification-wrapper">

            <button
                class="notification-btn"
                id="adminNotificationBtn"
                type="button"
                aria-label="Notifications"
            >

                <svg viewBox="0 0 24 24" fill="none">

                    <path
                        d="M18 9C18 5.69 15.76 3 12 3C8.24 3 6 5.69 6 9C6 13.5 4 15 4 16H20C20 15 18 13.5 18 9Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M10 20H14"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />

                </svg>

                <span
                    class="notification-badge"
                    id="adminNotificationBadge"
                    style="display:none;"
                >
                    0
                </span>

            </button>


            <div
                class="admin-notification-dropdown"
                id="adminNotificationDropdown"
            >

                <div class="notification-header">

                    <div>

                        <strong>
                            Notifications
                        </strong>

                        <span id="adminNotificationUnread">
                            0 unread
                        </span>

                    </div>

                    <button
                        type="button"
                        id="markAllAdminNotifications"
                    >
                        Mark all read
                    </button>

                </div>


                <div
                    class="notification-list"
                    id="adminNotificationList"
                >

                    <div class="notification-loading">
                        Loading notifications...
                    </div>

                </div>

            </div>

        </div>


        <!-- ADMIN PROFILE -->

        <div
            class="admin-profile"
            id="adminProfile"
        >

            <button
                class="profile-button"
                type="button"
            >

                <div class="profile-avatar">

                    <?= htmlspecialchars($avatarLetter); ?>

                    <span class="online-dot"></span>

                </div>


                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars($adminName); ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>


                <span class="profile-chevron">
                    ⌄
                </span>

            </button>


            <div class="profile-dropdown">

                <div class="dropdown-header">

                    <div class="dropdown-avatar">
                        <?= htmlspecialchars($avatarLetter); ?>
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars($adminName); ?>
                        </strong>

                        <span>
                            Administrator
                        </span>

                    </div>

                </div>


                <div class="dropdown-line"></div>


                <a
                    href="settings.php"
                    class="dropdown-item"
                >

                    <span class="dropdown-icon">
                        ⚙
                    </span>

                    <span>
                        Settings
                    </span>

                    <span class="item-arrow">
                        →
                    </span>

                </a>


                <div class="dropdown-line"></div>


                <a
                    href="../../BackEnd/logout.php"
                    class="dropdown-logout"
                >

                    <span class="logout-small-icon">
                        ↪
                    </span>

                    <span>
                        Logout
                    </span>

                </a>

            </div>

        </div>

    </div>

</header>


<!-- =====================================================
     SETTINGS CONTENT
===================================================== -->

<div class="settings-page">


    <div class="settings-intro">

        <div class="settings-intro-left">

            <span class="settings-eyebrow">
                CONTROL CENTER
            </span>

            <h2>
                WorkOnnect Settings
            </h2>

            <p>
                Manage your administrator account, platform rules,
                workers, customers, notifications and security
                from one place.
            </p>

        </div>


        <div class="settings-status">

            <span class="settings-status-dot"></span>

            System settings active

        </div>

    </div>


    <div class="settings-layout">


        <!-- =================================================
             SETTINGS NAVIGATION
        ================================================== -->

        <aside class="settings-navigation">

            <button
                class="settings-nav-button active"
                data-section="account"
            >

                <span class="settings-nav-icon">
                    👤
                </span>

                Account

            </button>


            <button
                class="settings-nav-button"
                data-section="platform"
            >

                <span class="settings-nav-icon">
                    ◈
                </span>

                Platform

            </button>


            <button
                class="settings-nav-button"
                data-section="workers"
            >

                <span class="settings-nav-icon">
                    👷
                </span>

                Workers

            </button>


            <button
                class="settings-nav-button"
                data-section="customers"
            >

                <span class="settings-nav-icon">
                    ♙
                </span>

                Customers

            </button>


            <button
                class="settings-nav-button"
                data-section="moderation"
            >

                <span class="settings-nav-icon">
                    ⚠
                </span>

                Moderation

            </button>


            <button
                class="settings-nav-button"
                data-section="notifications"
            >

                <span class="settings-nav-icon">
                    ♢
                </span>

                Notifications

            </button>


            <button
                class="settings-nav-button"
                data-section="security"
            >

                <span class="settings-nav-icon">
                    ◉
                </span>

                Security

            </button>


            <button
                class="settings-nav-button"
                data-section="maintenance"
            >

                <span class="settings-nav-icon">
                    ⚙
                </span>

                Maintenance

            </button>

        </aside>


        <!-- =================================================
             CONTENT
        ================================================== -->

        <div class="settings-content">


            <!-- =================================================
                 ACCOUNT
            ================================================== -->

            <section
                class="settings-section active"
                id="section-account"
            >

                <div class="section-heading">

                    <h3>
                        Administrator account
                    </h3>

                    <p>
                        Update the account used to manage WorkOnnect.
                    </p>

                </div>


                <form
                    method="POST"
                    class="settings-card"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="update_account"
                    >


                    <div class="card-top">

                        <div class="card-icon">
                            👤
                        </div>

                        <div>

                            <h4>
                                Account information
                            </h4>

                            <span>
                                Your administrator identity
                            </span>

                        </div>

                    </div>


                    <div class="account-badge">
                        Administrator account
                    </div>


                    <div class="form-grid">

                        <div class="form-group">

                            <label>
                                Admin name
                            </label>

                            <input
                                class="form-control"
                                type="text"
                                name="admin_name"
                                value="<?= htmlspecialchars($adminName); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Admin email
                            </label>

                            <input
                                class="form-control"
                                type="email"
                                name="admin_email"
                                value="<?= htmlspecialchars($adminEmail); ?>"
                                required
                            >

                            <span class="form-help">
                                This email will be used for your administrator account.
                            </span>

                        </div>


                        <div class="form-group full">

                            <label>
                                Current password
                            </label>

                            <div class="password-wrap">

                                <input
                                    class="form-control"
                                    type="password"
                                    id="currentPassword"
                                    name="current_password"
                                    placeholder="Required when changing email or password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="currentPassword"
                                >
                                    ◉
                                </button>

                            </div>

                        </div>


                        <div class="form-group">

                            <label>
                                New password
                            </label>

                            <div class="password-wrap">

                                <input
                                    class="form-control"
                                    type="password"
                                    id="newPassword"
                                    name="new_password"
                                    placeholder="Leave empty to keep current password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="newPassword"
                                >
                                    ◉
                                </button>

                            </div>

                            <span class="form-help">
                                Minimum 8 characters.
                            </span>

                        </div>


                        <div class="form-group">

                            <label>
                                Confirm new password
                            </label>

                            <div class="password-wrap">

                                <input
                                    class="form-control"
                                    type="password"
                                    id="confirmPassword"
                                    name="confirm_password"
                                    placeholder="Repeat new password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="confirmPassword"
                                >
                                    ◉
                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="warning-box">

                        <strong>
                            Security check
                        </strong>

                        <span>
                            Your current password is required whenever
                            you change your administrator email or password.
                        </span>

                    </div>


                    <div style="margin-top:18px;">

                        <button
                            type="submit"
                            class="save-button"
                        >
                            Save account changes
                        </button>

                    </div>

                </form>

            </section>


            <!-- =================================================
                 PLATFORM
            ================================================== -->

            <section
                class="settings-section"
                id="section-platform"
            >

                <div class="section-heading">

                    <h3>
                        Platform settings
                    </h3>

                    <p>
                        Control the general behaviour of WorkOnnect.
                    </p>

                </div>


                <form
                    method="POST"
                    id="platformSettingsForm"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="save_settings"
                    >


                    <div class="settings-card">

                        <div class="card-top">

                            <div class="card-icon">
                                ◈
                            </div>

                            <div>

                                <h4>
                                    General platform
                                </h4>

                                <span>
                                    Core WorkOnnect configuration
                                </span>

                            </div>

                        </div>


                        <div class="form-grid">

                            <div class="form-group">

                                <label>
                                    Platform name
                                </label>

                                <input
                                    class="form-control"
                                    type="text"
                                    name="platform_name"
                                    value="<?= htmlspecialchars($adminSettings["platform_name"]); ?>"
                                >

                            </div>

                        </div>


                        <div class="setting-list" style="margin-top:22px;">

                            <div class="setting-row">

                                <div class="setting-info">

                                    <strong>
                                        Allow new registrations
                                    </strong>

                                    <span>
                                        Customers and workers can create new accounts.
                                    </span>

                                </div>

                                <label class="switch">

                                    <input
                                        type="checkbox"
                                        name="registration_enabled"
                                        <?= $adminSettings["registration_enabled"] ? "checked" : ""; ?>
                                    >

                                    <span class="slider"></span>

                                </label>

                            </div>


                            <div class="setting-row">

                                <div class="setting-info">

                                    <strong>
                                        Maintenance mode
                                    </strong>

                                    <span>
                                        Temporarily restrict normal platform access.
                                    </span>

                                </div>

                                <label class="switch">

                                    <input
                                        type="checkbox"
                                        name="maintenance_mode"
                                        <?= $adminSettings["maintenance_mode"] ? "checked" : ""; ?>
                                    >

                                    <span class="slider"></span>

                                </label>

                            </div>

                        </div>

                    </div>

                </form>

            </section>


            <!-- =================================================
                 WORKERS
            ================================================== -->

            <section
                class="settings-section"
                id="section-workers"
            >

                <div class="section-heading">

                    <h3>
                        Worker management
                    </h3>

                    <p>
                        Control worker approval and visibility.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            👷
                        </div>

                        <div>

                            <h4>
                                Worker approval
                            </h4>

                            <span>
                                Protect customers by controlling worker visibility.
                            </span>

                        </div>

                    </div>


                    <div class="setting-list">

                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Require admin approval
                                </strong>

                                <span>
                                    New worker profiles remain pending until reviewed.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="worker_approval_required"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["worker_approval_required"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Show approved workers to customers
                                </strong>

                                <span>
                                    Only approved workers become discoverable.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="worker_visible_after_approval"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["worker_visible_after_approval"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Worker approval notifications
                                </strong>

                                <span>
                                    Notify workers when their profile is approved or rejected.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="worker_approval_notification"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["worker_approval_notification"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>


                            </label>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 CUSTOMERS
            ================================================== -->

            <section
                class="settings-section"
                id="section-customers"
            >

                <div class="section-heading">

                    <h3>
                        Customer settings
                    </h3>

                    <p>
                        Configure what customers can do on the platform.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            ♙
                        </div>

                        <div>

                            <h4>
                                Customer permissions
                            </h4>

                            <span>
                                Manage customer-worker interaction.
                            </span>

                        </div>

                    </div>


                    <div class="setting-list">

                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Allow customers to connect with workers
                                </strong>

                                <span>
                                    Customers can send connection/request messages.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="customer_connect_enabled"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["customer_connect_enabled"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Customer complaints
                                </strong>

                                <span>
                                    Allow customers to report problems with workers.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="customer_complaints_enabled"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["customer_complaints_enabled"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Customer feedback
                                </strong>

                                <span>
                                    Allow customers to submit platform feedback.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="customer_feedback_enabled"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["customer_feedback_enabled"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 MODERATION
            ================================================== -->

           <section
    class="settings-section"
    id="section-moderation"
>

    <div class="section-heading">

        <h3>
            Complaints & moderation
        </h3>

        <p>
            Control complaints, warnings, automated actions
            and administrator message templates.
        </p>

    </div>


    <!-- =====================================================
         BASIC MODERATION
    ====================================================== -->

    <div class="settings-card">

        <div class="card-top">

            <div class="card-icon">
                ⚠
            </div>

            <div>

                <h4>
                    Complaint controls
                </h4>

                <span>
                    Manage reports, warnings and worker discipline.
                </span>

            </div>

        </div>


        <div class="setting-list">

            <div class="setting-row">

                <div class="setting-info">

                    <strong>
                        Complaint system
                    </strong>

                    <span>
                        Allow customers to submit complaints.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="complaints_enabled"
                        form="platformSettingsForm"
                        <?= $adminSettings["complaints_enabled"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="setting-row">

                <div class="setting-info">

                    <strong>
                        Worker warning system
                    </strong>

                    <span>
                        Allow administrators to issue formal worker warnings.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="warning_system_enabled"
                        form="platformSettingsForm"
                        <?= $adminSettings["warning_system_enabled"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="setting-row">

                <div class="setting-info">

                    <strong>
                        Automatic suspension
                    </strong>

                    <span>
                        Suspend workers after reaching the configured warning limit.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="auto_suspend_enabled"
                        form="platformSettingsForm"
                        <?= $adminSettings["auto_suspend_enabled"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="setting-row">

                <div class="setting-info">

                    <strong>
                        Warning limit
                    </strong>

                    <span>
                        Maximum warnings before suspension.
                    </span>

                </div>

                <select
                    class="setting-select"
                    name="warning_limit"
                    form="platformSettingsForm"
                >

                    <?php for ($i = 1; $i <= 10; $i++): ?>

                        <option
                            value="<?= $i; ?>"
                            <?= (int)$adminSettings["warning_limit"] === $i ? "selected" : ""; ?>
                        >
                            <?= $i; ?>
                            warning<?= $i > 1 ? "s" : ""; ?>
                        </option>

                    <?php endfor; ?>

                </select>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SMART AUTOMATION
    ====================================================== -->

    <div class="automation-panel">

        <div class="automation-header">

            <div class="automation-title">

                <div class="automation-orb">
                    ✦
                </div>

                <div>

                    <h4>
                        Smart moderation automation
                    </h4>

                    <span>
                        Configure what WorkOnnect should handle automatically.
                    </span>

                </div>

            </div>


            <span class="automation-status">

                <span class="automation-status-dot"></span>

                Automation ready

            </span>

        </div>


        <div class="automation-grid">

            <div class="automation-feature">

                <div>

                    <strong>
                        Moderation automation
                    </strong>

                    <span>
                        Master switch for automatic moderation actions.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="moderation_automation_enabled"
                        form="platformSettingsForm"
                        <?= $adminSettings["moderation_automation_enabled"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="automation-feature">

                <div>

                    <strong>
                        Auto complaint acknowledgement
                    </strong>

                    <span>
                        Automatically notify customers after submitting a complaint.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="auto_complaint_acknowledgement"
                        form="platformSettingsForm"
                        <?= $adminSettings["auto_complaint_acknowledgement"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="automation-feature">

                <div>

                    <strong>
                        Automatic warning
                    </strong>

                    <span>
                        Automatically create a warning when configured conditions are met.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="auto_warning_enabled"
                        form="platformSettingsForm"
                        <?= $adminSettings["auto_warning_enabled"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="automation-feature">

                <div>

                    <strong>
                        Automatic complaint closing
                    </strong>

                    <span>
                        Allow resolved complaints to be closed automatically.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="auto_close_complaints"
                        form="platformSettingsForm"
                        <?= $adminSettings["auto_close_complaints"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="automation-feature">

                <div>

                    <strong>
                        Admin review required
                    </strong>

                    <span>
                        Keep complaints pending until an administrator reviews them.
                    </span>

                </div>

                <label class="switch">

                    <input
                        type="checkbox"
                        name="complaint_review_required"
                        form="platformSettingsForm"
                        <?= $adminSettings["complaint_review_required"] ? "checked" : ""; ?>
                    >

                    <span class="slider"></span>

                </label>

            </div>


            <div class="automation-feature">

                <div>

                    <strong>
                        Complaint warning threshold
                    </strong>

                    <span>
                        Number of complaints before automation can escalate action.
                    </span>

                </div>

                <select
                    class="setting-select"
                    name="complaint_warning_threshold"
                    form="platformSettingsForm"
                >

                    <?php for ($i = 1; $i <= 10; $i++): ?>

                        <option
                            value="<?= $i; ?>"
                            <?= (int)$adminSettings["complaint_warning_threshold"] === $i ? "selected" : ""; ?>
                        >
                            <?= $i; ?>
                            complaint<?= $i > 1 ? "s" : ""; ?>
                        </option>

                    <?php endfor; ?>

                </select>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MESSAGE CENTER
    ====================================================== -->

    <div class="message-center">

        <div class="message-center-header">

            <div class="message-center-icon">
                ✉
            </div>

            <div>

                <h4>
                    Complaint & warning message center
                </h4>

                <span>
                    Customize the messages automatically sent by WorkOnnect.
                </span>

            </div>

        </div>


        <!-- CUSTOMER COMPLAINT -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Complaint received
                </span>

                <span class="message-template-tag">
                    Customer
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="complaint_received_message"
                form="platformSettingsForm"
                placeholder="Message sent after a complaint is submitted..."
            ><?= htmlspecialchars($adminSettings["complaint_received_message"]); ?></textarea>


            <div class="message-hint">

                Available:
                <span class="placeholder-chip">
                    {customer_name}
                </span>

            </div>

        </div>


        <!-- WORKER WARNING -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Worker warning
                </span>

                <span class="message-template-tag">
                    Worker
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="worker_warning_message"
                form="platformSettingsForm"
                placeholder="Warning message sent to workers..."
            ><?= htmlspecialchars($adminSettings["worker_warning_message"]); ?></textarea>


            <div class="message-hint">

                Available:

                <span class="placeholder-chip">
                    {worker_name}
                </span>

                <span class="placeholder-chip">
                    {warning_count}
                </span>

                <span class="placeholder-chip">
                    {warning_limit}
                </span>

            </div>

        </div>


        <!-- SUSPENSION -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Worker suspension
                </span>

                <span class="message-template-tag">
                    Worker
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="worker_suspension_message"
                form="platformSettingsForm"
                placeholder="Suspension message..."
            ><?= htmlspecialchars($adminSettings["worker_suspension_message"]); ?></textarea>


            <div class="message-hint">

                Available:

                <span class="placeholder-chip">
                    {worker_name}
                </span>

                <span class="placeholder-chip">
                    {warning_count}
                </span>

            </div>

        </div>


        <!-- RESOLVED -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Complaint resolved
                </span>

                <span class="message-template-tag">
                    Customer
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="complaint_resolved_message"
                form="platformSettingsForm"
                placeholder="Resolution message..."
            ><?= htmlspecialchars($adminSettings["complaint_resolved_message"]); ?></textarea>


            <div class="message-hint">

                Available:

                <span class="placeholder-chip">
                    {customer_name}
                </span>

            </div>

        </div>


        <!-- APPROVAL -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Worker approved
                </span>

                <span class="message-template-tag">
                    Worker
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="worker_approval_message"
                form="platformSettingsForm"
            ><?= htmlspecialchars($adminSettings["worker_approval_message"]); ?></textarea>

            <div class="message-hint">

                Available:

                <span class="placeholder-chip">
                    {worker_name}
                </span>

            </div>

        </div>


        <!-- REJECTION -->

        <div class="message-template">

            <div class="message-template-top">

                <span class="message-template-title">
                    Worker rejected
                </span>

                <span class="message-template-tag">
                    Worker
                </span>

            </div>


            <textarea
                class="message-textarea"
                name="worker_rejection_message"
                form="platformSettingsForm"
            ><?= htmlspecialchars($adminSettings["worker_rejection_message"]); ?></textarea>

            <div class="message-hint">

                Available:

                <span class="placeholder-chip">
                    {worker_name}
                </span>

            </div>

        </div>


        <div class="message-preview">

            <div class="message-preview-label">
                ✦ Message automation
            </div>

            <div class="message-preview-content">

                Messages are stored in your administrator settings.
                Your complaint/warning backend can use these templates
                when sending notifications.

            </div>

        </div>

    </div>


    <div class="warning-box">

        <strong>
            Recommended moderation flow
        </strong>

        <span>
            Complaint → Automatic acknowledgement → Admin review →
            Warning → Follow-up → Suspension when necessary.
        </span>

    </div>

</section>

        


            <!-- =================================================
                 NOTIFICATIONS
            ================================================== -->

            <section
                class="settings-section"
                id="section-notifications"
            >

                <div class="section-heading">

                    <h3>
                        Notifications
                    </h3>

                    <p>
                        Decide which platform events should alert administrators.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            ♢
                        </div>

                        <div>

                            <h4>
                                Admin notifications
                            </h4>

                            <span>
                                Keep the administration team informed.
                            </span>

                        </div>

                    </div>


                    <div class="setting-list">

                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    New user registrations
                                </strong>

                                <span>
                                    Notify when a new account is created.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notify_new_users"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["notify_new_users"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    New worker profiles
                                </strong>

                                <span>
                                    Notify when a worker needs approval.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notify_new_workers"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["notify_new_workers"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Complaints
                                </strong>

                                <span>
                                    Notify administrators about new complaints.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notify_complaints"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["notify_complaints"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Feedback
                                </strong>

                                <span>
                                    Notify when customers submit feedback.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notify_feedback"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["notify_feedback"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Worker approval updates
                                </strong>

                                <span>
                                    Notify admins about worker approval activity.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notify_worker_approval"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["notify_worker_approval"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 SECURITY
            ================================================== -->

            <section
                class="settings-section"
                id="section-security"
            >

                <div class="section-heading">

                    <h3>
                        Security
                    </h3>

                    <p>
                        Strengthen administrator and platform security.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            ◉
                        </div>

                        <div>

                            <h4>
                                Security controls
                            </h4>

                            <span>
                                Configure session and login protection.
                            </span>

                        </div>

                    </div>


                    <div class="setting-list">

                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Strong password requirement
                                </strong>

                                <span>
                                    Encourage secure passwords for accounts.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="strong_passwords"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["strong_passwords"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Login protection
                                </strong>

                                <span>
                                    Enable additional login security checks.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="login_protection"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["login_protection"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Admin activity logging
                                </strong>

                                <span>
                                    Keep a record of important administrative actions.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="audit_logging"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["audit_logging"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Session timeout
                                </strong>

                                <span>
                                    Automatically expire inactive administrator sessions.
                                </span>

                            </div>

                            <select
                                class="setting-select"
                                name="session_timeout"
                                form="platformSettingsForm"
                            >

                                <option
                                    value="15"
                                    <?= (int)$adminSettings["session_timeout"] === 15 ? "selected" : ""; ?>
                                >
                                    15 minutes
                                </option>

                                <option
                                    value="30"
                                    <?= (int)$adminSettings["session_timeout"] === 30 ? "selected" : ""; ?>
                                >
                                    30 minutes
                                </option>

                                <option
                                    value="60"
                                    <?= (int)$adminSettings["session_timeout"] === 60 ? "selected" : ""; ?>
                                >
                                    1 hour
                                </option>

                                <option
                                    value="120"
                                    <?= (int)$adminSettings["session_timeout"] === 120 ? "selected" : ""; ?>
                                >
                                    2 hours
                                </option>

                                <option
                                    value="480"
                                    <?= (int)$adminSettings["session_timeout"] === 480 ? "selected" : ""; ?>
                                >
                                    8 hours
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <div class="settings-card danger-card">

                    <div class="card-top">

                        <div class="card-icon">
                            !
                        </div>

                        <div>

                            <h4>
                                Administrator security
                            </h4>

                            <span>
                                Important actions should always be handled carefully.
                            </span>

                        </div>

                    </div>


                    <p style="
                        margin:0;
                        color:#66767a;
                        font-size:12px;
                        line-height:1.6;
                    ">

                        Changing your administrator email or password
                        requires your current password.

                    </p>

                </div>

            </section>


            <!-- =================================================
                 MAINTENANCE
            ================================================== -->

            <section
                class="settings-section"
                id="section-maintenance"
            >

                <div class="section-heading">

                    <h3>
                        Maintenance
                    </h3>

                    <p>
                        Platform health and administrative maintenance controls.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            ⚙
                        </div>

                        <div>

                            <h4>
                                System status
                            </h4>

                            <span>
                                Current WorkOnnect environment.
                            </span>

                        </div>

                    </div>


                    <div class="setting-list">

                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    MongoDB connection
                                </strong>

                                <span>
                                    Database connection is currently being used by this page.
                                </span>

                            </div>

                            <span class="account-badge">
                                Connected
                            </span>

                        </div>


                        <div class="setting-row">

                            <div class="setting-info">

                                <strong>
                                    Maintenance mode
                                </strong>

                                <span>
                                    Temporarily disable normal platform access.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="maintenance_mode"
                                    form="platformSettingsForm"
                                    <?= $adminSettings["maintenance_mode"] ? "checked" : ""; ?>
                                >

                                <span class="slider"></span>

                            </label>

                        </div>

                    </div>

                </div>


                <div class="settings-card">

                    <div class="card-top">

                        <div class="card-icon">
                            ✓
                        </div>

                        <div>

                            <h4>
                                Safe maintenance
                            </h4>

                            <span>
                                Recommended administration workflow.
                            </span>

                        </div>

                    </div>


                    <div style="
                        display:grid;
                        gap:10px;
                        color:#66767a;
                        font-size:12px;
                        line-height:1.55;
                    ">

                        <div>
                            ✓ Review pending workers regularly
                        </div>

                        <div>
                            ✓ Review complaints and reports
                        </div>

                        <div>
                            ✓ Keep administrator credentials secure
                        </div>

                        <div>
                            ✓ Monitor platform notifications
                        </div>

                    </div>

                </div>

            </section>


            <!-- SAVE BAR -->

            <div class="settings-savebar">

                <div class="savebar-text">

                    <strong>
                        Platform configuration
                    </strong>

                    Changes here are saved to your administrator settings.

                </div>


                <button
                    type="submit"
                    form="platformSettingsForm"
                    class="save-button"
                >

                    Save settings

                </button>

            </div>

        </div>

    </div>

</div>


</main>

</div>


<!-- TOAST -->

<div
    class="settings-toast"
    id="settingsToast"
></div>


<script>

/* =========================================================
   SETTINGS NAVIGATION
========================================================= */

const settingsButtons =
    document.querySelectorAll(
        ".settings-nav-button"
    );

const settingsSections =
    document.querySelectorAll(
        ".settings-section"
    );


settingsButtons.forEach(button => {

    button.addEventListener(
        "click",
        function () {

            const section =
                this.dataset.section;


            settingsButtons.forEach(item => {

                item.classList.remove(
                    "active"
                );

            });


            settingsSections.forEach(item => {

                item.classList.remove(
                    "active"
                );

            });


            this.classList.add(
                "active"
            );


            const target =
                document.getElementById(
                    "section-" + section
                );


            if (target) {

                target.classList.add(
                    "active"
                );

            }

        }
    );

});


/* =========================================================
   PASSWORD SHOW / HIDE
========================================================= */

document
    .querySelectorAll(".password-toggle")
    .forEach(button => {

        button.addEventListener(
            "click",
            function () {

                const target =
                    document.getElementById(
                        this.dataset.target
                    );

                if (!target) return;


                if (
                    target.type ===
                    "password"
                ) {

                    target.type =
                        "text";

                    this.textContent =
                        "◉";

                } else {

                    target.type =
                        "password";

                    this.textContent =
                        "◉";
                }

            }
        );

    });


/* =========================================================
   PASSWORD VALIDATION
========================================================= */

const accountForm =
    document.querySelector(
        'form input[name="action"][value="update_account"]'
    )?.closest("form");


if (accountForm) {

    accountForm.addEventListener(
        "submit",
        function (event) {

            const newPassword =
                document.getElementById(
                    "newPassword"
                )?.value || "";

            const confirmPassword =
                document.getElementById(
                    "confirmPassword"
                )?.value || "";


            if (
                newPassword &&
                newPassword.length < 8
            ) {

                event.preventDefault();

                showToast(
                    "Password must contain at least 8 characters.",
                    "error"
                );

                return;
            }


            if (
                newPassword &&
                newPassword !== confirmPassword
            ) {

                event.preventDefault();

                showToast(
                    "New passwords do not match.",
                    "error"
                );

            }

        }
    );

}


/* =========================================================
   TOAST
========================================================= */

function showToast(
    message,
    type = "success"
) {

    const toast =
        document.getElementById(
            "settingsToast"
        );

    if (!toast) return;


    toast.textContent =
        message;

    toast.className =
        "settings-toast " +
        type +
        " show";


    setTimeout(
        function () {

            toast.classList.remove(
                "show"
            );

        },
        3500
    );

}


/* =========================================================
   SERVER MESSAGE
========================================================= */

<?php if ($successMessage): ?>

showToast(
    <?= json_encode($successMessage); ?>,
    "success"
);

<?php endif; ?>


<?php if ($errorMessage): ?>

showToast(
    <?= json_encode($errorMessage); ?>,
    "error"
);

<?php endif; ?>


/* =========================================================
   ADMIN PROFILE DROPDOWN
========================================================= */

const adminProfile =
    document.getElementById(
        "adminProfile"
    );

const profileButton =
    adminProfile?.querySelector(
        ".profile-button"
    );

if (profileButton) {

    profileButton.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

            adminProfile.classList.toggle(
                "open"
            );

        }
    );

}


document.addEventListener(
    "click",
    function () {

        adminProfile?.classList.remove(
            "open"
        );

    }
);


/* =========================================================
   ADMIN NOTIFICATIONS
========================================================= */

const adminNotificationBtn =
    document.getElementById(
        "adminNotificationBtn"
    );

const adminNotificationDropdown =
    document.getElementById(
        "adminNotificationDropdown"
    );

const adminNotificationBadge =
    document.getElementById(
        "adminNotificationBadge"
    );

const adminNotificationUnread =
    document.getElementById(
        "adminNotificationUnread"
    );

const adminNotificationList =
    document.getElementById(
        "adminNotificationList"
    );

const markAllAdminNotifications =
    document.getElementById(
        "markAllAdminNotifications"
    );


if (adminNotificationBtn) {

    adminNotificationBtn.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

            adminNotificationDropdown.classList.toggle(
                "show"
            );

        }
    );

}


document.addEventListener(
    "click",
    function (event) {

        const wrapper =
            document.querySelector(
                ".admin-notification-wrapper"
            );

        if (
            wrapper &&
            !wrapper.contains(
                event.target
            )
        ) {

            adminNotificationDropdown?.classList.remove(
                "show"
            );

        }

    }
);


async function loadAdminNotifications() {

    if (!adminNotificationList)
        return;


    try {

        const response =
            await fetch(
                "../../BackEnd/get_admin_notifications.php",
                {
                    method: "GET",
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );


        const data =
            await response.json();


        if (!data.success) {

            adminNotificationList.innerHTML =
                `
                <div class="notification-empty">
                    Unable to load notifications.
                </div>
                `;

            return;
        }


        const notifications =
            data.notifications || [];

        const unreadCount =
            Number(
                data.unread_count || 0
            );


        if (unreadCount > 0) {

            adminNotificationBadge.textContent =
                unreadCount > 99
                    ? "99+"
                    : unreadCount;

            adminNotificationBadge.style.display =
                "flex";

        } else {

            adminNotificationBadge.style.display =
                "none";

        }


        adminNotificationUnread.textContent =
            `${unreadCount} unread`;


        if (
            notifications.length === 0
        ) {

            adminNotificationList.innerHTML =
                `
                <div class="notification-empty">

                    <div class="notification-empty-icon">
                        ✓
                    </div>

                    <strong>
                        You're all caught up
                    </strong>

                    <span>
                        No new notifications.
                    </span>

                </div>
                `;

            return;
        }


        adminNotificationList.innerHTML =
            notifications
                .map(
                    createAdminNotification
                )
                .join("");


    } catch (error) {

        console.error(
            "Notification error:",
            error
        );

    }

}


function escapeHtml(value) {

    return String(
        value ?? ""
    )
    .replace(
        /&/g,
        "&amp;"
    )
    .replace(
        /</g,
        "&lt;"
    )
    .replace(
        />/g,
        "&gt;"
    )
    .replace(
        /"/g,
        "&quot;"
    )
    .replace(
        /'/g,
        "&#039;"
    );

}


function createAdminNotification(
    notification
) {

    const unread =
        !notification.read;


    return `

        <div
            class="admin-notification-item ${unread ? "unread" : ""}"
        >

            <div class="admin-notification-icon">

                ${getAdminNotificationIcon(
                    notification.type
                )}

            </div>


            <div class="admin-notification-content">

                <strong>
                    ${escapeHtml(
                        notification.title ||
                        "Notification"
                    )}
                </strong>

                <p>
                    ${escapeHtml(
                        notification.message ||
                        ""
                    )}
                </p>

                <span class="notification-time">
                    ${formatAdminNotificationTime(
                        notification.created_at
                    )}
                </span>

            </div>

        </div>

    `;

}


function getAdminNotificationIcon(type) {

    switch (type) {

        case "worker_pending":
            return "👷";

        case "worker_approval":
            return "✓";

        case "report":
            return "⚠";

        case "complaint":
            return "⚠";

        case "feedback":
            return "💬";

        case "user_registered":
            return "♙";

        default:
            return "•";
    }

}


function formatAdminNotificationTime(
    dateValue
) {

    if (!dateValue)
        return "";


    const date =
        new Date(dateValue);


    if (
        Number.isNaN(
            date.getTime()
        )
    )
        return "";


    const now =
        new Date();


    const seconds =
        Math.floor(
            (now - date) / 1000
        );


    if (seconds < 60)
        return "Just now";


    const minutes =
        Math.floor(
            seconds / 60
        );


    if (minutes < 60)
        return `${minutes}m ago`;


    const hours =
        Math.floor(
            minutes / 60
        );


    if (hours < 24)
        return `${hours}h ago`;


    const days =
        Math.floor(
            hours / 24
        );


    if (days < 7)
        return `${days}d ago`;


    return date.toLocaleDateString(
        "en-IN",
        {
            day: "numeric",
            month: "short"
        }
    );

}


if (markAllAdminNotifications) {

    markAllAdminNotifications.addEventListener(
        "click",
        async function () {

            try {

                await fetch(
                    "../../BackEnd/mark_admin_notifications_read.php",
                    {
                        method: "POST",
                        credentials: "same-origin"
                    }
                );

                loadAdminNotifications();

            } catch (error) {

                console.error(
                    error
                );

            }

        }
    );

}


document.addEventListener(
    "DOMContentLoaded",
    function () {

        loadAdminNotifications();

        setInterval(
            loadAdminNotifications,
            15000
        );

    }
);

</script>

</body>

</html>