<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? "user") !== "admin"
) {
    header("Location: ../pages/login.html?error=Admin+access+required");
    exit;
}

require_once __DIR__ . "/../../BackEnd/db.php";

$users = isset($users) ? $users : null;

try {
    $totalUsers = $users?->countDocuments() ?? 0;

    $recentUsers = $users?->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 5
        ]
    ) ?? [];

} catch (Throwable $e) {
    $totalUsers = 0;
    $recentUsers = [];
}

$adminName = $_SESSION["user_name"] ?? "Admin";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard | WorkOnnect</title>

   <link rel="stylesheet" href="../assets/css/admin.css?v=2">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

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

            <a href="dash_board.php" class="nav-item active">

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


            <a href="settings.php" class="nav-item">

                <span class="nav-icon">⚙</span>

                <span>Settings</span>

            </a>

        </nav>


        <div class="sidebar-bottom">

            <!-- <a
                href="../../BackEnd/logout.php"
                class="logout"
            >

                <span>↪</span>

                Logout

            </a> -->
            <a href="../../BackEnd/logout.php" class="logout">

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


    <!-- MAIN CONTENT -->

    <main class="main-content">


        <!-- TOP BAR -->

        <header class="topbar">

             <div>

                <p class="small-title">
                    ADMIN PANEL
                </p>

                <h1>
                    Dashboard
                </h1>

            </div>


            <!-- <div class="admin-profile">

                <div class="profile-avatar">
                    <?= strtoupper(substr($adminName, 0, 1)); ?>
                </div>

                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars($adminName); ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div> -->

            <div class="top-actions">

    <!-- Notification -->
    <!-- ADMIN NOTIFICATION -->
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


    <!-- NOTIFICATION DROPDOWN -->
    <div
        class="admin-notification-dropdown"
        id="adminNotificationDropdown"
    >

        <div class="notification-header">

            <div>
                <strong>Notifications</strong>
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


    <!-- Admin Profile -->
    <div class="admin-profile" id="adminProfile">

        <button class="profile-button" type="button">

            <div class="profile-avatar">
                <?php echo strtoupper(substr($adminName, 0, 1)); ?>

                <span class="online-dot"></span>
            </div>

            <div class="profile-info">
                <strong>
                    <?php echo htmlspecialchars($adminName); ?>
                </strong>

                <span>Administrator</span>
            </div>

            <span class="profile-chevron">
               ⌄
            </span>

        </button>


        <!-- Dropdown -->
        <div class="profile-dropdown">

            <div class="dropdown-header">

                <div class="dropdown-avatar">
                    <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                </div>

                <div>
                    <strong>
                        <?php echo htmlspecialchars($adminName); ?>
                    </strong>

                    <span>Administrator</span>
                </div>

            </div>


            <div class="dropdown-line"></div>


            <a href="#" class="dropdown-item">

                <span class="dropdown-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle
                            cx="12"
                            cy="8"
                            r="3"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                        <path
                            d="M5 20C5.8 16.8 8.1 15 12 15C15.9 15 18.2 16.8 19 20"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>

                <span>My Profile</span>

                <span class="item-arrow">→</span>

            </a>


            <a href="#" class="dropdown-item">

                <span class="dropdown-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                        <path
                            d="M19 13.5V10.5L16.8 9.8C16.5 9.1 16.2 8.6 15.7 8.1L16 5.8L13.2 4.5L11.7 6.2C11 6.1 10.4 6.1 9.7 6.2L8.2 4.5L5.4 5.8L5.7 8.1C5.2 8.6 4.9 9.1 4.6 9.8L2.5 10.5V13.5L4.6 14.2C4.9 14.9 5.2 15.4 5.7 15.9L5.4 18.2L8.2 19.5L9.7 17.8C10.4 17.9 11 17.9 11.7 17.8L13.2 19.5L16 18.2L15.7 15.9C16.2 15.4 16.5 14.9 16.8 14.2L19 13.5Z"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>

                <span>Settings</span>

                <span class="item-arrow">→</span>

            </a>


            <div class="dropdown-line"></div>


            <!-- Logout -->
            <a href="../../BackEnd/logout.php" class="dropdown-logout">

                <span class="logout-small-icon">
                    <svg viewBox="0 0 24 24" fill="none">
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

                <span>Logout</span>

            </a>

        </div>

    </div>

