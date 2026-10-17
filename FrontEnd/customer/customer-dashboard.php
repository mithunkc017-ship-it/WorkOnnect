<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/* Only customers can access this page */
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "customer"
) {
    header(
        "Location: ../pages/login.html?error=Please+login+as+a+customer"
    );
    exit;
}

require_once __DIR__ . "/../../BackEnd/db.php";

/** @var MongoDB\Collection $users */
try {

    $userId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );

    $user = $users->findOne([
        "_id" => $userId
    ]);

    if (!$user) {
        session_destroy();

        header(
            "Location: ../pages/login.html?error=User+not+found"
        );
        exit;
    }

    $customerName =
        $user["name"] ?? "Customer";

    $email =
        $user["email"] ?? "";

    $customerProfile =
        $user["customer_profile"] ?? [];

    $location =
        $customerProfile["location"] ?? "Not added";

    $requiredService =
        $customerProfile["required_service"] ?? "Not selected";

} catch (Throwable $e) {

    die(
        "Unable to load customer dashboard: " .
        htmlspecialchars($e->getMessage())
    );
}

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
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

    <meta
        name="description"
        content="WorkOnnect customer dashboard."
    >

    <title>Customer Dashboard | WorkOnnect</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/css/customer-dashboard.css?v=5">

</head>

<body>

<div class="dashboard-page">

    <!-- ================= NAVBAR ================= -->

    <header class="dashboard-nav">

        <div class="nav-logo">

            <div class="logo-mark">
                W
            </div>

            <div class="logo-text">
                Work<span>Onnect</span>
            </div>

        </div>


        <nav class="nav-links">

            <a
                href="#home"
                class="active"
            >
                Home
            </a>

            <a href="#services">
                Services
            </a>

            <a href="#requests">
                My Requests
            </a>

            <a href="#saved">
                Saved
            </a>

        </nav>


        <div class="nav-actions">

            <!-- <button
                class="notification-btn"
                type="button"
                aria-label="Notifications"
            >
                <span>♡</span>
            </button> -->
            <div class="notification-wrapper">

    <button
        class="notification-btn"
        id="customerNotificationBtn"
        type="button"
        aria-label="Notifications"
    >
        <span class="notification-bell">🔔
                <!-- <button
                    class="notification-btn"
                    id="customerNotificationBtn"
                    type="button"
                    aria-label="Notifications"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                        />

                        <path
                            d="M13.73 21a2 2 0 0 1-3.46 0"
                        />

                    </svg>

                </button> -->
        </span>


         

        <span
            class="notification-badge"
            id="customerNotificationBadge"
        >
            0
        </span>
    </button>


    <div
        class="notification-dropdown"
        id="customerNotificationDropdown"
    >

        <div class="notification-header">

            <div>
                <span>WORKONNECT</span>
                <h3>Notifications</h3>
            </div>

            <span
                class="notification-unread"
                id="customerNotificationUnread"
            >
                0 unread
            </span>

        </div>


        <div
            class="notification-list"
            id="customerNotificationList"
        >

            <div class="notification-loading">
                Loading notifications...
            </div>

        </div>

    </div>

</div>


            <!-- <div class="profile-mini">

                <div class="profile-avatar">
                    <?= e(strtoupper(substr($customerName, 0, 1))) ?>
                </div>

                <div class="profile-name">
                    <?= e($customerName) ?>
                </div>

            </div> -->
            <a
                href="customer-profile.php"
                class="profile-mini"
                id="customerProfileButton"
            >

                <div class="profile-avatar">
                    <?= e(strtoupper(substr($customerName, 0, 1))) ?>
                </div>

                <div class="profile-name">
                    <?= e($customerName) ?>
                </div>

            </a>

            <a
                href="../../BackEnd/logout.php"
                class="logout-btn"
            >
                Logout
            </a>

        </div>

    </header>


    <!-- ================= MAIN ================= -->

    <main>

        <!-- HERO -->

        <section
            class="welcome-section"
            id="home"
        >

            <div class="welcome-content">

                <div class="welcome-label">
                    CUSTOMER DASHBOARD
                </div>

                <h1>
                    Find the right
                    <span>person</span>
                    for the job.
                </h1>

                <p>
                    Welcome back,
                    <strong><?= e($customerName) ?></strong>.
                    Tell us what you need and discover
                    skilled people ready to help.
                </p>


                <!-- SEARCH -->
                 <div class="worker-search">

                    <div class="search-icon">
                        ⌕
                    </div>

                    <input
                        type="text"
                        id="workerSearchInput"
                        placeholder="What service do you need?"
                    >

                    <button
                        type="button"
                        id="findWorkersBtn"
                    >
                        Find Workers
                        <span>→</span>
                    </button>

