<?php
/**
 * Script d'installation automatique du projet d'actualités
 */

echo "=== INSTALLATION DU PROJET D'ACTUALITÉS ===\n\n";

// Vérification des prérequis
echo "1. Vérification des prérequis...\n";

$requirements = [
    'php' => '7.4.0',
    'extensions' => ['soap', 'curl', 'json', 'pdo', 'pdo_mysql'],
    'composer' => true
];

// Vérifier la version PHP
if (version_compare(PHP_VERSION, $requirements['php'], '<')) {
    die("❌ PHP " . $requirements['php'] . " ou supérieur requis. Version actuelle: " . PHP_VERSION . "\n");
}
echo "✓ PHP " . PHP_VERSION . " OK\n";

// Vérifier les extensions
foreach ($requirements['extensions'] as $ext) {
    if (!extension_loaded($ext)) {
        die("❌ Extension PHP '$ext' manquante\n");
    }
    echo "✓ Extension $ext OK\n";
}

// Vérifier Composer
if (!file_exists('composer.json')) {
    die("❌ Fichier composer.json manquant\n");
}
echo "✓ Composer OK\n";

echo "\n2. Installation des dépendances PHP...\n";
if (!file_exists('vendor/autoload.php')) {
    echo "Installation avec Composer...\n";
    system('composer install', $returnCode);
    if ($returnCode !== 0) {
        die("❌ Erreur lors de l'installation des dépendances\n");
    }
}
echo "✓ Dépendances installées\n";

echo "\n3. Configuration de la base de données...\n";

// Demander les paramètres de base de données
echo "Paramètres de la base de données:\n";
$host = readline("Host (localhost): ") ?: 'localhost';
$dbname = readline("Nom de la base de données (actualite_db): ") ?: 'actualite_db';
$username = readline("Nom d'utilisateur (root): ") ?: 'root';
$password = readline("Mot de passe: ");

// Tester la connexion
try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion à MySQL réussie\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion à MySQL: " . $e->getMessage() . "\n");
}

// Créer la base de données
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de données '$dbname' créée\n";
} catch (PDOException $e) {
    die("❌ Erreur lors de la création de la base de données: " . $e->getMessage() . "\n");
}

// Importer le schéma
try {
    $schema = file_get_contents('database/schema.sql');
    if (!$schema) {
        die("❌ Fichier database/schema.sql manquant\n");
    }
    
    // Remplacer USE actualite_db par la base de données choisie
    $schema = str_replace('USE actualite_db;', "USE `$dbname`;", $schema);
    
    $pdo->exec("USE `$dbname`");
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✓ Schéma de base de données importé\n";
} catch (PDOException $e) {
    die("❌ Erreur lors de l'import du schéma: " . $e->getMessage() . "\n");
}

echo "\n4. Configuration des fichiers...\n";

// Mettre à jour la configuration de la base de données
$configContent = file_get_contents('backend/config/database.php');
if ($configContent) {
    $configContent = preg_replace(
        "/private const HOST = '[^']*';/",
        "private const HOST = '$host';",
        $configContent
    );
    $configContent = preg_replace(
        "/private const DB_NAME = '[^']*';/",
        "private const DB_NAME = '$dbname';",
        $configContent
    );
    $configContent = preg_replace(
        "/private const USERNAME = '[^']*';/",
        "private const USERNAME = '$username';",
        $configContent
    );
    $configContent = preg_replace(
        "/private const PASSWORD = '[^']*';/",
        "private const PASSWORD = '$password';",
        $configContent
    );
    
    file_put_contents('backend/config/database.php', $configContent);
    echo "✓ Configuration de la base de données mise à jour\n";
}

// Créer le fichier .htaccess si nécessaire
if (!file_exists('.htaccess')) {
    $htaccess = "RewriteEngine On\n";
    $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
    $htaccess .= "RewriteRule ^(.*)$ index.php [QSA,L]\n";
    
    file_put_contents('.htaccess', $htaccess);
    echo "✓ Fichier .htaccess créé\n";
}

echo "\n5. Test des services web...\n";

// Tester l'API REST
$restUrl = "http://localhost/projet-actualite/backend/api/rest_api.php/articles";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
]);

$response = @file_get_contents($restUrl, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "✓ API REST fonctionnelle\n";
    } else {
        echo "⚠ API REST accessible mais réponse inattendue\n";
    }
} else {
    echo "⚠ Impossible de tester l'API REST (serveur web non démarré?)\n";
}

// Tester le service SOAP
$soapUrl = "http://localhost/projet-actualite/backend/api/soap_server.php?wsdl";
$soapResponse = @file_get_contents($soapUrl);
if ($soapResponse !== false && strpos($soapResponse, '<?xml') === 0) {
    echo "✓ Service SOAP accessible\n";
} else {
    echo "⚠ Impossible de tester le service SOAP (serveur web non démarré?)\n";
}

echo "\n6. Vérification de l'application Java...\n";

if (file_exists('java-client/pom.xml')) {
    echo "✓ Projet Java détecté\n";
    echo "Pour compiler l'application Java:\n";
    echo "  cd java-client\n";
    echo "  mvn clean package\n";
    echo "  java -jar target/user-management-client-1.0.0.jar\n";
} else {
    echo "⚠ Projet Java non trouvé\n";
}

echo "\n=== INSTALLATION TERMINÉE ===\n\n";

echo "🎉 Le projet a été installé avec succès!\n\n";

echo "📋 Prochaines étapes:\n";
echo "1. Démarrer votre serveur web (Apache/Nginx)\n";
echo "2. Accéder au site: http://localhost/projet-actualite/frontend/\n";
echo "3. Tester l'API REST: http://localhost/projet-actualite/backend/api/rest_api.php/articles\n";
echo "4. Tester le service SOAP: http://localhost/projet-actualite/backend/api/soap_server.php?wsdl\n\n";

echo "🔐 Comptes de test:\n";
echo "- Admin: admin / password\n";
echo "- Éditeur: editeur1 / password\n";
echo "- Visiteur: user1 / password\n\n";

echo "📚 Documentation: README.md\n";
echo "🐛 Support: Consulter les logs d'erreur en cas de problème\n\n";

echo "Merci d'utiliser notre projet d'architecture logicielle!\n"; 