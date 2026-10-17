
<?php
session_start();

$workerId = trim($_GET["id"] ?? "");

if ($workerId === "") {
    header("Location: customer-dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connect on WorkOnnect</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap');

        :root {
            --ink: #17252b;
            --muted: #66767a;
            --blue: #d9eff2;
            --blue-deep: #197487;
            --blue-dark: #115c6b;
            --yellow: #f4c95d;
            --coral: #f28573;
            --green: #cde5c2;
            --cream: #fafaf5;
            --white: #ffffff;
            --line: #dce6e5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: "DM Sans", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(
                    circle at 10% 15%,
                    rgba(217, 239, 242, .85),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 80%,
                    rgba(244, 201, 93, .22),
                    transparent 30%
                ),
                var(--cream);

            overflow-x: hidden;
        }


        /* ==============================
           FLOATING BACKGROUND SHAPES
        ============================== */

        .background-shape {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(1px);
            opacity: .65;
            z-index: 0;
        }

        .shape-one {
            width: 180px;
            height: 180px;
            background: var(--blue);
            top: 8%;
            left: -70px;
            animation: floatOne 7s ease-in-out infinite;
        }

        .shape-two {
            width: 120px;
            height: 120px;
            background: var(--yellow);
            opacity: .25;
            right: 4%;
            top: 18%;
            animation: floatTwo 6s ease-in-out infinite;
        }

        .shape-three {
            width: 150px;
            height: 150px;
            background: var(--green);
            opacity: .4;
            bottom: -50px;
            left: 15%;
            animation: floatThree 8s ease-in-out infinite;
        }


        /* ==============================
           PAGE
        ============================== */

        .page {
            position: relative;
            z-index: 2;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 45px 20px;
        }

        .container {
            width: 100%;
            max-width: 720px;
        }


        /* ==============================
           BRAND
        ============================== */
        /* .brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    margin-bottom: 25px;

    font-family: "Outfit", sans-serif;

    animation: fadeDown .7s ease both;
}

.brand-logo {
    width: 43px;
    height: 43px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background: #197487;
    color: #ffffff;

    font-family: "Outfit", sans-serif;
    font-size: 22px;
    font-weight: 800;

    box-shadow:
        0 8px 20px rgba(25, 116, 135, .20);

    transform: rotate(-4deg);

    transition:
        transform .3s ease,
        box-shadow .3s ease;
}

.brand:hover .brand-logo {
    transform: rotate(0deg) scale(1.06);

    box-shadow:
        0 12px 25px rgba(25, 116, 135, .28);
}

.brand-name {
    color: #17252b;

    font-size: 25px;
    font-weight: 800;

    letter-spacing: -.7px;
}

.brand-name span {
    color: #197487;
}
         */


.brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;

    margin-bottom: 25px;

    text-decoration: none;

    font-family: "Outfit", sans-serif;

    animation: fadeDown .7s ease both;

    cursor: pointer;
}

.brand-logo {
    width: 43px;
    height: 43px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background: #197487;
    color: #ffffff;

    font-size: 22px;
    font-weight: 800;

    box-shadow:
        0 8px 20px rgba(25, 116, 135, .20);

    transform: rotate(-4deg);

    transition:
        transform .3s ease,
        box-shadow .3s ease;
}

.brand-name {
    color: #17252b;

    font-size: 25px;
    font-weight: 800;

    letter-spacing: -.7px;

    transition: color .25s ease;
}

.brand-name span {
    color: #197487;
}

.brand:hover .brand-logo {
    transform: rotate(0deg) scale(1.07);

    box-shadow:
        0 12px 28px rgba(25, 116, 135, .28);
}

