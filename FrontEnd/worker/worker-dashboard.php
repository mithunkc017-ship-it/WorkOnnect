<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/*
|--------------------------------------------------------------------------
| Worker Login Protection
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "worker"
) {
    header(
        "Location: ../pages/login.html?error=Please+login+as+a+worker"
    );
    exit;
}

require_once __DIR__ . "/../../BackEnd/db.php";

/*
|--------------------------------------------------------------------------
| Get Worker From MongoDB
|--------------------------------------------------------------------------
*/
try {

    $userId = new MongoDB\BSON\ObjectId($_SESSION["user_id"]);

    $worker = $users->findOne([
        "_id" => $userId
    ]);

    if (!$worker) {
        session_destroy();

        header(
            "Location: ../pages/login.html?error=Worker+account+not+found"
        );
        exit;
    }

} catch (Throwable $e) {

    die(
        "Unable to load worker profile: " .
        htmlspecialchars($e->getMessage())
    );
}

/*
|--------------------------------------------------------------------------
| Worker Profile Data
|--------------------------------------------------------------------------
*/
$profile = $worker["worker_profile"] ?? [];

$workerName = $profile["worker_name"]
    ?? $worker["name"]
    ?? "Worker";

$contactNumber = $profile["contact_number"]
    ?? "Not added";

$experience = $profile["experience"]
    ?? 0;

$serviceCategory = $profile["service_category"]
    ?? "Service Professional";

$skills = $profile["skills"]
    ?? "Skills not added";

$location = $profile["location"]
    ?? "Location not added";

$availability = $profile["availability"]
    ?? "Not specified";

$about = $profile["about"]
    ?? "Add information about yourself so customers can know more about you.";

$approvalStatus = $profile["approval_status"] 
    ?? "pending";

$email = $worker["email"]
    ?? $_SESSION["user_email"]
    ?? "";

/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
| We will connect the real uploaded DP here later.
|--------------------------------------------------------------------------
*/
$profileImage = $profile["profile_image"] ?? "";

/*
|--------------------------------------------------------------------------
| Create Initial
|--------------------------------------------------------------------------
*/
$initial = strtoupper(
    substr(trim($workerName), 0, 1)
);

if ($initial === "") {
    $initial = "W";
}

