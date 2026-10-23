
<?php
require_once __DIR__ . "/functions.php";
$pageTitle = $pageTitle ?? "Student Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="head">
        <a class="brand" href="index.php">Student Management</a>
        <nav>
            <a href="index.php">Students</a>
            <a href="add.php">+ Add Student</a>
        </nav>
    </div>
</header>
<main class="container">
<?php if (!empty($_SESSION["flash"])): ?>
    <div class="alert <?= h($_SESSION["flash"][0]) ?>">
        <?= h($_SESSION["flash"][1]) ?>
    </div>
    <?php unset($_SESSION["flash"]); ?>
<?php endif; ?>