</div>

        </header>


        

        <section class="welcome-card">

            <div>

                <p class="welcome-label">
                    Welcome back 👋
                </p>

                <h2>
                    Good to see you, <?= htmlspecialchars($adminName); ?>!
                </h2>

                <p>
                    Manage your WorkOnnect platform from one place.
                </p>

            </div>


            <div class="welcome-shape">

                <div class="shape-circle"></div>

                <div class="shape-circle small"></div>

            </div>

        </section>
 


        <section class="stats-grid">


            <div class="stat-card">

                <div class="stat-icon users-icon">
                    ♙
                </div>

                <div>

                    <span class="stat-label">
                        Total Users
                    </span>

                    <strong class="stat-number">
                        <?= $totalUsers; ?>
                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon project-icon">
                    ▣
                </div>

                <div>

                    <span class="stat-label">
                        Showcases
                    </span>

                    <strong class="stat-number">
                        0
                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon activity-icon">
                    ↗
                </div>

                <div>

                    <span class="stat-label">
                        Platform Activity
                    </span>

                    <strong class="stat-number">
                        Active
                    </strong>

                </div>

            </div>

        </section> 


   

        <section class="content-grid">


         
            <div class="panel users-panel">

                <div class="panel-header">

                    <div>

                        <p class="panel-label">
                            USER MANAGEMENT
                        </p>

                        <h3>
                            Recent Users
                        </h3>

                    </div>

                    <a href="#" class="view-all">
                        View all →
                    </a>

                </div>


                <div class="users-list">

                    <?php foreach ($recentUsers as $user): ?>

                        <div class="user-row">

                            <div class="user-avatar">

                                <?= strtoupper(
                                    substr(
                                        $user["name"] ?? "U",
                                        0,
                                        1
                                    )
                                ); ?>

                            </div>


                            <div class="user-details">

                                <strong>

                                    <?= htmlspecialchars(
                                        $user["name"] ?? "Unknown User"
                                    ); ?>

                                </strong>

                                <span>

                                    <?= htmlspecialchars(
                                        $user["email"] ?? ""
                                    ); ?>

                                </span>

                            </div>


                            <div class="user-role">

                                <?= htmlspecialchars(
                                    $user["role"] ?? "user"
                                ); ?>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <?php if ($totalUsers == 0): ?>

                        <div class="empty-state">

                            No users registered yet.

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="panel actions-panel">

                <div class="panel-header">

                    <div>

                        <p class="panel-label">
                            QUICK ACCESS
                        </p>

                        <h3>
                            Quick Actions
                        </h3>

                    </div>

                </div>


                <div class="action-list">


                    <a href="#" class="action-card">

                        <div class="action-icon">
                            +
                        </div>

                        <div>

                            <strong>
                                Add User
                            </strong>

                            <span>
                                Create a new account
                            </span>

                        </div>

                        <span class="arrow">
                            →
                        </span>

                    </a>


                    <a href="#" class="action-card">

                        <div class="action-icon">
                            ▣
                        </div>

                        <div>

                            <strong>
                                Manage Showcase
                            </strong>

                            <span>
                                Review student work
                            </span>

                        </div>

                        <span class="arrow">
                            →
                        </span>

                    </a>


                    <a href="settings.php" class="action-card">

                        <div class="action-icon">
                            ⚙
                        </div>

                        <div>

                            <strong>
                                Settings
                            </strong>

                            <span>
                                Configure WorkOnnect
                            </span>

                        </div>

                        <span class="arrow">
                            →
                        </span>

                    </a>

                </div>

            </div>

        </section>
        <section class="pending-workers-section">

    <div class="section-heading">
        <div>
            <span class="section-eyebrow">WORKER MANAGEMENT</span>
            <h2>Pending Workers</h2>
            <p>Review worker profiles before they become available to customers.</p>
        </div>

        <span class="pending-count" id="pendingWorkerCount">0 Pending</span>
    </div>

    <div id="pendingWorkersList" class="pending-workers-list">
        <div class="pending-loading">
            Loading pending workers...
        </div>
    </div>