/*
|--------------------------------------------------------------------------
| Format Skills
|--------------------------------------------------------------------------
*/
$skillList = array_filter(
    array_map(
        "trim",
        preg_split("/[,|]+/", $skills)
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

    <meta
        name="description"
        content="WorkOnnect Worker Dashboard"
    >

    <title>
        Worker Dashboard | WorkOnnect
    </title>

    <!-- Main Worker Dashboard CSS -->
    <link rel="stylesheet" href="../assets/css/worker-dashboard.css">
</head>

<body>

<div class="worker-app">

    <!-- =====================================================
         TOP NAVIGATION
    ====================================================== -->

    <header class="topbar">

        <a
            href="../index.html"
            class="brand"
        >

            <span class="brand-mark">
                W
            </span>

            <span class="brand-text">
                Work<span>Onnect</span>
            </span>

        </a>


        <nav class="top-navigation">

            <a
                href="#overview"
                class="nav-link active"
            >
                Overview
            </a>

            <a
                href="#services"
                class="nav-link"
            >
                My Services
            </a>

            <a
                href="#requests"
                class="nav-link"
            >
                Requests
            </a>

            <a
                href="#profile"
                class="nav-link"
            >
                Profile
            </a>

        </nav>


        <div class="top-actions">

            <!-- Notification -->
             <!-- =====================================================
     NOTIFICATIONS
====================================================== -->

<div class="notification-wrapper">

    <button
        class="icon-button notification-button"
        type="button"
        aria-label="Notifications"
        id="notificationButton"
    >

        <svg
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
            ></path>

            <path
                d="M10 21h4"
            ></path>
        </svg>

        <!-- Unread count -->
        <span
            class="notification-badge"
            id="notificationBadge"
            style="display:none;"
        >
            0
        </span>

    </button>


    <!-- Notification Dropdown -->

    <div
        class="notification-dropdown"
        id="notificationDropdown"
    >

        <div class="notification-header">

            <div>
                <strong>Notifications</strong>

                <span id="notificationUnreadText">
                    No new notifications
                </span>
            </div>

        </div>


        <div
            class="notification-list"
            id="notificationList"
        >

            <div class="notification-loading">
                Loading notifications...
            </div>

        </div>

    </div>

</div>

            <!-- <button
                class="icon-button notification-button"
                type="button"
                aria-label="Notifications"
            >

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                    ></path>

                    <path
                        d="M10 21h4"
                    ></path>

                </svg>

                <span class="notification-dot"></span>

            </button> -->


            <!-- Mini Profile -->

          <button
    type="button"
    class="mini-profile"
    id="workerSettingsTrigger"
    onclick="openWorkerSettingsDrawer(event)"
>
                <div class="mini-avatar">

                    <?php if ($profileImage !== ""): ?>

                        <!-- <img
                            src="<?= htmlspecialchars($profileImage) ?>"
                            alt="Profile"
                        > -->
                        <?php
                        $profileImage = $profile["profile_image"] ?? "";

                        if (!empty($profileImage)) {
                            $profileImageUrl = "../../" . ltrim($profileImage, "/");
                        } else {
                            $profileImageUrl = "../assets/images/default-profile.png";
                        }
                        ?>

                        <img
                            src="<?= htmlspecialchars($profileImageUrl) ?>"
                            alt="Worker Profile"
                            class="large-avatar"
                            onerror="console.error('Profile image failed:', this.src);"
                        >

                    <?php else: ?>

                        <?= htmlspecialchars($initial) ?>

                    <?php endif; ?>

                </div>

                <span>
                    <?= htmlspecialchars($workerName) ?>
                </span>

                <svg
                    class="chevron"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        d="m6 9 6 6 6-6"
                    ></path>
                </svg>

            </button>

        </div>

    </header>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="dashboard-container">

        <!-- =================================================
             WELCOME
        ================================================== -->

        <section
            class="welcome-section"
            id="overview"
        >

            <div>

                <p class="eyebrow">
                    WORKER DASHBOARD
                </p>

                <h1>
                    Welcome back,
                    <span>
                        <?= htmlspecialchars($workerName) ?>
                    </span>
                    👋
                </h1>

                <p class="welcome-text">
                    Manage your professional profile,
                    services and customer requests from one place.
                </p>

            </div>


            <div class="quick-actions">

                <a
                    href="../pages/worker-profile.html"
                    class="primary-button"
                >

                    <span>✦</span>

                    Edit Profile

                </a>

                <button
                    type="button"
                    class="secondary-button"
                    onclick="shareProfile()"
                >

                    <span>↗</span>

                    Share Profile

                </button>

            </div>

        </section>


        <!-- =================================================
             PROFILE HERO
        ================================================== -->

        <section
            class="profile-hero"
            id="profile"
        >

            <div class="profile-main">

                <!-- Profile Image -->

                <div class="large-avatar">

                    <?php if ($profileImage !== ""): ?>

                        <img
                            src="<?= htmlspecialchars($profileImage) ?>"
                            alt="<?= htmlspecialchars($workerName) ?>"
                        >

                    <?php else: ?>

                        <span>
                            <?= htmlspecialchars($initial) ?>
                        </span>

                    <?php endif; ?>

                    <span class="online-indicator"></span>

                </div>


                <!-- Worker Details -->

                <div class="profile-information">

                    <div class="profile-name-row">

                        <h2>
                            <?= htmlspecialchars($workerName) ?>
                        </h2>

                        <!-- <span class="verified-badge">
                            ✓ Verified
                        </span> -->

                  
                    <?php if ($approvalStatus === "approved"): ?>

                        <span class="verified-badge">
                            ✓ Approved
                        </span>

                    <?php elseif ($approvalStatus === "rejected"): ?>

                        <span class="verified-badge rejected-badge">
                            Rejected
                        </span>

                    <?php else: ?>

                        <span class="verified-badge pending-badge">
                            Pending Approval
                        </span>

                    <?php endif; ?>
                  </div>

                    <p class="profession">

                        <?= htmlspecialchars($serviceCategory) ?>

                    </p>


                    <div class="profile-meta">

                        <span>

                            <span class="meta-icon">
                                ★
                            </span>

                            <strong>
                                2.0
                            </strong>

                            <span>
                                Professional
                            </span>

                        </span>


                        <span>

                            <span class="meta-icon">
                                📍
                            </span>

                            <?= htmlspecialchars($location) ?>

                        </span>


                        <span>

                            <span class="status-circle"></span>

                            <?= htmlspecialchars($availability) ?>

                        </span>

                    </div>

                </div>

            </div>


            <div class="profile-side">

                <p>
                    Experience
                </p>

                <strong>
                    <?= htmlspecialchars($experience) ?>
                    <small>Years</small>
                </strong>

            </div>

        </section>


      <section
    class="requests-section"
    id="requests">

    <div class="section-heading">
        <div>
            <span>NEW OPPORTUNITIES</span>
            <h2>Service Requests</h2>
        </div>

        <div class="request-count" id="requestCount">
            0 Requests
        </div>
    </div>

    <div id="workerRequests" class="worker-requests">

        <div class="requests-loading">
            Loading requests...
        </div>

    </div>

</section>

        <!-- =================================================
             DASHBOARD GRID
        ================================================== -->

        <section class="dashboard-grid">


            <!-- =============================================
                 ABOUT ME
            ============================================== -->

            <article class="dashboard-card about-card">

                <div class="card-heading">

                    <div>

                        <span class="card-icon">
                            👋
                        </span>

                        <div>

                            <h3>
                                About Me
                            </h3>

                            <p>
                                A little about my work
                            </p>

                        </div>

                    </div>

                    <a
                        href="../pages/worker-profile.html"
                        class="small-edit"
                    >
                        Edit
                    </a>

                </div>


                <p class="about-text">

                    <?= nl2br(
                        htmlspecialchars($about)
                    ) ?>

                </p>


                <div class="contact-information">

                    <div>

                        <span class="contact-icon">
                            ✉
                        </span>

                        <div>

                            <small>
                                Email
                            </small>

                            <strong>
                                <?= htmlspecialchars($email) ?>
                            </strong>

                        </div>

                    </div>


                    <div>

                        <span class="contact-icon">
                            ☎
                        </span>

                        <div>

                            <small>
                                Contact
                            </small>

                            <strong>
                                <?= htmlspecialchars($contactNumber) ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </article>


            <!-- =============================================
                 MY SERVICES
            ============================================== -->

            <!-- <article
                class="dashboard-card services-card"
                id="services"
            >

                <div class="card-heading">

                    <div>

                        <span class="card-icon service-card-icon">
                            ✦
                        </span>

                        <div>

                            <h3>
                                My Services
                            </h3>

                            <p>
                                What I can help customers with
                            </p>

                        </div>

                    </div>

                    <button
                        type="button"
                        class="plus-button"
                    >
                        +
                    </button>

                </div>


                <div class="service-item">

                    <div class="service-symbol">
                        🛠
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars($serviceCategory) ?>
                        </strong>

                        <span>
                            Professional service
                        </span>

                    </div>

                    <span class="service-arrow">
                        →
                    </span>

                </div>


                <div class="service-item">

                    <div class="service-symbol">
                        ⚙
                    </div>

                    <div>

                        <strong>
                            Custom Work
                        </strong>

                        <span>
                            Discuss with customers
                        </span>

                    </div>

                    <span class="service-arrow">
                        →
                    </span>

                </div>

            </article>
 -->

            <!-- =============================================
                 EXPERIENCE
            ============================================== -->

            <article class="dashboard-card experience-card">

                <div class="card-heading">

                    <div>

                        <span class="card-icon">
                            🏆
                        </span>

                        <div>

                            <h3>
                                Experience
                            </h3>

                            <p>
                                My professional journey
                            </p>

                        </div>

                    </div>

                </div>


                <div class="experience-number">

                    <strong>
                        <?= htmlspecialchars($experience) ?>
                    </strong>

                    <span>
                        YEARS
                    </span>

                </div>


                <div class="experience-line">

                    <span></span>

                </div>


                <p class="experience-description">

                    Providing reliable and professional
                    services to customers through WorkOnnect.

                </p>

            </article>


            <!-- =============================================
                 SKILLS
            ============================================== -->

            <article class="dashboard-card skills-card">

                <div class="card-heading">

                    <div>

                        <span class="card-icon">
                            ⚡
                        </span>

                        <div>

                            <h3>
                                Skills
                            </h3>

                            <p>
                                My professional skills
                            </p>

                        </div>

                    </div>

                    <a
                        href="../pages/worker-profile.html"
                        class="small-edit"
                    >
                        Edit
                    </a>

                </div>


                <div class="skill-list">

                    <?php if (!empty($skillList)): ?>

                        <?php foreach ($skillList as $skill): ?>

                            <span class="skill-tag">

                                <?= htmlspecialchars($skill) ?>

                            </span>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <span class="skill-tag">
                            Add your skills
                        </span>

                    <?php endif; ?>

                </div>

            </article>


            <!-- =============================================
                 AVAILABILITY
            ============================================== -->

            <article class="dashboard-card availability-card">

                <div class="card-heading">

                    <div>

                        <span class="card-icon availability-icon">
                            ●
                        </span>

                        <div>

                            <h3>
                                Availability
                            </h3>

                            <p>
                                Current working status
                            </p>

                        </div>

                    </div>

                </div>


                <div class="availability-status">

                    <span class="big-status-dot"></span>

                    <div>

                        <strong>
                            <?= htmlspecialchars($availability) ?>
                        </strong>

                        <span>
                            Available for customer requests
                        </span>

                    </div>

                </div>


                <a
                    href="../pages/worker-profile.html"
                    class="availability-link"
                >

                    Update availability

                    <span>
                        →
                    </span>

                </a>

            </article>


            <!-- =============================================
                 CUSTOMER REQUESTS
            ============================================== -->

            <article
                class="dashboard-card requests-card"
                id="requests"
            >

                <div class="card-heading">

                    <div>

                        <span class="card-icon request-icon">
                            💬
                        </span>

                        <div>

                            <h3>
                                Customer Requests
                            </h3>

                            <p>
                                New opportunities for you
                            </p>

                        </div>

                    </div>

                    <span class="request-count">
                        0
                    </span>

                </div>


                <div class="empty-requests">

                    <div class="empty-icon">
                        📭
                    </div>

                    <h4>
                        No new requests
                    </h4>

                    <p>
                        New customer service requests
                        will appear here.
                    </p>

                </div>

            </article>

        </section>

                        <!-- =================================================
     CUSTOMER FEEDBACK
================================================= -->

<section
    class="worker-feedback-section"
    id="feedback"
>

    <div class="section-heading">

        <div>
            <span>CUSTOMER EXPERIENCE</span>

            <h2>Customer Feedback</h2>
        </div>

        <div
            class="feedback-count"
            id="feedbackCount"
        >
            0 Reviews
        </div>

    </div>


    <div
        class="worker-feedback-grid"
        id="workerFeedback"
    >

        <div class="feedback-loading">
            Loading customer feedback...
        </div>

    </div>

</section>
        <!-- =================================================
             PROFILE COMPLETION
        ================================================== -->

        <section class="profile-completion">

            <div class="completion-content">

                <div class="completion-icon">
                    ✨
                </div>

                <div>

                    <h3>
                        Keep your profile updated
                    </h3>

                    <p>
                        A complete profile helps customers
                        understand your experience and services.
                    </p>

                </div>

            </div>


            <a
                href="../pages/worker-profile.html"
                class="completion-button"
            >
                Update Profile →
            </a>

        </section>


    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="dashboard-footer">

        <p>
            © <?= date("Y") ?> WorkOnnect.
            Connect. Work. Grow.
        </p>

        <a href="../index.html">
            Back to Home
        </a>

    </footer>


</div>

<!-- =========================================================
     WORKER SETTINGS DRAWER
========================================================== -->

<div
    class="worker-settings-overlay"
    id="workerSettingsOverlay"
    aria-hidden="true"
></div>

<aside
    class="worker-settings-drawer"
    id="workerSettingsDrawer"
    aria-hidden="true"
>

    <!-- Drawer Header -->
    <div class="settings-drawer-header">

        <div>
            <span class="settings-eyebrow">
                WORKONNECT
            </span>

            <h2>
                Worker Settings
            </h2>

            <p>
                Manage your workspace and account.
            </p>
        </div>

        <button
            type="button"
            class="settings-close"
            id="closeWorkerSettings"
            aria-label="Close settings"
        >
            ×
        </button>

    </div>


    <!-- Worker Profile -->
    <div class="settings-profile-card">

        <div class="settings-profile-avatar">

            <?php if (!empty($profileImage)): ?>

                <?php
                $drawerProfileImage =
                    "../../" . ltrim($profileImage, "/");
                ?>

                <img
                    src="<?= htmlspecialchars($drawerProfileImage) ?>"
                    alt="Profile"
                    onerror="this.style.display='none';"
                >

            <?php else: ?>

                <?= htmlspecialchars($initial) ?>

            <?php endif; ?>

        </div>

        <div>

            <strong>
                <?= htmlspecialchars($workerName) ?>
            </strong>

            <span>
                <?= htmlspecialchars($serviceCategory) ?>
            </span>

            <small>
                <?= htmlspecialchars($email) ?>
            </small>

        </div>

    </div>


    <!-- Scrollable Content -->
    <div class="settings-drawer-content">


        <!-- Appearance -->
        <div class="settings-block">

            <div class="settings-block-title">

                <span class="settings-block-icon">
                    ◐
                </span>

                <div>

                    <strong>
                        Appearance
                    </strong>

                    <small>
                        Personalize your dashboard
                    </small>

                </div>

            </div>


            <div class="theme-buttons">

                <button
                    type="button"
                    class="theme-choice active"
                    data-theme-choice="light"
                >
                    <span>☀</span>
                    Light
                </button>

                <button
                    type="button"
                    class="theme-choice"
                    data-theme-choice="dark"
                >
                    <span>◐</span>
                    Dark
                </button>

            </div>

        </div>


        <!-- Notifications -->
        <div class="settings-block">

            <div class="settings-block-title">

                <span class="settings-block-icon">
                    ♢
                </span>

                <div>

                    <strong>
                        Notifications
                    </strong>

                    <small>
                        Control your alerts
                    </small>

                </div>

            </div>


            <label class="drawer-setting-row">

                <div>

                    <strong>
                        New requests
                    </strong>

                    <small>
                        Customer service requests
                    </small>

                </div>

                <span class="drawer-switch">

                    <input
                        type="checkbox"
                        id="drawerRequestNotifications"
                        checked
                    >

                    <span></span>

                </span>

            </label>


            <label class="drawer-setting-row">

                <div>

                    <strong>
                        Approval updates
                    </strong>

                    <small>
                        Profile approval notifications
                    </small>

                </div>

                <span class="drawer-switch">

                    <input
                        type="checkbox"
                        id="drawerApprovalNotifications"
                        checked
                    >

                    <span></span>

                </span>

            </label>


            <label class="drawer-setting-row">

                <div>

                    <strong>
                        Email updates
                    </strong>

                    <small>
                        Important account updates
                    </small>

                </div>

                <span class="drawer-switch">

                    <input
                        type="checkbox"
                        id="drawerEmailNotifications"
                        checked
                    >

                    <span></span>

                </span>

            </label>

        </div>


        <!-- Privacy -->
        <div class="settings-block">

            <div class="settings-block-title">

                <span class="settings-block-icon">
                    ◎
                </span>

                <div>

                    <strong>
                        Privacy & Visibility
                    </strong>

                    <small>
                        Control customer visibility
                    </small>

                </div>

            </div>


            <label class="drawer-setting-row">

                <div>

                    <strong>
                        Profile visibility
                    </strong>

                    <small>
                        Allow customers to find you
                    </small>

                </div>

                <span class="drawer-switch">

                    <input
                        type="checkbox"
                        id="drawerProfileVisibility"
                        checked
                    >

                    <span></span>

                </span>

            </label>

        </div>


        <!-- Account -->
        <div class="settings-block">

            <div class="settings-block-title">

                <span class="settings-block-icon">
                    ⌁
                </span>

                <div>

                    <strong>
                        Account & Security
                    </strong>

                    <small>
                        Protect your account
                    </small>

                </div>

            </div>


            <button
                type="button"
                class="drawer-action"
                id="openPasswordPanel"
            >

                <div>

                    <strong>
                        Change password
                    </strong>

                    <small>
                        Update your account password
                    </small>

                </div>

                <span>
                    →
                </span>

            </button>


            <button
                type="button"
                class="drawer-action"
                id="openFullSettings"
            >

                <div>

                    <strong>
                        Advanced settings
                    </strong>

                    <small>
                        Open complete worker settings
                    </small>

                </div>

                <span>
                    →
                </span>

            </button>

        </div>


        <!-- Logout -->
        <a
            href="../../BackEnd/logout.php"
            class="drawer-logout"
        >

            <span>
                ⇥
            </span>

            Logout

        </a>


    </div>

</aside>
<!-- =========================================================
     SHARE PROFILE SCRIPT
========================================================== -->
<!-- =========================================================
     DASHBOARD SCRIPTS
========================================================== -->

<script>

function shareProfile() {

    const profileUrl = window.location.href;

    if (navigator.share) {

        navigator.share({
            title: "<?= htmlspecialchars($workerName, ENT_QUOTES) ?> | WorkOnnect",
            text: "Check out my professional profile on WorkOnnect.",
            url: profileUrl
        });

    } else {

        navigator.clipboard.writeText(profileUrl)
            .then(function () {

                alert("Profile link copied!");

            })
            .catch(function () {

                alert("Unable to copy profile link.");

            });

    }

}


/* =========================================================
   LOAD WORKER REQUESTS
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    loadWorkerRequests();

});


async function loadWorkerRequests() {

    const container =
        document.getElementById("workerRequests");

    const count =
        document.getElementById("requestCount");

    if (!container) {
        console.error("workerRequests element not found.");
        return;
    }

    if (!count) {
        console.error("requestCount element not found.");
        return;
    }

    try {

        console.log("Loading worker requests...");

        const response = await fetch(
            "../../BackEnd/get_worker_requests.php",
            {
                method: "GET",
                credentials: "same-origin",
                cache: "no-store"
            }
        );

        console.log(
            "Request API status:",
            response.status
        );

        if (!response.ok) {

            throw new Error(
                "Server returned HTTP " +
                response.status
            );

        }

        const data = await response.json();

        console.log(
            "Worker request data:",
            data
        );


        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to load requests."
            );

        }


        count.textContent =
            data.count +
            (
                data.count === 1
                    ? " Request"
                    : " Requests"
            );


        if (
            !data.requests ||
            data.requests.length === 0
        ) {

            container.innerHTML = `

                <div class="empty-worker-requests">

                    <div class="empty-request-icon">
                        ✓
                    </div>

                    <h3>
                        No requests yet
                    </h3>

                    <p>
                        Customer service requests
                        will appear here when someone
                        contacts you.
                    </p>

                </div>

            `;

            return;

        }


        container.innerHTML =
            data.requests
                .map(createRequestCard)
                .join("");


    } catch (error) {

        console.error(
            "REQUEST ERROR:",
            error
        );

        container.innerHTML = `

            <div class="empty-worker-requests">

                <div class="empty-request-icon">
                    !
                </div>

                <h3>
                    Unable to load requests
                </h3>

                <p>
                    ${escapeRequestHtml(error.message)}
                </p>

            </div>

        `;

    }

}


/* =========================================================
   CREATE REQUEST CARD
========================================================= */
function createRequestCard(request) {

    const status =
        request.status || "pending";


    let actionButtons = "";


    if (status === "pending") {

        actionButtons = `

            <div class="request-actions">

               <button
    type="button"
    class="accept-request-btn"
    data-request-id="${escapeRequestHtml(request.id)}"
    data-request-status="accepted"
>
    ✓ Accept
</button>

                <button
    type="button"
    class="reject-request-btn"
    data-request-id="${escapeRequestHtml(request.id)}"
    data-request-status="rejected"
>
    × Reject
</button>

            </div>

        `;

              } else if (status === "accepted") {

    actionButtons = `

        <div class="request-actions">

            <button
                type="button"
                class="complete-request-btn"
                data-request-id="${escapeRequestHtml(request.id)}"
                data-request-status="completed"
            >
                ✓ Mark as Completed
            </button>

        </div>

    `;

} else if (status === "completed") {

    actionButtons = `

        <div class="request-result completed-result">
            ✓ Service completed
        </div>

    `;

} else if (status === "rejected") {          

        actionButtons = `

            <div class="request-result rejected-result">
                × You rejected this request
            </div>

        `;

    }


    return `

        <article class="request-card">

            <div class="request-card-top">

                <div class="customer-avatar">

                    ${escapeRequestHtml(
                        (
                            request.customer_name ||
                            "C"
                        )
                        .charAt(0)
                        .toUpperCase()
                    )}

                </div>


                <span
                    class="request-status ${escapeRequestHtml(status)}"
                >
                    ${escapeRequestHtml(status)}
                </span>

            </div>


            <div class="request-customer">

                <span>CUSTOMER</span>

                <h3>
                    ${escapeRequestHtml(
                        request.customer_name ||
                        "Customer"
                    )}
                </h3>

            </div>


            <div class="request-service">

                <span>SERVICE</span>

                <strong>
                    ${escapeRequestHtml(
                        request.service ||
                        "Service"
                    )}
                </strong>

            </div>


            <div class="request-message">

                <span>MESSAGE</span>

                <p>
                    ${escapeRequestHtml(
                        request.message ||
                        "No message provided."
                    )}
                </p>

            </div>


            <div class="request-bottom">

                <div>

                    <span>
                        Preferred Date
                    </span>

                    <strong>
                        ${escapeRequestHtml(
                            request.preferred_date ||
                            "Not specified"
                        )}
                    </strong>

                </div>


                <div class="request-date">

                    ${escapeRequestHtml(
                        request.created_at || ""
                    )}

                </div>

            </div>


            ${actionButtons}

        </article>

    `;
}



/* =========================================================
   SECURITY HTML ESCAPE
========================================================= */

function escapeRequestHtml(value) {

    return String(value)

        .replace(/&/g, "&amp;")

        .replace(/</g, "&lt;")

        .replace(/>/g, "&gt;")

        .replace(/"/g, "&quot;")

        .replace(/'/g, "&#039;");

}


document.addEventListener("click", function (event) {

        const button =
    event.target.closest(
        ".accept-request-btn, .reject-request-btn, .complete-request-btn"
    );

    if (!button) {
        return;
    }

    const requestId =
        button.dataset.requestId;

    const status =
        button.dataset.requestStatus;

    if (!requestId || !status) {
        console.error("Request information missing.");
        return;
    }

    updateRequestStatus(
        requestId,
        status
    );

});


async function updateRequestStatus(
    requestId,
    status
) {

    let action;

if (status === "accepted") {

    action = "accept";

} else if (status === "rejected") {

    action = "reject";

} else if (status === "completed") {

    action = "mark this service as completed";

}

    if (
        !confirm(
            "Are you sure you want to " +
            action +
            " this request?"
        )
    ) {
        return;
    }

    try {

        const formData =
            new FormData();

        formData.append(
            "request_id",
            requestId
        );

        formData.append(
            "status",
            status
        );


        const response =
            await fetch(
                "../../BackEnd/update_request_status.php",
                {
                    method: "POST",
                    body: formData,
                    credentials: "same-origin"
                }
            );


        const data =
            await response.json();


        console.log(
            "Status update:",
            data
        );


        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to update request."
            );

        }


        await loadWorkerRequests();


    } catch (error) {

        console.error(
            "STATUS UPDATE ERROR:",
            error
        );

        alert(
            "Unable to update request: " +
            error.message
        );

    }

}


/* =========================================================
   WORKER NOTIFICATIONS
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    loadWorkerNotifications();

});


const notificationButton =
    document.getElementById("notificationButton");

const notificationDropdown =
    document.getElementById("notificationDropdown");


/*
|----------------------------------------------------------
| Open / Close Notification Dropdown
|----------------------------------------------------------
*/

if (notificationButton) {

    notificationButton.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

            notificationDropdown.classList.toggle(
                "show"
            );

        }
    );

}


