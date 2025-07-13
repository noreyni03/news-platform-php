<?php
/**
 * Script de test pour le design moderne Actu-Web
 * 
 * Ce script teste les différentes fonctionnalités du nouveau design
 */

echo "=== Test du Design Moderne Actu-Web ===\n\n";

// Test 1: Vérification des fichiers
echo "1. Vérification des fichiers...\n";
$files_to_check = [
    'frontend/index.php',
    'frontend/article.php',
    'frontend/assets/css/modern.css',
    'frontend/config/gemini.php',
    'frontend/config/gemini.php.example',
    'frontend/api/gemini.php',
    'backend/services/GeminiService.php',
    'README_MODERN.md'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file (manquant)\n";
    }
}

// Test 2: Vérification de la configuration Gemini
echo "\n2. Test de la configuration Gemini...\n";
if (file_exists('frontend/config/gemini.php')) {
    $config = require 'frontend/config/gemini.php';
    if (!empty($config['api_key']) && $config['api_key'] !== 'VOTRE_CLE_API_ICI') {
        echo "   ✅ Configuration Gemini trouvée\n";
    } else {
        echo "   ⚠️  Configuration Gemini non configurée (utilisez gemini.php.example)\n";
    }
} else {
    echo "   ❌ Fichier de configuration Gemini manquant\n";
}

// Test 3: Vérification de l'autoloader
echo "\n3. Test de l'autoloader...\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Autoloader Composer trouvé\n";
} else {
    echo "   ❌ Autoloader Composer manquant (exécutez: composer install)\n";
}

// Test 4: Vérification de la base de données
echo "\n4. Test de la base de données...\n";
try {
    require_once 'vendor/autoload.php';
    use App\Models\Article;
    use App\Models\Category;
    
    $articleModel = new Article();
    $categoryModel = new Category();
    
    $articles = $articleModel->getAll(1, 1, true);
    $categories = $categoryModel->getAll();
    
    echo "   ✅ Connexion à la base de données réussie\n";
    echo "   📊 Articles trouvés: " . count($articles) . "\n";
    echo "   📊 Catégories trouvées: " . count($categories) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

// Test 5: Vérification du service Gemini
echo "\n5. Test du service Gemini...\n";
try {
    require_once 'vendor/autoload.php';
    use App\Services\GeminiService;
    
    $geminiService = new GeminiService();
    
    if ($geminiService->isConfigured()) {
        echo "   ✅ Service Gemini configuré\n";
    } else {
        echo "   ⚠️  Service Gemini non configuré (clé API manquante)\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur du service Gemini: " . $e->getMessage() . "\n";
}

// Test 6: Vérification des URLs
echo "\n6. Test des URLs...\n";
$base_url = 'http://localhost/projet-actualite/frontend/';
echo "   🌐 Page d'accueil: $base_url" . "index.php\n";
echo "   🌐 API Gemini: $base_url" . "api/gemini.php\n";
echo "   🌐 Configuration: $base_url" . "config/gemini.php\n";

// Test 7: Recommandations
echo "\n7. Recommandations...\n";
echo "   📝 Pour activer les fonctionnalités IA:\n";
echo "      1. Obtenez une clé API depuis https://makersuite.google.com/app/apikey\n";
echo "      2. Copiez gemini.php.example vers gemini.php\n";
echo "      3. Configurez votre clé API dans gemini.php\n\n";

echo "   🎨 Fonctionnalités du design moderne:\n";
echo "      ✅ Design responsive avec Tailwind CSS\n";
echo "      ✅ Animations et transitions fluides\n";
echo "      ✅ Modal interactif pour les articles\n";
echo "      ✅ Filtrage par catégories\n";
echo "      ✅ Pagination élégante\n";
echo "      ✅ Fonctionnalités IA intégrées\n\n";

echo "   🚀 Pour démarrer:\n";
echo "      1. Assurez-vous que votre serveur web pointe vers le dossier du projet\n";
echo "      2. Accédez à $base_url" . "index.php\n";
echo "      3. Testez les fonctionnalités IA en cliquant sur un article\n\n";

echo "=== Test terminé ===\n";
echo "Consultez README_MODERN.md pour plus d'informations.\n"; 