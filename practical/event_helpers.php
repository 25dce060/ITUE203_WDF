
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

require_login($pdo, "admin");

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function event_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function verify_event_csrf()
{
    $submitted = $_POST["csrf_token"] ?? "";
    $stored = $_SESSION["csrf_token"] ?? "";

    if (
        !is_string($submitted) ||
        !is_string($stored) ||
        $stored === "" ||
        !hash_equals($stored, $submitted)
    ) {
        http_response_code(403);
        exit("Invalid request token. Please go back and try again.");
    }
}

function validate_event_fields()
{
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $eventDate = trim($_POST["event_date"] ?? "");
    $venue = trim($_POST["venue"] ?? "");

    if ($title === "" || mb_strlen($title) > 150) {
        throw new RuntimeException("Title is required and must be 150 characters or fewer.");
    }

    if ($description === "") {
        throw new RuntimeException("Description is required.");
    }

    if ($category === "" || mb_strlen($category) > 100) {
        throw new RuntimeException("Category is required and must be 100 characters or fewer.");
    }

    if ($venue === "" || mb_strlen($venue) > 200) {
        throw new RuntimeException("Venue is required and must be 200 characters or fewer.");
    }

    $date = DateTime::createFromFormat("Y-m-d\TH:i", $eventDate);

    if (!$date || $date->format("Y-m-d\TH:i") !== $eventDate) {
        throw new RuntimeException("Please enter a valid event date and time.");
    }

    return [
        "title" => $title,
        "description" => $description,
        "category" => $category,
        "event_date" => $date->format("Y-m-d H:i:s"),
        "venue" => $venue
    ];
}

function save_event_poster()
{
    if (
        !isset($_FILES["poster"]) ||
        $_FILES["poster"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    $file = $_FILES["poster"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("The poster upload failed.");
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        throw new RuntimeException("Invalid uploaded file.");
    }

    if ($file["size"] <= 0 || $file["size"] > 2 * 1024 * 1024) {
        throw new RuntimeException("Poster size must be between 1 byte and 2 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPG, JPEG, PNG, and WEBP posters are allowed.");
    }

    $directory = __DIR__ . "/uploads/posters";

    if (
        !is_dir($directory) &&
        !mkdir($directory, 0755, true) &&
        !is_dir($directory)
    ) {
        throw new RuntimeException("Could not create the poster upload directory.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    $destination = $directory . "/" . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        throw new RuntimeException("Could not save the poster.");
    }

    return "uploads/posters/" . $filename;
}

function remove_event_poster($path)
{
    if (!is_string($path) || $path === "") {
        return;
    }

    $directory = realpath(__DIR__ . "/uploads/posters");
    $file = realpath(__DIR__ . "/" . $path);

    if (
        $directory !== false &&
        $file !== false &&
        str_starts_with($file, $directory . DIRECTORY_SEPARATOR) &&
        is_file($file)
    ) {
        unlink($file);
    }
}