/*
|----------------------------------------------------------
| Close dropdown when clicking outside
|----------------------------------------------------------
*/

document.addEventListener(
    "click",
    function (event) {

        if (
            notificationDropdown &&
            !event.target.closest(
                ".notification-wrapper"
            )
        ) {

            notificationDropdown.classList.remove(
                "show"
            );

        }

    }
);


/*
|----------------------------------------------------------
| Load Notifications
|----------------------------------------------------------
*/

async function loadWorkerNotifications() {

    const badge =
        document.getElementById(
            "notificationBadge"
        );

    const list =
        document.getElementById(
            "notificationList"
        );

    const unreadText =
        document.getElementById(
            "notificationUnreadText"
        );


    if (!badge || !list) {

        console.error(
            "Notification elements not found."
        );

        return;

    }


    try {

        console.log(
            "Loading worker notifications..."
        );


        /*
        IMPORTANT:

        Change this filename ONLY if your actual
        notification API has a different name.
        */

        const response = await fetch(
            "../../BackEnd/get_notifications.php",
            {
                method: "GET",
                credentials: "same-origin",
                cache: "no-store"
            }
        );


        console.log(
            "Notification API status:",
            response.status
        );


        if (!response.ok) {

            throw new Error(
                "Notification API returned HTTP " +
                response.status
            );

        }


        const data =
            await response.json();


        console.log(
            "Notification data:",
            data
        );


        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to load notifications."
            );

        }


        const unreadCount =
            Number(
                data.unread_count || 0
            );


        /*
        |--------------------------------------------------
        | Update badge
        |--------------------------------------------------
        */

        if (unreadCount > 0) {

            badge.textContent =
                unreadCount > 99
                    ? "99+"
                    : unreadCount;

            badge.style.display =
                "flex";

            if (unreadText) {

                unreadText.textContent =
                    unreadCount +
                    (
                        unreadCount === 1
                            ? " unread notification"
                            : " unread notifications"
                    );

            }

        } else {

            badge.style.display =
                "none";

            if (unreadText) {

                unreadText.textContent =
                    "No new notifications";

            }

        }


        /*
        |--------------------------------------------------
        | Render notifications
        |--------------------------------------------------
        */

        if (
            !data.notifications ||
            data.notifications.length === 0
        ) {

            list.innerHTML = `

                <div class="notification-empty">

                    <div class="notification-empty-icon">
                        ✓
                    </div>

                    <strong>
                        You're all caught up
                    </strong>

                    <p>
                        No notifications yet.
                    </p>

                </div>

            `;

            return;

        }


        list.innerHTML =
            data.notifications
                .map(
                    createNotificationCard
                )
                .join("");


    } catch (error) {

        console.error(
            "NOTIFICATION ERROR:",
            error
        );


        list.innerHTML = `

            <div class="notification-error">

                Unable to load notifications.

            </div>

        `;

    }

}