</div>
<div
    id="workerResults"
    class="worker-results"
></div>

                <!-- <div class="worker-search">

                    <div class="search-icon">
                        ⌕
                    </div>

                    <input
                        type="text"
                        placeholder="What service do you need?"
                    >

                    <button type="button">
                        Find Workers
                        <span>→</span>
                    </button>

                </div> -->


                <div class="search-hint">
                    Search for cleaning, plumbing, tutoring,
                    electrical work and more.
                </div>

            </div>


            <!-- HERO VISUAL -->

            <div class="welcome-visual">

                <div class="visual-orbit orbit-one"></div>
                <div class="visual-orbit orbit-two"></div>

                <div class="visual-card main-card">

                    <div class="visual-icon">
                        ✦
                    </div>

                    <div>

                        <strong>
                            Skilled people
                        </strong>

                        <span>
                            Ready to help
                        </span>

                    </div>

                </div>


                <div class="floating-card card-top">

                    <span class="floating-icon">
                        ✓
                    </span>

                    <div>
                        <strong>
                            Trusted
                        </strong>

                        <small>
                            Local workers
                        </small>
                    </div>

                </div>


                <div class="floating-card card-bottom">

                    <span class="floating-icon">
                        ↗
                    </span>

                    <div>
                        <strong>
                            Easy to connect
                        </strong>

                        <small>
                            Direct communication
                        </small>
                    </div>

                </div>

            </div>

        </section>


        <!-- QUICK INFO -->

        <section class="quick-section">

            <div class="quick-card">

                <div class="quick-icon">
                    ◎
                </div>

                <div>
                    <span>Your location</span>
                    <strong><?= e($location) ?></strong>
                </div>

            </div>


            <div class="quick-card">

                <div class="quick-icon">
                    ✦
                </div>

                <div>
                    <span>Looking for</span>
                    <strong><?= e($requiredService) ?></strong>
                </div>

            </div>


            <div class="quick-card">

                <div class="quick-icon">
                    +
                </div>

                <div>
                    <span>Need something else?</span>
                    <strong>Post a request</strong>
                </div>

            </div>

        </section>


        <!-- SERVICES -->

        <section
            class="services-section"
            id="services"
        >

            <div class="section-heading">

                <div>

                    <span>
                        EXPLORE SERVICES
                    </span>

                    <h2>
                        What can we help you with?
                    </h2>

                </div>

                <p>
                    Choose a service and find
                    suitable workers.
                </p>

            </div>


            <div class="service-grid">

                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ✦
                    </div>

                    <h3>
                        Cleaning
                    </h3>

                    <p>
                        Home and workspace cleaning
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ⚙
                    </div>

                    <h3>
                        Plumbing
                    </h3>

                    <p>
                        Repairs and installations
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ◈
                    </div>

                    <h3>
                        Electrical
                    </h3>

                    <p>
                        Electrical repairs and work
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ◆
                    </div>

                    <h3>
                        Painting
                    </h3>

                    <p>
                        Interior and exterior painting
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ✎
                    </div>

                    <h3>
                        Tutoring
                    </h3>

                    <p>
                        Learn from skilled tutors
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ❋
                    </div>

                    <h3>
                        Gardening
                    </h3>

                    <p>
                        Garden maintenance and care
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card" type="button">

                    <div class="service-symbol">
                        ⚒
                    </div>

                    <h3>
                        Repair
                    </h3>

                    <p>
                        General repair and maintenance
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>


                <button class="service-card other-card" type="button">

                    <div class="service-symbol">
                        +
                    </div>

                    <h3>
                        Other
                    </h3>

                    <p>
                        Find another type of service
                    </p>

                    <span class="service-arrow">
                        →
                    </span>

                </button>

            </div>

        </section>


        <!-- REQUESTS -->

        <section class="customer-requests-section" id="requests">

    <div class="customer-requests-heading">

        <div>
            <span>YOUR ACTIVITY</span>

            <h2>My Service Requests</h2>

            <p>
                Track the services you've requested from workers.
            </p>
        </div>

        <div
            class="customer-request-count"
            id="customerRequestCount"
        >
            0 Requests
        </div>

    </div>


    <div
        id="customerRequests"
        class="customer-requests-grid"
    >

        <div class="customer-requests-loading">
            Loading your requests...
        </div>

    </div>

