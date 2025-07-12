<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\DatabaseConfig;

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() > 0) {
        // reset password
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $update = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
        $update->execute([$hash, 'admin']);
        echo "User 'admin' password reset to 'password'.\n";
        exit;
    }

    $hash = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?,?,?)');
    $stmt->execute(['admin', $hash, 'admin']);
    echo "Admin user created with password 'password'.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
