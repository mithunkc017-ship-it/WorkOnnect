
document.addEventListener("DOMContentLoaded", () => {

    const passwordToggles =
        document.querySelectorAll(".password-toggle");


    passwordToggles.forEach((toggle) => {

        toggle.addEventListener("click", () => {

            const targetId =
                toggle.dataset.target;

            const input =
                document.getElementById(targetId);

            if (!input) {
                return;
            }

            if (input.type === "password") {

                input.type = "text";

                toggle.textContent = "Hide";

                toggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                input.type = "password";

                toggle.textContent = "Show";

                toggle.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        });

    });


    const registerPassword =
        document.getElementById(
            "registerPassword"
        );

    const passwordStrength =
        document.getElementById(
            "passwordStrength"
        );


    if (
        registerPassword &&
        passwordStrength
    ) {

        registerPassword.addEventListener(
            "input",
            () => {

                const password =
                    registerPassword.value;

                updatePasswordStrength(
                    password,
                    passwordStrength
                );

            }
        );

    }


    const loginForm =
        document.getElementById(
            "loginForm"
        );


    if (loginForm) {

        loginForm.addEventListener(
            "submit",
            (event) => {

                event.preventDefault();

                const email =
                    document.getElementById(
                        "loginEmail"
                    );

                const password =
                    document.getElementById(
                        "loginPassword"
                    );

                const emailError =
                    document.getElementById(
                        "loginEmailError"
                    );

                const passwordError =
                    document.getElementById(
                        "loginPasswordError"
                    );

                const message =
                    document.getElementById(
                        "loginMessage"
                    );

                clearErrors(
                    [
                        emailError,
                        passwordError
                    ],
                    [
                        email,
                        password
                    ]
                );

                hideMessage(message);

                let valid = true;


                if (
                    !email.value.trim()
                ) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter your email."
                    );

                    valid = false;

                } else if (
                    !isValidEmail(
                        email.value.trim()
                    )
                ) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter a valid email."
                    );

                    valid = false;

                }


                if (!password.value) {

                    showFieldError(
                        password,
                        passwordError,
                        "Please enter your password."
                    );

                    valid = false;

                }


                if (!valid) {
                    return;
                }


                const submitButton =
                    loginForm.querySelector(
                        ".auth-submit"
                    );

                setLoading(
                    submitButton,
                    "Signing in..."
                );


                setTimeout(() => {

                    stopLoading(
                        submitButton,
                        "Login",
                        "↗"
                    );

                    showMessage(
                        message,
                        "Login is ready for backend connection.",
                        "success"
                    );

                }, 700);

            }
        );

    }


    const registerForm =
        document.getElementById(
            "registerForm"
        );


    if (registerForm) {

        registerForm.addEventListener(
            "submit",
            (event) => {

                event.preventDefault();

                const name =
                    document.getElementById(
                        "registerName"
                    );

                const email =
                    document.getElementById(
                        "registerEmail"
                    );

                const password =
                    document.getElementById(
                        "registerPassword"
                    );

                const confirmPassword =
                    document.getElementById(
                        "confirmPassword"
                    );

                const terms =
                    document.getElementById(
                        "acceptTerms"
                    );

                const nameError =
                    document.getElementById(
                        "registerNameError"
                    );

                const emailError =
                    document.getElementById(
                        "registerEmailError"
                    );

                const passwordError =
                    document.getElementById(
                        "registerPasswordError"
                    );

                const confirmError =
                    document.getElementById(
                        "confirmPasswordError"
                    );

                const message =
                    document.getElementById(
                        "registerMessage"
                    );

                clearErrors(
                    [
                        nameError,
                        emailError,
                        passwordError,
                        confirmError
                    ],
                    [
                        name,
                        email,
                        password,
                        confirmPassword
                    ]
                );

                hideMessage(message);

                let valid = true;


                if (
                    name.value.trim().length < 2
                ) {

                    showFieldError(
                        name,
                        nameError,
                        "Please enter your full name."
                    );

                    valid = false;

                }


                if (
                    !email.value.trim()
                ) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter your email."
                    );

                    valid = false;

                } else if (
                    !isValidEmail(
                        email.value.trim()
                    )
                ) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter a valid email."
                    );

                    valid = false;

                }


                if (
                    password.value.length < 8
                ) {

                    showFieldError(
                        password,
                        passwordError,
                        "Password must contain at least 8 characters."
                    );

                    valid = false;

                }


                if (
                    password.value !==
                    confirmPassword.value
                ) {

                    showFieldError(
                        confirmPassword,
                        confirmError,
                        "Passwords do not match."
                    );

                    valid = false;

                }


                if (!terms.checked) {

                    showMessage(
                        message,
                        "Please accept the Terms and Privacy Policy.",
                        "error"
                    );

                    valid = false;

                }


                if (!valid) {
                    return;
                }


                const submitButton =
                    registerForm.querySelector(
                        ".auth-submit"
                    );

                setLoading(
                    submitButton,
                    "Creating..."
                );

                registerForm.submit();
                // setTimeout(() => {

                //     stopLoading(
                //         submitButton,
                //         "Create Account",
                //         "↗"
                //     );

                //     showMessage(
                //         message,
                //         "Account form is ready for backend connection.",
                //         "success"
                //     );

                // }, 700);

            }
        );

    }


    const forgotForm =
        document.getElementById(
            "forgotForm"
        );


    if (forgotForm) {

        forgotForm.addEventListener(
            "submit",
            (event) => {

                event.preventDefault();

                const email =
                    document.getElementById(
                        "forgotEmail"
                    );

                const emailError =
                    document.getElementById(
                        "forgotEmailError"
                    );

                const message =
                    document.getElementById(
                        "forgotMessage"
                    );

                clearErrors(
                    [emailError],
                    [email]
                );

                hideMessage(message);


                if (!email.value.trim()) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter your email."
                    );

                    return;

                }


                if (
                    !isValidEmail(
                        email.value.trim()
                    )
                ) {

                    showFieldError(
                        email,
                        emailError,
                        "Please enter a valid email."
                    );

                    return;

                }


                const submitButton =
                    forgotForm.querySelector(
                        ".auth-submit"
                    );

                setLoading(
                    submitButton,
                    "Sending..."
                );


                setTimeout(() => {

                    stopLoading(
                        submitButton,
                        "Send Reset Link",
                        "↗"
                    );

                    showMessage(
                        message,
                        "Password reset is ready for backend connection.",
                        "success"
                    );

                }, 700);

            }
        );

    }


    function updatePasswordStrength(
        password,
        container
    ) {

        container.className =
            "password-strength";


        if (!password) {

            container.querySelector(
                ".strength-text"
            ).textContent =
                "Use 8 or more characters";

            return;

        }


        let score = 0;


        if (password.length >= 8) {
            score++;
        }

        if (password.length >= 12) {
            score++;
        }

        if (/[A-Z]/.test(password)) {
            score++;
        }

        if (/[0-9]/.test(password)) {
            score++;
        }

        if (/[^A-Za-z0-9]/.test(password)) {
            score++;
        }


        const text =
            container.querySelector(
                ".strength-text"
            );


        if (score <= 1) {

            container.classList.add("weak");

            text.textContent =
                "Weak password";

        } else if (score <= 2) {

            container.classList.add("medium");

            text.textContent =
                "Medium password";

        } else if (score <= 3) {

            container.classList.add("strong");

            text.textContent =
                "Strong password";

        } else {

            container.classList.add(
                "very-strong"
            );

            text.textContent =
                "Very strong password";

        }

    }


    function isValidEmail(email) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            .test(email);

    }


    function showFieldError(
        input,
        errorElement,
        message
    ) {

        const group =
            input.closest(".form-group");

        if (group) {
            group.classList.add("has-error");
        }

        errorElement.textContent =
            message;

    }


    function clearErrors(
        errorElements,
        inputs
    ) {

        errorElements.forEach(
            (element) => {

                if (element) {
                    element.textContent = "";
                }

            }
        );


        inputs.forEach(
            (input) => {

                if (!input) {
                    return;
                }

                const group =
                    input.closest(
                        ".form-group"
                    );

                if (group) {

                    group.classList.remove(
                        "has-error",
                        "has-success"
                    );

                }

            }
        );

    }


    function showMessage(
        element,
        message,
        type
    ) {

        if (!element) {
            return;
        }

        element.textContent =
            message;

        element.className =
            "form-message show " + type;

    }


    function hideMessage(element) {

        if (!element) {
            return;
        }

        element.textContent = "";

        element.className =
            "form-message";

    }


    function setLoading(
        button,
        text
    ) {

        if (!button) {
            return;
        }

        button.disabled = true;

        button.dataset.originalText =
            button.innerHTML;

        button.innerHTML =
            `<span>${text}</span>
             <span class="submit-arrow">...</span>`;

    }


    function stopLoading(
        button,
        text,
        arrow
    ) {

        if (!button) {
            return;
        }

        button.disabled = false;

        button.innerHTML =
            `<span>${text}</span>
             <span class="submit-arrow">${arrow}</span>`;

    }

});

