
<?php
require_once __DIR__ . "/event_helpers.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Method not allowed.");
}

verify_event_csrf();

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid event ID.");
}

$stmt = $pdo->prepare("SELECT poster FROM events WHERE event_id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    exit("Event not found.");
}

$stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->execute([$id]);

if (!empty($event["poster"])) {
    remove_event_poster($event["poster"]);
}

header("Location: admin_events.php");
exit;
