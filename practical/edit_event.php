
<?php
require_once __DIR__ . "/event_helpers.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid event ID.");
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    exit("Event not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPoster = null;

    try {
        verify_event_csrf();
        $data = validate_event_fields();
        $newPoster = save_event_poster();

        if ($newPoster !== null) {
            $poster = $newPoster;
        } else {
            $poster = $event["poster"];
        }

        $stmt = $pdo->prepare(
            "UPDATE events
             SET title = :title,
                 description = :description,
                 category = :category,
                 event_date = :event_date,
                 venue = :venue,
                 poster = :poster
             WHERE event_id = :id"
        );

        $stmt->execute([
            ":title" => $data["title"],
            ":description" => $data["description"],
            ":category" => $data["category"],
            ":event_date" => $data["event_date"],
            ":venue" => $data["venue"],
            ":poster" => $poster,
            ":id" => $id
        ]);

        if ($newPoster !== null && !empty($event["poster"])) {
            remove_event_poster($event["poster"]);
        }

        header("Location: admin_events.php");
        exit;
    } catch (Throwable $exception) {
        if ($newPoster !== null) {
            remove_event_poster($newPoster);
        }

        if ($exception instanceof RuntimeException) {
            $error = $exception->getMessage();
        } else {
            error_log("Event update failed: " . $exception->getMessage());
            $error = "Could not update the event. Check the database setup.";
        }
    }
}

$title = $_POST["title"] ?? $event["title"];
$description = $_POST["description"] ?? $event["description"];
$category = $_POST["category"] ?? $event["category"];
$venue = $_POST["venue"] ?? $event["venue"];
$dateValue = $_POST["event_date"] ?? date("Y-m-d\TH:i", strtotime($event["event_date"]));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="event-admin">
    <h1>Edit Event</h1>

    <?php if ($error !== ""): ?>
        <p role="alert"><?= event_escape($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($event["poster"])): ?>
        <p>Current poster:</p>
        <img src="<?= event_escape($event["poster"]) ?>" alt="Current event poster" width="180">
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= event_escape($_SESSION["csrf_token"]) ?>">

        <p>
            <label for="title">Title</label><br>
            <input id="title" name="title" maxlength="150" required value="<?= event_escape($title) ?>">
        </p>

        <p>
            <label for="description">Description</label><br>
            <textarea id="description" name="description" required rows="5"><?= event_escape($description) ?></textarea>
        </p>

        <p>
            <label for="category">Category</label><br>
            <input id="category" name="category" maxlength="100" required value="<?= event_escape($category) ?>">
        </p>

        <p>
            <label for="event_date">Date and time</label><br>
            <input id="event_date" type="datetime-local" name="event_date" required value="<?= event_escape($dateValue) ?>">
        </p>

        <p>
            <label for="venue">Venue</label><br>
            <input id="venue" name="venue" maxlength="200" required value="<?= event_escape($venue) ?>">
        </p>

        <p>
            <label for="poster">Replace poster (JPG, PNG, WEBP; maximum 2 MB)</label><br>
            <input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </p>

        <button type="submit">Save Changes</button>
        <a href="admin_events.php">Cancel</a>
    </form>
</main>
</body>
</html>
