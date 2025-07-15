<?php
namespace App\Config;

use App\Exceptions\DatabaseException;

class DatabaseConfig {
    private const HOST = 'localhost';
    private const DB_NAME = 'actualite_db';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const CHARSET = 'utf8mb4';
    
    public static function getConnection() {
        $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::CHARSET;
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            return new \PDO($dsn, self::USERNAME, self::PASSWORD, $options);
        } catch (\PDOException $e) {
            throw new DatabaseException("Erreur de connexion : " . $e->getMessage());
        }
    }
}