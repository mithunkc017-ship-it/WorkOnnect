/* =========================================================
   WORKONNECT - MAIN JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
       REGISTRATION SUCCESS
    ===================================================== */

    const registrationCompleted =
        new URLSearchParams(window.location.search)
            .get("registered");

    if (registrationCompleted === "success") {

        window.alert(
            "Account created successfully. Stored in MongoDB: workonnect.users."
        );

        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    }


    /* =====================================================
       ELEMENTS
    ===================================================== */

    const siteHeader =
        document.getElementById("siteHeader");

    const menuToggle =
        document.getElementById("menuToggle");

    const navLinks =
        document.getElementById("navLinks");

    const workerSearchForm =
        document.getElementById("workerSearchForm");

    const workerSearchInput =
        document.getElementById("workerSearchInput");

    const searchClear =
        document.getElementById("searchClear");

    const popularButtons =
        document.querySelectorAll("[data-search]");

    const workerGrid =
        document.getElementById("landingWorkerGrid");

    const workerEmpty =
        document.getElementById("workerEmpty");

    const workerLoading =
        document.getElementById("workerLoading");

    const workerSearchMessage =
        document.getElementById("workerSearchMessage");


    /* =====================================================
       HEADER SCROLL
    ===================================================== */

    if (siteHeader) {

        const updateHeader = () => {

            siteHeader.classList.toggle(
                "scrolled",
                window.scrollY > 25
            );

        };

        updateHeader();

        window.addEventListener(
            "scroll",
            updateHeader,
            { passive: true }
        );
    }


    /* =====================================================
       MOBILE MENU
    ===================================================== */

    if (menuToggle && navLinks) {

        menuToggle.addEventListener("click", () => {

            const isOpen =
                navLinks.classList.toggle("active");

            menuToggle.classList.toggle(
                "active",
                isOpen
            );

            menuToggle.setAttribute(
                "aria-expanded",
                String(isOpen)
            );

        });


        navLinks.querySelectorAll("a")
            .forEach((link) => {

                link.addEventListener(
                    "click",
                    () => {

                        navLinks.classList.remove(
                            "active"
                        );

                        menuToggle.classList.remove(
                            "active"
                        );

                        menuToggle.setAttribute(
                            "aria-expanded",
                            "false"
                        );

                    }
                );

            });

    }


    /* =====================================================
       SEARCH CLEAR BUTTON
    ===================================================== */

    if (workerSearchInput && searchClear) {

        const updateClearButton = () => {

            searchClear.classList.toggle(
                "show",
                workerSearchInput.value.trim() !== ""
            );

        };


        workerSearchInput.addEventListener(
            "input",
            updateClearButton
        );


        searchClear.addEventListener(
            "click",
            () => {

                workerSearchInput.value = "";

                updateClearButton();

                workerSearchInput.focus();

                hideWorkerResults();

            }
        );

    }


    /* =====================================================
       POPULAR SEARCH BUTTONS
    ===================================================== */

    popularButtons.forEach((button) => {

        button.addEventListener(
            "click",
            () => {

                if (!workerSearchInput) {
                    return;
                }

                const searchValue =
                    button.dataset.search || "";

                workerSearchInput.value =
                    searchValue;

                searchClear?.classList.add(
                    "show"
                );

                loadWorkers(searchValue);

            }
        );

    });


    /* =====================================================
       MAIN SEARCH FORM
    ===================================================== */

    if (workerSearchForm) {

        workerSearchForm.addEventListener(
            "submit",
            async (event) => {

                event.preventDefault();


                const searchValue =
                    workerSearchInput
                        ? workerSearchInput.value.trim()
                        : "";


                if (!searchValue) {

                    if (workerSearchInput) {
                        workerSearchInput.focus();
                    }

                    hideWorkerResults();

                    return;
                }


                const button =
                    workerSearchForm.querySelector(
                        ".worker-search-button"
                    );


                let originalContent = "";


                if (button) {

                    originalContent =
                        button.innerHTML;

                    button.disabled = true;

                    button.innerHTML =
                        "<span>Searching...</span>" +
                        "<span class='search-button-icon'>⌕</span>";

                }


                await loadWorkers(
                    searchValue
                );


                if (button) {

                    button.disabled = false;

                    button.innerHTML =
                        originalContent;

                }

            }
        );

    }


    /* =====================================================
       LOAD WORKERS FROM PHP API
    ===================================================== */

    async function loadWorkers(searchValue) {

        if (!workerGrid) {
            return;
        }


        /* ================================================
           SHOW WORKER SECTION
        ================================================ */

        workerGrid.style.display = "grid";


        if (workerEmpty) {
            workerEmpty.style.display = "none";
        }


        if (workerLoading) {
            workerLoading.style.display = "flex";
        }


        workerGrid.innerHTML = "";


        if (workerSearchMessage) {

            workerSearchMessage.textContent =
                `Searching for "${searchValue}"...`;

        }


        /* ================================================
           API URL
        ================================================ */

        const apiUrl =
            "../BackEnd/search_workers.php?search=" +
            encodeURIComponent(searchValue);


        try {

            const response =
                await fetch(apiUrl);


            if (!response.ok) {

                throw new Error(
                    "HTTP error: " +
                    response.status
                );

            }


            const data =
                await response.json();


            console.log(
                "WORKER SEARCH RESULT:",
                data
            );


            /* ============================================
               HIDE LOADING
            ============================================ */

            if (workerLoading) {
                workerLoading.style.display = "none";
            }


            /* ============================================
               API ERROR
            ============================================ */

            if (!data.success) {

                throw new Error(
                    data.message ||
                    "Worker search failed."
                );

            }


            /* ============================================
               NO WORKERS
            ============================================ */

            if (
                !data.workers ||
                data.workers.length === 0
            ) {

                workerGrid.style.display =
                    "none";


                if (workerEmpty) {

                    workerEmpty.style.display =
                        "block";

                }


                if (workerSearchMessage) {

                    workerSearchMessage.textContent =
                        `No approved workers found for "${searchValue}".`;

                }

                return;
            }


            /* ============================================
               WORKERS FOUND
            ============================================ */

            if (workerSearchMessage) {

                workerSearchMessage.textContent =
                    `${data.count} approved worker${data.count === 1 ? "" : "s"} found for "${searchValue}".`;

            }


            renderWorkers(
                data.workers
            );


            /* ============================================
               SCROLL TO RESULTS
            ============================================ */

            const workersSection =
                document.getElementById(
                    "workers"
                );


            if (workersSection) {

                setTimeout(() => {

                    workersSection.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });

                }, 150);

            }


        } catch (error) {

            console.error(
                "WORKER SEARCH ERROR:",
                error
            );


            if (workerLoading) {
                workerLoading.style.display = "none";
            }


            workerGrid.style.display =
                "grid";


            workerGrid.innerHTML = `

                <div class="worker-load-error">

                    <div class="error-icon">
                        ⚠
                    </div>

                    <h3>
                        Unable to search workers
                    </h3>

                    <p>
                        Please try again.
                    </p>

                    <button
                        type="button"
                        class="retry-workers-btn"
                        id="retryWorkers"
                    >
                        Try Again
                    </button>

                </div>

            `;


            const retryButton =
                document.getElementById(
                    "retryWorkers"
                );


            if (retryButton) {

                retryButton.addEventListener(
                    "click",
                    () => {

                        loadWorkers(
                            searchValue
                        );

                    }
                );

            }

        }

    }


    /* =====================================================
       RENDER WORKER CARDS
    ===================================================== */

    function renderWorkers(workers) {

        if (!workerGrid) {
            return;
        }


        workerGrid.style.display =
            "grid";


        workerGrid.innerHTML =
            workers.map((worker) => {


                const workerName =
                    escapeHTML(
                        worker.name ||
                        "Worker"
                    );


                const service =
                    escapeHTML(
                        worker.service ||
                        "Service"
                    );


                const skills =
                    escapeHTML(
                        worker.skills ||
                        "Professional skills"
                    );


                const location =
                    escapeHTML(
                        worker.location ||
                        "Location not added"
                    );


                const experience =
                    escapeHTML(
                        String(
                            worker.experience ??
                            "0"
                        )
                    );


                const availability =
                    escapeHTML(
                        worker.availability ||
                        "Not specified"
                    );


                const about =
                    escapeHTML(
                        worker.about ||
                        "Skilled professional available for service requests."
                    );


                const workerId =
                    encodeURIComponent(
                        worker.id
                    );


                return `

                    <article class="landing-worker-card">

                        <div class="worker-card-top">

                            <div class="worker-avatar">
                                ${getInitials(workerName)}
                            </div>


                            <div class="worker-main-info">

                                <h3>
                                    ${workerName}
                                </h3>

                                <span class="worker-service">
                                    ${service}
                                </span>

                            </div>


                            <span class="approved-badge">
                                ✓ Approved
                            </span>

                        </div>


                        <div class="worker-card-details">

                            <div class="worker-detail">

                                <span class="detail-icon">
                                    📍
                                </span>

                                <span>
                                    ${location}
                                </span>

                            </div>


                            <div class="worker-detail">

                                <span class="detail-icon">
                                    💼
                                </span>

                                <span>
                                    ${experience} years experience
                                </span>

                            </div>


                            <div class="worker-detail">

                                <span class="detail-icon">
                                    🕒
                                </span>

                                <span>
                                    ${availability}
                                </span>

                            </div>

                        </div>


                        <div class="worker-card-skills">

                            <strong>
                                Skills
                            </strong>

                            <p>
                                ${skills}
                            </p>

                        </div>


                        <p class="worker-card-about">
                            ${about}
                        </p>


                        <div class="worker-card-footer">

                            <a
                                href="customer/worker-profile-view.php?id=${workerId}"
                                class="view-worker-btn"
                            >
                                View Profile
                                <span>→</span>
                            </a>

                        </div>

                    </article>

                `;

            }).join("");

    }


    /* =====================================================
       HIDE WORKER RESULTS
    ===================================================== */

    function hideWorkerResults() {

        if (workerGrid) {

            workerGrid.innerHTML = "";

            workerGrid.style.display =
                "none";

        }


        if (workerEmpty) {

            workerEmpty.style.display =
                "none";

        }


        if (workerLoading) {

            workerLoading.style.display =
                "none";

        }


        if (workerSearchMessage) {

            workerSearchMessage.textContent =
                "Search for a service to discover approved professionals.";

        }

    }


    /* =====================================================
       ESCAPE HTML
    ===================================================== */

    function escapeHTML(value) {

        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    }


    /* =====================================================
       WORKER INITIALS
    ===================================================== */

    function getInitials(name) {

        const cleanName =
            String(name)
                .replace(
                    /&quot;|&#039;|&amp;/g,
                    ""
                )
                .trim();


        if (!cleanName) {
            return "W";
        }


        const parts =
            cleanName
                .split(/\s+/)
                .filter(Boolean);


        if (parts.length === 1) {

            return parts[0]
                .substring(0, 2)
                .toUpperCase();

        }


        return (
            parts[0][0] +
            parts[parts.length - 1][0]
        ).toUpperCase();

    }


    /* =====================================================
       REVEAL ANIMATION
    ===================================================== */

    const revealElements =
        document.querySelectorAll(
            ".reveal"
        );


    if (
        "IntersectionObserver" in window
    ) {

        const revealObserver =
            new IntersectionObserver(
                (entries, observer) => {

                    entries.forEach(
                        (entry) => {

                            if (
                                entry.isIntersecting
                            ) {

                                entry.target.classList.add(
                                    "visible"
                                );

                                observer.unobserve(
                                    entry.target
                                );

                            }

                        }
                    );

                },
                {
                    threshold: 0.12
                }
            );


        revealElements.forEach(
            (element) => {

                revealObserver.observe(
                    element
                );

            }
        );

    } else {

        revealElements.forEach(
            (element) => {

                element.classList.add(
                    "visible"
                );

            }
        );

    }


    /* =====================================================
       ACTIVE NAVIGATION
    ===================================================== */

    const navSections =
        document.querySelectorAll(
            "main section[id]"
        );


    const navigationItems =
        document.querySelectorAll(
            ".nav-link"
        );


    if (
        navSections.length &&
        navigationItems.length &&
        "IntersectionObserver" in window
    ) {

        const sectionObserver =
            new IntersectionObserver(
                (entries) => {

                    entries.forEach(
                        (entry) => {

                            if (
                                !entry.isIntersecting
                            ) {
                                return;
                            }


                            navigationItems.forEach(
                                (link) => {

                                    link.classList.remove(
                                        "active"
                                    );


                                    if (
                                        link.getAttribute(
                                            "href"
                                        ) ===
                                        "#" +
                                        entry.target.id
                                    ) {

                                        link.classList.add(
                                            "active"
                                        );

                                    }

                                }
                            );

                        }
                    );

                },
                {
                    rootMargin:
                        "-35% 0px -55% 0px"
                }
            );


        navSections.forEach(
            (section) => {

                sectionObserver.observe(
                    section
                );

            }
        );

    }


    /* =====================================================
       SMOOTH SCROLL
    ===================================================== */

    document
        .querySelectorAll(
            "a[href^='#']"
        )
        .forEach(
            (link) => {

                link.addEventListener(
                    "click",
                    (event) => {

                        const targetId =
                            link
                                .getAttribute(
                                    "href"
                                )
                                .substring(1);


                        const target =
                            document.getElementById(
                                targetId
                            );


                        if (!target) {
                            return;
                        }


                        event.preventDefault();


                        target.scrollIntoView({
                            behavior: "smooth",
                            block: "start"
                        });

                    }
                );

            }
        );


    /* =====================================================
       CARD TILT
    ===================================================== */

    document
        .querySelectorAll(
            ".process-card"
        )
        .forEach(
            (card) => {

                card.addEventListener(
                    "mousemove",
                    (event) => {

                        if (
                            window.innerWidth < 800
                        ) {
                            return;
                        }


                        const rect =
                            card.getBoundingClientRect();


                        const x =
                            event.clientX -
                            rect.left;


                        const y =
                            event.clientY -
                            rect.top;


                        const centerX =
                            rect.width / 2;


                        const centerY =
                            rect.height / 2;


                        const rotateX =
                            ((y - centerY) /
                                centerY) *
                            -2;


                        const rotateY =
                            ((x - centerX) /
                                centerX) *
                            2;


                        card.style.transform =
                            `translateY(-5px)
                             rotateX(${rotateX}deg)
                             rotateY(${rotateY}deg)`;

                    }
                );


                card.addEventListener(
                    "mouseleave",
                    () => {

                        card.style.transform = "";

                    }
                );

            }
        );

});