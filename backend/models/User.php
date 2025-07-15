<?php
namespace App\Models;

use PDO;
use App\Config\DatabaseConfig;

class User {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Ne pas retourner le mot de passe
            return $user;
        }
        return false;
    }
    
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['role'] ?? 'visiteur'
        ]);
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $values[] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $values[] = $data['role'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function isAdmin($userId) {
        $user = $this->getById($userId);
        return $user && $user['role'] === 'admin';
    }
    
    public function isEditor($userId) {
        $user = $this->getById($userId);
        return $user && in_array($user['role'], ['editeur', 'admin']);
    }
} 