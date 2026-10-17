<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

/* =========================================
   ADMIN ACCESS ONLY
========================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "admin"
) {
    header(
        "Location: ../pages/login.html?error=Unauthorized"
    );
    exit;
}

require_once __DIR__ . "/../../BackEnd/db.php";


/* =========================================
   GET SHOWCASE SUBMISSIONS
========================================= */

$showcases = [];

try {

    $showcaseCollection =
        $database->selectCollection("showcases");

    $cursor =
        $showcaseCollection->find(
            [],
            [
                "sort" => [
                    "created_at" => -1
                ]
            ]
        );

    foreach ($cursor as $showcase) {

        $showcases[] = $showcase;
    }

} catch (Throwable $e) {

    $showcaseError =
        $e->getMessage();
}


/* =========================================
   HELPER FUNCTIONS
========================================= */

function escapeHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function formatShowcaseDate($date)
{
    if (!$date) {
        return "Unknown date";
    }

    try {

        return $date
            ->toDateTime()
            ->format("d M Y, h:i A");

    } catch (Throwable $e) {

        return "Unknown date";
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

    <title>Showcase | WorkOnnect Admin</title>

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

        :root {

            --ink: #17252b;
            --muted: #66767a;
            --blue: #d9eff2;
            --blue-deep: #197487;
            --yellow: #f4c95d;
            --coral: #f28573;
            --green: #cde5c2;
            --line: #dce6e5;
            --cream: #fafaf5;
            --white: #ffffff;

        }


        * {

            box-sizing: border-box;
            margin: 0;
            padding: 0;

        }


        body {

            font-family: "DM Sans", sans-serif;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(217,239,242,.65),
                    transparent 28%
                ),
                var(--cream);

            color: var(--ink);

            min-height: 100vh;

        }


        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 240px;
            height: 100vh;

            background: var(--white);

            border-right:
                1px solid var(--line);

            padding: 28px 18px;

        }


        .logo {

            font-family: "Outfit", sans-serif;

            font-size: 25px;

            font-weight: 700;

            padding: 0 14px 30px;

        }


        .logo span {

            color: var(--blue-deep);

        }


        .nav {

            display: flex;

            flex-direction: column;

            gap: 8px;

        }


        .nav a {

            text-decoration: none;

            color: var(--muted);

            padding: 13px 15px;

            border-radius: 12px;

            font-weight: 600;

            transition: .25s ease;

        }


        .nav a:hover {

            background: var(--blue);

            color: var(--blue-deep);

            transform: translateX(3px);

        }


        .nav a.active {

            background: var(--blue);

            color: var(--blue-deep);

        }


        /* =========================================
           MAIN
        ========================================= */

        .main {

            margin-left: 240px;

            padding: 35px;

            max-width: 1500px;

        }


        .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 35px;

        }


        .topbar small {

            color: var(--muted);

        }


        .admin-name {

            background: white;

            border: 1px solid var(--line);

            padding: 10px 16px;

            border-radius: 50px;

            font-weight: 600;

        }


        /* =========================================
           HEADING
        ========================================= */

        .heading {

            display: flex;

            justify-content: space-between;

            align-items: flex-end;

            margin-bottom: 25px;

        }


        .heading h1 {

            font-family: "Outfit", sans-serif;

            font-size: 38px;

            letter-spacing: -1px;

        }


        .heading p {

            color: var(--muted);

            margin-top: 6px;

        }


        .count {

            background: var(--ink);

            color: white;

            padding: 12px 20px;

            border-radius: 14px;

            font-weight: 700;

        }


        /* =========================================
           SHOWCASE GRID
        ========================================= */

        .showcase-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(280px, 1fr)
                );

            gap: 22px;

        }


        .showcase-card {

            background: white;

            border: 1px solid var(--line);

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 12px 35px
                rgba(23,37,43,.06);

            transition: .3s ease;

            animation: cardIn .5s ease both;

        }


        .showcase-card:hover {

            transform: translateY(-6px);

            box-shadow:
                0 20px 45px
                rgba(23,37,43,.11);

        }


        @keyframes cardIn {

            from {

                opacity: 0;

                transform:
                    translateY(15px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        /* =========================================
           IMAGE
        ========================================= */

        .showcase-image {

            width: 100%;

            height: 230px;

            background:
                linear-gradient(
                    135deg,
                    #edf8f8,
                    #f7faf7
                );

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

        }


        .showcase-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            transition: .4s ease;

        }


        .showcase-card:hover
        .showcase-image img {

            transform: scale(1.05);

        }


        .no-image {

            text-align: center;

            color: var(--muted);

        }


        .no-image-icon {

            font-size: 42px;

            margin-bottom: 8px;

        }


        /* =========================================
           CONTENT
        ========================================= */

        .showcase-content {

            padding: 20px;

        }


        .showcase-name {

            font-family: "Outfit", sans-serif;

            font-size: 21px;

            font-weight: 700;

            margin-bottom: 5px;

        }


        .instagram {

            color: var(--blue-deep);

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 13px;

        }


        .description {

            color: var(--muted);

            font-size: 14px;

            line-height: 1.6;

            min-height: 45px;

            margin-bottom: 16px;

        }


        /* =========================================
           STATUS
        ========================================= */

       .card-footer {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;

            margin-top: 4px;

        }


        .status {

            padding: 7px 12px;

            border-radius: 50px;

            font-size: 12px;

            font-weight: 700;

            text-transform: capitalize;

        }


        .status.pending {

            background: #fff5d9;

            color: #896d12;

        }


        .status.approved {

            background: #e8f6e2;

            color: #39712e;

        }


        .status.rejected {

            background: #fff0ed;

            color: #a4493a;

        }


        .date {

            color: var(--muted);

            font-size: 11px;

        }


        /* =========================================
           EMPTY
        ========================================= */

        .empty {

            background: white;

            border:
                1px dashed
                var(--line);

            border-radius: 22px;

            padding: 70px 30px;

            text-align: center;

        }


        .empty-icon {

            width: 75px;

            height: 75px;

            margin: 0 auto 18px;

            border-radius: 22px;

            background: var(--blue);

            color: var(--blue-deep);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 32px;

        }


        .empty h2 {

            font-family: "Outfit", sans-serif;

            margin-bottom: 8px;

        }


        .empty p {

            color: var(--muted);

        }


        /* =========================================
           ERROR
        ========================================= */

        .error-box {

            background: #fff0ed;

            border: 1px solid #f2c0b6;

            color: #963c2e;

            padding: 18px;

            border-radius: 14px;

            margin-bottom: 20px;

        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 800px) {

            .sidebar {

                position: static;

                width: 100%;

                height: auto;

            }


            .nav {

                flex-direction: row;

                flex-wrap: wrap;

            }


            .main {

                margin-left: 0;

                padding: 22px;

            }


            .heading {

                align-items: flex-start;

                flex-direction: column;

                gap: 15px;

            }

        }


        @media (max-width: 500px) {

            .topbar {

                align-items: flex-start;

                gap: 15px;

                flex-direction: column;

            }


            .showcase-grid {

                grid-template-columns: 1fr;

            }

        }

        /* =========================================
   SHOWCASE ACTIONS
========================================= */
        /* =========================================
   SHOWCASE ACTIONS
========================================= */

.showcase-actions {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;

    margin-top: 16px;

    padding-top: 14px;

    border-top: 1px solid var(--line);

}


.showcase-actions form {

    margin: 0;

}


.showcase-actions button {

    width: 100%;

    border: none;

    padding: 12px 14px;

    border-radius: 11px;

    font-family: "DM Sans", sans-serif;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    transition: all .25s ease;

}


.details-btn {

    width: 100%;

    margin-top: 14px;

    border: 1px solid var(--line);

    padding: 11px 14px;

    border-radius: 11px;

    background: white;

    color: var(--blue-deep);

    font-family: "DM Sans", sans-serif;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    transition: background .2s ease;

}


.details-btn:hover {

    background: var(--blue);

}


.details-modal {

    position: fixed;

    inset: 0;

    z-index: 10;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: rgba(23,37,43,.58);

}


.details-modal[hidden] {

    display: none;

}


.details-panel {

    position: relative;

    width: min(620px, 100%);

    max-height: 90vh;

    overflow-y: auto;

    padding: 26px;

    border-radius: 16px;

    background: white;

    box-shadow: 0 24px 70px rgba(0,0,0,.22);

}


.details-close {

    position: absolute;

    top: 12px;

    right: 12px;

    width: 38px;

    height: 38px;

    border: 1px solid var(--line);

    border-radius: 50%;

    background: white;

    color: var(--ink);

    font-size: 22px;

    cursor: pointer;

}


.details-image {

    width: 100%;

    max-height: 360px;

    margin-bottom: 20px;

    border-radius: 10px;

    object-fit: contain;

    background: #f4f7f6;

}


.details-panel h2 {

    padding-right: 38px;

    font-family: "Outfit", sans-serif;

    font-size: 26px;

}


.details-instagram {

    margin: 6px 0 16px;

    color: var(--blue-deep);

    font-weight: 600;

}


.details-description {

    color: var(--muted);

    line-height: 1.7;

    white-space: pre-wrap;

    overflow-wrap: anywhere;

}


/* APPROVE */

.approve-btn {

    background: #e8f6e2;

    color: #39712e;

}


.approve-btn:hover {

    background: #cde5c2;

    transform: translateY(-2px);

    box-shadow:
        0 7px 16px rgba(57,113,46,.12);

}


/* REJECT */

.reject-btn {

    background: #fff0ed;

    color: #a4493a;

}


.reject-btn:hover {

    background: #f28573;

    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 7px 16px rgba(242,133,115,.18);

}

    </style>

</head>


<body>


    <!-- =========================================
         SIDEBAR
    ========================================== -->

    <aside class="sidebar">

        <div class="logo">

            Work<span>Onnect</span>

        </div>


        <nav class="nav">

            <a href="dash_board.php">
                Dashboard
            </a>

            <a href="users.php">
                Users
            </a>

            <a
                href="showcase.php"
                class="active"
            >
                Showcase
            </a>

            <a href="#">
                Settings
            </a>

        </nav>

    </aside>


    <!-- =========================================
         MAIN
    ========================================== -->

    <main class="main">


        <div class="topbar">

            <small>
                Admin Panel / Showcase
            </small>

            <div class="admin-name">

                <?= escapeHtml(
                    $_SESSION["user_name"] ?? "Admin"
                ) ?>

            </div>

        </div>


        <!-- HEADING -->

        <section class="heading">

            <div>

                <h1>
                    Showcase Submissions
                </h1>

                <p>
                    Review and manage community showcase submissions.
                </p>

            </div>


            <div class="count">

                <?= count($showcases) ?>

                <?= count($showcases) === 1
                    ? "Submission"
                    : "Submissions"
                ?>

            </div>

        </section>


        <?php if (isset($showcaseError)): ?>

            <div class="error-box">

                Unable to load showcase submissions:

                <?= escapeHtml($showcaseError) ?>

            </div>

        <?php endif; ?>


        <?php if (empty($showcases)): ?>


            <!-- EMPTY STATE -->

            <section class="empty">

                <div class="empty-icon">
                    ✦
                </div>

                <h2>
                    No submissions yet
                </h2>

                <p>
                    Showcase submissions will appear here
                    when users submit their work.
                </p>

            </section>


        <?php else: ?>


            <!-- SHOWCASE CARDS -->

            <section class="showcase-grid">


                <?php foreach ($showcases as $showcase): ?>


                    <?php

                    $name =
                        $showcase["name"] ?? "Unknown User";

                    $instagram =
                        $showcase["instagram"] ?? "";

                    $description =
                        $showcase["description"] ?? "";

                    $status =
                        strtolower(
                            $showcase["status"] ?? "pending"
                        );

                    $image =
                        $showcase["image"] ?? "";

                    ?>


                    <article class="showcase-card">


                        <!-- IMAGE -->

                        <div class="showcase-image">


                            <?php if ($image !== ""): ?>

                                <img
                                    src="../../<?= escapeHtml($image) ?>"
                                    alt="<?= escapeHtml($name) ?> showcase"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="no-image">

                                    <div class="no-image-icon">
                                        🖼️
                                    </div>

                                    <div>
                                        No photo uploaded
                                    </div>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- CONTENT -->

                        <div class="showcase-content">


                            <div class="showcase-name">

                                <?= escapeHtml($name) ?>

                            </div>


                            <div class="instagram">

                                <?= escapeHtml($instagram) ?>

                            </div>


                            <div class="description">

                                <?= escapeHtml(
                                    $description !== ""
                                        ? $description
                                        : "No description provided."
                                ) ?>

                            </div>

                            <div class="card-footer">

    <span
        class="status <?= escapeHtml($status) ?>"
    >
        <?= escapeHtml($status) ?>
    </span>

    <span class="date">

        <?= formatShowcaseDate(
            $showcase["created_at"] ?? null
        ) ?>

    </span>

</div>


<button
    type="button"
    class="details-btn"
    data-name="<?= escapeHtml($name) ?>"
    data-instagram="<?= escapeHtml($instagram !== "" ? $instagram : "No Instagram provided.") ?>"
    data-description="<?= escapeHtml($description !== "" ? $description : "No description provided.") ?>"
    data-image="<?= escapeHtml($image) ?>"
>
    Details
</button>


<?php if ($status === "pending"): ?>

    <div class="showcase-actions">

        <form
            action="../../BackEnd/update_showcase_status.php"
            method="POST"
        >

            <input
                type="hidden"
                name="showcase_id"
                value="<?= escapeHtml(
                    (string) $showcase["_id"]
                ) ?>"
            >

            <input
                type="hidden"
                name="status"
                value="approved"
            >

            <button
                type="submit"
                class="approve-btn"
            >
                ✓ Approve
            </button>

        </form>


        <form
            action="../../BackEnd/update_showcase_status.php"
            method="POST"
        >

            <input
                type="hidden"
                name="showcase_id"
                value="<?= escapeHtml(
                    (string) $showcase["_id"]
                ) ?>"
            >

            <input
                type="hidden"
                name="status"
                value="rejected"
            >

            <button
                type="submit"
                class="reject-btn"
            >
                × Reject
            </button>

        </form>

    </div>

    <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

    </section>

    <?php endif; ?>


    </main>


    <div
        class="details-modal"
        id="showcase-details-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="details-name"
        hidden
    >
        <section class="details-panel">
            <button
                type="button"
                class="details-close"
                aria-label="Close details"
            >&times;</button>
            <img class="details-image" alt="" hidden>
            <h2 id="details-name"></h2>
            <p class="details-instagram"></p>
            <p class="details-description"></p>
        </section>
    </div>

    <script>
        const detailsModal = document.getElementById("showcase-details-modal");
        const detailsClose = detailsModal.querySelector(".details-close");
        const detailsImage = detailsModal.querySelector(".details-image");
        let detailsTrigger = null;

        document.querySelectorAll(".details-btn").forEach((button) => {
            button.addEventListener("click", () => {
                detailsTrigger = button;
                detailsModal.querySelector("#details-name").textContent = button.dataset.name;
                detailsModal.querySelector(".details-instagram").textContent = button.dataset.instagram;
                detailsModal.querySelector(".details-description").textContent = button.dataset.description;

                if (button.dataset.image) {
                    detailsImage.src = `../../${button.dataset.image}`;
                    detailsImage.alt = `${button.dataset.name} showcase`;
                    detailsImage.hidden = false;
                } else {
                    detailsImage.removeAttribute("src");
                    detailsImage.alt = "";
                    detailsImage.hidden = true;
                }

                detailsModal.hidden = false;
                detailsClose.focus();
            });
        });

        function closeDetails() {
            detailsModal.hidden = true;
            detailsTrigger?.focus();
        }

        detailsClose.addEventListener("click", closeDetails);
        detailsModal.addEventListener("click", (event) => {
            if (event.target === detailsModal) closeDetails();
        });
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && !detailsModal.hidden) closeDetails();
        });
    </script>

</body>

</html>