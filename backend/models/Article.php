<?php
namespace App\Models;

use PDO;
use App\Config\DatabaseConfig;

class Article {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function getAll($page = 1, $limit = 10, $publishedOnly = true) {
        $offset = ($page - 1) * $limit;
        $whereClause = $publishedOnly ? "WHERE published = 1" : "";
        
        $sql = "
            SELECT a.*, c.name as category_name, u.username as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            $whereClause
            ORDER BY a.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getById($id, $publishedOnly = true) {
        $whereClause = $publishedOnly ? "AND published = 1" : "";
        
        $sql = "
            SELECT a.*, c.name as category_name, u.username as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.id = ? $whereClause
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByCategory($categoryId, $page = 1, $limit = 10, $publishedOnly = true) {
        $offset = ($page - 1) * $limit;
        $whereClause = $publishedOnly ? "AND published = 1" : "";
        
        $sql = "
            SELECT a.*, c.name as category_name, u.username as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.category_id = ? $whereClause
            ORDER BY a.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getGroupedByCategory($publishedOnly = true) {
        // Ajouter la condition de publication directement dans la clause ON pour éviter une erreur de syntaxe
        $articlePublishedCondition = $publishedOnly ? " AND a.published = 1" : "";

        $sql = "
            SELECT c.id AS category_id,
                   c.name AS category_name,
                   c.description AS category_description,
                   a.id AS article_id,
                   a.title,
                   a.summary,
                   a.created_at,
                   u.username AS author_name
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id" . $articlePublishedCondition . "
            LEFT JOIN users u ON a.author_id = u.id
            ORDER BY c.name, a.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Grouper par catégorie
        $grouped = [];
        foreach ($results as $row) {
            $categoryId = $row['category_id'];
            if (!isset($grouped[$categoryId])) {
                $grouped[$categoryId] = [
                    'category' => [
                        'id' => $row['category_id'],
                        'name' => $row['category_name'],
                        'description' => $row['category_description']
                    ],
                    'articles' => []
                ];
            }
            
            if ($row['article_id']) {
                $grouped[$categoryId]['articles'][] = [
                    'id' => $row['article_id'],
                    'title' => $row['title'],
                    'summary' => $row['summary'],
                    'created_at' => $row['created_at'],
                    'author_name' => $row['author_name']
                ];
            }
        }
        
        return array_values($grouped);
    }
    
    public function count($publishedOnly = true) {
        $whereClause = $publishedOnly ? "WHERE published = 1" : "";
        $sql = "SELECT COUNT(*) FROM articles $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function countByCategory($categoryId, $publishedOnly = true) {
        $whereClause = $publishedOnly ? "AND published = 1" : "";
        $sql = "SELECT COUNT(*) FROM articles WHERE category_id = ? $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchColumn();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO articles (title, content, summary, category_id, author_id, published) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $data['summary'] ?? null,
            $data['category_id'] ?? null,
            $data['author_id'],
            $data['published'] ?? false
        ]);
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $values[] = $data['title'];
        }
        
        if (isset($data['content'])) {
            $fields[] = "content = ?";
            $values[] = $data['content'];
        }
        
        if (isset($data['summary'])) {
            $fields[] = "summary = ?";
            $values[] = $data['summary'];
        }
        
        if (isset($data['category_id'])) {
            $fields[] = "category_id = ?";
            $values[] = $data['category_id'];
        }
        
        if (isset($data['published'])) {
            $fields[] = "published = ?";
            $values[] = $data['published'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE articles SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = ?");
        return $stmt->execute([$id]);
    }
} 