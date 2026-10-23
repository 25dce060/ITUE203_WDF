<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    msg("error", "Invalid student ID.");
    go("index.php");
}

$stmt = $conn->prepare(
    "SELECT first_name, last_name, email, phone, course, year_level
     FROM students
     WHERE id = ? AND is_deleted = 0"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    msg("error", "Student not found.");
    go("index.php");
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student = input_student();
    $errors = validate_student($student);

    if (empty($errors) && email_exists($conn, $student["email"], $id)) {
        $errors[] = "This email is already registered.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE students
             SET first_name = ?, last_name = ?, email = ?,
                 phone = ?, course = ?, year_level = ?
             WHERE id = ? AND is_deleted = 0"
        );

        $stmt->bind_param(
            "sssssii",
            $student["first_name"],
            $student["last_name"],
            $student["email"],
            $student["phone"],
            $student["course"],
            $student["year_level"],
            $id
        );

        try {
            $stmt->execute();
            msg("success", "Student updated successfully.");
            go("index.php");
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $errors[] = "This email is already registered.";
            } else {
                $errors[] = "Unable to update student. Please try again.";
            }
        }
    }
}

$page_title = "Edit Student";
require_once __DIR__ . "/header.php";
?>

<h2>Edit Student</h2>

<?php
$is_edit = true;
require __DIR__ . "/form.php";
?>

<?php require_once __DIR__ . "/footer.php"; ?>