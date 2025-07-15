-- 1. Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS actualite_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Sélectionner la base
USE actualite_db;

-- 3. Créer les tables

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('visiteur', 'editeur', 'admin') DEFAULT 'visiteur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- Table des articles
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    summary TEXT,
    category_id INT,
    author_id INT,
    published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL,
    FOREIGN KEY (author_id)
        REFERENCES users(id)
        ON DELETE SET NULL
);

-- Table des tokens d'authentification
CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- 4. Insérer des données de test

-- Catégories
INSERT INTO categories (name, description)
VALUES 
  ('Technologie', 'Articles sur les nouvelles technologies'),
  ('Sport',       'Actualités sportives'),
  ('Politique',   'Actualités politiques'),
  ('Économie',    'Actualités économiques')
ON DUPLICATE KEY UPDATE name = name;

-- Utilisateurs (mot de passe haché bcrypt = "password")
INSERT INTO users (username, email, password, role)
VALUES 
  ('admin',    'admin@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
  ('editeur1', 'editeur@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editeur'),
  ('user1',    'user@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'visiteur')
ON DUPLICATE KEY UPDATE username = username;

-- Articles de test
INSERT INTO articles (title, content, summary, category_id, author_id, published)
VALUES 
  ('Nouvelle révolution dans l\'IA', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'Les dernières avancées en intelligence artificielle promettent de transformer notre quotidien.', 1, 2, TRUE),
  ('Élections présidentielles 2024', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'Analyse des enjeux des prochaines élections présidentielles.', 3, 2, TRUE),
  ('Championnat de football', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'Résultats et analyses du dernier match de championnat.', 2, 2, TRUE),
  ('Marché boursier en hausse', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'Le marché boursier enregistre une forte hausse ce mois-ci.', 4, 2, TRUE)
ON DUPLICATE KEY UPDATE title = title; 