// :root {
//     --ink: #17252b;
//     --muted: #718083;
//     --blue: #d9eff2;
//     --blue-deep: #197487;
//     --yellow: #f4c95d;
//     --coral: #f28573;
//     --green: #cde5c2;
//     --cream: #fafaf5;
//     --white: #ffffff;
//     --line: #e4eceb;
// }

// * {
//     box-sizing: border-box;
//     margin: 0;
//     padding: 0;
// }

// body {
//     font-family: Arial, Helvetica, sans-serif;
//     background: var(--cream);
//     color: var(--ink);
// }

// a {
//     text-decoration: none;
//     color: inherit;
// }

// .dashboard {
//     display: flex;
//     min-height: 100vh;
// }


// /* =========================
//    SIDEBAR
// ========================= */

// .sidebar {
//     width: 250px;
//     background: var(--white);
//     border-right: 1px solid var(--line);

//     display: flex;
//     flex-direction: column;

//     padding: 28px 18px;

//     position: fixed;
//     left: 0;
//     top: 0;
//     bottom: 0;
// }

// .brand {
//     display: flex;
//     align-items: center;
//     gap: 12px;

//     padding: 0 12px 35px;
// }

// .brand-logo {
//     width: 42px;
//     height: 42px;

//     display: flex;
//     align-items: center;
//     justify-content: center;

//     border-radius: 13px;

//     background: var(--blue-deep);
//     color: white;

//     font-size: 22px;
//     font-weight: 800;

//     box-shadow: 0 8px 20px rgba(25, 116, 135, .2);
// }

// .brand-text {
//     font-size: 20px;
//     font-weight: 800;
// }

// .brand-text span {
//     color: var(--blue-deep);
// }

// .navigation {
//     display: flex;
//     flex-direction: column;
//     gap: 7px;
// }

// .nav-item {
//     display: flex;
//     align-items: center;
//     gap: 14px;

//     padding: 13px 15px;

//     border-radius: 12px;

//     color: var(--muted);

//     font-size: 14px;
//     font-weight: 600;

//     transition: .25s ease;
// }

// .nav-item:hover {
//     background: var(--blue);
//     color: var(--blue-deep);
// }

// .nav-item.active {
//     background: var(--blue);
//     color: var(--blue-deep);
// }

// .nav-icon {
//     width: 25px;
//     text-align: center;
//     font-size: 18px;
// }

// .sidebar-bottom {
//     margin-top: auto;
// }

// .logout {
//     display: flex;
//     gap: 12px;
//     align-items: center;

//     padding: 13px 15px;

//     border-radius: 12px;

//     color: #d96b5a;
//     font-weight: 600;

//     transition: .25s ease;
// }

// .logout:hover {
//     background: #fff0ed;
// }


// /* =========================
//    MAIN
// ========================= */

// .main-content {
//     margin-left: 250px;
//     width: calc(100% - 250px);

//     padding: 35px 45px;
// }

// .topbar {
//     display: flex;
//     align-items: center;
//     justify-content: space-between;

//     margin-bottom: 28px;
// }

// .small-title {
//     color: var(--blue-deep);

//     font-size: 11px;
//     font-weight: 800;

//     letter-spacing: 2px;

//     margin-bottom: 6px;
// }

// .topbar h1 {
//     font-size: 30px;
// }

// .admin-profile {
//     display: flex;
//     align-items: center;
//     gap: 12px;
// }

// .profile-avatar {
//     width: 44px;
//     height: 44px;

//     border-radius: 50%;

//     background: var(--yellow);

//     display: flex;
//     align-items: center;
//     justify-content: center;

//     font-weight: 800;
// }

// .profile-info {
//     display: flex;
//     flex-direction: column;
//     gap: 3px;
// }

// .profile-info strong {
//     font-size: 14px;
// }

// .profile-info span {
//     color: var(--muted);
//     font-size: 12px;
// }


// /* =========================
//    WELCOME
// ========================= */

// .welcome-card {
//     min-height: 190px;

//     border-radius: 24px;

//     background: var(--blue);

//     padding: 35px;

//     display: flex;
//     align-items: center;
//     justify-content: space-between;

//     overflow: hidden;

//     position: relative;

//     margin-bottom: 25px;
// }

// .welcome-label {
//     color: var(--blue-deep);

//     font-size: 12px;
//     font-weight: 800;

//     margin-bottom: 8px;
// }

// .welcome-card h2 {
//     font-size: 26px;
//     margin-bottom: 9px;
// }

// .welcome-card p:last-child {
//     color: #547176;
//     font-size: 14px;
// }

// .welcome-shape {
//     width: 200px;
//     height: 200px;

//     position: relative;
// }

// .shape-circle {
//     width: 170px;
//     height: 170px;

//     border-radius: 50%;

//     background: var(--yellow);

//     position: absolute;

//     right: -45px;
//     top: -15px;
// }

// .shape-circle.small {
//     width: 80px;
//     height: 80px;

//     background: var(--coral);

//     right: 80px;
//     top: 85px;
// }


// /* =========================
//    STAT CARDS
// ========================= */

// .stats-grid {
//     display: grid;

//     grid-template-columns:
//         repeat(3, 1fr);

//     gap: 20px;

//     margin-bottom: 25px;
// }

// .stat-card {
//     background: var(--white);

//     border: 1px solid var(--line);

//     border-radius: 18px;

