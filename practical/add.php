<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$student = [
    "first_name" => "",
    "last_name" => "",
    "email" => "",
    "phone" => "",
    "course" => "",
    "year_level" => ""
];

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student = input_student();
    $errors = validate_student($student);

    if (empty($errors) && email_exists($conn, $student["email"])) {
        $errors[] = "This email is already registered.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO students
            (first_name, last_name, email, phone, course, year_level)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssi",
            $student["first_name"],
            $student["last_name"],
            $student["email"],
            $student["phone"],
            $student["course"],
            $student["year_level"]
        );

        try {
            $stmt->execute();
            msg("success", "Student added successfully.");
            go("index.php");
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $errors[] = "This email is already registered.";
            } else {
                $errors[] = "Unable to add student. Please try again.";
            }
        }
    }
}

$page_title = "Add Student";
require_once __DIR__ . "/header.php";
?>

<h2>Add Student</h2>

<?php
$is_edit = false;
require __DIR__ . "/form.php";
?>

<?php require_once __DIR__ . "/footer.php"; ?>