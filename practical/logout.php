<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

start_secure_session();

revoke_remember_token($pdo);

destroy_session_completely();

header("Location: login.php?msg=logged_out");
exit;