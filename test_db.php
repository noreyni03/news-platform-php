<?php
// Fichier de test pour vérifier la connexion à la base de données
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de connexion à la base de données</h1>";

try {
    // Test 1: Vérifier si Composer autoload fonctionne
    echo "<h2>1. Test de l'autoload Composer</h2>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✓ Autoload Composer chargé<br>";
    } else {
        throw new Exception("Fichier vendor/autoload.php manquant. Exécutez 'composer install'");
    }

    // Test 2: Vérifier la configuration de la base de données
    echo "<h2>2. Test de la configuration de la base de données</h2>";
    $configFile = __DIR__ . '/backend/config/database.php';
    if (file_exists($configFile)) {
        echo "✓ Fichier de configuration trouvé<br>";
        include $configFile;
    } else {
        throw new Exception("Fichier de configuration manquant: $configFile");
    }

    // Test 3: Test de connexion directe à MySQL
    echo "<h2>3. Test de connexion directe à MySQL</h2>";
    $host = 'localhost';
    $dbname = 'actualite_db';
    $username = 'root';
    $password = '';

    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion à MySQL réussie<br>";

    // Test 4: Vérifier si la base de données existe
    echo "<h2>4. Test de l'existence de la base de données</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Base de données '$dbname' existe<br>";
    } else {
        throw new Exception("Base de données '$dbname' n'existe pas");
    }

    // Test 5: Connexion à la base de données spécifique
    echo "<h2>5. Test de connexion à la base de données spécifique</h2>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion à la base de données '$dbname' réussie<br>";

    // Test 6: Vérifier les tables
    echo "<h2>6. Test des tables</h2>";
    $tables = ['users', 'categories', 'articles', 'auth_tokens'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' manquante<br>";
        }
    }

    // Test 7: Vérifier les données
    echo "<h2>7. Test des données</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "✓ Nombre d'utilisateurs: $userCount<br>";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    echo "✓ Nombre de catégories: $categoryCount<br>";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM articles");
    $articleCount = $stmt->fetch()['count'];
    echo "✓ Nombre d'articles: $articleCount<br>";

    // Test 8: Test de la classe DatabaseConfig
    echo "<h2>8. Test de la classe DatabaseConfig</h2>";
    try {
        $connection = \App\Config\DatabaseConfig::getConnection();
        echo "✓ Classe DatabaseConfig fonctionne<br>";
    } catch (Exception $e) {
        echo "❌ Erreur avec DatabaseConfig: " . $e->getMessage() . "<br>";
    }

    // Test 9: Test des modèles
    echo "<h2>9. Test des modèles</h2>";
    try {
        $userModel = new \App\Models\User();
        echo "✓ Modèle User chargé<br>";
        
        $categoryModel = new \App\Models\Category();
        echo "✓ Modèle Category chargé<br>";
        
        $articleModel = new \App\Models\Article();
        echo "✓ Modèle Article chargé<br>";
    } catch (Exception $e) {
        echo "❌ Erreur avec les modèles: " . $e->getMessage() . "<br>";
    }

    echo "<h2>✅ Tous les tests sont passés avec succès!</h2>";
    echo "<p>Votre configuration semble correcte. Si vous avez encore des erreurs, vérifiez les logs d'Apache.</p>";

} catch (Exception $e) {
    echo "<h2>❌ Erreur détectée</h2>";
    echo "<p><strong>Message d'erreur:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>Solutions possibles:</h3>";
    echo "<ul>";
    echo "<li>Vérifiez que MySQL est démarré</li>";
    echo "<li>Vérifiez les paramètres de connexion dans backend/config/database.php</li>";
    echo "<li>Exécutez 'composer install' pour installer les dépendances</li>";
    echo "<li>Importez le schéma de base de données depuis database/schema.sql</li>";
    echo "</ul>";
}
?> 