</section>

        <!-- SAVED -->

        <section
            class="saved-section"
            id="saved"
        >

            <div class="saved-box">

                <div class="saved-content">

                    <span>
                        SAVED WORKERS
                    </span>

                    <h2>
                        Your trusted people,
                        all in one place.
                    </h2>

                    <p>
                        Save workers you like and
                        quickly find them again when
                        you need their services.
                    </p>

                </div>

                <div class="saved-visual">
                    ♡
                </div>

            </div>

        </section>

        
    </main>


    <!-- FOOTER -->

    <footer class="dashboard-footer">

        <div class="footer-logo">
            Work<span>Onnect</span>
        </div>

        <p>
            Connecting people with skills that matter.
        </p>

        <span>
            © <?= date("Y") ?> WorkOnnect
        </span>

    </footer>


<!-- =====================================================
     FEEDBACK MODAL
===================================================== -->

<div
    class="feedback-modal"
    id="feedbackModal"
>

    <div class="feedback-modal-box">

        <div class="feedback-modal-header">

            <div>

                <span class="feedback-modal-label">
                    WORKONNECT
                </span>

                <h3>
                    Share your experience
                </h3>

            </div>


            <button
                type="button"
                class="feedback-close-btn"
                id="feedbackCloseBtn"
                aria-label="Close feedback"
            >
                ×
            </button>

        </div>


        <div class="feedback-service-info">

            <span>
                SERVICE
            </span>

            <strong id="feedbackServiceName">
                Service
            </strong>

            <span style="margin-top: 10px;">
                WORKER
            </span>

            <strong id="feedbackWorkerName">
                Worker
            </strong>

        </div>


        <!-- RATING -->

        <div class="feedback-rating">

            <span class="feedback-rating-label">
                How was your experience?
            </span>


            <div
                class="feedback-stars"
                id="feedbackStars"
            >

                <button
                    type="button"
                    class="feedback-star"
                    data-rating="1"
                >
                    ★
                </button>

                <button
                    type="button"
                    class="feedback-star"
                    data-rating="2"
                >
                    ★
                </button>

                <button
                    type="button"
                    class="feedback-star"
                    data-rating="3"
                >
                    ★
                </button>

                <button
                    type="button"
                    class="feedback-star"
                    data-rating="4"
                >
                    ★
                </button>

                <button
                    type="button"
                    class="feedback-star"
                    data-rating="5"
                >
                    ★
                </button>

            </div>

        </div>


        <!-- COMMENT -->

        <div class="feedback-comment">

            <label for="feedbackComment">
                Your feedback
            </label>

            <textarea
                id="feedbackComment"
                maxlength="1000"
                placeholder="Tell us about your experience..."
            ></textarea>

        </div>


        <button
            type="button"
            class="feedback-submit-btn"
            id="feedbackSubmitBtn"
        >
            Submit Feedback
        </button>

    </div>

</div>


</div>
<script>

/* =========================================================
   CUSTOMER DASHBOARD
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* -----------------------------------------------------
       WORKER SEARCH
    ----------------------------------------------------- */

    const searchInput =
        document.getElementById("workerSearchInput");

    const searchButton =
        document.getElementById("findWorkersBtn");

    const workerResults =
        document.getElementById("workerResults");


    if (
        searchInput &&
        searchButton &&
        workerResults
    ) {

        async function searchWorkers() {

            const search =
                searchInput.value.trim();


            if (search === "") {

                workerResults.innerHTML = `
                    <div class="search-message">

                        <div class="message-icon">
                            ⌕
                        </div>

                        <h3>
                            Search for a service
                        </h3>

                        <p>
                            Enter something like Painting,
                            Cleaning, Plumbing or Tutoring.
                        </p>

                    </div>
                `;

                return;
            }


            workerResults.innerHTML = `
                <div class="search-loading">
                    Finding skilled workers...
                </div>
            `;


            try {

                const response =
                    await fetch(
                        "../../BackEnd/search_workers.php?search="
                        + encodeURIComponent(search),
                        {
                            method: "GET",
                            credentials: "same-origin",
                            cache: "no-store"
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        "Server returned HTTP " +
                        response.status
                    );

                }


                const data =
                    await response.json();


                console.log(
                    "Worker search:",
                    data
                );


                if (!data.success) {

                    throw new Error(
                        data.message ||
                        "Unable to search workers."
                    );

                }


                if (
                    !data.workers ||
                    data.workers.length === 0
                ) {

                    workerResults.innerHTML = `

                        <div class="search-message">

                            <div class="message-icon">
                                ⌕
                            </div>

                            <h3>
                                No workers found
                            </h3>

                            <p>
                                We couldn't find a worker
                                matching "${escapeHtml(search)}".
                            </p>

                        </div>

                    `;

                    return;
                }


                workerResults.innerHTML = `

                    <div class="results-header">

                        <div>

                            <span>
                                WORKERS FOUND
                            </span>

                            <h3>
                                ${data.workers.length}
                                ${data.workers.length === 1
                                    ? "worker"
                                    : "workers"}
                            </h3>

                        </div>

                    </div>


                    <div class="worker-grid">

                        ${data.workers
                            .map(createWorkerCard)
                            .join("")}

                    </div>

                `;

            } catch (error) {

                console.error(
                    "SEARCH ERROR:",
                    error
                );

                workerResults.innerHTML = `

                    <div class="search-message">

                        <div class="message-icon">
                            !
                        </div>

                        <h3>
                            Unable to search
                        </h3>

                        <p>
                            ${escapeHtml(error.message)}
                        </p>

                    </div>

                `;

            }

        }


        searchButton.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                searchWorkers();

            }
        );


        searchInput.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Enter") {

                    event.preventDefault();

                    searchWorkers();

                }

            }
        );

    /* -----------------------------------------------------
   VIEW WORKER PROFILE
----------------------------------------------------- */

