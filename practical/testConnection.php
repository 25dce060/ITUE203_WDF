<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/function.php";

$databaseResult = $conn->query("SELECT DATABASE() AS database_name");
$database = $databaseResult->fetch_assoc();

$tablesResult = $conn->query("SHOW TABLES");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Database Connection Test</title>

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

        <h2>Database Connection Test</h2>

        <p class="success">
            Database connection successful!
        </p>

        <p>
            <strong>Database:</strong>
            <?= e($database["database_name"]) ?>
        </p>

        <h3>Available Tables</h3>

        <ul>
            <?php while ($table = $tablesResult->fetch_row()): ?>

                <li><?= e($table[0]) ?></li>

            <?php endwhile; ?>
        </ul>

    </main>

    <footer>
        <p>© 2026 StudentHub</p>
    </footer>

</div>

</body>
</html>