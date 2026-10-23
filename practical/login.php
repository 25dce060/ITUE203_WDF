
<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

start_secure_session();

if (isset($_SESSION["user_id"])) {
    $lastActivity = (int) ($_SESSION["last_activity"] ?? time());

    if (time() - $lastActivity <= SESSION_TIMEOUT) {
        redirect_by_role($_SESSION["role"] ?? "");
    }

    revoke_remember_token($pdo);
    destroy_session_completely();
    start_secure_session();
}

$messages = [
    "logged_out" => "You have been logged out successfully.",
    "timeout" => "Session expired due to inactivity.",
    "login_required" => "Please log in to access that page.",
    "forbidden" => "You do not have permission to access that page.",
    "invalid_role" => "The account role is not recognized."
];

$msgCode = $_GET["msg"] ?? "";
$notice = is_string($msgCode) ? ($messages[$msgCode] ?? "") : "";

$errorMessage = "";
$usernameValue = "";

if (
    $_SERVER["REQUEST_METHOD"] === "GET" &&
    !isset($_GET["msg"]) &&
    login_from_remember_cookie($pdo)
) {
    redirect_by_role($_SESSION["role"] ?? "");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        $errorMessage = "Your login form expired. Please refresh and try again.";
        http_response_code(400);
    } else {
        $postedUsername = $_POST["username"] ?? "";
        $postedPassword = $_POST["password"] ?? "";

        $usernameValue = is_string($postedUsername)
            ? trim($postedUsername)
            : "";

        $passwordValue = is_string($postedPassword)
            ? $postedPassword
            : "";

        try {
            $stmt = $pdo->prepare(
                "SELECT id, username, password_hash, role, last_login
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );

            $stmt->execute([$usernameValue]);
            $user = $stmt->fetch();

            if (
                $user &&
                password_verify($passwordValue, $user["password_hash"]) &&
                in_array($user["role"], ["admin", "student"], true)
            ) {
                $previousLogin = $user["last_login"];

                $update = $pdo->prepare(
                    "UPDATE users SET last_login = NOW() WHERE id = ?"
                );
                $update->execute([$user["id"]]);

                session_regenerate_id(true);

                $_SESSION["user_id"] = (int) $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["previous_login"] = $previousLogin;
                $_SESSION["last_activity"] = time();
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                $rememberRequested =
                    isset($_POST["remember_me"]) &&
                    $_POST["remember_me"] === "1";

                if ($rememberRequested) {
                    issue_remember_token($pdo, (int) $user["id"]);
                } else {
                    revoke_remember_token($pdo);
                }

                redirect_by_role($user["role"]);
            }

            $errorMessage = "Invalid username or password.";

        } catch (Throwable $error) {
            error_log("Login error: " . $error->getMessage());
            $errorMessage = "Login is temporarily unavailable. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudentHub</title>
</head>
<body>
    <h1>StudentHub Login</h1>

    <?php if ($notice !== ""): ?>
        <p><?= htmlspecialchars($notice, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <?php if ($errorMessage !== ""): ?>
        <p role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
        >

        <p>
            <label for="username">Username</label><br>
            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($usernameValue, ENT_QUOTES, "UTF-8") ?>"
                maxlength="50"
                autocomplete="username"
                required
            >
        </p>

        <p>
            <label for="password">Password</label><br>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </p>

        <p>
            <label>
                <input type="checkbox" name="remember_me" value="1">
                Remember me for 30 days
            </label>
        </p>

        <button type="submit">Login</button>
    </form>

    <p>
        Don't have an account?
        <a href="/practical/STUDENT%20HUB/PAGES/register.html">Register</a>
    </p>
</body>
</html>