/*
|----------------------------------------------------------
| Create Notification
|----------------------------------------------------------
*/

function createNotificationCard(
    notification
) {

    const unread =
        notification.read === false;


    const notificationId =
        escapeRequestHtml(
            notification.id
        );


    const title =
        escapeRequestHtml(
            notification.title ||
            "Notification"
        );


    const message =
        escapeRequestHtml(
            notification.message ||
            ""
        );


    const createdAt =
        escapeRequestHtml(
            notification.created_at ||
            ""
        );


    return `

             <article
                class="notification-item
                ${unread ? "unread" : ""}"
                data-notification-id="${notificationId}"
                data-notification-type="${escapeRequestHtml(
                    notification.type || ""
                )}"
>

            <div class="notification-icon">

              ${
                notification.type === "worker_approval"
                    ? "✓"
                    : notification.type === "feedback_received"
                        ? "★"
                        : "🔔"
}
            </div>


            <div class="notification-content">

                <div class="notification-title-row">

                    <strong>
                        ${title}
                    </strong>

                    ${
                        unread
                            ? `
                                <span
                                    class="notification-unread-dot"
                                ></span>
                            `
                            : ""
                    }

                </div>


                <p>
                    ${message}
                </p>


                <small>
                    ${createdAt}
                </small>

            </div>

        </article>

    `;

}


