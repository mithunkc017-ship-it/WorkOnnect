<?php

session_start();

/* =========================================
   ADMIN ACCESS PROTECTION
========================================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "user") !== "admin"
) {
    header(
        "Location: ../pages/login.html?error=Admin+access+required"
    );
    exit;
}


/* =========================================
   DATABASE
========================================= */

require_once __DIR__ . "/../../BackEnd/db.php";


/* =========================================
   GET USERS
========================================= */

try {

    $allUsers = $users->find(
        [],
        [
            "sort" => [
                "created_at" => -1
            ]
        ]
    );

    $totalUsers = $users->countDocuments();

} catch (Throwable $e) {

    $allUsers = [];
    $totalUsers = 0;

}


/* =========================================
   ADMIN NAME
========================================= */

$adminName =
    $_SESSION["user_name"] ?? "Admin";


/* =========================================
   INITIALS
========================================= */

function getInitials(string $name): string
{
    $name = trim($name);

    if ($name === "") {
        return "U";
    }

    $parts = preg_split(
        '/\s+/',
        $name
    );

    if (count($parts) >= 2) {

        return strtoupper(
            substr($parts[0], 0, 1) .
            substr($parts[1], 0, 1)
        );

    }

    return strtoupper(
        substr($name, 0, 2)
    );
}


/* =========================================
   FORMAT DATE
========================================= */

