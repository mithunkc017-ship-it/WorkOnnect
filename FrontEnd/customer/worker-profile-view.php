<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/../../BackEnd/db.php";

/** @var MongoDB\Collection $users */


/*
|--------------------------------------------------------------------------
| GET WORKER ID
|--------------------------------------------------------------------------
*/

$workerId = trim($_GET["id"] ?? "");

if ($workerId === "") {
    die("Worker ID is missing.");
}


/*
|--------------------------------------------------------------------------
| VALIDATE OBJECT ID
|--------------------------------------------------------------------------
*/

if (!preg_match('/^[a-fA-F0-9]{24}$/', $workerId)) {
    die("Invalid worker ID.");
}


try {

    $objectId = new MongoDB\BSON\ObjectId($workerId);


    /*
    |--------------------------------------------------------------------------
    | FIND WORKER
    |--------------------------------------------------------------------------
    */

    $worker = $users->findOne([
        "_id" => $objectId,
        "role" => "worker"
    ]);


    if (!$worker) {

        die(
            "Worker not found.<br><br>" .
            "ID: " . htmlspecialchars($workerId)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WORKER PROFILE
    |--------------------------------------------------------------------------
    */

    $profile = $worker["worker_profile"] ?? [];


    $workerName =
        $profile["worker_name"]
        ?? $worker["name"]
        ?? "Worker";


    $service =
        $profile["service_category"]
        ?? "Service";


    $skills =
        $profile["skills"]
        ?? "Not added";


    $location =
        $profile["location"]
        ?? "Not added";


    $experience =
        $profile["experience"]
        ?? 0;


    $availability =
        $profile["availability"]
        ?? "Not specified";


    $about =
        $profile["about"]
        ?? "This worker has not added an introduction yet.";


    $contact =
        $profile["contact_number"]
        ?? "Not available";


} catch (MongoDB\Driver\Exception\Exception $e) {

    die(
        "MongoDB error: " .
        htmlspecialchars($e->getMessage())
    );

} catch (Throwable $e) {

    die(
        "Unable to load worker profile: " .
        htmlspecialchars($e->getMessage())
    );
}


/*
|--------------------------------------------------------------------------
| ESCAPE HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


$firstLetter = strtoupper(
    substr($workerName, 0, 1)
);


/*
|--------------------------------------------------------------------------
| CURRENT USER
|--------------------------------------------------------------------------
*/

$isLoggedIn =
    isset($_SESSION["user_id"]);

$userRole =
    $_SESSION["user_role"] ?? "";


/*
|--------------------------------------------------------------------------
| BACK LINK
|--------------------------------------------------------------------------
*/

if ($isLoggedIn && $userRole === "customer") {

    $backLink = "customer-dashboard.php";
    $backText = "← Back to Dashboard";

} else {

    $backLink = "../pages/index.html#workers";
    $backText = "← Back to Workers";

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

    <title>
        <?= e($workerName) ?> | WorkOnnect
    </title>


    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>


    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>

        :root {
            --ink: #17252b;
            --muted: #66767a;
            --blue: #d9eff2;
            --blue-deep: #197487;
            --yellow: #f4c95d;
            --green: #cde5c2;
            --line: #dce6e5;
            --cream: #fafaf5;
        }


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            min-height: 100vh;

            background: var(--cream);

            color: var(--ink);

            font-family: "DM Sans", sans-serif;
        }


        /* ================= NAVBAR ================= */

        .profile-nav {

            height: 76px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            background: rgba(250, 250, 245, 0.92);

            border-bottom: 1px solid var(--line);

            backdrop-filter: blur(12px);

            position: sticky;

            top: 0;

            z-index: 100;
        }


        .brand {

            display: flex;

            align-items: center;

            gap: 10px;

            font-family: "Outfit", sans-serif;

            font-size: 21px;

            font-weight: 800;
        }


        .brand-mark {

            width: 38px;

            height: 38px;

            display: grid;

            place-items: center;

            border-radius: 12px;

            background: var(--ink);

            color: white;

            font-weight: 800;
        }


        .brand span {
            color: var(--blue-deep);
        }


        .back-btn {

            text-decoration: none;

            color: var(--ink);

            font-size: 13px;

            font-weight: 700;

            padding: 10px 15px;

            border: 1px solid var(--line);

            border-radius: 10px;

            background: white;

            transition: .25s ease;
        }


        .back-btn:hover {

            transform: translateX(-3px);

            border-color: var(--blue-deep);

            color: var(--blue-deep);
        }


        /* ================= PAGE ================= */

        .profile-page {

            max-width: 1050px;

            margin: 55px auto;

            padding: 0 25px;

        }


        /* ================= HERO ================= */

        .profile-hero {

            display: grid;

            grid-template-columns: 260px 1fr;

            gap: 35px;

            padding: 38px;

            background: white;

            border: 1px solid var(--line);

            border-radius: 28px;

            box-shadow:
                0 20px 60px rgba(23,37,43,.07);

            animation: appear .5s ease both;
        }


        /* ================= AVATAR ================= */

        .avatar-area {

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .avatar {

            width: 170px;

            height: 170px;

            display: grid;

            place-items: center;

            border-radius: 45px;

            background:
                linear-gradient(
                    135deg,
                    var(--blue),
                    #f1f8f7
                );

            border: 1px solid #cfe3e2;

            color: var(--blue-deep);

            font-family: "Outfit", sans-serif;

            font-size: 62px;

            font-weight: 800;

            box-shadow:
                0 15px 40px rgba(25,116,135,.12);
        }


        /* ================= DETAILS ================= */

        .profile-details {

            padding: 8px 0;
        }


        .profile-label {

            color: var(--blue-deep);

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 2px;
        }


        .profile-details h1 {

            margin-top: 8px;

            font-family: "Outfit", sans-serif;

            font-size: 42px;

            line-height: 1.05;
        }


        .service {

            margin-top: 10px;

            color: var(--blue-deep);

            font-size: 15px;

            font-weight: 700;
        }


        .availability {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            margin-top: 17px;

            padding: 8px 12px;

            border-radius: 30px;

            background: #edf7ea;

            color: #55734d;

            font-size: 11px;

            font-weight: 800;
        }


        .availability::before {

            content: "";

            width: 7px;

            height: 7px;

            border-radius: 50%;

            background: #6f9b62;
        }


        /* ================= INFO ================= */

        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-top: 25px;
        }


        .info-box {

            padding: 17px;

            border-radius: 15px;

            background: #f5f8f7;

            border: 1px solid #e5ecea;
        }


        .info-box span {

            display: block;

            color: var(--muted);

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .7px;

            margin-bottom: 5px;
        }


        .info-box strong {

            font-size: 13px;

            word-break: break-word;
        }


        /* ================= CONTENT ================= */

        .content-grid {

            display: grid;

            grid-template-columns:
                1.3fr .7fr;

            gap: 20px;

            margin-top: 20px;
        }


        .content-card {

            padding: 28px;

            background: white;

            border: 1px solid var(--line);

            border-radius: 22px;

            box-shadow:
                0 10px 35px rgba(23,37,43,.04);

            animation: appear .6s ease both;
        }


        .content-card h2 {

            font-family: "Outfit", sans-serif;

            font-size: 22px;

            margin-bottom: 14px;
        }


        .content-card p {

            color: var(--muted);

            font-size: 14px;

            line-height: 1.8;
        }


        .skills {

            margin-top: 18px;

            padding: 13px;

            border-radius: 12px;

            background: var(--blue);

            color: var(--blue-deep);

            font-size: 13px;

            font-weight: 700;

            line-height: 1.6;
        }


        /* ================= CONTACT ================= */

        .contact-btn {

            width: 100%;

            margin-top: 20px;

            padding: 14px;

            border: none;

            border-radius: 12px;

            background: var(--ink);

            color: white;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

            transition: .25s ease;
        }


        .contact-btn:hover {

            background: var(--blue-deep);

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(25,116,135,.16);
        }


        /* ================= REQUEST MODAL ================= */

        .request-overlay {

            position: fixed;

            inset: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

            background: rgba(23, 37, 43, 0.45);

            backdrop-filter: blur(8px);

            opacity: 0;

            visibility: hidden;

            transition:
                opacity .3s ease,
                visibility .3s ease;

            z-index: 999;
        }


        .request-overlay.show {

            opacity: 1;

            visibility: visible;
        }


        .request-modal {

            width: 100%;

            max-width: 520px;

            position: relative;

            padding: 35px;

            background: white;

            border-radius: 25px;

            box-shadow:
                0 30px 80px rgba(23,37,43,.2);

            transform:
                translateY(20px)
                scale(.97);

            transition:
                transform .3s ease;
        }


        .request-overlay.show .request-modal {

            transform:
                translateY(0)
                scale(1);
        }


        .close-request {

            position: absolute;

            top: 16px;

            right: 16px;

            width: 36px;

            height: 36px;

            border: none;

            border-radius: 50%;

            background: #f1f5f4;

            color: var(--ink);

            font-size: 23px;

            cursor: pointer;

            transition: .25s ease;
        }


        .close-request:hover {

            background: var(--blue);

            color: var(--blue-deep);

            transform: rotate(90deg);
        }


        .request-label {

            color: var(--blue-deep);

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 2px;

            margin-bottom: 8px;
        }


        .request-modal h2 {

            font-family: "Outfit", sans-serif;

            font-size: 30px;

            color: var(--ink);
        }


        .request-modal > p {

            margin-top: 7px;

            color: var(--muted);

            font-size: 13px;
        }


        .request-field {

            margin-top: 20px;
        }


        .request-field label {

            display: block;

            margin-bottom: 7px;

            color: var(--ink);

            font-size: 12px;

            font-weight: 700;
        }


        .request-field input,
        .request-field textarea {

            width: 100%;

            padding: 13px 14px;

            border: 1px solid var(--line);

            border-radius: 11px;

            outline: none;

            background: var(--cream);

            color: var(--ink);

            font: inherit;

            font-size: 13px;

            transition: .25s ease;
        }


        .request-field textarea {

            resize: vertical;
        }


        .request-field input:focus,
        .request-field textarea:focus {

            border-color: var(--blue-deep);

            background: white;

            box-shadow:
                0 0 0 4px rgba(25,116,135,.08);
        }


        .send-request-btn {

            width: 100%;

            margin-top: 25px;

            padding: 14px 18px;

            border: none;

            border-radius: 12px;

            background: var(--ink);

            color: white;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

            transition: .25s ease;
        }


        .send-request-btn:hover {

            background: var(--blue-deep);

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(25,116,135,.2);
        }


        .send-request-btn span {

            margin-left: 8px;
        }


        /* ================= ANIMATION ================= */

        @keyframes appear {

            from {

                opacity: 0;

                transform: translateY(15px);
            }

            to {

                opacity: 1;

                transform: translateY(0);
            }
        }


        /* ================= MOBILE ================= */

        @media (max-width: 750px) {

            .profile-nav {

                padding: 0 20px;
            }


            .profile-hero {

                grid-template-columns: 1fr;

                text-align: center;

                padding: 28px 22px;
            }


            .info-grid {

                grid-template-columns: 1fr;
            }


            .content-grid {

                grid-template-columns: 1fr;
            }


            .profile-details h1 {

                font-size: 34px;
            }


            .request-modal {

                padding: 28px 22px;
            }

        }

    </style>

</head>


<body>


<header class="profile-nav">

    <div class="brand">

        <div class="brand-mark">
            W
        </div>

        Work<span>Onnect</span>

    </div>


    <a
        href="<?= e($backLink) ?>"
        class="back-btn"
    >
        <?= e($backText) ?>
    </a>

</header>


<main class="profile-page">


    <!-- ================= PROFILE HERO ================= -->

    <section class="profile-hero">


        <div class="avatar-area">

            <div class="avatar">
                <?= e($firstLetter) ?>
            </div>

        </div>


        <div class="profile-details">

            <div class="profile-label">
                WORKER PROFILE
            </div>


            <h1>
                <?= e($workerName) ?>
            </h1>


            <div class="service">
                <?= e($service) ?>
            </div>


            <div class="availability">
                <?= e($availability) ?>
            </div>


            <div class="info-grid">


                <div class="info-box">

                    <span>
                        Location
                    </span>

                    <strong>
                        <?= e($location) ?>
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Experience
                    </span>

                    <strong>
                        <?= e($experience) ?>
                        years
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Contact
                    </span>

                    <strong>
                        <?= e($contact) ?>
                    </strong>

                </div>


            </div>

        </div>

    </section>


    <!-- ================= CONTENT ================= -->

    <section class="content-grid">


        <div class="content-card">

            <h2>
                About this worker
            </h2>


            <p>
                <?= e($about) ?>
            </p>


            <div class="skills">

                Skills:
                <?= e($skills) ?>

            </div>

        </div>


        <div class="content-card">

            <h2>
                Need this service?
            </h2>


            <p>
                You can connect with this worker
                and discuss your requirements.
            </p>


            <button
                type="button"
                class="contact-btn"
                onclick="handleContactWorker()"
            >
                Contact Worker
                <span>→</span>
            </button>

        </div>


    </section>


</main>


<!-- ================= REQUEST MODAL ================= -->

<div
    class="request-overlay"
    id="requestOverlay"
>


    <div class="request-modal">


        <button
            type="button"
            class="close-request"
            onclick="closeRequestForm()"
        >
            ×
        </button>


        <div class="request-label">
            SERVICE REQUEST
        </div>


        <h2>
            Contact this worker
        </h2>


        <p>
            Tell the worker what service you need.
        </p>


        <form
            action="../../BackEnd/create_request.php"
            method="POST"
        >


            <input
                type="hidden"
                name="worker_id"
                value="<?= e($workerId) ?>"
            >


            <div class="request-field">

                <label>
                    Service
                </label>

                <input
                    type="text"
                    name="service"
                    value="<?= e($service) ?>"
                    required
                >

            </div>


            <div class="request-field">

                <label>
                    Message
                </label>

                <textarea
                    name="message"
                    rows="5"
                    placeholder="Describe what you need..."
                    required
                ></textarea>

            </div>


            <div class="request-field">

                <label>
                    Preferred Date
                </label>

                <input
                    type="date"
                    name="preferred_date"
                    required
                >

            </div>


            <button
                type="submit"
                class="send-request-btn"
            >
                Send Request
                <span>→</span>
            </button>


        </form>


    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| CONTACT WORKER
|--------------------------------------------------------------------------
|
| Guest:
|   → Login page
|
| Customer:
|   → Existing request modal
|
| Worker/Admin/other:
|   → Message
|--------------------------------------------------------------------------
*/
    

function handleContactWorker() {

    const workerId = <?= json_encode($workerId) ?>;

    <?php if (!$isLoggedIn): ?>

        // Guest user
        window.location.href =
            "connect-worker.php?id=" +
            encodeURIComponent(workerId);

    <?php elseif ($userRole !== "customer"): ?>

        // Logged-in worker/admin
        // No popup — show the login/create-account page
        window.location.href =
            "connect-worker.php?id=" +
            encodeURIComponent(workerId);

    <?php else: ?>

        // Logged-in customer
        // Open the request form normally
        openRequestForm();

    <?php endif; ?>
}
/*
|--------------------------------------------------------------------------
| OPEN REQUEST FORM
|--------------------------------------------------------------------------
*/

function openRequestForm() {

    const overlay =
        document.getElementById("requestOverlay");

    if (!overlay) {
        return;
    }

    overlay.classList.add("show");
}


/*
|--------------------------------------------------------------------------
| CLOSE REQUEST FORM
|--------------------------------------------------------------------------
*/

function closeRequestForm() {

    const overlay =
        document.getElementById("requestOverlay");

    if (!overlay) {
        return;
    }

    overlay.classList.remove("show");
}


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE MODAL
|--------------------------------------------------------------------------
*/

const requestOverlay =
    document.getElementById("requestOverlay");


if (requestOverlay) {

    requestOverlay.addEventListener(
        "click",
        function(event) {

            if (event.target === this) {

                closeRequestForm();

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| ESC KEY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeRequestForm();

        }

    }
);

</script>


</body>

</html>