/*
|----------------------------------------------------------
| Click Notification
|----------------------------------------------------------
*/

document.addEventListener(
    "click",
    function (event) {

        const notification =
            event.target.closest(
                ".notification-item"
            );


        if (!notification) {
            return;
        }


        const notificationId =
            notification.dataset.notificationId;


        if (!notificationId) {
            return;
        }


        /*
        Already read?
        */

        if (
            !notification.classList.contains(
                "unread"
            )
        ) {

            return;

        }

                        markNotificationAsRead(
    notificationId
);

/* Open feedback when feedback notification is clicked */

if (
    notification.dataset.notificationType ===
    "feedback_received"
) {

    setTimeout(function () {

        const feedbackSection =
            document.getElementById("feedback");

        if (feedbackSection) {

            feedbackSection.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        }

    }, 300);

}
       
    }
);


/*
|----------------------------------------------------------
| Mark Notification As Read
|----------------------------------------------------------
*/

async function markNotificationAsRead(
    notificationId
) {

    try {

        const formData =
            new FormData();


        formData.append(
            "notification_id",
            notificationId
        );


        const response =
            await fetch(
                "../../BackEnd/mark_notification_read.php",
                {
                    method: "POST",
                    body: formData,
                    credentials: "same-origin"
                }
            );


        const data =
            await response.json();


        console.log(
            "Mark notification result:",
            data
        );


        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to mark notification as read."
            );

        }


        /*
        Reload notifications so the badge
        automatically changes from 1 → 0.
        */

        await loadWorkerNotifications();


    } catch (error) {

        console.error(
            "MARK NOTIFICATION ERROR:",
            error
        );

    }

}

/* =========================================================
   CUSTOMER FEEDBACK
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        loadWorkerFeedback();

    }
);


async function loadWorkerFeedback() {

    const container =
        document.getElementById(
            "workerFeedback"
        );

    const count =
        document.getElementById(
            "feedbackCount"
        );


    if (!container) {
        console.error(
            "workerFeedback element not found."
        );
        return;
    }


    try {

        console.log(
            "Loading worker feedback..."
        );


        const response =
            await fetch(
                "../../BackEnd/get_worker_feedback.php",
                {
                    method: "GET",
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );


        console.log(
            "Feedback API status:",
            response.status
        );


        if (!response.ok) {

            throw new Error(
                "Feedback API returned HTTP " +
                response.status
            );

        }


        const data =
            await response.json();


        console.log(
            "Worker feedback data:",
            data
        );


        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to load feedback."
            );

        }


        const feedback =
            data.feedback || [];


        if (count) {

            count.textContent =
                data.count +
                (
                    data.count === 1
                        ? " Review"
                        : " Reviews"
                );

        }


        if (feedback.length === 0) {

            container.innerHTML = `

                <div class="feedback-empty">

                    <div class="feedback-empty-icon">
                        ☆
                    </div>

                    <h3>
                        No feedback yet
                    </h3>

                    <p>
                        Customer feedback will appear
                        here after you complete services.
                    </p>

                </div>

            `;

            return;

        }


        container.innerHTML =
            feedback
                .map(
                    createWorkerFeedbackCard
                )
                .join("");


    } catch (error) {

        console.error(
            "FEEDBACK ERROR:",
            error
        );


        container.innerHTML = `

            <div class="feedback-empty">

                <div class="feedback-empty-icon">
                    !
                </div>

                <h3>
                    Unable to load feedback
                </h3>

                <p>
                    ${escapeRequestHtml(
                        error.message
                    )}
                </p>

            </div>

        `;

    }

}


/*
|----------------------------------------------------------
| CREATE FEEDBACK CARD
|----------------------------------------------------------
*/

function createWorkerFeedbackCard(
    feedback
) {

    const rating =
        Number(
            feedback.rating || 0
        );


    let stars = "";

    for (
        let i = 1;
        i <= 5;
        i++
    ) {

        stars +=
            i <= rating
                ? "★"
                : "☆";

    }


    return `

        <article
            class="worker-feedback-card"
        >

            <div class="worker-feedback-top">

                <div class="feedback-customer-avatar">

                    ${escapeRequestHtml(
                        (
                            feedback.customer_name ||
                            "C"
                        )
                        .charAt(0)
                        .toUpperCase()
                    )}

                </div>


                <div class="feedback-customer-info">

                    <strong>
                        ${escapeRequestHtml(
                            feedback.customer_name ||
                            "Customer"
                        )}
                    </strong>

                    <span>
                        ${escapeRequestHtml(
                            feedback.service ||
                            "Service"
                        )}
                    </span>

                </div>


                <div class="feedback-rating">

                    ${stars}

                </div>

            </div>


            <div class="feedback-comment">

                <p>
                    “${escapeRequestHtml(
                        feedback.comment ||
                        "No written feedback."
                    )}”
                </p>

            </div>


            <div class="feedback-date">

                ${escapeRequestHtml(
                    feedback.created_at || ""
                )}

            </div>

        </article>

    `;

}




