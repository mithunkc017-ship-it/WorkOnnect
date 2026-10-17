<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/../../BackEnd/db.php";

use MongoDB\BSON\ObjectId;

/*
|--------------------------------------------------------------------------
| WORKER AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "worker"
) {
    header("Location: ../pages/login.html?error=Worker+login+required");
    exit;
}

$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET WORKER
|--------------------------------------------------------------------------
*/

try {

    $worker = null;

    try {
        $worker = $users->findOne([
            "_id" => new ObjectId($userId),
            "role" => "worker"
        ]);
    } catch (Throwable $e) {
        $worker = $users->findOne([
            "_id" => $userId,
            "role" => "worker"
        ]);
    }

    if (!$worker) {
        session_destroy();
        header("Location: ../pages/login.html?error=Worker+account+not+found");
        exit;
    }

} catch (Throwable $e) {

    die(
        "<h2>Database error</h2><p>" .
        htmlspecialchars($e->getMessage()) .
        "</p>"
    );
}

/*
|--------------------------------------------------------------------------
| CONVERT BSON DOCUMENT
|--------------------------------------------------------------------------
*/

$workerArray = $worker instanceof \MongoDB\Model\BSONDocument
    ? $worker->getArrayCopy()
    : (array)$worker;

/*
|--------------------------------------------------------------------------
| DEFAULT SETTINGS
|--------------------------------------------------------------------------
*/

$defaultSettings = [
    "theme" => "light",
    "notifications_enabled" => true,
    "email_notifications" => true,
    "request_notifications" => true,
    "approval_notifications" => true,
    "profile_visible" => true,
    "availability_status" => "available",
    "compact_dashboard" => false,
    "auto_refresh_notifications" => true,
    "security_alerts" => true
];

$savedSettings = [];

if (
    isset($workerArray["worker_settings"]) &&
    $workerArray["worker_settings"] instanceof \MongoDB\Model\BSONDocument
) {
    $savedSettings = $workerArray["worker_settings"]->getArrayCopy();
} elseif (
    isset($workerArray["worker_settings"]) &&
    is_array($workerArray["worker_settings"])
) {
    $savedSettings = $workerArray["worker_settings"];
}

$settings = array_merge($defaultSettings, $savedSettings);