.brand:hover .brand-name {
    color: #197487;
}




        /* ==============================
           MAIN CARD
        ============================== */

        .card {
            position: relative;

            background: rgba(255, 255, 255, .88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border: 1px solid rgba(220, 230, 229, .9);
            border-radius: 32px;

            padding: 48px;

            box-shadow:
                0 30px 80px rgba(23, 37, 43, .10),
                0 8px 25px rgba(23, 37, 43, .04);

            overflow: hidden;

            animation: cardEnter .75s cubic-bezier(.22, 1, .36, 1) both;
        }

        .card::before {
            content: "";

            position: absolute;
            width: 260px;
            height: 260px;

            top: -170px;
            right: -100px;

            background: var(--blue);
            border-radius: 50%;

            opacity: .5;
        }


        /* ==============================
           ICON
        ============================== */

        .icon-wrapper {
            position: relative;

            width: 92px;
            height: 92px;

            margin: 0 auto 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                145deg,
                #e6f7f8,
                #cfecef
            );

            border: 1px solid rgba(25, 116, 135, .12);

            border-radius: 28px;

            box-shadow:
                0 12px 30px rgba(25, 116, 135, .12);

            animation: iconFloat 4s ease-in-out infinite;
        }

        .icon-wrapper::after {
            content: "";

            position: absolute;
            inset: -7px;

            border: 1px solid rgba(25, 116, 135, .10);
            border-radius: 33px;

            animation: iconRing 2.5s ease-in-out infinite;
        }

        .icon {
            font-size: 40px;
            animation: handshake 3s ease-in-out infinite;
        }


        /* ==============================
           TEXT
        ============================== */

        .eyebrow {
            text-align: center;

            color: var(--blue-deep);

            font-size: 12px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: 1.8px;

            margin-bottom: 10px;
        }

        h1 {
            position: relative;

            text-align: center;

            font-family: "Outfit", sans-serif;
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.05;

            letter-spacing: -1.5px;

            margin-bottom: 17px;
        }

        h1 span {
            color: var(--blue-deep);
        }

        .intro {
            max-width: 520px;

            margin: 0 auto 36px;

            text-align: center;

            color: var(--muted);

            font-size: 15px;
            line-height: 1.75;
        }


        /* ==============================
           OPTIONS
        ============================== */

        .options {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 16px;
        }

        .option {
            position: relative;

            display: flex;
            flex-direction: column;

            padding: 25px;

            border-radius: 22px;

            border: 1px solid var(--line);

            background: rgba(255, 255, 255, .82);

            transition:
                transform .3s ease,
                box-shadow .3s ease,
                border-color .3s ease;

            animation: optionEnter .7s ease both;
        }

        .option:nth-child(2) {
            animation-delay: .12s;
        }

        .option:hover {
            transform: translateY(-6px);

            border-color: rgba(25, 116, 135, .3);

            box-shadow:
                0 18px 40px rgba(23, 37, 43, .09);
        }

        .option-badge {
            width: 43px;
            height: 43px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            margin-bottom: 17px;

            font-size: 19px;
        }

        .login-option .option-badge {
            background: var(--blue);
        }

        .signup-option .option-badge {
            background: #fff3c9;
        }

        .option-title {
            font-family: "Outfit", sans-serif;

            font-size: 19px;
            font-weight: 700;

            margin-bottom: 8px;
        }

        .option-text {
            color: var(--muted);

            font-size: 13px;
            line-height: 1.65;

            margin-bottom: 20px;
        }


        /* ==============================
           BUTTONS
        ============================== */

        .btn {
            position: relative;

            width: 100%;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            padding: 14px 18px;

            border-radius: 13px;

            text-decoration: none;

            font-size: 14px;
            font-weight: 700;

            overflow: hidden;

            transition:
                transform .25s ease,
                box-shadow .25s ease;
        }

        .btn::before {
            content: "";

            position: absolute;

            top: 0;
            left: -100%;

            width: 70%;
            height: 100%;

            background: linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.35),
                transparent
            );

            transition: left .55s ease;
        }

        .btn:hover::before {
            left: 130%;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .login-btn {
            color: white;
            background: var(--blue-deep);

            box-shadow:
                0 8px 18px rgba(25, 116, 135, .18);
        }

        .login-btn:hover {
            background: var(--blue-dark);

            box-shadow:
                0 12px 25px rgba(25, 116, 135, .25);
        }

        .register-btn {
            color: var(--ink);
            background: var(--yellow);

            box-shadow:
                0 8px 18px rgba(244, 201, 93, .2);
        }

        .register-btn:hover {
            box-shadow:
                0 12px 25px rgba(244, 201, 93, .3);
        }

        .arrow {
            transition: transform .25s ease;
        }

        .btn:hover .arrow {
            transform: translateX(4px);
        }


        /* ==============================
           FOOTER
        ============================== */

        .back-link {
            display: flex;

            justify-content: center;
            align-items: center;

            gap: 6px;

            margin-top: 29px;

            color: var(--muted);

            font-size: 13px;
            font-weight: 600;

            text-decoration: none;

            transition: color .2s ease;
        }

        .back-link:hover {
            color: var(--blue-deep);
        }

        .trust {
            display: flex;
            justify-content: center;
            align-items: center;

            gap: 7px;

            margin-top: 20px;

            color: #8a989b;

            font-size: 11px;
        }

        .trust-dot {
            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #78a96c;

            box-shadow: 0 0 0 4px rgba(120, 169, 108, .12);
        }


        /* ==============================
           ANIMATIONS
        ============================== */

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: translateY(35px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes optionEnter {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes iconFloat {
            0%, 100% {
                transform: translateY(0) rotate(0);
            }

            50% {
                transform: translateY(-6px) rotate(1deg);
            }
        }

        @keyframes handshake {
            0%, 100% {
                transform: rotate(0);
            }

            50% {
                transform: rotate(-5deg) scale(1.04);
            }
        }

        @keyframes iconRing {
            0%, 100% {
                opacity: .3;
                transform: scale(1);
            }

            50% {
                opacity: .7;
                transform: scale(1.04);
            }
        }

        @keyframes pulseDot {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.35);
                opacity: .65;
            }
        }

        @keyframes floatOne {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(25px);
            }
        }

        @keyframes floatTwo {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes floatThree {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-25px);
            }
        }


        /* ==============================
           MOBILE
        ============================== */

        @media (max-width: 700px) {

            .page {
                padding: 28px 14px;
            }

            .card {
                padding: 34px 20px;
                border-radius: 25px;
            }

            .options {
                grid-template-columns: 1fr;
            }

            .icon-wrapper {
                width: 78px;
                height: 78px;
                border-radius: 23px;
            }

            .icon {
                font-size: 33px;
            }

            h1 {
                font-size: 34px;
            }

            .intro {
                font-size: 14px;
                margin-bottom: 28px;
            }

            .option {
                padding: 21px;
            }
        }
    </style>