/* =========================================================
   WORKER SETTINGS DRAWER
========================================================= */
/* =========================================================
   WORKER SETTINGS DRAWER
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const trigger =
        document.getElementById("workerSettingsTrigger");

    const drawer =
        document.getElementById("workerSettingsDrawer");

    const overlay =
        document.getElementById("workerSettingsOverlay");

    const closeButton =
        document.getElementById("closeWorkerSettings");


    console.log("WORKER SETTINGS:", {
        trigger: !!trigger,
        drawer: !!drawer,
        overlay: !!overlay,
        closeButton: !!closeButton
    });


    if (!trigger || !drawer || !overlay) {

        console.error(
            "Worker settings drawer elements are missing."
        );

        return;
    }


    /* ==============================================
       OPEN DRAWER
    ============================================== */

    function openWorkerSettings() {

        drawer.classList.add("open");

        overlay.classList.add("show");

        drawer.setAttribute(
            "aria-hidden",
            "false"
        );

        overlay.setAttribute(
            "aria-hidden",
            "false"
        );

        document.body.classList.add(
            "worker-settings-open"
        );

    }


    /* ==============================================
       CLOSE DRAWER
    ============================================== */

    function closeWorkerSettings() {

        drawer.classList.remove("open");

        overlay.classList.remove("show");

        drawer.setAttribute(
            "aria-hidden",
            "true"
        );

        overlay.setAttribute(
            "aria-hidden",
            "true"
        );

        document.body.classList.remove(
            "worker-settings-open"
        );

    }


    /* ==============================================
       PROFILE BUTTON
    ============================================== */

    trigger.addEventListener(
        "click",
        function (event) {

            event.preventDefault();

            event.stopPropagation();

            console.log(
                "Worker profile button clicked"
            );

            openWorkerSettings();

        }
    );


    /* ==============================================
       CLOSE BUTTON
    ============================================== */

    if (closeButton) {

        closeButton.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                closeWorkerSettings();

            }
        );

    }


    /* ==============================================
       CLICK OUTSIDE
    ============================================== */

    overlay.addEventListener(
        "click",
        function () {

            closeWorkerSettings();

        }
    );


    /* ==============================================
       ESCAPE KEY
    ============================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeWorkerSettings();

            }

        }
    );


    /* ==============================================
       LIGHT / DARK MODE
    ============================================== */

    const themeButtons =
        document.querySelectorAll(
            "[data-theme-choice]"
        );


    themeButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const theme =
                    button.dataset.themeChoice;


                themeButtons.forEach(function (item) {

                    item.classList.remove(
                        "active"
                    );

                });


                button.classList.add(
                    "active"
                );


                if (theme === "dark") {

                    document.body.classList.add(
                        "worker-dark-mode"
                    );

                } else {

                    document.body.classList.remove(
                        "worker-dark-mode"
                    );

                }

            }
        );

    });

});


function openWorkerSettingsDrawer(event) {

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const drawer =
        document.getElementById("workerSettingsDrawer");

    const overlay =
        document.getElementById("workerSettingsOverlay");

    console.log("PROFILE BUTTON CLICKED");
    console.log("Drawer:", drawer);
    console.log("Overlay:", overlay);

    if (!drawer) {
        alert("Worker settings drawer HTML is missing.");
        return;
    }

    drawer.classList.add("open");

    if (overlay) {
        overlay.classList.add("open");
    }

    document.body.style.overflow = "hidden";
}


function closeWorkerSettingsDrawer() {

    const drawer =
        document.getElementById("workerSettingsDrawer");

    const overlay =
        document.getElementById("workerSettingsOverlay");

    if (drawer) {
        drawer.classList.remove("open");
    }

    if (overlay) {
        overlay.classList.remove("open");
    }

    document.body.style.overflow = "";

}

</script>



<div
    class="worker-settings-overlay"
    id="workerSettingsOverlay"
></div>

<aside
    class="worker-settings-drawer"
    id="workerSettingsDrawer"
    aria-hidden="true"
>

    <div class="settings-drawer-header">

        <div>
            <span class="settings-eyebrow">
                WORKONNECT
            </span>

            <h2>
                Worker Settings
            </h2>

            <p>
                Manage your account and workspace.
            </p>
        </div>

        <button
            type="button"
            class="settings-close"
            id="closeWorkerSettings"
            aria-label="Close settings"
        >
            ×
        </button>

    </div>


    <div class="settings-profile-card">

        <div class="settings-profile-avatar">

            <?php if (!empty($profileImage)): ?>

                <img
                    src="<?= htmlspecialchars($profileImageUrl ?? '') ?>"
                    alt="Profile"
                >

            <?php else: ?>

                <?= htmlspecialchars($initial) ?>

            <?php endif; ?>

        </div>

        <div>

            <strong>
                <?= htmlspecialchars($workerName) ?>
            </strong>

            <span>
                <?= htmlspecialchars($serviceCategory) ?>
            </span>

            <small>
                <?= htmlspecialchars($email) ?>
            </small>

        </div>

    </div>


    <div class="settings-drawer-content">

        <div class="settings-block">

            <h3 class="settings-block-title">
                Account
            </h3>

            <div class="drawer-setting-row">

                <div>
                    <strong>Profile</strong>
                    <span>Manage your worker profile</span>
                </div>

            </div>

            <div class="drawer-setting-row">

                <div>
                    <strong>Availability</strong>
                    <span>
                        <?= htmlspecialchars($availability) ?>
                    </span>
                </div>

            </div>

        </div>


        <div class="settings-block">

            <h3 class="settings-block-title">
                Preferences
            </h3>

            <div class="drawer-setting-row">

                <div>
                    <strong>Notifications</strong>
                    <span>
                        Receive worker request updates
                    </span>
                </div>

            </div>

            <div class="drawer-setting-row">

                <div>
                    <strong>Profile visibility</strong>
                    <span>
                        Your profile is visible to customers
                    </span>
                </div>

            </div>

        </div>


        <div class="settings-block">

            <h3 class="settings-block-title">
                Settings
            </h3>

            <button
                type="button"
                class="drawer-action"
                onclick="window.location.href='worker-settings.php';"
            >
                Open Full Settings
            </button>

        </div>

    </div>

</aside>
<style>
/* =========================================================
   WORKER SETTINGS DRAWER - FORCE VISIBLE
========================================================= */

.worker-settings-overlay {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;

    background: rgba(23, 37, 43, 0.35) !important;
    backdrop-filter: blur(4px);

    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;

    z-index: 99998 !important;

    transition:
        opacity 0.25s ease,
        visibility 0.25s ease;
}

.worker-settings-overlay.open,
.worker-settings-overlay.show {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
}


/* DRAWER */

.worker-settings-drawer {
    position: fixed !important;

    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;

    width: min(430px, 92vw) !important;
    height: 100vh !important;

    background: #fafaf5 !important;

    border-left: 1px solid #dce6e5;

    box-shadow:
        -20px 0 60px rgba(23, 37, 43, 0.18) !important;

    z-index: 99999 !important;

    overflow-y: auto !important;
    overflow-x: hidden !important;

    transform: translateX(110%) !important;

    visibility: hidden !important;

    transition:
        transform 0.35s cubic-bezier(.22,.61,.36,1),
        visibility 0.35s ease !important;
}


/* THIS IS THE IMPORTANT PART */

.worker-settings-drawer.open {
    transform: translateX(0) !important;
    visibility: visible !important;
}


/* HEADER */

