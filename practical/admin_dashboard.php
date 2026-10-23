
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

require_login($pdo, "admin");

function dashboard_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$totalUsers = 0;
$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;
$latestUsers = [];
$error = "";

try {
    $stmt = $pdo->query(
        "SELECT
            COUNT(*) AS total_users,
            SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) AS total_students,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS total_admins
         FROM users"
    );

    $stats = $stmt->fetch();

    $totalUsers = (int) ($stats["total_users"] ?? 0);
    $totalStudents = (int) ($stats["total_students"] ?? 0);
    $totalAdmins = (int) ($stats["total_admins"] ?? 0);

    $stmt = $pdo->query("SELECT COUNT(*) AS total_events FROM events");
    $totalEvents = (int) $stmt->fetch()["total_events"];

    $stmt = $pdo->query(
        "SELECT id, username, role
         FROM users
         ORDER BY id DESC
         LIMIT 5"
    );

    $latestUsers = $stmt->fetchAll();
} catch (PDOException $exception) {
    error_log("Admin dashboard error: " . $exception->getMessage());
    $error = "Could not load dashboard statistics. Please check the database tables and columns.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6fa;
            color: #243047;
            font-family: Arial, sans-serif;
        }

        .dashboard-header {
            background: #243f70;
            color: white;
            padding: 22px 5%;
        }

        .dashboard-header h1 {
            margin: 0 0 8px;
        }

        .dashboard-header p {
            margin: 0;
        }

        .dashboard {
            max-width: 1150px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .dashboard-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 30px;
        }

        .dashboard-nav a {
            display: inline-block;
            padding: 11px 16px;
            background: #2457a7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .dashboard-nav a:hover {
            background: #193f7d;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0 35px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #e0e5ee;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
        }

        .stat-card h2 {
            font-size: 16px;
            font-weight: normal;
            color: #56647b;
            margin: 0 0 15px;
        }

        .stat-number {
            font-size: 34px;
            font-weight: bold;
            margin: 0;
            color: #2457a7;
        }

        .registrations-section {
            background: white;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #e0e5ee;
            overflow-x: auto;
        }

        .registrations-section h2 {
            margin-top: 0;
        }

        .registrations {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .registrations th,
        .registrations td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #e0e5ee;
        }

        .registrations th {
            background: #edf2fa;
        }

        .role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            background: #e8eefb;
            color: #2457a7;
            text-transform: capitalize;
        }

        .error-message {
            background: #fff0f0;
            color: #9c2020;
            border: 1px solid #e7b8b8;
            border-radius: 5px;
            padding: 14px;
        }

        .empty-message {
            color: #64748b;
            padding: 15px 0;
        }

        @media (max-width: 600px) {
            .dashboard-header {
                padding: 20px;
            }

            .dashboard {
                margin-top: 20px;
                padding: 0 12px;
            }

            .stat-card {
                padding: 18px;
            }

            .registrations-section {
                padding: 14px;
            }

            .registrations th,
            .registrations td {
                padding: 9px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

<header class="dashboard-header">
    <h1>StudentHub Admin Dashboard</h1>
    <p>
        Welcome,
        <?= dashboard_escape($_SESSION["username"] ?? "Admin") ?>
    </p>
</header>

<main class="dashboard">

    <nav class="dashboard-nav" aria-label="Admin navigation">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_events.php">Manage Events</a>
        <a href="events_api.php">Events API</a>
        <a href="index.php">StudentHub Home</a>
        <a href="logout.php">Logout</a>
    </nav>

    <?php if ($error !== ""): ?>

        <p class="error-message" role="alert">
            <?= dashboard_escape($error) ?>
        </p>

    <?php else: ?>

        <section>
            <h2>User Statistics</h2>

            <div class="stats">

                <article class="stat-card">
                    <h2>Total Users</h2>
                    <p class="stat-number"><?= $totalUsers ?></p>
                </article>

                <article class="stat-card">
                    <h2>Total Students</h2>
                    <p class="stat-number"><?= $totalStudents ?></p>
                </article>

                <article class="stat-card">
                    <h2>Total Admins</h2>
                    <p class="stat-number"><?= $totalAdmins ?></p>
                </article>

                <article class="stat-card">
                    <h2>Total Events</h2>
                    <p class="stat-number"><?= $totalEvents ?></p>
                </article>

            </div>
        </section>

        <section class="registrations-section">
            <h2>Latest Registrations</h2>
            <p>Recently created user accounts</p>

            <?php if (empty($latestUsers)): ?>

                <p class="empty-message">No registrations found.</p>

            <?php else: ?>

                <table class="registrations">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($latestUsers as $user): ?>
                            <tr>
                                <td><?= (int) $user["id"] ?></td>

                                <td>
                                    <?= dashboard_escape($user["username"]) ?>
                                </td>

                                <td>
                                    <span class="role">
                                        <?= dashboard_escape($user["role"]) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
        </section>

    <?php endif; ?>

</main>

</body>
</html>