workerResults.addEventListener(
    "click",
    function (event) {

        const button =
            event.target.closest(
                ".view-profile-btn"
            );

        if (!button) {
            return;
        }

        event.preventDefault();

        const workerId =
            button.dataset.workerId;

        console.log(
            "WORKER ID FROM BUTTON:",
            workerId
        );

        if (
            !workerId ||
            workerId === "undefined" ||
            workerId === "null"
        ) {

            alert(
                "Worker ID is missing."
            );

            console.error(
                "Worker ID missing:",
                workerId
            );

            return;
        }

        window.location.href =
            "/WorkOnnect/FrontEnd/customer/worker-profile-view.php?id=" +
            encodeURIComponent(workerId);

    }
);

    }


    /* -----------------------------------------------------
       CUSTOMER REQUESTS
    ----------------------------------------------------- */

    loadCustomerRequests();

});


/* =========================================================
   LOAD CUSTOMER REQUESTS
========================================================= */

async function loadCustomerRequests() {

    const container =
        document.getElementById(
            "customerRequests"
        );

    const count =
        document.getElementById(
            "customerRequestCount"
        );


    console.log(
        "Loading customer requests..."
    );


    if (!container) {

        console.error(
            "customerRequests element NOT FOUND."
        );

        return;
    }


    if (!count) {

        console.error(
            "customerRequestCount element NOT FOUND."
        );

        return;
    }


    container.innerHTML = `

        <div class="customer-requests-loading">
            Loading your requests...
        </div>

    `;


    try {

        const response =
            await fetch(
                "../../BackEnd/get_customer_requests.php",
                {
                    method: "GET",
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );


        console.log(
            "Customer request API:",
            response.status
        );


        if (!response.ok) {

            throw new Error(
                "Server returned HTTP " +
                response.status
            );

        }


        const data =
            await response.json();


        console.log(
            "Customer request data:",
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

                <div class="customer-empty-requests">

                    <div class="customer-empty-icon">
                        ✦
                    </div>

                    <h3>
                        No service requests yet
                    </h3>

                    <p>
                        When you contact a worker,
                        your requests will appear here.
                    </p>

                </div>

            `;

            return;
        }


        container.innerHTML =
            data.requests
                .map(
                    createCustomerRequestCard
                )
                .join("");


    } catch (error) {

        console.error(
            "CUSTOMER REQUEST ERROR:",
            error
        );


        container.innerHTML = `

            <div class="customer-empty-requests">

                <div class="customer-empty-icon">
                    !
                </div>

                <h3>
                    Unable to load requests
                </h3>

                <p>
                    ${escapeCustomerHtml(
                        error.message
                    )}
                </p>

            </div>

        `;

    }

}


/* =========================================================
   CREATE CUSTOMER REQUEST CARD
========================================================= */

function createCustomerRequestCard(request) {

    const status =
        request.status || "pending";


    /* =====================================================
       STATUS TEXT
    ===================================================== */

    let statusText = "Pending";

    if (status === "accepted") {
        statusText = "Accepted";
    }

    if (status === "rejected") {
        statusText = "Rejected";
    }

    if (status === "completed") {
        statusText = "Completed";
    }


    /* =====================================================
       STATUS ICON
    ===================================================== */

    let statusIcon = "";

    if (status === "accepted") {
        statusIcon = "✓ ";
    }

    if (status === "rejected") {
        statusIcon = "× ";
    }

    if (status === "completed") {
        statusIcon = "✓ ";
    }


    /* =====================================================
       FEEDBACK AREA
    ===================================================== */

    let feedbackArea = "";


    if (status === "completed") {

        if (request.feedback_submitted === true) {

            feedbackArea = `

                <div class="feedback-submitted">

                    ✓ Feedback Submitted

                </div>

            `;

        } else {

            feedbackArea = `

                <button
                    type="button"
                    class="give-feedback-btn"
                    data-request-id="${escapeCustomerHtml(
                        request.id || ""
                    )}"
                    data-worker-name="${escapeCustomerHtml(
                        request.worker_name || "Worker"
                    )}"
                    data-service="${escapeCustomerHtml(
                        request.service || "Service"
                    )}"
                >

                    ★ Give Feedback

                </button>

            `;

        }

    }


    /* =====================================================
       REQUEST CARD
    ===================================================== */

    return `

        <article class="customer-request-card">

            <div class="customer-request-top">

                <div class="worker-request-avatar">

                    ${escapeCustomerHtml(
                        (
                            request.worker_name ||
                            "W"
                        )
                        .charAt(0)
                        .toUpperCase()
                    )}

                </div>


                <span
                    class="customer-request-status ${escapeCustomerHtml(
                        status
                    )}"
                >

                    ${statusIcon}${statusText}

                </span>

            </div>


            <div class="customer-request-worker">

                <span>
                    WORKER
                </span>

                <h3>
                    ${escapeCustomerHtml(
                        request.worker_name ||
                        "Worker"
                    )}
                </h3>

            </div>


            <div class="customer-request-service">

                <span>
                    SERVICE
                </span>

                <strong>
                    ${escapeCustomerHtml(
                        request.service ||
                        "Service"
                    )}
                </strong>

            </div>


            <div class="customer-request-message">

                <span>
                    YOUR MESSAGE
                </span>

                <p>
                    ${escapeCustomerHtml(
                        request.message ||
                        "No message"
                    )}
                </p>

            </div>


            <div class="customer-request-bottom">

                <div>

                    <span>
                        PREFERRED DATE
                    </span>

                    <strong>
                        ${escapeCustomerHtml(
                            request.preferred_date ||
                            "Not specified"
                        )}
                    </strong>

                </div>


                <div class="customer-request-created">

                    ${escapeCustomerHtml(
                        request.created_at || ""
                    )}

                </div>

            </div>


            ${feedbackArea}

        </article>

    `;

}

/* =========================================================
   WORKER CARD
========================================================= */

function createWorkerCard(worker) {

    const name = worker.name || "Worker";

    const firstLetter =
        name.charAt(0).toUpperCase();

    const workerId =
        worker.id ||
        worker._id ||
        "";

    console.log("WORKER CARD:", worker);
    console.log("WORKER ID:", workerId);

    return `

        <article class="worker-card">

            <div class="worker-card-top">

                <div class="worker-avatar">
                    ${escapeHtml(firstLetter)}
                </div>

                <div class="worker-status">
                    Available
                </div>

            </div>


            <h3>
                ${escapeHtml(name)}
            </h3>


            <div class="worker-service">
                ${escapeHtml(
                    worker.service ||
                    "Service"
                )}
            </div>


            <div class="worker-info">

                <div>
                    <span>
                        Location
                    </span>

                    <strong>
                        ${escapeHtml(
                            worker.location ||
                            "Not added"
                        )}
                    </strong>
                </div>


                <div>
                    <span>
                        Experience
                    </span>

                    <strong>
                        ${escapeHtml(
                            worker.experience ||
                            0
                        )}
                        years
                    </strong>
                </div>

            </div>


            <div class="worker-skills">

                ${escapeHtml(
                    worker.skills ||
                    "Skills not added"
                )}

            </div>


            <div class="worker-card-footer">

                <span>
                    ${escapeHtml(
                        worker.availability ||
                        "Not specified"
                    )}
                </span>


                <button
                    type="button"
                    class="view-profile-btn"
                    data-worker-id="${escapeHtml(workerId)}"
                >
                    View Profile
                    <span>→</span>
                </button>

            </div>

        </article>

    `;
}

// function createWorkerCard(worker) {

//     const name =
//         worker.name || "Worker";


//     const firstLetter =
//         name.charAt(0).toUpperCase();


//     return `

