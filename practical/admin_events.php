
<?php
require_once __DIR__ . "/event_helpers.php";

$stmt = $pdo->query(
    "SELECT event_id, title, description, category, event_date, venue, poster
     FROM events
     ORDER BY event_date DESC"
);

$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .event-admin { max-width: 1100px; margin: 30px auto; padding: 20px; }
        .event-admin table { width: 100%; border-collapse: collapse; }
        .event-admin th, .event-admin td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .event-admin img { width: 100px; height: 70px; object-fit: cover; }
        .event-admin .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .event-admin a, .event-admin button { cursor: pointer; }
        .event-admin .add-link { display: inline-block; margin-bottom: 20px; padding: 10px 15px; background: #2457a7; color: white; text-decoration: none; border-radius: 5px; }
        .event-admin form { margin: 0; }
    </style>
</head>
<body>
<main class="event-admin">
    <h1>Manage Events</h1>
    <p>Signed in as <?= event_escape($_SESSION["username"] ?? "Admin") ?></p>
    <a class="add-link" href="add_event.php">Add New Event</a>
    <a href="admin_dashboard.php">Back to Dashboard</a>

    <?php if (!$events): ?>
        <p>No events have been added yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td>
                        <?php if (!empty($event["poster"])): ?>
                            <img src="<?= event_escape($event["poster"]) ?>" alt="Event poster">
                        <?php else: ?>
                            No poster
                        <?php endif; ?>
                    </td>
                    <td><?= event_escape($event["title"]) ?></td>
                    <td><?= event_escape($event["category"]) ?></td>
                    <td><?= event_escape($event["event_date"]) ?></td>
                    <td><?= event_escape($event["venue"]) ?></td>
                    <td>
                        <div class="actions">
                            <a href="edit_event.php?id=<?= (int) $event["event_id"] ?>">Edit</a>
                            <form action="delete_event.php" method="post" onsubmit="return confirm('Delete this event?');">
                                <input type="hidden" name="csrf_token" value="<?= event_escape($_SESSION["csrf_token"]) ?>">
                                <input type="hidden" name="id" value="<?= (int) $event["event_id"] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
