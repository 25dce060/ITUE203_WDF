document.addEventListener("DOMContentLoaded", function () {

    const body = document.body;


    const themeButtons = document.querySelectorAll(".theme-toggle");

    function setTheme(theme) {
        const dark = theme === "dark";

        body.classList.toggle("dark-theme", dark);

        themeButtons.forEach(function (button) {
            button.textContent = dark ? "☀️" : "🌙";
            button.setAttribute(
                "aria-label",
                dark ? "Switch to light theme" : "Switch to dark theme"
            );
            button.setAttribute("aria-pressed", String(dark));
        });

        try {
            localStorage.setItem("studenthub-theme", theme);
        } catch (error) {
            return;
        }
    }

    let savedTheme = "light";

    try {
        savedTheme = localStorage.getItem("studenthub-theme") || "light";
    } catch (error) {
        savedTheme = "light";
    }

    setTheme(savedTheme);

    themeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const nextTheme = body.classList.contains("dark-theme")
                ? "light"
                : "dark";

            setTheme(nextTheme);
        });
    });


    document.querySelectorAll(".hamburger").forEach(function (button) {
        button.addEventListener("click", function () {
            const header = button.closest(".site-header");

            if (!header) {
                return;
            }

            const open = header.classList.toggle("menu-open");

            button.textContent = open ? "×" : "☰";
            button.setAttribute("aria-expanded", String(open));
            button.setAttribute(
                "aria-label",
                open ? "Close navigation menu" : "Open navigation menu"
            );
        });
    });


    document.querySelectorAll(".notification-banner").forEach(function (banner) {
        const closeButton = banner.querySelector(".notification-close");

        if (!closeButton) {
            return;
        }

        closeButton.addEventListener("click", function () {
            banner.classList.add("is-hidden");
        });
    });


    const modal = document.querySelector(".modal");
    let lastFocusedElement = null;

    function openModal() {
        if (!modal) {
            return;
        }

        lastFocusedElement = document.activeElement;

        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");

        const closeButton = modal.querySelector(".modal-close");

        if (closeButton) {
            closeButton.focus();
        }
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");

        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    document.querySelectorAll(".modal-open").forEach(function (button) {
        button.addEventListener("click", function () {
            if (!modal) {
                return;
            }

            const title = modal.querySelector(".modal-title");
            const content = modal.querySelector(".modal-content");

            if (title && button.dataset.modalTitle) {
                title.textContent = button.dataset.modalTitle;
            }

            if (content && button.dataset.modalContent) {
                content.textContent = button.dataset.modalContent;
            }

            openModal();
        });
    });

    if (modal) {
        modal.querySelectorAll(".modal-close").forEach(function (button) {
            button.addEventListener("click", closeModal);
        });

        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape" &&
            modal &&
            modal.classList.contains("is-open")
        ) {
            closeModal();
        }
    });


    document.querySelectorAll(".faq-item").forEach(function (item) {
        const question = item.querySelector(".faq-question");

        if (!question) {
            return;
        }

        question.setAttribute("aria-expanded", "false");

        question.addEventListener("click", function () {
            const willOpen = !item.classList.contains("is-open");
            const group = item.parentElement;

            if (group) {
                group.querySelectorAll(".faq-item").forEach(
                    function (otherItem) {
                        otherItem.classList.remove("is-open");

                        const otherQuestion =
                            otherItem.querySelector(".faq-question");

                        if (otherQuestion) {
                            otherQuestion.setAttribute(
                                "aria-expanded",
                                "false"
                            );
                        }
                    }
                );
            }

            if (willOpen) {
                item.classList.add("is-open");
                question.setAttribute("aria-expanded", "true");
            }
        });
    });


    document.querySelectorAll(".event-slider").forEach(function (slider) {
        const slides = Array.from(
            slider.querySelectorAll(".slider-slide")
        );

        const previous = slider.querySelector(".slider-prev");
        const next = slider.querySelector(".slider-next");

        const dots = Array.from(
            slider.querySelectorAll(".slider-dot")
        );

        let current = 0;
        let timer = null;

        if (slides.length === 0) {
            return;
        }

        function showSlide(index) {
            current = (index + slides.length) % slides.length;

            slides.forEach(function (slide, i) {
                const active = i === current;

                slide.classList.toggle("is-active", active);
                slide.setAttribute("aria-hidden", String(!active));
            });

            dots.forEach(function (dot, i) {
                const active = i === current;

                dot.classList.toggle("is-active", active);
                dot.setAttribute("aria-current", String(active));
            });
        }

        function startTimer() {
            window.clearInterval(timer);

            if (
                window.matchMedia("(prefers-reduced-motion: reduce)").matches
            ) {
                return;
            }

            timer = window.setInterval(function () {
                showSlide(current + 1);
            }, 5000);
        }

        if (previous) {
            previous.addEventListener("click", function () {
                showSlide(current - 1);
                startTimer();
            });
        }

        if (next) {
            next.addEventListener("click", function () {
                showSlide(current + 1);
                startTimer();
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener("click", function () {
                showSlide(index);
                startTimer();
            });
        });

        slider.addEventListener("mouseenter", function () {
            window.clearInterval(timer);
        });

        slider.addEventListener("mouseleave", startTimer);

        showSlide(0);
        startTimer();
    });


    const dynamicPage = document.querySelector(".dynamic-container");

    if (dynamicPage) {

        const pageSize = 3;

        const configurations = {
            events: {
                file: "../data/events.json",
                listId: "eventList",
                paginationId: "eventPagination",
                searchId: "eventSearch",
                filterId: "eventFilter",
                sortId: "eventSort",
                filterKey: "category",
                filterLabel: "All Categories"
            },

            students: {
                file: "../data/students.json",
                listId: "studentList",
                paginationId: "studentPagination",
                searchId: "studentSearch",
                filterId: "studentFilter",
                sortId: "studentSort",
                filterKey: "course",
                filterLabel: "All Courses"
            },

            notices: {
                file: "../data/notices.json",
                listId: "noticeList",
                paginationId: "noticePagination",
                searchId: "noticeSearch",
                filterId: "noticeFilter",
                sortId: "noticeSort",
                filterKey: "type",
                filterLabel: "All Types"
            },

            faqs: {
                file: "../data/faqs.json",
                listId: "faqList",
                paginationId: "faqPagination",
                searchId: "faqSearch",
                filterId: "faqFilter",
                sortId: "faqSort",
                filterKey: "category",
                filterLabel: "All Categories"
            }
        };

        const state = {};

        Object.keys(configurations).forEach(function (key) {
            const config = configurations[key];
            const sortControl = document.getElementById(config.sortId);

            state[key] = {
                data: [],
                page: 1,
                search: "",
                filter: "All",
                sort: sortControl ? sortControl.value : "title-asc",
                loaded: false
            };
        });


        function showStatus(container, text) {
            const paragraph = document.createElement("p");

            paragraph.className = "data-status";
            paragraph.textContent = text;

            container.replaceChildren(paragraph);
        }


        function createTextElement(tag, text, className) {
            const element = document.createElement(tag);

            element.textContent = text == null ? "" : String(text);

            if (className) {
                element.className = className;
            }

            return element;
        }


        function formatDate(value) {
            const date = new Date(value + "T00:00:00");

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleDateString("en-IN", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            });
        }


        function createMetadata(values) {
            const meta = document.createElement("div");

            meta.className = "data-meta";

            values.forEach(function (value, index) {
                if (value === undefined || value === null || value === "") {
                    return;
                }

                const span = createTextElement("span", value);

                if (index === 0) {
                    span.className = "data-tag";
                }

                meta.appendChild(span);
            });

            return meta;
        }


        function createCard(type, item) {
            const card = document.createElement("article");

            card.className = "data-card";

            if (type === "events") {
                card.appendChild(
                    createTextElement("h3", item.title)
                );

                card.appendChild(
                    createMetadata([
                        item.category,
                        formatDate(item.date),
                        item.venue
                    ])
                );

                card.appendChild(
                    createTextElement("p", item.description)
                );
            }

            if (type === "students") {
                card.appendChild(
                    createTextElement("h3", item.name)
                );

                card.appendChild(
                    createMetadata([
                        item.id,
                        item.course,
                        "Year " + item.year
                    ])
                );

                card.appendChild(
                    createTextElement("p", item.email)
                );
            }

            if (type === "notices") {
                card.appendChild(
                    createTextElement("h3", item.title)
                );

                card.appendChild(
                    createMetadata([
                        item.type,
                        formatDate(item.date)
                    ])
                );

                card.appendChild(
                    createTextElement("p", item.description)
                );
            }

            if (type === "faqs") {
                card.appendChild(
                    createTextElement("h3", item.question)
                );

                card.appendChild(
                    createMetadata([item.category])
                );

                card.appendChild(
                    createTextElement("p", item.answer)
                );
            }

            return card;
        }


        function createFilterOptions(type) {
            const config = configurations[type];
            const select = document.getElementById(config.filterId);

            const values = Array.from(
                new Set(
                    state[type].data.map(function (item) {
                        return item[config.filterKey];
                    })
                )
            ).filter(Boolean).sort(function (a, b) {
                return String(a).localeCompare(String(b));
            });

            select.replaceChildren();

            const allOption = document.createElement("option");

            allOption.value = "All";
            allOption.textContent = config.filterLabel;

            select.appendChild(allOption);

            values.forEach(function (value) {
                const option = document.createElement("option");

                option.value = value;
                option.textContent = value;

                select.appendChild(option);
            });
        }


        function getSortedData(type) {
            const config = configurations[type];
            const current = state[type];

            const filtered = current.data.filter(function (item) {
                const searchable = Object.values(item)
                    .join(" ")
                    .toLowerCase();

                const matchesSearch = searchable.includes(
                    current.search.toLowerCase()
                );

                const matchesFilter =
                    current.filter === "All" ||
                    item[config.filterKey] === current.filter;

                return matchesSearch && matchesFilter;
            });

            const parts = current.sort.split("-");
            const direction = parts.pop();
            const field = parts.join("-");

            return filtered.sort(function (a, b) {
                let first = a[field];
                let second = b[field];

                if (field === "year") {
                    first = Number(first);
                    second = Number(second);
                } else {
                    first = String(first ?? "").toLowerCase();
                    second = String(second ?? "").toLowerCase();
                }

                let comparison = 0;

                if (first < second) {
                    comparison = -1;
                } else if (first > second) {
                    comparison = 1;
                }

                return direction === "desc"
                    ? -comparison
                    : comparison;
            });
        }


        function createPagination(type, totalPages) {
            const config = configurations[type];
            const container = document.getElementById(
                config.paginationId
            );

            const currentPage = state[type].page;

            container.replaceChildren();

            if (totalPages <= 1) {
                return;
            }

            const previous = document.createElement("button");

            previous.type = "button";
            previous.textContent = "Previous";
            previous.disabled = currentPage === 1;

            previous.addEventListener("click", function () {
                state[type].page--;
                render(type);
            });

            container.appendChild(previous);

            for (let page = 1; page <= totalPages; page++) {
                const button = document.createElement("button");

                button.type = "button";
                button.textContent = page;

                if (page === currentPage) {
                    button.classList.add("active");
                    button.setAttribute("aria-current", "page");
                }

                button.addEventListener("click", function () {
                    state[type].page = page;
                    render(type);
                });

                container.appendChild(button);
            }

            const next = document.createElement("button");

            next.type = "button";
            next.textContent = "Next";
            next.disabled = currentPage === totalPages;

            next.addEventListener("click", function () {
                state[type].page++;
                render(type);
            });

            container.appendChild(next);
        }


        function render(type) {
            const config = configurations[type];
            const current = state[type];
            const list = document.getElementById(config.listId);

            if (!current.loaded) {
                showStatus(list, "Loading data...");
                return;
            }

            const records = getSortedData(type);

            const totalPages = Math.ceil(records.length / pageSize);

            if (current.page > Math.max(totalPages, 1)) {
                current.page = Math.max(totalPages, 1);
            }

            list.replaceChildren();

            if (records.length === 0) {
                showStatus(list, "No matching records found.");

                document.getElementById(
                    config.paginationId
                ).replaceChildren();

                return;
            }

            const start = (current.page - 1) * pageSize;

            const visibleRecords = records.slice(
                start,
                start + pageSize
            );

            visibleRecords.forEach(function (item) {
                list.appendChild(createCard(type, item));
            });

            createPagination(type, totalPages);
        }


        function loadData(type) {
            const config = configurations[type];
            const list = document.getElementById(config.listId);

            showStatus(list, "Loading data...");

            fetch(config.file)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error(
                            "Unable to load " + config.file
                        );
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (!Array.isArray(data)) {
                        throw new Error(
                            "Expected an array in " + config.file
                        );
                    }

                    state[type].data = data;
                    state[type].loaded = true;

                    createFilterOptions(type);
                    render(type);
                })
                .catch(function (error) {
                    console.error(error);

                    showStatus(
                        list,
                        "Unable to load data. Check the JSON file path and run the website through a local web server."
                    );
                });
        }


        Object.keys(configurations).forEach(function (type) {
            const config = configurations[type];

            const search = document.getElementById(config.searchId);
            const filter = document.getElementById(config.filterId);
            const sort = document.getElementById(config.sortId);

            if (!search || !filter || !sort) {
                return;
            }

            search.addEventListener("input", function () {
                state[type].search = search.value.trim();
                state[type].page = 1;

                render(type);
            });

            filter.addEventListener("change", function () {
                state[type].filter = filter.value;
                state[type].page = 1;

                render(type);
            });

            sort.addEventListener("change", function () {
                state[type].sort = sort.value;
                state[type].page = 1;

                render(type);
            });

            loadData(type);
        });
    }


    const form = document.getElementById("registrationForm");

    if (form) {

        const fields = {
            studentName: document.getElementById("studentName"),
            studentEmail: document.getElementById("studentEmail"),
            mobile: document.getElementById("mobile"),
            course: document.getElementById("course"),
            year: document.getElementById("year"),
            password: document.getElementById("password"),
            confirmPassword: document.getElementById("confirmPassword"),
            terms: document.getElementById("terms")
        };

        const errorIds = {
            studentName: "studentNameError",
            studentEmail: "studentEmailError",
            mobile: "mobileError",
            course: "courseError",
            year: "yearError",
            gender: "genderError",
            password: "passwordError",
            confirmPassword: "confirmPasswordError",
            terms: "termsError"
        };

        const message = document.getElementById(
            "registrationMessage"
        );

        const namePattern = /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/;

        const emailPattern =
            /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

        const mobilePattern = /^[6-9][0-9]{9}$/;

        const passwordPattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,20}$/;

        const allowedCourses = [
            "B.Tech CSE",
            "B.Tech IT",
            "B.Tech ECE",
            "B.Tech Mechanical"
        ];

        const allowedYears = ["1", "2", "3", "4"];

        const allowedGenders = ["Male", "Female", "Other"];


        function showError(key, field, text) {
            const errorElement = document.getElementById(
                errorIds[key]
            );

            if (errorElement) {
                errorElement.textContent = text;
            }

            if (field) {
                field.classList.add("invalid-field");
                field.setAttribute("aria-invalid", "true");
            }

            if (key === "gender") {
                form.querySelectorAll(
                    'input[name="gender"]'
                ).forEach(function (radio) {
                    radio.setAttribute("aria-invalid", "true");
                });
            }
        }


        function clearError(key) {
            const errorElement = document.getElementById(
                errorIds[key]
            );

            if (errorElement) {
                errorElement.textContent = "";
            }

            if (fields[key]) {
                fields[key].classList.remove("invalid-field");
                fields[key].removeAttribute("aria-invalid");
            }

            if (key === "gender") {
                form.querySelectorAll(
                    'input[name="gender"]'
                ).forEach(function (radio) {
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

            const name = fields.studentName.value.trim();
            const email = fields.studentEmail.value.trim();
            const mobile = fields.mobile.value.trim();
            const password = fields.password.value;
            const confirmPassword = fields.confirmPassword.value;

            const selectedGender = form.querySelector(
                'input[name="gender"]:checked'
            );

            if (
                name.length < 2 ||
                name.length > 100 ||
                !namePattern.test(name)
            ) {
                showError(
                    "studentName",
                    fields.studentName,
                    "Enter a valid full name."
                );

                valid = false;
            }

            if (
                email.length > 254 ||
                !emailPattern.test(email)
            ) {
                showError(
                    "studentEmail",
                    fields.studentEmail,
                    "Enter a valid email address."
                );

                valid = false;
            }

            if (!mobilePattern.test(mobile)) {
                showError(
                    "mobile",
                    fields.mobile,
                    "Enter a valid 10-digit Indian mobile number."
                );

                valid = false;
            }

            if (!allowedCourses.includes(fields.course.value)) {
                showError(
                    "course",
                    fields.course,
                    "Please select a valid course."
                );

                valid = false;
            }

            if (!allowedYears.includes(fields.year.value)) {
                showError(
                    "year",
                    fields.year,
                    "Please select your year of study."
                );

                valid = false;
            }

            if (
                !selectedGender ||
                !allowedGenders.includes(selectedGender.value)
            ) {
                showError(
                    "gender",
                    null,
                    "Please select your gender."
                );

                valid = false;
            }

            if (!passwordPattern.test(password)) {
                showError(
                    "password",
                    fields.password,
                    "Use 8–20 characters with uppercase, lowercase, a number and a special character."
                );

                valid = false;
            }

            if (
                confirmPassword === "" ||
                confirmPassword !== password
            ) {
                showError(
                    "confirmPassword",
                    fields.confirmPassword,
                    "Passwords do not match."
                );

                valid = false;
            }

            if (!fields.terms.checked) {
                showError(
                    "terms",
                    fields.terms,
                    "You must accept the Terms and Conditions."
                );

                valid = false;
            }

            return valid;
        }


        form.addEventListener("submit", function (event) {
            if (!validateForm()) {
                event.preventDefault();

                if (message) {
                    message.textContent =
                        "Please correct the errors shown above.";

                    message.className =
                        "registration-message show error";
                }

                const firstInvalid = form.querySelector(
                    '[aria-invalid="true"]'
                );

                if (firstInvalid) {
                    firstInvalid.focus();
                }

                return;
            }

            /*
             * Do not prevent a valid submission.
             * The browser sends the form to register.php using POST.
             */
        });


        fields.studentName.addEventListener("input", function () {
            clearError("studentName");
        });

        fields.studentEmail.addEventListener("input", function () {
            clearError("studentEmail");
        });

        fields.mobile.addEventListener("input", function () {
            fields.mobile.value = fields.mobile.value.replace(/\D/g, "");
            clearError("mobile");
        });

        fields.course.addEventListener("change", function () {
            clearError("course");
        });

        fields.year.addEventListener("change", function () {
            clearError("year");
        });

        fields.password.addEventListener("input", function () {
            clearError("password");

            if (
                fields.confirmPassword.value !== "" &&
                fields.confirmPassword.value === fields.password.value
            ) {
                clearError("confirmPassword");
            }
        });

        fields.confirmPassword.addEventListener("input", function () {
            if (
                fields.confirmPassword.value === fields.password.value
            ) {
                clearError("confirmPassword");
            }
        });

        form.querySelectorAll(
            'input[name="gender"]'
        ).forEach(function (radio) {
            radio.addEventListener("change", function () {
                clearError("gender");
            });
        });

        fields.terms.addEventListener("change", function () {
            clearError("terms");
        });

        form.addEventListener("reset", function () {
            window.setTimeout(clearErrors, 0);
        });
    }

});