//         <article class="worker-card">

//             <div class="worker-card-top">

//                 <div class="worker-avatar">
//                     ${escapeHtml(firstLetter)}
//                 </div>

//                 <div class="worker-status">
//                     Available
//                 </div>

//             </div>


//             <h3>
//                 ${escapeHtml(name)}
//             </h3>


//             <div class="worker-service">
//                 ${escapeHtml(
//                     worker.service ||
//                     "Service"
//                 )}
//             </div>


//             <div class="worker-info">

//                 <div>

//                     <span>
//                         Location
//                     </span>

//                     <strong>
//                         ${escapeHtml(
//                             worker.location ||
//                             "Not added"
//                         )}
//                     </strong>

//                 </div>


//                 <div>

//                     <span>
//                         Experience
//                     </span>

//                     <strong>
//                         ${escapeHtml(
//                             worker.experience ||
//                             0
//                         )}
//                         years
//                     </strong>

//                 </div>

//             </div>


//             <div class="worker-skills">

//                 ${escapeHtml(
//                     worker.skills ||
//                     "Skills not added"
//                 )}

//             </div>


//             <div class="worker-card-footer">

//                 <span>
//                     ${escapeHtml(
//                         worker.availability ||
//                         "Not specified"
//                     )}
//                 </span>


//                  <a
//                 href="/WorkOnnect/FrontEnd/customer/worker-profile-view.php?id=${encodeURIComponent(worker.id || "")}"
//                 class="view-profile-btn"
//             >
//                 View Profile
//                 <span>→</span>
// </a>
//             </div>

