document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");

    if (!form) {
        return;
    }

    const fields = {
        name: document.getElementById("studentName"),
        username: document.getElementById("username"),
        email: document.getElementById("studentEmail"),
        mobile: document.getElementById("mobile"),
        course: document.getElementById("course"),
        year: document.getElementById("year"),
        password: document.getElementById("password"),
        confirmPassword: document.getElementById("confirmPassword"),
        terms: document.getElementById("terms")
    };

    const errorIds = {
        name: "studentNameError",
        username: "usernameError",
        email: "studentEmailError",
        mobile: "mobileError",
        course: "courseError",
        year: "yearError",
        gender: "genderError",
        password: "passwordError",
        confirmPassword: "confirmPasswordError",
        terms: "termsError"
    };

    const namePattern = /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/;
    const usernamePattern = /^[A-Za-z0-9_]{4,30}$/;
    const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    const mobilePattern = /^[6-9][0-9]{9}$/;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,20}$/;

    const allowedCourses = [
        "B.Tech CSE",
        "B.Tech IT",
        "B.Tech ECE",
        "B.Tech Mechanical"
    ];

    const allowedYears = ["1", "2", "3", "4"];
    const allowedGenders = ["Male", "Female", "Other"];

    const message = document.getElementById("registrationMessage");

    function showError(key, field, text) {
        const error = document.getElementById(errorIds[key]);

        if (error) {
            error.textContent = text;
        }

        if (field) {
            field.classList.add("invalid-field");
            field.setAttribute("aria-invalid", "true");
        }

        if (key === "gender") {
            form.querySelectorAll('input[name="gender"]').forEach(function (radio) {
                radio.setAttribute("aria-invalid", "true");
            });
        }
    }

    function clearError(key) {
        const error = document.getElementById(errorIds[key]);

        if (error) {
            error.textContent = "";
        }

        if (fields[key]) {
            fields[key].classList.remove("invalid-field");
            fields[key].removeAttribute("aria-invalid");
        }

        if (key === "gender") {
            form.querySelectorAll('input[name="gender"]').forEach(function (radio) {
                radio.removeAttribute("aria-invalid");
            });
        }
    }

    function clearErrors() {
        Object.keys(errorIds).forEach(clearError);

        if (message) {
            message.textContent = "";
            message.className = "registration-message";
        }
    }

    function validateForm() {
        let valid = true;

        clearErrors();

        const name = fields.name.value.trim();
        const username = fields.username.value.trim();
        const email = fields.email.value.trim();
        const mobile = fields.mobile.value.trim();
        const password = fields.password.value;
        const confirmPassword = fields.confirmPassword.value;

        const gender = form.querySelector('input[name="gender"]:checked');

        if (name.length < 2 || name.length > 100 || !namePattern.test(name)) {
            showError("name", fields.name, "Enter a valid full name.");
            valid = false;
        }

        if (!usernamePattern.test(username)) {
            showError("username", fields.username, "Username must be 4–30 characters using letters, numbers or underscores.");
            valid = false;
        }

        if (email.length > 254 || !emailPattern.test(email)) {
            showError("email", fields.email, "Enter a valid email address.");
            valid = false;
        }

        if (!mobilePattern.test(mobile)) {
            showError("mobile", fields.mobile, "Enter a valid 10-digit Indian mobile number.");
            valid = false;
        }

        if (!allowedCourses.includes(fields.course.value)) {
            showError("course", fields.course, "Select a valid course.");
            valid = false;
        }

        if (!allowedYears.includes(fields.year.value)) {
            showError("year", fields.year, "Select your year of study.");
            valid = false;
        }

        if (!gender || !allowedGenders.includes(gender.value)) {
            showError("gender", null, "Select your gender.");
            valid = false;
        }

        if (!passwordPattern.test(password)) {
            showError("password", fields.password, "Use 8–20 characters with uppercase, lowercase, a number and a special character.");
            valid = false;
        }

        if (!confirmPassword || confirmPassword !== password) {
            showError("confirmPassword", fields.confirmPassword, "Passwords do not match.");
            valid = false;
        }

        if (!fields.terms.checked) {
            showError("terms", fields.terms, "You must accept the terms and conditions.");
            valid = false;
        }

        return valid;
    }

    form.addEventListener("submit", function (event) {
        if (!validateForm()) {
            event.preventDefault();

            if (message) {
                message.textContent = "Please correct the errors shown above.";
                message.className = "registration-message show error";
            }

            const firstInvalid = form.querySelector('[aria-invalid="true"]');

            if (firstInvalid) {
                firstInvalid.focus();
            }
        }

        // A valid submission continues to register.php via POST.
    });

    fields.mobile.addEventListener("input", function () {
        fields.mobile.value = fields.mobile.value.replace(/\D/g, "");
        clearError("mobile");
    });

    fields.name.addEventListener("input", function () {
        clearError("name");
    });

    fields.username.addEventListener("input", function () {
        clearError("username");
    });

    fields.email.addEventListener("input", function () {
        clearError("email");
    });

    fields.course.addEventListener("change", function () {
        clearError("course");
    });

    fields.year.addEventListener("change", function () {
        clearError("year");
    });

    fields.password.addEventListener("input", function () {
        clearError("password");

        if (fields.confirmPassword.value === fields.password.value) {
            clearError("confirmPassword");
        }
    });

    fields.confirmPassword.addEventListener("input", function () {
        if (fields.confirmPassword.value === fields.password.value) {
            clearError("confirmPassword");
        }
    });

    form.querySelectorAll('input[name="gender"]').forEach(function (radio) {
        radio.addEventListener("change", function () {
            clearError("gender");
        });
    });

    fields.terms.addEventListener("change", function () {
        clearError("terms");
    });
});