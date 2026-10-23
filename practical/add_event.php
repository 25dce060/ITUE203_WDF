
<?php
require_once __DIR__ . "/event_helpers.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $poster = null;

    try {
        verify_event_csrf();
        $data = validate_event_fields();
        $poster = save_event_poster();

        $stmt = $pdo->prepare(
            "INSERT INTO events (title, description, category, event_date, venue, poster)
             VALUES (:title, :description, :category, :event_date, :venue, :poster)"
        );

        $stmt->execute([
            ":title" => $data["title"],
            ":description" => $data["description"],
            ":category" => $data["category"],
            ":event_date" => $data["event_date"],
            ":venue" => $data["venue"],
            ":poster" => $poster
        ]);

        header("Location: admin_events.php");
        exit;
    } catch (Throwable $exception) {
        if ($poster !== null) {
            remove_event_poster($poster);
        }

        if ($exception instanceof RuntimeException) {
            $error = $exception->getMessage();
        } else {
            error_log("Event creation failed: " . $exception->getMessage());
            $error = "Could not add the event. Check the database setup.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="event-admin">
    <h1>Add Event</h1>

    <?php if ($error !== ""): ?>
        <p role="alert"><?= event_escape($error) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= event_escape($_SESSION["csrf_token"]) ?>">

        <p>
            <label for="title">Title</label><br>
            <input id="title" name="title" maxlength="150" required value="<?= event_escape($_POST["title"] ?? "") ?>">
        </p>

        <p>
            <label for="description">Description</label><br>
            <textarea id="description" name="description" required rows="5"><?= event_escape($_POST["description"] ?? "") ?></textarea>
        </p>

        <p>
            <label for="category">Category</label><br>
            <input id="category" name="category" maxlength="100" required value="<?= event_escape($_POST["category"] ?? "") ?>">
        </p>

        <p>
            <label for="event_date">Date and time</label><br>
            <input id="event_date" type="datetime-local" name="event_date" required value="<?= event_escape($_POST["event_date"] ?? "") ?>">
        </p>

        <p>
            <label for="venue">Venue</label><br>
            <input id="venue" name="venue" maxlength="200" required value="<?= event_escape($_POST["venue"] ?? "") ?>">
        </p>

        <p>
            <label for="poster">Event poster (JPG, PNG, WEBP; maximum 2 MB)</label><br>
            <input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </p>

        <button type="submit">Add Event</button>
        <a href="admin_events.php">Cancel</a>
    </form>
</main>
</body>
</html>
