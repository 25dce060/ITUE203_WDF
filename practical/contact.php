<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/function.php";

$errors = [];

$name = "";
$email = "";
$subject = "";
$message = "";

$success = isset($_GET["success"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = postString("name");
    $email = postString("email");
    $subject = postString("subject");
    $message = postString("message");

    if (
        !preg_match("/^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/", $name) ||
        strlen($name) > 100
    ) {
        $errors[] = "Enter a valid name.";
    }

    if (
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = "Enter a valid email address.";
    }

    if ($subject === "" || strlen($subject) > 150) {
        $errors[] = "Enter a subject of no more than 150 characters.";
    }

    if (strlen($message) < 10 || strlen($message) > 5000) {
        $errors[] = "The message must contain between 10 and 5000 characters.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO contact_messages
                (name, email, subject, message)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $subject,
                $message
            );

            $stmt->execute();
            $stmt->close();

            header("Location: contact.php?success=1");
            exit;

        } catch (mysqli_sql_exception $error) {
            error_log("Contact submission failed: " . $error->getMessage());
            $errors[] = "Your message could not be saved. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact StudentHub</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <header>
        <h1>StudentHub</h1>

        <nav>
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="contact.php">Contact</a>
        </nav>
    </header>

    <main class="card">

        <h2>Contact Us</h2>

        <?php if ($success): ?>
            <p class="success">
                Your message has been submitted successfully.
            </p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="contact.php" method="POST">

            <label for="name">Name</label>
            <input type="text" id="name" name="name"
                   value="<?= e($name) ?>" maxlength="100" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= e($email) ?>" maxlength="254" required>

            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject"
                   value="<?= e($subject) ?>" maxlength="150" required>

            <label for="message">Message</label>
            <textarea id="message" name="message"
                      minlength="10" maxlength="5000"
                      required><?= e($message) ?></textarea>

            <button type="submit" class="button">
                Send Message
            </button>

        </form>

    </main>

    <footer>
        <p>© 2026 StudentHub</p>
    </footer>

</div>

</body>
</html>