.settings-drawer-header {
    position: sticky;
    top: 0;

    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    padding: 28px 24px 22px;

    background: rgba(250,250,245,.96);
    backdrop-filter: blur(14px);

    border-bottom: 1px solid #dce6e5;

    z-index: 2;
}

.settings-eyebrow {
    display: block;

    font-size: 11px;
    font-weight: 800;
    letter-spacing: .14em;

    color: #197487;

    margin-bottom: 6px;
}

.settings-drawer-header h2 {
    margin: 0;

    font-family: "Outfit", sans-serif;

    font-size: 25px;

    color: #17252b;
}

.settings-drawer-header p {
    margin: 6px 0 0;

    font-family: "DM Sans", sans-serif;

    font-size: 13px;

    color: #66767a;
}


/* CLOSE BUTTON */

.settings-close {
    width: 40px;
    height: 40px;

    border: 1px solid #dce6e5;

    border-radius: 12px;

    background: white;

    color: #17252b;

    font-size: 25px;
    line-height: 1;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: .2s ease;
}

.settings-close:hover {
    background: #d9eff2;

    color: #197487;

    transform: rotate(5deg);
}


/* PROFILE CARD */

.settings-profile-card {
    display: flex;
    align-items: center;
    gap: 14px;

    margin: 20px 24px;

    padding: 16px;

    background: white;

    border: 1px solid #dce6e5;

    border-radius: 18px;

    box-shadow: 0 8px 24px rgba(23,37,43,.06);
}

.settings-profile-avatar {
    width: 54px;
    height: 54px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #d9eff2;

    color: #197487;

    display: flex;
    align-items: center;
    justify-content: center;

    font-family: "Outfit", sans-serif;

    font-size: 22px;
    font-weight: 800;

    overflow: hidden;
}

.settings-profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.settings-profile-card strong {
    display: block;

    font-family: "Outfit", sans-serif;

    color: #17252b;

    font-size: 16px;
}

.settings-profile-card span,
.settings-profile-card small {
    display: block;

    margin-top: 3px;

    font-family: "DM Sans", sans-serif;

    color: #66767a;

    font-size: 12px;
}


/* CONTENT */

.settings-drawer-content {
    padding: 0 24px 40px;
}

.settings-block {
    margin-bottom: 22px;
}

.settings-block-title {
    margin: 0 0 10px;

    font-family: "Outfit", sans-serif;

    font-size: 13px;

    font-weight: 800;

    color: #17252b;

    text-transform: uppercase;

    letter-spacing: .08em;
}

.drawer-setting-row {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    padding: 15px 16px;

    background: white;

    border: 1px solid #dce6e5;

    border-radius: 14px;

    margin-bottom: 9px;
}

.drawer-setting-row div {
    min-width: 0;
}

.drawer-setting-row strong {
    display: block;

    font-family: "DM Sans", sans-serif;

    color: #17252b;

    font-size: 14px;
}

.drawer-setting-row span {
    display: block;

    margin-top: 3px;

    font-family: "DM Sans", sans-serif;

    color: #66767a;

    font-size: 11px;
}


/* ACTION BUTTON */

.drawer-action {
    width: 100%;

    border: 1px solid #dce6e5;

    border-radius: 14px;

    padding: 14px 16px;

    background: white;

    color: #17252b;

    text-align: left;

    cursor: pointer;

    font-family: "DM Sans", sans-serif;

    font-weight: 700;

    transition: .2s ease;
}

.drawer-action:hover {
    background: #d9eff2;

    border-color: #197487;

    color: #197487;
}


/* MOBILE */

@media (max-width: 600px) {

    .worker-settings-drawer {
        width: 94vw !important;
    }

    .settings-drawer-header {
        padding: 22px 18px;
    }

    .settings-profile-card {
        margin: 16px 18px;
    }

    .settings-drawer-content {
        padding: 0 18px 30px;
    }
}





</style>



<style>
/* =========================================================
   WORKONNECT — ISOLATED WORKER SETTINGS DRAWER
   Does NOT modify the dashboard behind it
========================================================= */

/* ---------- BACKDROP ---------- */

#workerSettingsOverlay {
    position: fixed !important;
    inset: 0 !important;

    width: 100% !important;
    height: 100% !important;

    background: rgba(23, 37, 43, 0.18) !important;

    backdrop-filter: blur(2px) !important;
    -webkit-backdrop-filter: blur(2px) !important;

    opacity: 0 !important;
    visibility: hidden !important;

    pointer-events: none !important;

    z-index: 99990 !important;

    transition:
        opacity 0.28s ease,
        visibility 0.28s ease !important;
}

#workerSettingsOverlay.open,
#workerSettingsOverlay.show {
    opacity: 1 !important;
    visibility: visible !important;

    pointer-events: auto !important;
}


/* =========================================================
   DRAWER
========================================================= */

#workerSettingsDrawer {
    position: fixed !important;

    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;

    width: 430px !important;
    max-width: 92vw !important;

    height: 100vh !important;

    margin: 0 !important;
    padding: 0 !important;

    background: #fafaf5 !important;

    color: #17252b !important;

    border-left: 1px solid #dce6e5 !important;

    box-shadow:
        -18px 0 55px rgba(23, 37, 43, 0.16) !important;

    overflow-x: hidden !important;
    overflow-y: auto !important;

    transform: translate3d(105%, 0, 0) !important;

    opacity: 0 !important;
    visibility: hidden !important;

    z-index: 99999 !important;

    transition:
        transform 0.42s cubic-bezier(.22, .61, .36, 1),
        opacity 0.28s ease,
        visibility 0.42s ease !important;

    box-sizing: border-box !important;

    isolation: isolate !important;
}


/* OPEN */

#workerSettingsDrawer.open {
    transform: translate3d(0, 0, 0) !important;

    opacity: 1 !important;
    visibility: visible !important;
}


/* =========================================================
   DRAWER HEADER
========================================================= */

#workerSettingsDrawer .settings-drawer-header {
    position: sticky !important;

    top: 0 !important;

    min-height: 92px !important;

    display: flex !important;

    align-items: flex-start !important;
    justify-content: space-between !important;

    gap: 20px !important;

    padding: 24px !important;

    background: rgba(250, 250, 245, 0.94) !important;

    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;

    border-bottom: 1px solid #dce6e5 !important;

    z-index: 10 !important;

    box-sizing: border-box !important;
}


#workerSettingsDrawer .settings-eyebrow {
    display: block !important;

    margin: 0 0 5px !important;

    color: #197487 !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 10px !important;

    font-weight: 800 !important;

    letter-spacing: 0.16em !important;
}


#workerSettingsDrawer .settings-drawer-header h2 {
    margin: 0 !important;

    color: #17252b !important;

    font-family: "Outfit", sans-serif !important;

    font-size: 25px !important;

    line-height: 1.15 !important;

    font-weight: 700 !important;
}


#workerSettingsDrawer .settings-drawer-header p {
    margin: 7px 0 0 !important;

    color: #66767a !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 12px !important;

    line-height: 1.5 !important;
}


/* =========================================================
   CLOSE BUTTON
========================================================= */

#workerSettingsDrawer .settings-close {
    flex: 0 0 auto !important;

    width: 40px !important;
    height: 40px !important;

    padding: 0 !important;

    border: 1px solid #dce6e5 !important;

    border-radius: 12px !important;

    background: #ffffff !important;

    color: #17252b !important;

    font-size: 25px !important;

    line-height: 1 !important;

    display: flex !important;

    align-items: center !important;
    justify-content: center !important;

    cursor: pointer !important;

    box-shadow:
        0 5px 16px rgba(23, 37, 43, 0.06) !important;

    transition:
        transform 0.22s ease,
        background 0.22s ease,
        color 0.22s ease !important;
}