$workerName = $workerArray["name"] ?? "Worker";
$workerEmail = $workerArray["email"] ?? "";

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| SAVE SETTINGS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | SAVE PREFERENCES
    |--------------------------------------------------------------------------
    */

    if ($action === "save_preferences") {

        $theme = $_POST["theme"] ?? "light";

        if (!in_array($theme, ["light", "dark"], true)) {
            $theme = "light";
        }

        $availability = $_POST["availability_status"] ?? "available";

        if (!in_array(
            $availability,
            ["available", "busy", "offline"],
            true
        )) {
            $availability = "available";
        }

        $newSettings = [
            "theme" => $theme,

            "notifications_enabled" =>
                isset($_POST["notifications_enabled"]),

            "email_notifications" =>
                isset($_POST["email_notifications"]),

            "request_notifications" =>
                isset($_POST["request_notifications"]),

            "approval_notifications" =>
                isset($_POST["approval_notifications"]),

            "profile_visible" =>
                isset($_POST["profile_visible"]),

            "availability_status" =>
                $availability,

            "compact_dashboard" =>
                isset($_POST["compact_dashboard"]),

            "auto_refresh_notifications" =>
                isset($_POST["auto_refresh_notifications"]),

            "security_alerts" =>
                isset($_POST["security_alerts"])
        ];

        try {

            $users->updateOne(
                [
                    "_id" => $worker["_id"]
                ],
                [
                    '$set' => [
                        "worker_settings" => $newSettings,
                        "updated_at" => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );

            $settings = array_merge(
                $defaultSettings,
                $newSettings
            );

            $message = "Your worker preferences have been saved successfully.";
            $messageType = "success";

        } catch (Throwable $e) {

            $message = "Unable to save settings.";
            $messageType = "error";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */

    if ($action === "change_password") {

        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        $storedPassword = $workerArray["password"] ?? "";

        if ($currentPassword === "") {

            $message = "Enter your current password.";
            $messageType = "error";

        } elseif (!password_verify($currentPassword, $storedPassword)) {

            $message = "Current password is incorrect.";
            $messageType = "error";

        } elseif (strlen($newPassword) < 8) {

            $message = "New password must contain at least 8 characters.";
            $messageType = "error";

        } elseif ($newPassword !== $confirmPassword) {

            $message = "New passwords do not match.";
            $messageType = "error";

        } elseif ($currentPassword === $newPassword) {

            $message = "New password must be different from the current password.";
            $messageType = "error";

        } else {

            try {

                $hashedPassword = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

                $users->updateOne(
                    [
                        "_id" => $worker["_id"]
                    ],
                    [
                        '$set' => [
                            "password" => $hashedPassword,
                            "password_updated_at" =>
                                new MongoDB\BSON\UTCDateTime()
                        ]
                    ]
                );

                $message = "Password changed successfully.";
                $messageType = "success";

            } catch (Throwable $e) {

                $message = "Unable to change password.";
                $messageType = "error";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Worker Settings | WorkOnnect</title>

    <link
        rel="stylesheet"
        href="worker-settings.css"
    >

</head>

<body
    data-theme="<?= htmlspecialchars($settings["theme"]) ?>"
>

<div class="settings-shell">

    <!-- SIDEBAR -->

    <aside class="settings-sidebar">

        <div class="brand">

            <div class="brand-mark">
                W
            </div>

            <div>
                <strong>WorkOnnect</strong>
                <span>Worker Space</span>
            </div>

        </div>

        <nav>

            <a href="worker-dashboard.php">
                <span>⌂</span>
                Dashboard
            </a>

            <a href="worker-profile.php">
                <span>◉</span>
                My Profile
            </a>

            <a href="worker-requests.php">
                <span>↗</span>
                Requests
            </a>

            <a
                href="worker-settings.php"
                class="active"
            >
                <span>⚙</span>
                Settings
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="mini-profile">

                <div class="avatar">
                    <?= strtoupper(substr($workerName, 0, 1)) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars($workerName) ?>
                    </strong>

                    <small>
                        Worker account
                    </small>
                </div>

            </div>

            <a
                class="logout-link"
                href="../../BackEnd/logout.php"
            >
                ⇥ Logout
            </a>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="settings-main">

        <header class="topbar">

            <div>

                <span class="eyebrow">
                    WORKER CONTROL CENTER
                </span>

                <h1>
                    Settings
                </h1>

                <p>
                    Personalize your WorkOnnect workspace,
                    notifications and account security.
                </p>

            </div>

            <div class="top-user">

                <div class="status-dot"></div>

                <div>
                    <strong>
                        <?= htmlspecialchars($workerName) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($workerEmail) ?>
                    </span>
                </div>

            </div>

        </header>


        <?php if ($message): ?>

            <div
                class="flash-message <?= $messageType ?>"
                id="flashMessage"
            >

                <span>
                    <?= $messageType === "success" ? "✓" : "!" ?>
                </span>

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <div class="settings-layout">

            <!-- SETTINGS NAVIGATION -->

            <div class="settings-tabs">

                <button
                    class="settings-tab active"
                    data-target="appearance"
                >
                    <span>◐</span>
                    Appearance
                </button>

                <button
                    class="settings-tab"
                    data-target="notifications"
                >
                    <span>♢</span>
                    Notifications
                </button>

                <button
                    class="settings-tab"
                    data-target="privacy"
                >
                    <span>◎</span>
                    Privacy
                </button>

                <button
                    class="settings-tab"
                    data-target="workspace"
                >
                    <span>▦</span>
                    Workspace
                </button>

                <button
                    class="settings-tab"
                    data-target="security"
                >
                    <span>⌁</span>
                    Security
                </button>

            </div>


            <div class="settings-content">

                <!-- APPEARANCE -->

                <section
                    class="settings-section active"
                    id="appearance"
                >

                    <div class="section-title">

                        <span class="section-icon">
                            ◐
                        </span>

                        <div>

                            <h2>
                                Appearance
                            </h2>

                            <p>
                                Make your worker dashboard feel
                                comfortable and personal.
                            </p>

                        </div>

                    </div>


                    <form
                        method="POST"
                        id="preferencesForm"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="save_preferences"
                        >


                        <div class="card">

                            <div class="card-heading">

                                <div>
                                    <h3>Interface theme</h3>

                                    <p>
                                        Choose how WorkOnnect looks
                                        on your device.
                                    </p>
                                </div>

                            </div>


                            <div class="theme-grid">

                                <label class="theme-option">

                                    <input
                                        type="radio"
                                        name="theme"
                                        value="light"
                                        <?= $settings["theme"] === "light" ? "checked" : "" ?>
                                    >

                                    <div class="theme-preview light-preview">

                                        <div class="preview-top"></div>

                                        <div class="preview-body">

                                            <span></span>
                                            <span></span>
                                            <span></span>

                                        </div>

                                    </div>

                                    <strong>
                                        ☀ Light
                                    </strong>

                                    <small>
                                        Clean and bright
                                    </small>

                                </label>


                                <label class="theme-option">

                                    <input
                                        type="radio"
                                        name="theme"
                                        value="dark"
                                        <?= $settings["theme"] === "dark" ? "checked" : "" ?>
                                    >

                                    <div class="theme-preview dark-preview">

                                        <div class="preview-top"></div>

                                        <div class="preview-body">

                                            <span></span>
                                            <span></span>
                                            <span></span>

                                        </div>

                                    </div>

                                    <strong>
                                        ◐ Dark
                                    </strong>

                                    <small>
                                        Easy on the eyes
                                    </small>

                                </label>

                            </div>

                        </div>


                        <div class="card">

                            <div class="card-heading">

                                <div>
                                    <h3>Availability</h3>

                                    <p>
                                        Tell customers when you are
                                        available for work.
                                    </p>
                                </div>

                            </div>

                            <select
                                name="availability_status"
                                class="select-control"
                            >

                                <option
                                    value="available"
                                    <?= $settings["availability_status"] === "available" ? "selected" : "" ?>
                                >
                                    🟢 Available
                                </option>

                                <option
                                    value="busy"
                                    <?= $settings["availability_status"] === "busy" ? "selected" : "" ?>
                                >
                                    🟡 Busy
                                </option>

                                <option
                                    value="offline"
                                    <?= $settings["availability_status"] === "offline" ? "selected" : "" ?>
                                >
                                    ⚫ Offline
                                </option>

                            </select>

                        </div>

                    </form>

                </section>


                <!-- NOTIFICATIONS -->

                <section
                    class="settings-section"
                    id="notifications"
                >

                    <div class="section-title">

                        <span class="section-icon">
                            ♢
                        </span>

                        <div>

                            <h2>
                                Notifications
                            </h2>

                            <p>
                                Decide what WorkOnnect should notify
                                you about.
                            </p>

                        </div>

                    </div>


                    <div class="card">

                        <div class="setting-row">

                            <div>
                                <strong>
                                    Notifications
                                </strong>

                                <span>
                                    Enable worker notifications.
                                </span>
                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="notifications_enabled"
                                    form="preferencesForm"
                                    <?= $settings["notifications_enabled"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div>
                                <strong>
                                    New request alerts
                                </strong>

                                <span>
                                    Get notified when a customer
                                    sends a request.
                                </span>
                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="request_notifications"
                                    form="preferencesForm"
                                    <?= $settings["request_notifications"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div>
                                <strong>
                                    Approval notifications
                                </strong>

                                <span>
                                    Receive worker approval and
                                    rejection updates.
                                </span>
                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="approval_notifications"
                                    form="preferencesForm"
                                    <?= $settings["approval_notifications"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div>
                                <strong>
                                    Email notifications
                                </strong>

                                <span>
                                    Allow important updates by email.
                                </span>
                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="email_notifications"
                                    form="preferencesForm"
                                    <?= $settings["email_notifications"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div>
                                <strong>
                                    Auto-refresh notifications
                                </strong>

                                <span>
                                    Automatically check for new alerts.
                                </span>
                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="auto_refresh_notifications"
                                    form="preferencesForm"
                                    <?= $settings["auto_refresh_notifications"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>

                    </div>

                </section>


                <!-- PRIVACY -->

                <section
                    class="settings-section"
                    id="privacy"
                >

                    <div class="section-title">

                        <span class="section-icon">
                            ◎
                        </span>

                        <div>

                            <h2>
                                Privacy
                            </h2>

                            <p>
                                Control how customers interact with
                                your worker profile.
                            </p>

                        </div>

                    </div>


                    <div class="card">

                        <div class="setting-row">

                            <div>

                                <strong>
                                    Profile visibility
                                </strong>

                                <span>
                                    Allow customers to discover your
                                    profile.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="profile_visible"
                                    form="preferencesForm"
                                    <?= $settings["profile_visible"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="privacy-info">

                            <span>🛡</span>

                            <div>

                                <strong>
                                    Your information stays protected
                                </strong>

                                <p>
                                    WorkOnnect only exposes the
                                    worker information needed for
                                    customers to evaluate and contact
                                    you.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- WORKSPACE -->

                <section
                    class="settings-section"
                    id="workspace"
                >

                    <div class="section-title">

                        <span class="section-icon">
                            ▦
                        </span>

                        <div>

                            <h2>
                                Workspace
                            </h2>

                            <p>
                                Tune the worker dashboard to your
                                preferred workflow.
                            </p>

                        </div>

                    </div>


                    <div class="card">

                        <div class="setting-row">

                            <div>

                                <strong>
                                    Compact dashboard
                                </strong>

                                <span>
                                    Reduce spacing and show more
                                    information at once.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="compact_dashboard"
                                    form="preferencesForm"
                                    <?= $settings["compact_dashboard"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="setting-row">

                            <div>

                                <strong>
                                    Security alerts
                                </strong>

                                <span>
                                    Notify you about important
                                    account security events.
                                </span>

                            </div>

                            <label class="switch">

                                <input
                                    type="checkbox"
                                    name="security_alerts"
                                    form="preferencesForm"
                                    <?= $settings["security_alerts"] ? "checked" : "" ?>
                                >

                                <span></span>

                            </label>

                        </div>


                        <div class="workspace-card">

                            <div class="orb"></div>

                            <div>

                                <strong>
                                    Worker Workspace
                                </strong>

                                <p>
                                    Your preferences are stored
                                    securely in MongoDB and loaded
                                    automatically whenever you sign in.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- SECURITY -->

                <section
                    class="settings-section"
                    id="security"
                >

                    <div class="section-title">

                        <span class="section-icon">
                            ⌁
                        </span>

                        <div>

                            <h2>
                                Security
                            </h2>

                            <p>
                                Keep your WorkOnnect worker account
                                protected.
                            </p>

                        </div>

                    </div>


                    <div class="card">

                        <div class="security-header">

                            <div class="security-icon">
                                🔐
                            </div>

                            <div>

                                <h3>
                                    Change password
                                </h3>

                                <p>
                                    Use a strong password that you
                                    do not reuse elsewhere.
                                </p>

                            </div>

                        </div>


                        <form
                            method="POST"
                            id="passwordForm"
                            autocomplete="off"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="change_password"
                            >


                            <div class="form-grid">

                                <div class="input-group full">

                                    <label>
                                        Current password
                                    </label>

                                    <div class="password-box">

                                        <input
                                            type="password"
                                            name="current_password"
                                            id="currentPassword"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="show-password"
                                            data-input="currentPassword"
                                        >
                                            Show
                                        </button>

                                    </div>

                                </div>


                                <div class="input-group">

                                    <label>
                                        New password
                                    </label>

                                    <div class="password-box">

                                        <input
                                            type="password"
                                            name="new_password"
                                            id="newPassword"
                                            minlength="8"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="show-password"
                                            data-input="newPassword"
                                        >
                                            Show
                                        </button>

                                    </div>

                                </div>


                                <div class="input-group">

                                    <label>
                                        Confirm password
                                    </label>

                                    <div class="password-box">

                                        <input
                                            type="password"
                                            name="confirm_password"
                                            id="confirmPassword"
                                            minlength="8"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="show-password"
                                            data-input="confirmPassword"
                                        >
                                            Show
                                        </button>

                                    </div>

                                </div>

                            </div>


                            <div
                                class="password-strength"
                                id="passwordStrength"
                            >
                                <div></div>
                                <span>
                                    Password strength
                                </span>
                            </div>


                            <button
                                type="submit"
                                class="primary-button"
                            >
                                Update password
                            </button>

                        </form>

                    </div>


                    <div class="security-notice">

                        <span>🛡</span>

                        <div>

                            <strong>
                                Security recommendation
                            </strong>

                            <p>
                                Use at least 8 characters with a mix
                                of uppercase, lowercase, numbers and
                                symbols.
                            </p>

                        </div>

                    </div>

                </section>

            </div>

        </div>


        <!-- SAVE BAR -->

        <div class="save-bar">

            <div>

                <span class="save-dot"></span>

                <div>

                    <strong>
                        Worker preferences
                    </strong>

                    <small>
                        Changes are saved to your account.
                    </small>

                </div>

            </div>

            <button
                type="submit"
                form="preferencesForm"
                class="save-button"
            >
                Save preferences
            </button>

        </div>

    </main>

</div>

<script src="worker-settings.js"> 


 </script>
<!-- =========================================================
     WORKER SETTINGS DRAWER
========================================================= -->
</body>
</html>