//         </article>

//     `;

// }


/* =========================================================
   ESCAPE FUNCTIONS
========================================================= */

function escapeHtml(value) {

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}


function escapeCustomerHtml(value) {

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}
/* =========================================================
   CUSTOMER FEEDBACK
========================================================= */

let selectedFeedbackRating = 0;
let selectedFeedbackRequestId = "";


/* =========================================================
   OPEN FEEDBACK MODAL
========================================================= */

function openFeedbackModal(
    requestId,
    workerName,
    service
) {

    const modal =
        document.getElementById(
            "feedbackModal"
        );

    const workerNameElement =
        document.getElementById(
            "feedbackWorkerName"
        );

    const serviceElement =
        document.getElementById(
            "feedbackServiceName"
        );

    const comment =
        document.getElementById(
            "feedbackComment"
        );


    if (!modal) {
        console.error(
            "Feedback modal not found."
        );
        return;
    }


    selectedFeedbackRequestId =
        requestId;


    selectedFeedbackRating = 0;


    workerNameElement.textContent =
        workerName || "Worker";


    serviceElement.textContent =
        service || "Service";


    comment.value = "";


    resetFeedbackStars();


    modal.classList.add("show");

}


/* =========================================================
   CLOSE FEEDBACK MODAL
========================================================= */

function closeFeedbackModal() {

    const modal =
        document.getElementById(
            "feedbackModal"
        );


    if (modal) {

        modal.classList.remove(
            "show"
        );

    }

}


/* =========================================================
   RESET STARS
========================================================= */

function resetFeedbackStars() {

    document
        .querySelectorAll(".feedback-star")
        .forEach(function (star) {

            star.classList.remove(
                "active"
            );

        });

}


/* =========================================================
   STAR RATING
========================================================= */

document.addEventListener(
    "click",
    function (event) {

        const star =
            event.target.closest(
                ".feedback-star"
            );


        if (!star) {
            return;
        }


        const rating =
            Number(
                star.dataset.rating
            );


        selectedFeedbackRating =
            rating;


        document
            .querySelectorAll(
                ".feedback-star"
            )
            .forEach(function (item) {

                const itemRating =
                    Number(
                        item.dataset.rating
                    );


                item.classList.toggle(
                    "active",
                    itemRating <= rating
                );

            });

    }
);


/* =========================================================
   CLOSE BUTTON
========================================================= */

document.addEventListener(
    "click",
    function (event) {

        if (
            event.target.closest(
                "#feedbackCloseBtn"
            )
        ) {

            closeFeedbackModal();

        }

    }
);


/* =========================================================
   CLICK OUTSIDE MODAL
========================================================= */

document.addEventListener(
    "click",
    function (event) {

        const modal =
            document.getElementById(
                "feedbackModal"
            );


        if (
            modal &&
            event.target === modal
        ) {

            closeFeedbackModal();

        }

    }
);


/* =========================================================
   SUBMIT FEEDBACK
========================================================= */

