<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

require_login($pdo, "student");

$username = htmlspecialchars(
    $_SESSION["username"],
    ENT_QUOTES,
    "UTF-8"
);

$previousLogin = $_SESSION["previous_login"] ?? null;

if ($previousLogin) {
    $lastLoginText = date(
        "d-m-Y H:i:s",
        strtotime($previousLogin)
    );
} else {
    $lastLoginText = "First login";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>

<body>

    <h1>Student Dashboard</h1>

    <h2>
        Welcome, <?= $username ?> (student)
    </h2>

    <p>
        Role: Student
    </p>

    <p>
        Last login: <?= htmlspecialchars($lastLoginText, ENT_QUOTES, "UTF-8") ?>
    </p>

    <p>
        You are viewing the student-only dashboard.
    </p>

    <a href="logout.php">Logout</a>

</body>
</html>