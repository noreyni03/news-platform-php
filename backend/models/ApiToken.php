<?php
namespace App\Models;

use PDO;
use App\Config\DatabaseConfig;

class ApiToken {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username 
            FROM auth_tokens t 
            JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByToken($token) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username, u.role 
            FROM auth_tokens t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW())
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    public function create($userId, $description = null, $expiresAt = null) {
        $token = $this->generateToken();

        $stmt = $this->db->prepare(
            "INSERT INTO auth_tokens (token, user_id, expires_at) VALUES (?, ?, ?)"
        );

        if ($stmt->execute([$token, $userId, $expiresAt])) {
            return $token; // retourner le jeton fraîchement créé
        }
        return false;
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
    
    public function deleteByToken($token) {
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->rowCount();
    }
    
    public function deleteExpired() {
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE expires_at IS NOT NULL AND expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    public function validateToken($token) {
        $tokenData = $this->getByToken($token);
        if (!$tokenData) {
            return false;
        }
        
        // Vérifier si le token a expiré
        if ($tokenData['expires_at'] && strtotime($tokenData['expires_at']) < time()) {
            $this->deleteByToken($token);
            return false;
        }
        
        return $tokenData;
    }
    
    private function generateToken($length = 64) {
        return bin2hex(random_bytes($length / 2));
    }
} 