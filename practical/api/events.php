
<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function api_response($status, $data)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function read_json()
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        api_response(400, ["success" => false, "message" => "Invalid JSON data."]);
    }

    return $data;
}

function validate_event($data)
{
    $title = trim($data["title"] ?? "");
    $description = trim($data["description"] ?? "");
    $category = trim($data["category"] ?? "");
    $eventDate = trim($data["event_date"] ?? "");
    $venue = trim($data["venue"] ?? "");

    if ($title === "" || mb_strlen($title) > 150) {
        api_response(422, ["success" => false, "message" => "Title is required and must be at most 150 characters."]);
    }

    if ($description === "") {
        api_response(422, ["success" => false, "message" => "Description is required."]);
    }

    if ($category === "" || mb_strlen($category) > 100) {
        api_response(422, ["success" => false, "message" => "Category is required and must be at most 100 characters."]);
    }

    if ($venue === "" || mb_strlen($venue) > 200) {
        api_response(422, ["success" => false, "message" => "Venue is required and must be at most 200 characters."]);
    }

    $date = DateTime::createFromFormat("Y-m-d\TH:i", $eventDate);

    if (!$date || $date->format("Y-m-d\TH:i") !== $eventDate) {
        api_response(422, ["success" => false, "message" => "Enter a valid event date and time."]);
    }

    return [
        "title" => $title,
        "description" => $description,
        "category" => $category,
        "event_date" => $date->format("Y-m-d H:i:s"),
        "venue" => $venue
    ];
}

try {
    require_login($pdo, "admin");

    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    $method = $_SERVER["REQUEST_METHOD"];

    if ($method !== "GET") {
        $submittedToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
        $storedToken = $_SESSION["csrf_token"] ?? "";

        if (
            !is_string($submittedToken) ||
            !is_string($storedToken) ||
            $storedToken === "" ||
            !hash_equals($storedToken, $submittedToken)
        ) {
            api_response(403, ["success" => false, "message" => "Invalid security token. Refresh the page and try again."]);
        }
    }

    if ($method === "GET") {
        $stmt = $pdo->query(
            "SELECT event_id, title, description, category, event_date, venue
             FROM events
             ORDER BY event_date DESC"
        );

        api_response(200, [
            "success" => true,
            "events" => $stmt->fetchAll()
        ]);
    }

    if ($method === "POST") {
        $data = validate_event(read_json());

        $stmt = $pdo->prepare(
            "INSERT INTO events (title, description, category, event_date, venue)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data["title"],
            $data["description"],
            $data["category"],
            $data["event_date"],
            $data["venue"]
        ]);

        api_response(201, [
            "success" => true,
            "message" => "Event added successfully.",
            "event_id" => (int) $pdo->lastInsertId()
        ]);
    }

    if ($method === "PUT") {
        $data = read_json();
        $id = filter_var($data["event_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            api_response(422, ["success" => false, "message" => "Valid event ID is required."]);
        }

        $event = validate_event($data);

        $stmt = $pdo->prepare(
            "UPDATE events
             SET title = ?, description = ?, category = ?, event_date = ?, venue = ?
             WHERE event_id = ?"
        );

        $stmt->execute([
            $event["title"],
            $event["description"],
            $event["category"],
            $event["event_date"],
            $event["venue"],
            $id
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ?");
            $check->execute([$id]);

            if (!$check->fetch()) {
                api_response(404, ["success" => false, "message" => "Event not found."]);
            }
        }

        api_response(200, ["success" => true, "message" => "Event updated successfully."]);
    }

    if ($method === "DELETE") {
        $data = read_json();
        $id = filter_var($data["event_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            api_response(422, ["success" => false, "message" => "Valid event ID is required."]);
        }

        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            api_response(404, ["success" => false, "message" => "Event not found."]);
        }

        api_response(200, ["success" => true, "message" => "Event deleted successfully."]);
    }

    header("Allow: GET, POST, PUT, DELETE");
    api_response(405, ["success" => false, "message" => "Method not allowed."]);
} catch (Throwable $error) {
    error_log("Events API error: " . $error->getMessage());
    api_response(500, ["success" => false, "message" => "A server error occurred."]);
}
