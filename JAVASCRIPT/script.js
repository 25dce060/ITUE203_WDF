document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;

    /* Theme switcher */
    const themeButtons = document.querySelectorAll(".theme-toggle");

    function applyTheme(theme) {
        body.classList.toggle("dark-theme", theme === "dark");
        themeButtons.forEach(function (button) {
            button.textContent = theme === "dark" ? "light" : "dark";
            button.setAttribute(
                "aria-label",
                theme === "dark" ? "Switch to light theme" : "Switch to dark theme"
            );
        });
        localStorage.setItem("depstar-theme", theme);
    }

    const savedTheme = localStorage.getItem("depstar-theme") || "light";
    applyTheme(savedTheme);

    themeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const nextTheme = body.classList.contains("dark-theme") ? "light" : "dark";
            applyTheme(nextTheme);
        });
    });

    /* Hamburger menu */
    const menuButtons = document.querySelectorAll(".hamburger");

    menuButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const header = button.closest(".site-header");
            if (!header) return;

            header.classList.toggle("menu-open");
            const expanded = header.classList.contains("menu-open");
            button.setAttribute("aria-expanded", expanded ? "true" : "false");
        });
    });

    /* Notification banner */
    const notification = document.querySelector(".notification-banner");
    const closeNotification = document.querySelector(".notification-close");

    if (notification && closeNotification) {
        closeNotification.addEventListener("click", function () {
            notification.classList.add("hidden");
            localStorage.setItem("depstar-notification-closed", "true");
        });

        if (localStorage.getItem("depstar-notification-closed") === "true") {
            notification.classList.add("hidden");
        }
    }

    /* Modal popup */
    const modal = document.querySelector(".modal");
    const modalOpenButtons = document.querySelectorAll(".modal-open");
    const modalCloseButtons = document.querySelectorAll(".modal-close");

    function closeModal() {
        if (modal) {
            modal.classList.remove("show");
            modal.setAttribute("aria-hidden", "true");
        }
    }

    modalOpenButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            if (!modal) return;

            const title = button.getAttribute("data-modal-title");
            const content = button.getAttribute("data-modal-content");
            const modalTitle = modal.querySelector(".modal-title");
            const modalContent = modal.querySelector(".modal-content");

            if (modalTitle && title) {
                modalTitle.textContent = title;
            }

            if (modalContent && content) {
                modalContent.textContent = content;
            }

            modal.classList.add("show");
            modal.setAttribute("aria-hidden", "false");
        });
    });

    modalCloseButtons.forEach(function (button) {
        button.addEventListener("click", closeModal);
    });

    if (modal) {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    /* Collapsible FAQ */
    const faqItems = document.querySelectorAll(".faq-item");

    faqItems.forEach(function (item) {
        const question = item.querySelector(".faq-question");

        if (!question) return;

        question.addEventListener("click", function () {
            const wasOpen = item.classList.contains("open");

            faqItems.forEach(function (otherItem) {
                otherItem.classList.remove("open");
                const otherQuestion = otherItem.querySelector(".faq-question");
                if (otherQuestion) {
                    otherQuestion.setAttribute("aria-expanded", "false");
                }
            });

            if (!wasOpen) {
                item.classList.add("open");
                question.setAttribute("aria-expanded", "true");
            }
        });
    });

    /* Image/content slider */
    const sliders = document.querySelectorAll(".slider");

    sliders.forEach(function (slider) {
        const slides = Array.from(slider.querySelectorAll(".slide"));
        const previous = slider.querySelector(".slider-prev");
        const next = slider.querySelector(".slider-next");
        const dots = Array.from(slider.querySelectorAll(".slider-dot"));
        let current = 0;
        let timer;

        if (!slides.length) return;

        function showSlide(index) {
            current = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle("active", slideIndex === current);
            });

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle("active", dotIndex === current);
                dot.setAttribute("aria-current", dotIndex === current ? "true" : "false");
            });
        }

        function startAutoSlide() {
            clearInterval(timer);
            timer = setInterval(function () {
                showSlide(current + 1);
            }, 5000);
        }

        if (previous) {
            previous.addEventListener("click", function () {
                showSlide(current - 1);
                startAutoSlide();
            });
        }

        if (next) {
            next.addEventListener("click", function () {
                showSlide(current + 1);
                startAutoSlide();
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener("click", function () {
                showSlide(index);
                startAutoSlide();
            });
        });

        showSlide(0);
        startAutoSlide();
    });
});

