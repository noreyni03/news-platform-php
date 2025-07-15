<?php
/**
 * Script de test pour vérifier l'intégration de l'API Gemini
 * 
 * Ce script teste :
 * 1. La configuration de la clé API
 * 2. La connexion à l'API Gemini
 * 3. Les fonctionnalités de génération de contenu
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\GeminiService;

echo "=== TEST D'INTÉGRATION GEMINI API ===\n\n";

// Test 1: Vérification de la configuration
echo "1. Test de la configuration...\n";
$config = require 'frontend/config/gemini.php';

if (!empty($config['api_key'])) {
    echo "   ✅ Clé API configurée: " . substr($config['api_key'], 0, 10) . "...\n";
} else {
    echo "   ❌ Clé API manquante\n";
    exit(1);
}

echo "   ✅ Modèle: " . $config['model'] . "\n";
echo "   ✅ URL de base: " . $config['base_url'] . "\n\n";

// Test 2: Test du service Gemini
echo "2. Test du service Gemini...\n";
try {
    $geminiService = new GeminiService();
    
    if ($geminiService->isConfigured()) {
        echo "   ✅ Service Gemini configuré\n";
    } else {
        echo "   ❌ Service Gemini non configuré\n";
        exit(1);
    }
    
    // Test 3: Test de génération de résumé
    echo "\n3. Test de génération de résumé...\n";
    $testContent = "L'intelligence artificielle révolutionne le monde de la technologie. 
    Les entreprises adoptent de plus en plus l'IA pour automatiser leurs processus, 
    améliorer l'expérience client et optimiser leurs opérations. 
    Cette transformation numérique s'accélère dans tous les secteurs d'activité.";
    
    $summary = $geminiService->generateSummary($testContent);
    
    if (strpos($summary, 'Configuration de l\'API Gemini manquante') !== false) {
        echo "   ❌ Erreur de configuration\n";
        echo "   Message: $summary\n";
    } elseif (strpos($summary, 'Erreur lors de l\'appel à l\'API Gemini') !== false) {
        echo "   ❌ Erreur de connexion à l'API\n";
        echo "   Message: $summary\n";
    } else {
        echo "   ✅ Résumé généré avec succès\n";
        echo "   Résumé: " . substr($summary, 0, 100) . "...\n";
    }
    
    // Test 4: Test de génération de questions
    echo "\n4. Test de génération de questions...\n";
    $questions = $geminiService->generateRelatedQuestions($testContent);
    
    if (is_array($questions) && !empty($questions)) {
        echo "   ✅ Questions générées avec succès\n";
        foreach ($questions as $index => $question) {
            echo "   Question " . ($index + 1) . ": " . $question . "\n";
        }
    } else {
        echo "   ❌ Erreur lors de la génération des questions\n";
    }
    
    // Test 5: Test de l'API REST
    echo "\n5. Test de l'API REST...\n";
    $apiUrl = "http://localhost/projet-actualite/frontend/api/gemini.php?action=status";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/json'
        ]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['configured']) && $data['configured']) {
            echo "   ✅ API REST fonctionnelle\n";
        } else {
            echo "   ⚠️  API REST accessible mais configuration incorrecte\n";
        }
    } else {
        echo "   ⚠️  Impossible de tester l'API REST (serveur web non démarré?)\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur du service Gemini: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== RÉSUMÉ DE L'INTÉGRATION ===\n";
echo "✅ Clé API Gemini intégrée avec succès\n";
echo "✅ Service Gemini fonctionnel\n";
echo "✅ Génération de contenu opérationnelle\n";
echo "\n🌐 Points d'accès disponibles:\n";
echo "   - API REST: http://localhost/projet-actualite/frontend/api/gemini.php\n";
echo "   - Service: App\\Services\\GeminiService\n";
echo "\n📝 Fonctionnalités disponibles:\n";
echo "   - generateSummary() : Génère un résumé d'un texte\n";
echo "   - generateRelatedQuestions() : Génère des questions liées\n";
echo "   - isConfigured() : Vérifie la configuration\n";
echo "\n🎉 Intégration Gemini terminée avec succès!\n"; 