//     padding: 22px;

//     display: flex;
//     align-items: center;

//     gap: 17px;

//     transition: .25s ease;
// }

// .stat-card:hover {
//     transform: translateY(-4px);

//     box-shadow:
//         0 15px 35px rgba(23, 37, 43, .07);
// }

// .stat-icon {
//     width: 50px;
//     height: 50px;

//     border-radius: 15px;

//     display: flex;
//     align-items: center;
//     justify-content: center;

//     font-size: 22px;
// }

// .users-icon {
//     background: var(--blue);
//     color: var(--blue-deep);
// }

// .project-icon {
//     background: var(--green);
// }

// .activity-icon {
//     background: #fff1cc;
// }

// .stat-label {
//     display: block;

//     color: var(--muted);

//     font-size: 12px;

//     margin-bottom: 5px;
// }

// .stat-number {
//     font-size: 22px;
// }


// /* =========================
//    CONTENT
// ========================= */

// .content-grid {
//     display: grid;

//     grid-template-columns:
//         1.5fr 1fr;

//     gap: 20px;
// }

// .panel {
//     background: var(--white);

//     border: 1px solid var(--line);

//     border-radius: 20px;

//     padding: 25px;
// }

// .panel-header {
//     display: flex;

//     justify-content: space-between;
//     align-items: center;

//     margin-bottom: 20px;
// }

// .panel-label {
//     color: var(--blue-deep);

//     font-size: 10px;

//     font-weight: 800;

//     letter-spacing: 1.5px;

//     margin-bottom: 5px;
// }

// .panel h3 {
//     font-size: 19px;
// }

// .view-all {
//     color: var(--blue-deep);

//     font-size: 12px;

//     font-weight: 700;
// }


// /* =========================
//    USERS
// ========================= */

// .users-list {
//     display: flex;
//     flex-direction: column;
// }

// .user-row {
//     display: flex;

//     align-items: center;

//     gap: 13px;

//     padding: 13px 0;

//     border-bottom: 1px solid var(--line);
// }

// .user-row:last-child {
//     border-bottom: none;
// }

// .user-avatar {
//     width: 39px;
//     height: 39px;

//     border-radius: 50%;

//     background: var(--green);

//     display: flex;
//     align-items: center;
//     justify-content: center;

//     font-weight: 800;

//     font-size: 13px;
// }

// .user-details {
//     flex: 1;

//     display: flex;
//     flex-direction: column;

//     gap: 3px;
// }

// .user-details strong {
//     font-size: 13px;
// }

// .user-details span {
//     color: var(--muted);

//     font-size: 11px;
// }

// .user-role {
//     padding: 6px 10px;

//     border-radius: 20px;

//     background: var(--blue);

//     color: var(--blue-deep);

//     font-size: 10px;

//     font-weight: 700;

//     text-transform: capitalize;
// }

// .empty-state {
//     text-align: center;

//     padding: 30px;

//     color: var(--muted);

//     font-size: 13px;
// }


// /* =========================
//    QUICK ACTIONS
// ========================= */

// .action-list {
//     display: flex;
//     flex-direction: column;

//     gap: 10px;
// }

// .action-card {
//     display: flex;

//     align-items: center;

//     gap: 12px;

//     padding: 14px;

//     border: 1px solid var(--line);

//     border-radius: 14px;

//     transition: .25s ease;
// }

// .action-card:hover {
//     border-color: var(--blue-deep);

//     background: #f8fcfc;

//     transform: translateX(3px);
// }

// .action-icon {
//     width: 38px;
//     height: 38px;

//     border-radius: 11px;

//     background: var(--blue);

//     color: var(--blue-deep);

//     display: flex;
//     align-items: center;
//     justify-content: center;

//     font-weight: 800;
// }

// .action-card div:nth-child(2) {
//     flex: 1;

//     display: flex;
//     flex-direction: column;

//     gap: 3px;
// }

// .action-card strong {
//     font-size: 12px;
// }

// .action-card span {
//     color: var(--muted);

//     font-size: 10px;
// }

// .action-card .arrow {
//     color: var(--blue-deep);

//     font-size: 17px;
// }


// /* =========================
//    RESPONSIVE
// ========================= */

// @media (max-width: 1000px) {

//     .stats-grid {
//         grid-template-columns: 1fr 1fr;
//     }

//     .content-grid {
//         grid-template-columns: 1fr;
//     }

// }


// @media (max-width: 750px) {

//     .sidebar {
//         width: 70px;

//         padding: 20px 10px;
//     }

//     .brand {
//         justify-content: center;

//         padding: 0 0 30px;
//     }

//     .brand-text,
//     .nav-item span:last-child,
//     .logout {
//         display: none;
//     }

//     .nav-item {
//         justify-content: center;
//     }

//     .main-content {
//         margin-left: 70px;

//         width: calc(100% - 70px);

//         padding: 25px 20px;
//     }

//     .welcome-card {
//         padding: 25px;
//     }

//     .welcome-shape {
//         display: none;
//     }

// }


// @media (max-width: 550px) {

//     .topbar {
//         align-items: flex-start;
//     }

//     .profile-info {
//         display: none;
//     }

//     .stats-grid {
//         grid-template-columns: 1fr;
//     }

//     .welcome-card h2 {
//         font-size: 21px;
//     }

//     .main-content {
//         padding: 20px 15px;
//     }

// }