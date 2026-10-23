
<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

require_login($pdo, "admin");

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events API - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .api-events { max-width: 1000px; margin: 30px auto; padding: 20px; }
        .api-events input, .api-events textarea { width: 100%; max-width: 500px; padding: 9px; margin: 5px 0 12px; }
        .api-events button { margin: 4px; padding: 8px 12px; cursor: pointer; }
        .api-events article { border: 1px solid #ddd; padding: 15px; margin: 12px 0; border-radius: 6px; }
        #message { margin: 15px 0; }
    </style>
</head>
<body>
<main class="api-events">
    <h1>Manage Events</h1>
    <p id="message" role="status" aria-live="polite"></p>

    <form id="eventForm">
        <input type="hidden" id="event_id">

        <label for="title">Event Title</label>
        <input type="text" id="title" maxlength="150" required>

        <label for="description">Description</label>
        <textarea id="description" rows="3" required></textarea>

        <label for="category">Category</label>
        <input type="text" id="category" maxlength="100" required>

        <label for="event_date">Date and Time</label>
        <input type="datetime-local" id="event_date" required>

        <label for="venue">Venue</label>
        <input type="text" id="venue" maxlength="200" required>

        <button type="submit" id="saveButton">Add Event</button>
        <button type="button" id="cancelButton" hidden>Cancel Edit</button>
    </form>

    <hr>

    <h2>Events List</h2>
    <button type="button" id="refreshButton">Refresh Events</button>
    <section id="eventsList" aria-live="polite"></section>
</main>

<script>
const API_URL = "api/events.php";
const CSRF_TOKEN = <?= json_encode($_SESSION["csrf_token"]) ?>;

const form = document.getElementById("eventForm");
const message = document.getElementById("message");
const eventsList = document.getElementById("eventsList");
const saveButton = document.getElementById("saveButton");
const cancelButton = document.getElementById("cancelButton");

function showMessage(text) {
    message.textContent = text;
}

async function apiRequest(method, data = null) {
    const options = {
        method,
        headers: {
            "Accept": "application/json"
        }
    };

    if (data !== null) {
        options.headers["Content-Type"] = "application/json";
        options.headers["X-CSRF-Token"] = CSRF_TOKEN;
        options.body = JSON.stringify(data);
    }

    const response = await fetch(API_URL, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}

function addTextElement(parent, tag, text, className = "") {
    const element = document.createElement(tag);
    element.textContent = text;

    if (className) {
        element.className = className;
    }

    parent.appendChild(element);
    return element;
}

async function loadEvents() {
    eventsList.replaceChildren();
    addTextElement(eventsList, "p", "Loading events...");

    try {
        const result = await apiRequest("GET");
        eventsList.replaceChildren();

        if (result.events.length === 0) {
            addTextElement(eventsList, "p", "No events found.");
            return;
        }

        result.events.forEach(event => {
            const article = document.createElement("article");

            addTextElement(article, "h3", event.title);
            addTextElement(article, "p", event.description);
            addTextElement(article, "p", "Category: " + event.category);
            addTextElement(article, "p", "Date: " + event.event_date);
            addTextElement(article, "p", "Venue: " + event.venue);

            const editButton = document.createElement("button");
            editButton.type = "button";
            editButton.textContent = "Edit";
            editButton.addEventListener("click", () => editEvent(event));
            article.appendChild(editButton);

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.textContent = "Delete";
            deleteButton.addEventListener("click", () => deleteEvent(event.event_id));
            article.appendChild(deleteButton);

            eventsList.appendChild(article);
        });
    } catch (error) {
        eventsList.replaceChildren();
        showMessage(error.message);
    }
}

function editEvent(event) {
    document.getElementById("event_id").value = event.event_id;
    document.getElementById("title").value = event.title;
    document.getElementById("description").value = event.description;
    document.getElementById("category").value = event.category;
    document.getElementById("venue").value = event.venue;

    const date = new Date(event.event_date.replace(" ", "T"));
    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    document.getElementById("event_date").value = localDate.toISOString().slice(0, 16);

    saveButton.textContent = "Update Event";
    cancelButton.hidden = false;
    showMessage("Editing event #" + event.event_id);
    form.scrollIntoView({ behavior: "smooth" });
}

function resetForm() {
    form.reset();
    document.getElementById("event_id").value = "";
    saveButton.textContent = "Add Event";
    cancelButton.hidden = true;
}

cancelButton.addEventListener("click", resetForm);

form.addEventListener("submit", async event => {
    event.preventDefault();

    const id = document.getElementById("event_id").value;

    const data = {
        title: document.getElementById("title").value.trim(),
        description: document.getElementById("description").value.trim(),
        category: document.getElementById("category").value.trim(),
        event_date: document.getElementById("event_date").value,
        venue: document.getElementById("venue").value.trim()
    };

    if (id) {
        data.event_id = Number(id);
    }

    try {
        const result = await apiRequest(id ? "PUT" : "POST", data);
        showMessage(result.message);
        resetForm();
        await loadEvents();
    } catch (error) {
        showMessage(error.message);
    }
});

async function deleteEvent(id) {
    if (!confirm("Are you sure you want to delete this event?")) {
        return;
    }

    try {
        const result = await apiRequest("DELETE", {
            event_id: Number(id)
        });

        showMessage(result.message);
        await loadEvents();
    } catch (error) {
        showMessage(error.message);
    }
}

document.getElementById("refreshButton").addEventListener("click", loadEvents);

loadEvents();
</script>
</body>
</html>