</head>

<body>

    <div class="background-shape shape-one"></div>
    <div class="background-shape shape-two"></div>
    <div class="background-shape shape-three"></div>


    <main class="page">

        <div class="container">

            <!-- <div class="brand">
                <div class="brand-logo">W</div>

                <div class="brand-name">
                    Work<span>Onnect</span>
                </div>
            </div> -->
            <div class="brand">
    <div class="brand-logo">W</div>

    <div class="brand-name">
        Work<span>Onnect</span>
    </div>
</div>
<!--                 
            <a href="/WorkOnnect/FrontEnd/pages/index.html" class="logo">
    <span class="logo-icon">W</span>

    <span>
        Work<span>Onnect</span>
    </span>
</a> -->


            <!-- <div class="brand">
                <span class="brand-work">Work</span><span class="brand-onnect">Onnect</span>
                <span class="brand-dot"></span>
            </div> -->


            <section class="card">

                <div class="icon-wrapper">
                    <div class="icon">🤝</div>
                </div>


                <div class="eyebrow">
                    One step closer
                </div>


                <h1>
                    Let's get you <span>connected.</span>
                </h1>


                <p class="intro">
                    You're about to connect with a worker on WorkOnnect.
                    Log in to your account or create one to send your
                    request and start your journey.
                </p>


                <div class="options">


                    <!-- LOGIN -->

                    <div class="option login-option">

                        <div class="option-badge">
                            👋
                        </div>

                        <div class="option-title">
                            Already with us?
                        </div>

                        <p class="option-text">
                            Welcome back! Sign in to your WorkOnnect
                            account and continue connecting with
                            trusted workers.
                        </p>

                        <a
                            class="btn login-btn"
                            href="../pages/login.html?redirect=connect&id=<?php echo urlencode($workerId); ?>"
                        >
                            Login to WorkOnnect
                            <span class="arrow">→</span>
                        </a>

                    </div>


                    <!-- CREATE ACCOUNT -->

                    <div class="option signup-option">

                        <div class="option-badge">
                            ✨
                        </div>

                        <div class="option-title">
                            New to WorkOnnect?
                        </div>

                        <p class="option-text">
                            Create your free account, explore skilled
                            workers and find the right person for your
                            needs.
                        </p>

                        <a
                            class="btn register-btn"
                            href="../pages/register.html?redirect=connect&id=<?php echo urlencode($workerId); ?>"
                        >
                            Create Free Account
                            <span class="arrow">→</span>
                        </a>

                    </div>

                </div>


                <a
                    class="back-link"
                    href="worker-profile-view.php?id=<?php echo urlencode($workerId); ?>"
                >
                    ← Back to worker profile
                </a>


                <div class="trust">
                    <span class="trust-dot"></span>
                    Simple, secure & built for better connections
                </div>

            </section>

        </div>

    </main>

</body>
</html>
```