function formatDate($date): string
{
    if (!$date) {
        return "Unknown";
    }

    try {

        return $date
            ->toDateTime()
            ->format("d M Y");

    } catch (Throwable $e) {

        return "Unknown";

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

    <title>
        Users | WorkOnnect Admin
    </title>


    <!-- ADMIN CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/admin.css?v=3"
    >


    <!-- USERS CSS -->

    <link
        rel="stylesheet"
        href="users.css?v=10"
    >

</head>


<body>


<div class="dashboard">


    <!-- =====================================
         SIDEBAR
    ====================================== -->

    <aside class="sidebar">


        <!-- BRAND -->

        <div class="brand">

            <div class="brand-logo">
                W
            </div>

            <div class="brand-text">
                Work<span>Onnect</span>
            </div>

        </div>


        <!-- NAVIGATION -->

        <nav class="navigation">


            <a
                href="dash_board.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    ⌂
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="users.php"
                class="nav-item active"
            >

                <span class="nav-icon">
                    ♙
                </span>

                <span>
                    Users
                </span>

            </a>


            <a
                href="showcase.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    ▣
                </span>

                <span>
                    Showcase
                </span>

            </a>


            <a
                href="settings.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    ⚙
                </span>

                <span>
                    Settings
                </span>

            </a>


        </nav>


        <!-- SIDEBAR BOTTOM -->

        <div class="sidebar-bottom">

            <a
                href="../../BackEnd/logout.php"
                class="logout"
            >

                <span class="logout-icon">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                        />

                        <path
                            d="M16 17l5-5-5-5"
                        />

                        <path
                            d="M21 12H9"
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



    <!-- =====================================
         MAIN CONTENT
    ====================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <header class="topbar">


            <div>

                <div class="small-title">
                    ADMIN PANEL
                </div>

                <h1>
                    Users
                </h1>

            </div>


            <div class="top-actions">


                <!-- NOTIFICATION -->

                <button
                    class="notification-btn"
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

                </button>


                <!-- ADMIN PROFILE -->

                <div class="admin-profile">


                    <div class="admin-avatar">

                        <?= htmlspecialchars(
                            getInitials($adminName)
                        ) ?>

                    </div>


                    <div class="admin-profile-text">

                        <strong>

                            <?= htmlspecialchars(
                                $adminName
                            ) ?>

                        </strong>

                        <span>
                            Administrator
                        </span>

                    </div>


                </div>


            </div>


        </header>



        <!-- =====================================
             USERS HEADING
        ====================================== -->

        <section class="users-heading">


            <div>

                <span class="users-label">
                    WORKONNECT COMMUNITY
                </span>


                <h2>
                    Registered Users
                </h2>


                <p>
                    Manage and view everyone who has joined WorkOnnect.
                </p>

            </div>


            <div class="users-count">

                <span>
                    Total Users
                </span>


                <strong>
                    <?= $totalUsers ?>
                </strong>

            </div>


        </section>



        <!-- =====================================
             SEARCH + FILTER
        ====================================== -->

        <section class="users-toolbar">


            <!-- SEARCH -->

            <div class="search-box">

                <span>
                    ⌕
                </span>


                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search users by name or email..."
                    autocomplete="off"
                >

            </div>



            <!-- ROLE FILTER -->

            <div class="role-filter">


                <button
                    type="button"
                    class="role-filter-btn active"
                    data-role="all"
                >
                    All Users
                </button>


                <button
                    type="button"
                    class="role-filter-btn"
                    data-role="customer"
                >
                    Customers
                </button>


                <button
                    type="button"
                    class="role-filter-btn"
                    data-role="worker"
                >
                    Workers
                </button>


                <button
                    type="button"
                    class="role-filter-btn"
                    data-role="admin"
                >
                    Admins
                </button>


            </div>


        </section>



        <!-- =====================================
             USERS PANEL
        ====================================== -->

        <section class="users-panel">


            <!-- TABLE HEADER -->

            <div class="users-table-header">


                <span>
                    USER
                </span>


                <span>
                    EMAIL
                </span>


                <span>
                    ROLE
                </span>


                <span>
                    JOINED
                </span>


                <span>
                    ACTION
                </span>


            </div>



            <!-- USERS -->

            <div id="usersList">


                <?php foreach ($allUsers as $user): ?>


                    <?php

                    $name =
                        $user["name"]
                        ?? "Unknown User";

                    $email =
                        $user["email"]
                        ?? "No email";

                    $role =
                        strtolower(
                            trim(
                                $user["role"]
                                ?? "user"
                            )
                        );

                    $userId =
                        (string)
                        $user["_id"];

                    ?>


                    <div
                        class="user-card"
                        data-name="<?= htmlspecialchars(
                            strtolower($name)
                        ) ?>"
                        data-email="<?= htmlspecialchars(
                            strtolower($email)
                        ) ?>"
                        data-role="<?= htmlspecialchars(
                            $role
                        ) ?>"
                    >


                        <!-- USER -->

                        <div class="user-main">


                            <div class="large-user-avatar">

                                <?= htmlspecialchars(
                                    getInitials($name)
                                ) ?>

                            </div>


                            <div>

                                <strong>

                                    <?= htmlspecialchars(
                                        $name
                                    ) ?>

                                </strong>


                                <span>
                                    WorkOnnect Member
                                </span>

                            </div>


                        </div>



                        <!-- EMAIL -->

                        <div class="user-email">

                            <?= htmlspecialchars(
                                $email
                            ) ?>

                        </div>



                        <!-- ROLE -->

                        <div>


                            <span
                                class="role-badge
                                <?=
                                $role === "admin"
                                    ? "admin-role"
                                    : ""
                                ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($role)
                                ) ?>

                            </span>


                        </div>



                        <!-- JOINED + DELETE -->

                        <div class="user-actions">


                            <span class="joined-date">

                                <?= htmlspecialchars(
                                    formatDate(
                                        $user["created_at"]
                                        ?? null
                                    )
                                ) ?>

                            </span>


                            <?php
                            if (
                                $userId !==
                                $_SESSION["user_id"]
                            ):
                            ?>


                        <button
                                type="button"
                                class="delete-user-btn"
                                data-user-id="<?= htmlspecialchars($userId, ENT_QUOTES) ?>"
                                data-user-name="<?= htmlspecialchars($name, ENT_QUOTES) ?>"
                                aria-label="Delete <?= htmlspecialchars($name, ENT_QUOTES) ?>"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v5"/>
                                    <path d="M14 11v5"/>
                                </svg>
                        </button>

                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>



                <?php if ($totalUsers === 0): ?>


                    <div class="no-users">

                        <p>
                            No users found.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>



<!-- =========================================
     DELETE MODAL
========================================= -->

<div
    class="delete-modal"
    id="deleteModal"
    aria-hidden="true"
