
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function msg($type, $text) {
    $_SESSION["flash"] = [$type, $text];
}

function go($url = "index.php") {
    header("Location: " . $url);
    exit;
}

function input_student() {
    return [
        "first_name" => trim($_POST["first_name"] ?? ""),
        "last_name" => trim($_POST["last_name"] ?? ""),
        "email" => trim($_POST["email"] ?? ""),
        "phone" => trim($_POST["phone"] ?? ""),
        "course" => trim($_POST["course"] ?? ""),
        "year_level" => trim($_POST["year_level"] ?? "")
    ];
}

function validate_student($s) {
    $errors = [];

    if ($s["first_name"] === "" ||
        !preg_match("/^[a-zA-Z .'-]{1,60}$/", $s["first_name"])) {
        $errors[] = "Enter a valid first name.";
    }

    if ($s["last_name"] === "" ||
        !preg_match("/^[a-zA-Z .'-]{1,60}$/", $s["last_name"])) {
        $errors[] = "Enter a valid last name.";
    }

    if (!filter_var($s["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (!preg_match("/^[0-9]{10,15}$/", $s["phone"])) {
        $errors[] = "Phone must contain 10 to 15 digits.";
    }

    if (!in_array($s["course"], ["BCA", "B.Tech", "MCA", "BBA", "B.Com"], true)) {
        $errors[] = "Choose a valid course.";
    }

    if (!in_array((int)$s["year_level"], [1, 2, 3, 4], true)) {
        $errors[] = "Choose a valid year.";
    }

    return $errors;
}

function email_exists($conn, $email, $except = 0) {
    $stmt = $conn->prepare(
        "SELECT id FROM students WHERE email = ? AND id <> ? LIMIT 1"
    );

    $stmt->bind_param("si", $email, $except);
    $stmt->execute();

    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $exists;
}
?>
