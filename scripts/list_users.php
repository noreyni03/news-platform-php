<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\DatabaseConfig;

$pdo = DatabaseConfig::getConnection();

$users = $pdo->query('SELECT id, username, role FROM users')->fetchAll();
foreach ($users as $u) {
    printf("%d\t%s\t%s\n", $u['id'], $u['username'], $u['role']);
}
