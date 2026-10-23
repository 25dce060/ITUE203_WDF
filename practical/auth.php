<?php

const SESSION_TIMEOUT = 300;
const REMEMBER_COOKIE = "remember_me";
const REMEMBER_DURATION = 2592000;

function start_secure_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");

    $secure = (
        isset($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $secure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}


function destroy_session_completely()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }

    $_SESSION = [];

    $params = session_get_cookie_params();

    $options = [
        "expires" => time() - 42000,
        "path" => $params["path"] ?: "/",
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ];

    if (!empty($params["domain"])) {
        $options["domain"] = $params["domain"];
    }

    setcookie(session_name(), "", $options);

    session_destroy();
}


    function redirect_by_role($role)
    {
        if ($role === "admin") {
            header("Location: admin_dashboard.php");
            exit;
        }

        if ($role === "student") {
            header("Location: student_dashboard.php");
            exit;
        }

        header("Location: login.php?msg=invalid_role");
        exit;
    }


function remember_cookie_options($expires)
{
    $secure = (
        isset($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    return [
        "expires" => $expires,
        "path" => "/",
        "secure" => $secure,
        "httponly" => true,
        "samesite" => "Lax"
    ];
}


function parse_remember_cookie()
{
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? null;

    if (
        !is_string($cookie) ||
        !preg_match(
            '/^([a-f0-9]{24}):([a-f0-9]{64})$/',
            $cookie,
            $matches
        )
    ) {
        return null;
    }

    return [
        "selector" => $matches[1],
        "validator" => $matches[2]
    ];
}


function clear_remember_cookie()
{
    setcookie(
        REMEMBER_COOKIE,
        "",
        remember_cookie_options(time() - 42000)
    );

    unset($_COOKIE[REMEMBER_COOKIE]);
}


function delete_remember_selector(PDO $pdo, $selector)
{
    try {
        $stmt = $pdo->prepare(
            "DELETE FROM remember_tokens WHERE selector = ?"
        );

        $stmt->execute([$selector]);

    } catch (Throwable $error) {
        error_log("Remember token deletion failed: " . $error->getMessage());
    }
}


function revoke_remember_token(PDO $pdo)
{
    $parts = parse_remember_cookie();

    if ($parts !== null) {
        delete_remember_selector(
            $pdo,
            $parts["selector"]
        );
    }

    clear_remember_cookie();
}


function issue_remember_token(PDO $pdo, $userId)
{
    $oldParts = parse_remember_cookie();

    if ($oldParts !== null) {
        delete_remember_selector(
            $pdo,
            $oldParts["selector"]
        );
    }

    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));

    $tokenHash = hash("sha256", $validator);

    $expiresTimestamp = time() + REMEMBER_DURATION;
    $expiresAt = gmdate(
        "Y-m-d H:i:s",
        $expiresTimestamp
    );

    $stmt = $pdo->prepare(
        "INSERT INTO remember_tokens
         (selector, token_hash, expires_at, user_id)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $selector,
        $tokenHash,
        $expiresAt,
        $userId
    ]);

    $cookieValue = $selector . ":" . $validator;

    setcookie(
        REMEMBER_COOKIE,
        $cookieValue,
        remember_cookie_options($expiresTimestamp)
    );

    $_COOKIE[REMEMBER_COOKIE] = $cookieValue;
}


function login_from_remember_cookie(PDO $pdo)
{
    if (isset($_SESSION["user_id"])) {
        return true;
    }

    if (!isset($_COOKIE[REMEMBER_COOKIE])) {
        return false;
    }

    $parts = parse_remember_cookie();

    if ($parts === null) {
        clear_remember_cookie();
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT
                rt.token_hash,
                rt.expires_at,
                u.id,
                u.username,
                u.role,
                u.last_login
             FROM remember_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.selector = ?
             LIMIT 1"
        );

        $stmt->execute([$parts["selector"]]);

        $row = $stmt->fetch();

    } catch (Throwable $error) {
        error_log("Remember-me lookup failed: " . $error->getMessage());

        return false;
    }

    if (!$row) {
        clear_remember_cookie();
        return false;
    }

    $expiresTimestamp = strtotime($row["expires_at"] . " UTC");

    if ($expiresTimestamp === false || $expiresTimestamp <= time()) {
        delete_remember_selector($pdo, $parts["selector"]);
        clear_remember_cookie();

        return false;
    }

    $calculatedHash = hash(
        "sha256",
        $parts["validator"]
    );

    if (!hash_equals($row["token_hash"], $calculatedHash)) {
        clear_remember_cookie();
        return false;
    }

    if (!in_array($row["role"], ["admin", "student"], true)) {
        delete_remember_selector($pdo, $parts["selector"]);
        clear_remember_cookie();

        return false;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int) $row["id"];
    $_SESSION["username"] = $row["username"];
    $_SESSION["role"] = $row["role"];
    $_SESSION["previous_login"] = $row["last_login"];
    $_SESSION["last_activity"] = time();
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    try {
        issue_remember_token($pdo, (int) $row["id"]);
    } catch (Throwable $error) {
        error_log("Remember token rotation failed: " . $error->getMessage());
        clear_remember_cookie();
    }

    return true;
}


function require_login(PDO $pdo, $requiredRole)
{
    start_secure_session();

    if (!isset($_SESSION["user_id"])) {
        if (!login_from_remember_cookie($pdo)) {
            header("Location: login.php?msg=login_required");
            exit;
        }
    } else {
        $lastActivity = (int) (
            $_SESSION["last_activity"] ?? time()
        );

        if (time() - $lastActivity > SESSION_TIMEOUT) {
            revoke_remember_token($pdo);
            destroy_session_completely();

            header("Location: login.php?msg=timeout");
            exit;
        }
    }

    if (
        !isset($_SESSION["role"]) ||
        !in_array($_SESSION["role"], ["admin", "student"], true)
    ) {
        revoke_remember_token($pdo);
        destroy_session_completely();

        header("Location: login.php?msg=invalid_role");
        exit;
    }

    if ($_SESSION["role"] !== $requiredRole) {
        revoke_remember_token($pdo);
        destroy_session_completely();

        header("Location: login.php?msg=forbidden");
        exit;
    }

    $_SESSION["last_activity"] = time();
}