</section>

    </main>

</div>
<script>

document.addEventListener("DOMContentLoaded", loadPendingWorkers);


async function loadPendingWorkers() {

    const container =
        document.getElementById("pendingWorkersList");

    const countElement =
        document.getElementById("pendingWorkerCount");

    if (!container) return;


    try {

        const response = await fetch(
            "../../BackEnd/get_pending_workers.php",
            {
                method: "GET",
                credentials: "same-origin"
            }
        );


        const data = await response.json();


        if (!data.success) {

            container.innerHTML = `
                <div class="pending-empty">
                    Unable to load pending workers.
                </div>
            `;

            return;
        }


        const workers = data.workers || [];


        if (countElement) {

            countElement.textContent =
                `${workers.length} Pending`;
        }


        if (workers.length === 0) {

            container.innerHTML = `
                <div class="pending-empty">
                    <div class="empty-icon">✓</div>
                    <h3>No Pending Workers</h3>
                    <p>There are no worker profiles waiting for approval.</p>
                </div>
            `;

            return;
        }


        container.innerHTML =
            workers.map(createPendingWorkerCard).join("");


    } catch (error) {

        console.error(
            "Could not load pending workers:",
            error
        );

        container.innerHTML = `
            <div class="pending-empty">
                Unable to connect to the server.
            </div>
        `;
    }
}


function createPendingWorkerCard(worker) {

    return `
        <article class="pending-worker-card">

            <div class="pending-worker-info">

                <div class="pending-worker-avatar">
                    ${escapeHtml(
                        (worker.name || "W").charAt(0).toUpperCase()
                    )}
                </div>

                <div class="pending-worker-details">

                    <h3>
                        ${escapeHtml(worker.name || "Worker")}
                    </h3>

                    <p class="worker-email">
                        ${escapeHtml(worker.email || "")}
                    </p>

                    <div class="worker-meta">

                        <span>
                            ${escapeHtml(
                                worker.service_category || "Service not specified"
                            )}
                        </span>

                        <span>
                            ${escapeHtml(
                                worker.location || "Location not specified"
                            )}
                        </span>

                        <span>
                            ${escapeHtml(
                                worker.experience || "0"
                            )} years experience
                        </span>

                    </div>

                </div>

            </div>


            <div class="pending-worker-description">

                <strong>Skills</strong>

                <p>
                    ${escapeHtml(
                        worker.skills || "No skills provided."
                    )}
                </p>

            </div>


            <div class="pending-worker-actions">

                <button
                    type="button"
                    class="approve-worker-btn"
                    data-worker-id="${worker.id}"
                    data-status="approved"
                >
                    ✓ Approve
                </button>

                <button
                    type="button"
                    class="reject-worker-btn"
                    data-worker-id="${worker.id}"
                    data-status="rejected"
                >
                    Reject
                </button>

            </div>

        </article>
    `;
}


document.addEventListener("click", function(event) {

    const button =
        event.target.closest(
            ".approve-worker-btn, .reject-worker-btn"
        );

    if (!button) return;


    const workerId =
        button.dataset.workerId;

    const status =
        button.dataset.status;


    if (!workerId || !status) return;


    updateWorkerApproval(
        workerId,
        status
    );
});


async function updateWorkerApproval(
    workerId,
    status
) {

    const formData = new FormData();

    formData.append(
        "worker_id",
        workerId
    );

    formData.append(
        "status",
        status
    );


    try {

        const response = await fetch(
            "../../BackEnd/update_worker_approval.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            }
        );


        const data =
            await response.json();


        if (!data.success) {

            alert(
                data.message ||
                "Could not update worker."
            );

            return;
        }


        alert(data.message);


        loadPendingWorkers();


    } catch (error) {

        console.error(
            "Approval update failed:",
            error
        );

        alert(
            "Could not connect to the server."
        );
    }
}


