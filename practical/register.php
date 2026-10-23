
<?php

require_once __DIR__ . "/db.php";

function h($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

function getPostValue($key)
{
    $value = $_POST[$key] ?? "";
    return is_string($value) ? trim($value) : "";
}

function showResult($title, $messages, $success, $statusCode = 200)
{
    http_response_code($statusCode);
    $class = $success ? "success" : "error";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?> - StudentHub</title>
        <link rel="stylesheet" href="/practical/STUDENT%20HUB/CSS/styles.css">
    </head>
    <body>
    <div class="page">
        <main class="card server-message <?= $class ?>">
            <h1><?= h($title) ?></h1>
            <?php foreach ((array) $messages as $message): ?>
                <p><?= h($message) ?></p>
            <?php endforeach; ?>
            <a class="btn" href="/practical/STUDENT%20HUB/PAGES/register.html">
                Back to Registration
            </a>
            <?php if ($success): ?>
                <p><a href="/practical/login.php">Go to Login</a></p>
            <?php endif; ?>
        </main>
    </div>
    </body>
    </html>
    <?php
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showResult(
        "Invalid Request",
        "Please submit the registration form.",
        false,
        405
    );
    exit;
}

$name = getPostValue("name");
$newUsername = getPostValue("username");
$email = getPostValue("email");
$mobile = getPostValue("mobile");
$course = getPostValue("course");
$year = getPostValue("year");
$gender = getPostValue("gender");

$passwordValue = $_POST["password"] ?? null;
$confirmPassword = $_POST["confirmPassword"] ?? null;
$terms = $_POST["terms"] ?? "";

$errors = [];

$namePattern = "/^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/";
$usernamePattern = "/^[A-Za-z0-9_]{4,30}$/";
$mobilePattern = "/^[6-9][0-9]{9}$/";
$passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,20}$/";

$allowedCourses = [
    "B.Tech CSE",
    "B.Tech IT",
    "B.Tech ECE",
    "B.Tech Mechanical"
];

$allowedYears = ["1", "2", "3", "4"];
$allowedGenders = ["Male", "Female", "Other"];

if (
    strlen($name) < 2 ||
    strlen($name) > 100 ||
    !preg_match($namePattern, $name)
) {
    $errors[] = "Enter a valid full name.";
}

if (!preg_match($usernamePattern, $newUsername)) {
    $errors[] = "Username must be 4–30 letters, numbers or underscores.";
}

if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $errors[] = "Enter a valid email address.";
}

if (!preg_match($mobilePattern, $mobile)) {
    $errors[] = "Enter a valid 10-digit Indian mobile number.";
}

if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Select a valid course.";
}

if (!in_array($year, $allowedYears, true)) {
    $errors[] = "Select a valid year.";
}

if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = "Select a valid gender.";
}

if (
    !is_string($passwordValue) ||
    !preg_match($passwordPattern, $passwordValue)
) {
    $errors[] = "Password must be 8–20 characters with uppercase, lowercase, a number and a special character.";
}

if (
    !is_string($confirmPassword) ||
    $confirmPassword === "" ||
    $confirmPassword !== $passwordValue
) {
    $errors[] = "Passwords do not match.";
}

if ($terms !== "accepted") {
    $errors[] = "You must accept the terms and conditions.";
}

if ($errors) {
    showResult("Registration Failed", $errors, false, 422);
    exit;
}

try {
    $check = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1"
    );

    $check->execute([$newUsername, $email]);

    if ($check->fetch()) {
        showResult(
            "Registration Error",
            "Username or email already exists. Please try again.",
            false,
            409
        );
        exit;
    }

    $passwordHash = password_hash($passwordValue, PASSWORD_DEFAULT);

    if ($passwordHash === false) {
        throw new RuntimeException("Password hashing failed.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO users
         (name, username, email, mobile, course, study_year, gender, password_hash, role)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student')"
    );

    $stmt->execute([
        $name,
        $newUsername,
        $email,
        $mobile,
        $course,
        (int) $year,
        $gender,
        $passwordHash
    ]);

    showResult(
        "Registration Successful",
        "Your account has been created successfully. You can now log in.",
        true
    );

} catch (PDOException $error) {
    error_log("Student registration failed: " . $error->getMessage());

    if ($error->getCode() === "23000") {
        showResult(
            "Registration Failed",
            "That username or email address is already registered.",
            false,
            409
        );
    } else {
        showResult(
            "Registration Error",
            "Your account could not be created. Check the database configuration and try again.",
            false,
            500
        );
    }

} catch (Throwable $error) {
    error_log("Registration processing failed: " . $error->getMessage());

    showResult(
        "Registration Error",
        "Your account could not be created. Please try again.",
        false,
        500
    );
}
