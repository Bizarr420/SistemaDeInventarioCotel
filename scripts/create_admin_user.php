<?php
$dbPath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($dbPath)) {
    echo "ERROR: database file not found: $dbPath\n";
    exit(1);
}
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "ERROR: could not open database: " . $e->getMessage() . "\n";
    exit(1);
}

$email = 'admin@local.test';
$name = 'Admin User';
$password = 'secret1234';
$now = date('Y-m-d H:i:s');

// Check if email exists
$check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$check->execute([$email]);
if ($check->fetch()) {
    echo "SKIPPED: user with email $email already exists\n";
    exit(0);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$remember = bin2hex(random_bytes(10));

$insert = $pdo->prepare('INSERT INTO users (name, email, email_verified_at, password, remember_token, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$insert->execute([$name, $email, $now, $hash, $remember, $now, $now]);
$lastId = $pdo->lastInsertId();

echo "CREATED: id={$lastId}, email={$email}, password={$password}\n";
exit(0);
