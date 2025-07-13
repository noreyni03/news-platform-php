<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\GeminiService;
use App\Models\Article;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $geminiService = new GeminiService();
    
    // Vérifier si l'API est configurée
    if (!$geminiService->isConfigured()) {
        http_response_code(500);
        echo json_encode([
            'error' => 'API Gemini non configurée',
            'message' => 'Veuillez configurer votre clé API Gemini dans le fichier de configuration.'
        ]);
        exit();
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'summarize':
                    if (!isset($input['content']) || empty($input['content'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Contenu manquant']);
                        exit();
                    }
                    
                    $summary = $geminiService->generateSummary($input['content']);
                    echo json_encode(['summary' => $summary]);
                    break;
                    
                case 'questions':
                    if (!isset($input['content']) || empty($input['content'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Contenu manquant']);
                        exit();
                    }
                    
                    $questions = $geminiService->generateRelatedQuestions($input['content']);
                    echo json_encode(['questions' => $questions]);
                    break;
                    
                case 'article_ai':
                    if (!isset($input['article_id']) || empty($input['article_id'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'ID d\'article manquant']);
                        exit();
                    }
                    
                    $articleModel = new Article();
                    $article = $articleModel->getById($input['article_id'], true);
                    
                    if (!$article) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Article non trouvé']);
                        exit();
                    }
                    
                    $content = strip_tags($article['content']);
                    $summary = $geminiService->generateSummary($content);
                    $questions = $geminiService->generateRelatedQuestions($content);
                    
                    echo json_encode([
                        'summary' => $summary,
                        'questions' => $questions
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Action non reconnue']);
                    break;
            }
            break;
            
        case 'GET':
            switch ($action) {
                case 'status':
                    echo json_encode([
                        'configured' => $geminiService->isConfigured(),
                        'status' => 'ready'
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Action non reconnue']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur',
        'message' => $e->getMessage()
    ]);
} 