const registrationForm = document.getElementById("registrationForm");

if (registrationForm) {

    registrationForm.addEventListener("submit", function(event) {

        event.preventDefault();

        const name = document.getElementById("name");
        const email = document.getElementById("email");
        const mobile = document.getElementById("mobile");
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirmPassword");
        const course = document.getElementById("course");
        const year = document.getElementById("year");
        const terms = document.getElementById("terms");
        const gender = document.querySelector('input[name="gender"]:checked');

        const nameError = document.getElementById("nameError");
        const emailError = document.getElementById("emailError");
        const mobileError = document.getElementById("mobileError");
        const passwordError = document.getElementById("passwordError");
        const confirmPasswordError = document.getElementById("confirmPasswordError");
        const courseError = document.getElementById("courseError");
        const yearError = document.getElementById("yearError");
        const genderError = document.getElementById("genderError");
        const termsError = document.getElementById("termsError");
        const successMessage = document.getElementById("successMessage");

        nameError.textContent = "";
        emailError.textContent = "";
        mobileError.textContent = "";
        passwordError.textContent = "";
        confirmPasswordError.textContent = "";
        courseError.textContent = "";
        yearError.textContent = "";
        genderError.textContent = "";
        termsError.textContent = "";

        successMessage.textContent = "";
        successMessage.classList.remove("show");

        name.classList.remove("invalid-field");
        email.classList.remove("invalid-field");
        mobile.classList.remove("invalid-field");
        password.classList.remove("invalid-field");
        confirmPassword.classList.remove("invalid-field");
        course.classList.remove("invalid-field");
        year.classList.remove("invalid-field");

        let valid = true;


        const namePattern = /^[A-Za-z]+(?:\s[A-Za-z]+)+$/;

        if (!namePattern.test(name.value.trim())) {

            nameError.textContent =
                "Enter a valid name using letters only.";

            name.classList.add("invalid-field");

            valid = false;
        }


        const emailPattern =
            /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

        if (!emailPattern.test(email.value.trim())) {

            emailError.textContent =
                "Enter a valid email address.";

            email.classList.add("invalid-field");

            valid = false;
        }


        const mobilePattern =
            /^[6-9][0-9]{9}$/;

        if (!mobilePattern.test(mobile.value.trim())) {

            mobileError.textContent =
                "Enter a valid 10-digit mobile number.";

            mobile.classList.add("invalid-field");

            valid = false;
        }


        if (course.value === "") {

            courseError.textContent =
                "Please select a course.";

            course.classList.add("invalid-field");

            valid = false;
        }


        if (year.value === "") {

            yearError.textContent =
                "Please select your year.";

            year.classList.add("invalid-field");

            valid = false;
        }


        if (!gender) {

            genderError.textContent =
                "Please select your gender.";

            valid = false;
        }


        const passwordPattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,20}$/;

        if (!passwordPattern.test(password.value)) {

            passwordError.textContent =
                "Password must contain uppercase, lowercase, number and special character.";

            password.classList.add("invalid-field");

            valid = false;
        }


        if (
            confirmPassword.value === "" ||
            confirmPassword.value !== password.value
        ) {

            confirmPasswordError.textContent =
                "Passwords do not match.";

            confirmPassword.classList.add("invalid-field");

            valid = false;
        }


        if (!terms.checked) {

            termsError.textContent =
                "You must accept the terms and conditions.";

            valid = false;
        }


        if (valid) {

            successMessage.textContent =
                "Registration successful!";

            successMessage.classList.add("show");

            registrationForm.reset();
        }

    });


    document.getElementById("mobile").addEventListener(
        "input",
        function() {
            this.value = this.value.replace(/[^0-9]/g, "");
        }
    );

}
