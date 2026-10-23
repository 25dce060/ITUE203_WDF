<?php

require_once __DIR__ . "/db.php";

$users = [
    [
        "username" => "admin1",
        "password" => "Admin@123",
        "role" => "admin"
    ],
    [
        "username" => "student1",
        "password" => "Student@123",
        "role" => "student"
    ]
];

$check = $pdo->prepare(
    "SELECT id FROM users WHERE username = ? LIMIT 1"
);

$insert = $pdo->prepare(
    "INSERT INTO users (username, password_hash, role)
     VALUES (?, ?, ?)"
);

$inserted = 0;

foreach ($users as $user) {
    $check->execute([$user["username"]]);

    if ($check->fetch()) {
        continue;
    }

    $hash = password_hash(
        $user["password"],
        PASSWORD_DEFAULT
    );

    $insert->execute([
        $user["username"],
        $hash,
        $user["role"]
    ]);

    $inserted++;
}

echo "Users seeded. New users inserted: " . $inserted;
echo "<br>Delete seed.php after running it once.";