document.addEventListener(
    "click",
    async function (event) {

        const submitButton =
            event.target.closest(
                "#feedbackSubmitBtn"
            );


        if (!submitButton) {
            return;
        }


        if (!selectedFeedbackRequestId) {

            alert(
                "Request information is missing."
            );

            return;
        }


        if (selectedFeedbackRating < 1) {

            alert(
                "Please select a rating."
            );

            return;
        }


        const commentElement =
            document.getElementById(
                "feedbackComment"
            );


        const comment =
            commentElement
                ? commentElement.value.trim()
                : "";


        const formData =
            new FormData();


        formData.append(
            "request_id",
            selectedFeedbackRequestId
        );


        formData.append(
            "rating",
            selectedFeedbackRating
        );


        formData.append(
            "comment",
            comment
        );


        const originalText =
            submitButton.textContent;


        submitButton.disabled = true;

        submitButton.textContent =
            "Submitting...";


        try {

            const response =
                await fetch(
                    "../../BackEnd/submit_feedback.php",
                    {
                        method: "POST",

                        body: formData,

                        credentials:
                            "same-origin"
                    }
                );


            const data =
                await response.json();


            console.log(
                "Feedback response:",
                data
            );


            if (!data.success) {

                throw new Error(
                    data.message ||
                    "Unable to submit feedback."
                );

            }


            closeFeedbackModal();


            alert(
                "Thank you! Your feedback has been submitted."
            );


            /* Refresh requests */

            loadCustomerRequests();


        } catch (error) {

            console.error(
                "FEEDBACK ERROR:",
                error
            );


            alert(
                error.message ||
                "Unable to submit feedback."
            );


        } finally {

            submitButton.disabled =
                false;

            submitButton.textContent =
                originalText;

        }

    }
);

/* =========================================================
   FEEDBACK BUTTON
========================================================= */

document.addEventListener(
    "click",
    function (event) {

        const feedbackButton =
            event.target.closest(
                ".give-feedback-btn"
            );


        if (!feedbackButton) {
            return;
        }


        const requestId =
            feedbackButton.dataset.requestId;

        const workerName =
            feedbackButton.dataset.workerName;

        const service =
            feedbackButton.dataset.service;


        console.log(
            "Feedback button clicked:",
            {
                requestId,
                workerName,
                service
            }
        );


        openFeedbackModal(
            requestId,
            workerName,
            service
        );

    }
);


