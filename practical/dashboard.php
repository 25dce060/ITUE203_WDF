
<?php
$currentRole = $_SESSION["role"] ?? "";
?>

<nav class="dashboard-nav" aria-label="Main navigation">
    <?php if ($currentRole === "admin"): ?>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_events.php">Manage Events</a>
        <a href="events_api.php">Events API</a>
    <?php elseif ($currentRole === "student"): ?>
        <a href="student_dashboard.php">Dashboard</a>
        <a href="index.php">Home</a>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</nav>