function escapeHtml(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


/* =========================================
   ADMIN NOTIFICATIONS
   ========================================= */

const adminNotificationBtn =
    document.getElementById("adminNotificationBtn");

const adminNotificationDropdown =
    document.getElementById("adminNotificationDropdown");

const adminNotificationBadge =
    document.getElementById("adminNotificationBadge");

const adminNotificationUnread =
    document.getElementById("adminNotificationUnread");

const adminNotificationList =
    document.getElementById("adminNotificationList");

const markAllAdminNotifications =
    document.getElementById("markAllAdminNotifications");


/* -----------------------------------------
   OPEN / CLOSE NOTIFICATION DROPDOWN
   ----------------------------------------- */

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


/* Close when clicking outside */

document.addEventListener(
    "click",
    function (event) {

        const wrapper =
            document.querySelector(
                ".admin-notification-wrapper"
            );

        if (
            wrapper &&
            !wrapper.contains(event.target)
        ) {

            adminNotificationDropdown.classList.remove(
                "show"
            );

        }

    }
);


/* -----------------------------------------
   LOAD NOTIFICATIONS
   ----------------------------------------- */

async function loadAdminNotifications() {

    if (!adminNotificationList) return;


    try {

        const response = await fetch(
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

            adminNotificationList.innerHTML = `
                <div class="notification-empty">
                    Unable to load notifications.
                </div>
            `;

            return;
        }


        const notifications =
            data.notifications || [];

        const unreadCount =
            Number(data.unread_count || 0);


        /* Badge */

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


        /* Unread text */

        adminNotificationUnread.textContent =
            `${unreadCount} unread`;


        /* Empty */

        if (notifications.length === 0) {

            adminNotificationList.innerHTML = `
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


        /* Render */

        adminNotificationList.innerHTML =
            notifications
                .map(createAdminNotification)
                .join("");


    } catch (error) {

        console.error(
            "Admin notification error:",
            error
        );


        adminNotificationList.innerHTML = `
            <div class="notification-empty">
                Could not connect to notification server.
            </div>
        `;
    }

}


/* -----------------------------------------
   CREATE NOTIFICATION
   ----------------------------------------- */

function createAdminNotification(notification) {

    const unread =
        !notification.read;


    return `

        <div
            class="admin-notification-item ${unread ? "unread" : ""}"
            data-notification-id="${escapeHtml(
                notification.id || ""
            )}"
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


/* -----------------------------------------
   NOTIFICATION ICON
   ----------------------------------------- */

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


/* -----------------------------------------
   FORMAT TIME
   ----------------------------------------- */

function formatAdminNotificationTime(
    dateValue
) {

    if (!dateValue) {
        return "";
    }


    const date =
        new Date(dateValue);


    if (Number.isNaN(date.getTime())) {
        return "";
    }


    const now =
        new Date();


    const seconds =
        Math.floor(
            (now - date) / 1000
        );


    if (seconds < 60) {
        return "Just now";
    }


    const minutes =
        Math.floor(seconds / 60);


    if (minutes < 60) {
        return `${minutes}m ago`;
    }


    const hours =
        Math.floor(minutes / 60);


    if (hours < 24) {
        return `${hours}h ago`;
    }


    const days =
        Math.floor(hours / 24);


    if (days < 7) {
        return `${days}d ago`;
    }


    return date.toLocaleDateString(
        "en-IN",
        {
            day: "numeric",
            month: "short"
        }
    );

}


/* -----------------------------------------
   MARK ALL AS READ
   ----------------------------------------- */

if (markAllAdminNotifications) {

    markAllAdminNotifications.addEventListener(
        "click",
        async function () {

            try {

                const response =
                    await fetch(
                        "../../BackEnd/mark_admin_notifications_read.php",
                        {
                            method: "POST",
                            credentials: "same-origin"
                        }
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    console.error(
                        data.message ||
                        "Could not mark notifications as read."
                    );

                    return;
                }


                await loadAdminNotifications();


            } catch (error) {

                console.error(
                    "Mark all read error:",
                    error
                );

            }

        }
    );

}


/* -----------------------------------------
   INITIAL LOAD
   ----------------------------------------- */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        loadAdminNotifications();

        /*
         * Automatically check for new notifications
         * every 15 seconds.
         */

        setInterval(
            loadAdminNotifications,
            15000
        );

    }
);

</script>

</body>

</html>


</script>

</body>

</html>