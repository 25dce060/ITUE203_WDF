<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$action = $_GET["action"] ?? "delete";

if (!$id || $id < 1) {
    msg("error", "Invalid student ID.");
    go("index.php");
}

if ($action === "restore") {
    $stmt = $conn->prepare(
        "UPDATE students SET is_deleted = 0 WHERE id = ? AND is_deleted = 1"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    msg(
        $stmt->affected_rows ? "success" : "error",
        $stmt->affected_rows
            ? "Student restored successfully."
            : "Deleted student not found."
    );
} elseif ($action === "permanent") {
    $stmt = $conn->prepare(
        "DELETE FROM students WHERE id = ? AND is_deleted = 1"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    msg(
        $stmt->affected_rows ? "success" : "error",
        $stmt->affected_rows
            ? "Student permanently deleted."
            : "Deleted student not found."
    );
} elseif ($action === "delete") {
    $stmt = $conn->prepare(
        "UPDATE students SET is_deleted = 1 WHERE id = ? AND is_deleted = 0"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    msg(
        $stmt->affected_rows ? "success" : "error",
        $stmt->affected_rows
            ? "Student moved to deleted list."
            : "Student not found."
    );
} else {
    msg("error", "Invalid action.");
}

go("index.php");