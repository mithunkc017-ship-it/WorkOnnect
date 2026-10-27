document.addEventListener("DOMContentLoaded", () => {

    const siteHeader = document.getElementById("siteHeader");
    const menuToggle = document.getElementById("menuToggle");
    const navLinks = document.getElementById("navLinks");

    const workerSearchForm =
        document.getElementById("workerSearchForm");

    const workerSearchInput =
        document.getElementById("workerSearchInput");

    const searchClear =
        document.getElementById("searchClear");

    const popularButtons =
        document.querySelectorAll("[data-search]");

    if (siteHeader) {
        const updateHeader = () => {
            siteHeader.classList.toggle("scrolled", window.scrollY > 25);
        };

        updateHeader();
        window.addEventListener("scroll", updateHeader, { passive: true });
    }

    if (menuToggle && navLinks) {
        menuToggle.addEventListener("click", () => {
            const isOpen = navLinks.classList.toggle("active");
            menuToggle.classList.toggle("active", isOpen);
            menuToggle.setAttribute("aria-expanded", String(isOpen));
        });

        navLinks.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                navLinks.classList.remove("active");
                menuToggle.classList.remove("active");
                menuToggle.setAttribute("aria-expanded", "false");
            });
        });
    }

    if (workerSearchInput && searchClear) {
        const updateClearButton = () => {
            searchClear.classList.toggle("show", workerSearchInput.value.trim() !== "");
        };

        workerSearchInput.addEventListener("input", updateClearButton);
        searchClear.addEventListener("click", () => {
            workerSearchInput.value = "";
            updateClearButton();
            workerSearchInput.focus();
        });
    }

    popularButtons.forEach((button) => {
        button.addEventListener("click", () => {
            if (!workerSearchInput) {
                return;
            }

            workerSearchInput.value = button.dataset.search;
            searchClear?.classList.add("show");
            workerSearchInput.focus();
        });
    });


    if (workerSearchForm) {

        workerSearchForm.addEventListener(
            "submit",
            (event) => {

                event.preventDefault();

                const searchValue =
                    workerSearchInput
                        ? workerSearchInput.value.trim()
                        : "";

                if (!searchValue) {

                    if (workerSearchInput) {
                        workerSearchInput.focus();
                    }

                    return;
                }

                    const button =
                    workerSearchForm.querySelector(
                        ".worker-search-button"
                    );

                if (!button) {
                    return;
                }

                const originalContent =
                    button.innerHTML;

                button.disabled = true;

                button.innerHTML =
                    "<span>Searching...</span>" +
                    "<span class='search-button-icon'>⌕</span>";

                setTimeout(() => {

                    button.disabled = false;

                    button.innerHTML =
                        originalContent;

                    window.location.href =
                        `pages/services.html?search=${encodeURIComponent(searchValue)}`;

                }, 650);

            }
        );

    }


    const revealElements =
        document.querySelectorAll(".reveal");


    if ("IntersectionObserver" in window) {

        const observer =
            new IntersectionObserver(
                (entries) => {

                    entries.forEach((entry) => {

                        if (entry.isIntersecting) {

                            entry.target.classList.add(
                                "visible"
                            );

                            observer.unobserve(
                                entry.target
                            );

                        }

                    });

                },
                {
                    threshold: 0.12
                }
            );


        revealElements.forEach((element) => {

            observer.observe(element);

        });

    } else {

        revealElements.forEach((element) => {

            element.classList.add("visible");

        });

    }


    const navSections =
        document.querySelectorAll("main section[id]");

    const navigationItems =
        document.querySelectorAll(".nav-link");


    if (
        navSections.length &&
        navigationItems.length &&
        "IntersectionObserver" in window
    ) {

        const sectionObserver =
            new IntersectionObserver(
                (entries) => {

                    entries.forEach((entry) => {

                        if (!entry.isIntersecting) {
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
                                    "#" + entry.target.id
                                ) {

                                    link.classList.add(
                                        "active"
                                    );

                                }

                            }
                        );

                    });

                },
                {
                    rootMargin:
                        "-35% 0px -55% 0px"
                }
            );


        navSections.forEach((section) => {

            sectionObserver.observe(section);

        });

    }


    document
        .querySelectorAll("a[href^='#']")
        .forEach((link) => {

            link.addEventListener(
                "click",
                (event) => {

                    const targetId =
                        link
                            .getAttribute("href")
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

        });

});