/* =========================================================
   CUSTOMER NOTIFICATIONS
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const notificationButton =
            document.getElementById(
                "customerNotificationBtn"
            );

        const notificationDropdown =
            document.getElementById(
                "customerNotificationDropdown"
            );

        const notificationBadge =
            document.getElementById(
                "customerNotificationBadge"
            );

        const notificationList =
            document.getElementById(
                "customerNotificationList"
            );

        const notificationUnread =
            document.getElementById(
                "customerNotificationUnread"
            );


        if (
            !notificationButton ||
            !notificationDropdown ||
            !notificationBadge ||
            !notificationList ||
            !notificationUnread
        ) {

            console.error(
                "Customer notification elements missing."
            );

            return;
        }


        /* -------------------------------------------------
           Load notifications
        ------------------------------------------------- */

        async function loadCustomerNotifications() {

            try {

                const response =
                    await fetch(
                        "../../BackEnd/get_notifications.php",
                        {
                            method: "GET",

                            credentials:
                                "same-origin",

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
                    "Customer notifications:",
                    data
                );


                if (!data.success) {

                    throw new Error(
                        data.message ||
                        "Unable to load notifications."
                    );
                }


                /* -----------------------------------------
                   Update badge
                ----------------------------------------- */

                const unreadCount =
                    Number(
                        data.unread_count || 0
                    );


                if (unreadCount > 0) {

                    notificationBadge.textContent =
                        unreadCount > 99
                            ? "99+"
                            : unreadCount;

                    notificationBadge.classList.add(
                        "show"
                    );

                } else {

                    notificationBadge.classList.remove(
                        "show"
                    );
                }


                notificationUnread.textContent =
                    unreadCount +
                    (
                        unreadCount === 1
                            ? " unread"
                            : " unread"
                    );


                /* -----------------------------------------
                   Empty
                ----------------------------------------- */

                if (
                    !data.notifications ||
                    data.notifications.length === 0
                ) {

                    notificationList.innerHTML = `

                        <div class="notification-empty">

                            <div class="notification-empty-icon">
                                ✓
                            </div>

                            <h4>
                                You're all caught up
                            </h4>

                            <p>
                                New request updates will
                                appear here.
                            </p>

                        </div>

                    `;

                    return;
                }


                /* -----------------------------------------
                   Render notifications
                ----------------------------------------- */

                notificationList.innerHTML =
                    data.notifications
                        .map(
                            createCustomerNotification
                        )
                        .join("");


            } catch (error) {

                console.error(
                    "CUSTOMER NOTIFICATION ERROR:",
                    error
                );


                notificationList.innerHTML = `

                    <div class="notification-empty">

                        <div class="notification-empty-icon">
                            !
                        </div>

                        <h4>
                            Unable to load notifications
                        </h4>

                        <p>
                            ${escapeCustomerHtml(
                                error.message
                            )}
                        </p>

                    </div>

                `;
            }

        }


        /* -------------------------------------------------
           Create notification HTML
        ------------------------------------------------- */

        function createCustomerNotification(
            notification
        ) {

            const isUnread =
                notification.read === false;


            let icon = "🔔";


            if (
                notification.type ===
                "request_accepted"
            ) {

                icon = "✓";

            } else if (
                notification.type ===
                "request_rejected"
            ) {

                icon = "×";
            }


            return `

                <div
                    class="customer-notification
                    ${isUnread ? "unread" : ""}"
                    data-notification-id="${escapeCustomerHtml(
                        notification.id || ""
                    )}"
                >

                    <div class="customer-notification-icon">
                        ${icon}
                    </div>


                    <div class="customer-notification-content">

                        <div class="customer-notification-title">

                            ${escapeCustomerHtml(
                                notification.title ||
                                "Notification"
                            )}

                        </div>


                        <div class="customer-notification-message">

                            ${escapeCustomerHtml(
                                notification.message ||
                                ""
                            )}

                        </div>


                        <span class="customer-notification-time">

                            ${formatNotificationTime(
                                notification.created_at
                            )}

                        </span>

                    </div>

                </div>

            `;
        }


        /* -------------------------------------------------
           Format notification time
        ------------------------------------------------- */

        function formatNotificationTime(
            dateString
        ) {

            if (!dateString) {
                return "";
            }


            const date =
                new Date(
                    dateString.replace(" ", "T")
                );


            if (isNaN(date.getTime())) {
                return dateString;
            }


            const now =
                new Date();


            const difference =
                Math.floor(
                    (now - date) / 1000
                );


            if (difference < 60) {
                return "Just now";
            }


            if (difference < 3600) {

                return (
                    Math.floor(
                        difference / 60
                    ) +
                    " min ago"
                );
            }


            if (difference < 86400) {

                return (
                    Math.floor(
                        difference / 3600
                    ) +
                    " hr ago"
                );
            }


            return date.toLocaleDateString(
                "en-IN",
                {
                    day: "2-digit",
                    month: "short",
                    year: "numeric"
                }
            );

        }


        /* -------------------------------------------------
           Open / close dropdown
        ------------------------------------------------- */

        notificationButton.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();

                notificationDropdown.classList.toggle(
                    "show"
                );

            }
        );


        /* Close when clicking outside */

        document.addEventListener(
            "click",
            function (event) {

                if (
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


        /* -------------------------------------------------
           Mark notification as read
        ------------------------------------------------- */

        notificationList.addEventListener(
            "click",
            async function (event) {

                const notification =
                    event.target.closest(
                        ".customer-notification"
                    );


                if (!notification) {
                    return;
                }


                const notificationId =
                    notification.dataset.notificationId;


                if (!notificationId) {
                    return;
                }


                if (
                    !notification.classList.contains(
                        "unread"
                    )
                ) {
                    return;
                }


                const formData =
                    new FormData();


                formData.append(
                    "notification_id",
                    notificationId
                );


                try {

                    const response =
                        await fetch(
                            "../../BackEnd/mark_notification_read.php",
                            {
                                method: "POST",

                                body: formData,

                                credentials:
                                    "same-origin"
                            }
                        );


                    const data =
                        await response.json();


                    console.log(
                        "Mark notification:",
                        data
                    );


                    if (data.success) {

                        notification.classList.remove(
                            "unread"
                        );


                        loadCustomerNotifications();
                    }


                } catch (error) {

                    console.error(
                        "MARK NOTIFICATION ERROR:",
                        error
                    );

                }

            }
        );


        /* -------------------------------------------------
           Initial load
        ------------------------------------------------- */

        loadCustomerNotifications();


        /* -------------------------------------------------
           Refresh every 15 seconds
        ------------------------------------------------- */
    /* -------------------------------------------------
   AUTO REFRESH NOTIFICATIONS
------------------------------------------------- */

            setInterval(
                loadCustomerNotifications,
                5000
            );

    }
);

</script>
</body>

</html>