>


    <div class="delete-modal-card">


        <button
            type="button"
            class="delete-modal-close"
            onclick="closeDeleteModal()"
            aria-label="Close"
        >
            ×
        </button>


        <div class="delete-modal-icon">


            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <path
                    d="M3 6h18"
                />

                <path
                    d="M8 6V4h8v2"
                />

                <path
                    d="M19 6l-1 14H6L5 6"
                />

                <path
                    d="M10 11v5"
                />

                <path
                    d="M14 11v5"
                />

            </svg>


        </div>


        <h3>
            Delete User?
        </h3>


        <p>

            Are you sure you want to delete

            <strong id="deleteUserName">
                this user
            </strong>?

        </p>


        <span class="delete-warning">
            This action cannot be undone.
        </span>


        <form
            action="../../BackEnd/delete_user.php"
            method="POST"
            class="delete-form"
        >


            <input
                type="hidden"
                name="user_id"
                id="deleteUserId"
            >


            <button
                type="button"
                class="cancel-delete-btn"
                onclick="closeDeleteModal()"
            >
                Cancel
            </button>


            <button
                type="submit"
                class="confirm-delete-btn"
            >
                Delete User
            </button>


        </form>


    </div>


</div>



<script>

document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       ELEMENTS
    ========================================= */

    const searchInput =
        document.getElementById("userSearch");

    const userCards =
        document.querySelectorAll(".user-card");

    const filterButtons =
        document.querySelectorAll(".role-filter-btn");

    const deleteModal =
        document.getElementById("deleteModal");

    const deleteUserId =
        document.getElementById("deleteUserId");

    const deleteUserName =
        document.getElementById("deleteUserName");


    let selectedRole = "all";


    /* =========================================
       SEARCH + FILTER
    ========================================= */

    function filterUsers() {

        const searchText = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";


        userCards.forEach(function (card) {

            const name =
                (card.dataset.name || "").toLowerCase();

            const email =
                (card.dataset.email || "").toLowerCase();

            const role =
                (card.dataset.role || "").toLowerCase().trim();


            const searchMatch =
                name.includes(searchText) ||
                email.includes(searchText);


            const roleMatch =
                selectedRole === "all" ||
                role === selectedRole;


            if (searchMatch && roleMatch) {

                card.classList.remove("user-hidden");

            } else {

                card.classList.add("user-hidden");

            }

        });

    }


    /* =========================================
       SEARCH
    ========================================= */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            filterUsers
        );

    }


    /* =========================================
       ROLE FILTER
    ========================================= */

    filterButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                selectedRole =
                    this.dataset.role || "all";


                filterButtons.forEach(
                    function (btn) {

                        btn.classList.remove(
                            "active"
                        );

                    }
                );


                this.classList.add("active");


                filterUsers();

            }
        );

    });


    /* =========================================
       DELETE BUTTONS
    ========================================= */

    document.querySelectorAll(
        ".delete-user-btn"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const userId =
                    this.dataset.userId;

                const userName =
                    this.dataset.userName;


                console.log(
                    "Delete button clicked"
                );

                console.log(
                    "User ID:",
                    userId
                );

                console.log(
                    "User Name:",
                    userName
                );


                if (!userId) {

                    console.error(
                        "User ID is missing"
                    );

                    alert(
                        "Unable to delete this user."
                    );

                    return;
                }


                /* Put ID into hidden input */

                deleteUserId.value =
                    userId;


                /* Put name into modal */

                deleteUserName.textContent =
                    userName;


                /* Show modal */

                deleteModal.classList.add(
                    "show"
                );

                deleteModal.setAttribute(
                    "aria-hidden",
                    "false"
                );

            }
        );

    });


    /* =========================================
       CLOSE MODAL WHEN CLICKING BACKDROP
    ========================================= */

    if (deleteModal) {

        deleteModal.addEventListener(
            "click",
            function (event) {

                if (
                    event.target ===
                    deleteModal
                ) {

                    closeDeleteModal();

                }

            }
        );

    }


    /* =========================================
       INITIAL FILTER
    ========================================= */

    filterUsers();

});


/* =========================================
   CLOSE DELETE MODAL
========================================= */

function closeDeleteModal() {

    const modal =
        document.getElementById(
            "deleteModal"
        );


    if (!modal) return;


    modal.classList.remove("show");

    modal.setAttribute(
        "aria-hidden",
        "true"
    );

}


/* =========================================
   ESC KEY
========================================= */

document.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Escape") {

            closeDeleteModal();

        }

    }
);

</script>

</body>

</html>