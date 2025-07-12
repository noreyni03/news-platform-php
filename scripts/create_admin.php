<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\DatabaseConfig;

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() > 0) {
        echo "User 'admin' already exists.\n";
        exit;
    }

    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?,?,?)');
    $stmt->execute(['admin', $hash, 'admin']);
    echo "Admin user created with password 'admin123'.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
