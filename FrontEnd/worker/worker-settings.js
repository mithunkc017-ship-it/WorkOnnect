document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | SETTINGS TABS
    |--------------------------------------------------------------------------
    */

    const tabs = document.querySelectorAll(".settings-tab");
    const sections = document.querySelectorAll(".settings-section");

    tabs.forEach(tab => {

        tab.addEventListener("click", () => {

            const target = tab.dataset.target;

            tabs.forEach(item => {
                item.classList.remove("active");
            });

            sections.forEach(section => {
                section.classList.remove("active");
            });

            tab.classList.add("active");

            const targetSection =
                document.getElementById(target);

            if (targetSection) {
                targetSection.classList.add("active");
            }

            /*
            Keep URL clean but allow browser navigation.
            */

            history.replaceState(
                null,
                "",
                "#" + target
            );
        });

    });


    /*
    |--------------------------------------------------------------------------
    | OPEN TAB FROM URL HASH
    |--------------------------------------------------------------------------
    */

    const hash = window.location.hash.replace("#", "");

    if (hash) {

        const tab =
            document.querySelector(
                `.settings-tab[data-target="${hash}"]`
            );

        if (tab) {
            tab.click();
        }

    }


    /*
    |--------------------------------------------------------------------------
    | THEME PREVIEW
    |--------------------------------------------------------------------------
    */

    const themeInputs =
        document.querySelectorAll(
            'input[name="theme"]'
        );

    themeInputs.forEach(input => {

        input.addEventListener("change", () => {

            if (input.checked) {

                document.body.dataset.theme =
                    input.value;

                localStorage.setItem(
                    "worker_theme_preview",
                    input.value
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | PASSWORD SHOW / HIDE
    |--------------------------------------------------------------------------
    */

    const passwordButtons =
        document.querySelectorAll(".show-password");

    passwordButtons.forEach(button => {

        button.addEventListener("click", () => {

            const inputId =
                button.dataset.input;

            const input =
                document.getElementById(inputId);

            if (!input) return;

            if (input.type === "password") {

                input.type = "text";
                button.textContent = "Hide";

            } else {

                input.type = "password";
                button.textContent = "Show";

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | PASSWORD STRENGTH
    |--------------------------------------------------------------------------
    */

    const newPassword =
        document.getElementById("newPassword");

    const strength =
        document.getElementById("passwordStrength");

    if (newPassword && strength) {

        const bar = strength.querySelector("div");
        const label = strength.querySelector("span");

        newPassword.addEventListener("input", () => {

            const value = newPassword.value;

            let score = 0;

            if (value.length >= 8) {
                score++;
            }

            if (/[A-Z]/.test(value)) {
                score++;
            }

            if (/[a-z]/.test(value)) {
                score++;
            }

            if (/[0-9]/.test(value)) {
                score++;
            }

            if (/[^A-Za-z0-9]/.test(value)) {
                score++;
            }


            if (score <= 1) {

                bar.style.width = "20%";
                label.textContent = "Weak password";

            } else if (score <= 3) {

                bar.style.width = "55%";
                label.textContent = "Medium password";

            } else {

                bar.style.width = "100%";
                label.textContent = "Strong password";

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD MATCH VALIDATION
    |--------------------------------------------------------------------------
    */

    const passwordForm =
        document.getElementById("passwordForm");

    const confirmPassword =
        document.getElementById("confirmPassword");

    if (passwordForm && confirmPassword) {

        passwordForm.addEventListener("submit", event => {

            const newPassword =
                document.getElementById("newPassword");

            if (
                newPassword &&
                newPassword.value !==
                confirmPassword.value
            ) {

                event.preventDefault();

                alert(
                    "New password and confirmation do not match."
                );

                confirmPassword.focus();

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | AUTO HIDE FLASH MESSAGE
    |--------------------------------------------------------------------------
    */

    const flash =
        document.getElementById("flashMessage");

    if (flash) {

        setTimeout(() => {

            flash.style.transition =
                "opacity .4s ease, transform .4s ease";

            flash.style.opacity = "0";
            flash.style.transform = "translateY(-5px)";

            setTimeout(() => {
                flash.remove();
            }, 400);

        }, 4500);

    }


    /*
    |--------------------------------------------------------------------------
    | DISABLE DEPENDENT NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    const masterNotification =
        document.querySelector(
            'input[name="notifications_enabled"]'
        );

    const notificationInputs =
        document.querySelectorAll(
            'input[name="request_notifications"],' +
            'input[name="approval_notifications"],' +
            'input[name="email_notifications"],' +
            'input[name="auto_refresh_notifications"]'
        );

    function updateNotificationState() {

        if (!masterNotification) return;

        notificationInputs.forEach(input => {

            input.disabled =
                !masterNotification.checked;

            input.closest(".setting-row")
                ?.classList.toggle(
                    "setting-disabled",
                    !masterNotification.checked
                );

        });

    }

    if (masterNotification) {

        masterNotification.addEventListener(
            "change",
            updateNotificationState
        );

        updateNotificationState();

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE BUTTON FEEDBACK
    |--------------------------------------------------------------------------
    */

    const preferencesForm =
        document.getElementById("preferencesForm");

    if (preferencesForm) {

        preferencesForm.addEventListener("submit", () => {

            const button =
                document.querySelector(
                    ".save-button"
                );

            if (button) {

                button.textContent =
                    "Saving...";

                button.style.opacity = ".7";

            }

        });

    }

});



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


    console.log(
        "Worker profile button:",
        trigger
    );

    console.log(
        "Worker drawer:",
        drawer
    );

    console.log(
        "Worker overlay:",
        overlay
    );


    if (!trigger || !drawer || !overlay) {

        console.error(
            "WORKER SETTINGS: Missing required element"
        );

        return;
    }


    function openWorkerSettings() {

        console.log(
            "OPENING WORKER SETTINGS"
        );

        drawer.classList.add("open");

        overlay.classList.add("open");

        overlay.classList.add("show");

        drawer.setAttribute(
            "aria-hidden",
            "false"
        );
    }


    function closeWorkerSettings() {

        console.log(
            "CLOSING WORKER SETTINGS"
        );

        drawer.classList.remove("open");

        overlay.classList.remove("open");

        overlay.classList.remove("show");

        drawer.setAttribute(
            "aria-hidden",
            "true"
        );

        /*
         * Important:
         * Never leave the page locked.
         */
        document.body.style.overflow = "";
    }


    trigger.addEventListener(
        "click",
        function (event) {

            event.preventDefault();

            event.stopPropagation();

            openWorkerSettings();

        }
    );


    closeButton?.addEventListener(
        "click",
        function (event) {

            event.preventDefault();

            closeWorkerSettings();

        }
    );


    overlay.addEventListener(
        "click",
        function () {

            closeWorkerSettings();

        }
    );


    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeWorkerSettings();

            }

        }
    );

});



document.addEventListener("DOMContentLoaded", function () {

    const trigger = document.getElementById("workerSettingsTrigger");
    const drawer = document.getElementById("workerSettingsDrawer");
    const overlay = document.getElementById("workerSettingsOverlay");
    const closeBtn = document.getElementById("workerSettingsClose");

    if (!trigger || !drawer || !overlay) {
        console.error("Worker settings elements not found");
        return;
    }

    function openSettings() {
        drawer.classList.add("open");
        overlay.classList.add("open");

        drawer.setAttribute("aria-hidden", "false");
        overlay.setAttribute("aria-hidden", "false");
    }

    function closeSettings() {
        drawer.classList.remove("open");
        overlay.classList.remove("open");

        drawer.setAttribute("aria-hidden", "true");
        overlay.setAttribute("aria-hidden", "true");
    }

    trigger.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();

        openSettings();
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            closeSettings();
        });
    }

    overlay.addEventListener("click", function () {
        closeSettings();
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeSettings();
        }
    });

});