#workerSettingsDrawer .settings-close:hover {
    transform: rotate(90deg) !important;

    background: #d9eff2 !important;

    color: #197487 !important;
}


/* =========================================================
   PROFILE CARD
========================================================= */

#workerSettingsDrawer .settings-profile-card {
    display: flex !important;

    align-items: center !important;

    gap: 14px !important;

    margin: 20px 22px !important;

    padding: 16px !important;

    background: #ffffff !important;

    border: 1px solid #dce6e5 !important;

    border-radius: 18px !important;

    box-shadow:
        0 10px 28px rgba(23, 37, 43, 0.06) !important;

    box-sizing: border-box !important;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease !important;
}


#workerSettingsDrawer .settings-profile-card:hover {
    transform: translateY(-2px) !important;

    box-shadow:
        0 14px 32px rgba(23, 37, 43, 0.09) !important;
}


#workerSettingsDrawer .settings-profile-avatar {
    width: 54px !important;
    height: 54px !important;

    min-width: 54px !important;

    border-radius: 50% !important;

    overflow: hidden !important;

    display: flex !important;

    align-items: center !important;
    justify-content: center !important;

    background: #d9eff2 !important;

    color: #197487 !important;

    font-family: "Outfit", sans-serif !important;

    font-size: 21px !important;

    font-weight: 800 !important;
}


#workerSettingsDrawer .settings-profile-avatar img {
    width: 100% !important;
    height: 100% !important;

    object-fit: cover !important;

    display: block !important;
}


#workerSettingsDrawer .settings-profile-card strong {
    display: block !important;

    color: #17252b !important;

    font-family: "Outfit", sans-serif !important;

    font-size: 15px !important;
}


#workerSettingsDrawer .settings-profile-card span {
    display: block !important;

    margin-top: 3px !important;

    color: #197487 !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 12px !important;

    font-weight: 600 !important;
}


#workerSettingsDrawer .settings-profile-card small {
    display: block !important;

    margin-top: 3px !important;

    color: #66767a !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 11px !important;
}


/* =========================================================
   CONTENT
========================================================= */

#workerSettingsDrawer .settings-drawer-content {
    padding: 0 22px 35px !important;

    box-sizing: border-box !important;
}


#workerSettingsDrawer .settings-block {
    margin: 0 0 24px !important;
}


#workerSettingsDrawer .settings-block-title {
    margin: 0 0 10px !important;

    color: #17252b !important;

    font-family: "Outfit", sans-serif !important;

    font-size: 11px !important;

    font-weight: 800 !important;

    letter-spacing: 0.1em !important;

    text-transform: uppercase !important;
}


/* =========================================================
   SETTING ROWS
========================================================= */

#workerSettingsDrawer .drawer-setting-row {
    display: flex !important;

    align-items: center !important;

    justify-content: space-between !important;

    min-height: 64px !important;

    margin: 0 0 9px !important;

    padding: 14px 15px !important;

    background: #ffffff !important;

    border: 1px solid #dce6e5 !important;

    border-radius: 15px !important;

    box-sizing: border-box !important;

    transition:
        transform 0.2s ease,
        border-color 0.2s ease,
        box-shadow 0.2s ease !important;
}


#workerSettingsDrawer .drawer-setting-row:hover {
    transform: translateX(-2px) !important;

    border-color: #b9dce1 !important;

    box-shadow:
        0 7px 20px rgba(23, 37, 43, 0.06) !important;
}


#workerSettingsDrawer .drawer-setting-row strong {
    display: block !important;

    color: #17252b !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 13px !important;

    font-weight: 700 !important;
}


#workerSettingsDrawer .drawer-setting-row span {
    display: block !important;

    margin-top: 4px !important;

    color: #66767a !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 11px !important;
}


/* =========================================================
   ACTION BUTTON
========================================================= */

#workerSettingsDrawer .drawer-action {
    width: 100% !important;

    min-height: 50px !important;

    padding: 13px 16px !important;

    border: 1px solid #197487 !important;

    border-radius: 14px !important;

    background: #197487 !important;

    color: #ffffff !important;

    font-family: "DM Sans", sans-serif !important;

    font-size: 13px !important;

    font-weight: 700 !important;

    text-align: left !important;

    cursor: pointer !important;

    box-sizing: border-box !important;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease !important;
}


#workerSettingsDrawer .drawer-action:hover {
    transform: translateY(-2px) !important;

    background: #146678 !important;

    box-shadow:
        0 10px 25px rgba(25, 116, 135, 0.2) !important;
}


/* =========================================================
   SCROLLBAR
========================================================= */

#workerSettingsDrawer::-webkit-scrollbar {
    width: 6px !important;
}

#workerSettingsDrawer::-webkit-scrollbar-track {
    background: transparent !important;
}

#workerSettingsDrawer::-webkit-scrollbar-thumb {
    background: #c8d8d9 !important;

    border-radius: 20px !important;
}

#workerSettingsDrawer::-webkit-scrollbar-thumb:hover {
    background: #197487 !important;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    #workerSettingsDrawer {
        width: 94vw !important;
        max-width: 94vw !important;
    }

    #workerSettingsDrawer .settings-drawer-header {
        padding: 20px 18px !important;
    }

    #workerSettingsDrawer .settings-profile-card {
        margin-left: 18px !important;
        margin-right: 18px !important;
    }

    #workerSettingsDrawer .settings-drawer-content {
        padding-left: 18px !important;
        padding-right: 18px !important;
    }
}
</style>


<script>
/* =========================================================
   WORKER SETTINGS DRAWER
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const trigger =
        document.getElementById("workerSettingsTrigger");

    const drawer =
        document.getElementById("workerSettingsDrawer");

    const overlay =
        document.getElementById("workerSettingsOverlay");

    const closeButton =
        document.getElementById("closeWorkerSettings");


    if (!trigger || !drawer || !overlay) {

        console.error(
            "Worker settings elements missing",
            {
                trigger,
                drawer,
                overlay
            }
        );

        return;
    }


    function openDrawer(event) {

        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        console.log("Worker settings opening");


        drawer.classList.add("open");

        overlay.classList.add("open");
        overlay.classList.add("show");


        /*
         * IMPORTANT:
         *
         * We DO NOT change:
         * body background
         * dashboard background
         * dashboard colors
         * body overflow
         *
         * Therefore the website behind the drawer
         * remains exactly as it was.
         */

        drawer.setAttribute(
            "aria-hidden",
            "false"
        );

        overlay.setAttribute(
            "aria-hidden",
            "false"
        );
    }


    function closeDrawer(event) {

        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }


        drawer.classList.remove("open");

        overlay.classList.remove("open");
        overlay.classList.remove("show");


        drawer.setAttribute(
            "aria-hidden",
            "true"
        );

        overlay.setAttribute(
            "aria-hidden",
            "true"
        );


        /*
         * Make absolutely sure no old code
         * has locked the page.
         */

        document.body.style.overflow = "";
        document.documentElement.style.overflow = "";
    }


    trigger.addEventListener(
        "click",
        openDrawer
    );


    if (closeButton) {

        closeButton.addEventListener(
            "click",
            closeDrawer
        );

    }


    overlay.addEventListener(
        "click",
        closeDrawer
    );


    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {
                closeDrawer();
            }

        }
    );


    /*
     * Clean up any old stuck state
     * when the page initially loads.
     */

    document.body.style.overflow = "";
    document.documentElement.style.overflow = "";

});
</script>
 </body>
</html>