<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Article;
use App\Models\Category;

// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fonction pour déterminer le format de réponse
function getResponseFormat() {
    $format = $_GET['format'] ?? 'json';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    
    if (strpos($accept, 'application/xml') !== false) {
        return 'xml';
    } elseif (strpos($accept, 'application/json') !== false) {
        return 'json';
    }
    
    return in_array($format, ['xml', 'json']) ? $format : 'json';
}

// Fonction pour envoyer la réponse
function sendResponse($data, $statusCode = 200) {
    $format = getResponseFormat();
    
    http_response_code($statusCode);
    
    if ($format === 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        echo arrayToXml($data, 'response');
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

// Fonction pour convertir un tableau en XML
function arrayToXml($data, $rootNodeName = 'root') {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<' . $rootNodeName . '>' . "\n";
    $xml .= arrayToXmlHelper($data);
    $xml .= '</' . $rootNodeName . '>';
    return $xml;
}

function arrayToXmlHelper($data, $indent = '  ') {
    $xml = '';
    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = 'item';
        }
        
        if (is_array($value)) {
            $xml .= $indent . '<' . $key . '>' . "\n";
            $xml .= arrayToXmlHelper($value, $indent . '  ');
            $xml .= $indent . '</' . $key . '>' . "\n";
        } else {
            $xml .= $indent . '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>' . "\n";
        }
    }
    return $xml;
}

// Récupération de la route
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Extraction du chemin après le nom du fichier
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$pathParts = explode('/', trim($path, '/'));
$scriptIndex = array_search($scriptName, $pathParts);

if ($scriptIndex !== false) {
    $pathParts = array_slice($pathParts, $scriptIndex + 1);
}

// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    $articleModel = new Article();
    $categoryModel = new Category();
    
    // Route: GET /articles
    if ($method === 'GET' && count($pathParts) === 1 && $pathParts[0] === 'articles') {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $articles = $articleModel->getAll($page, $limit, true);
        $totalCount = $articleModel->count(true);
        
        $response = [
            'success' => true,
            'data' => [
                'articles' => $articles,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'pages' => ceil($totalCount / $limit)
                ]
            ]
        ];
        
        sendResponse($response);
    }
    
    // Route: GET /articles/grouped
    elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'articles' && $pathParts[1] === 'grouped') {
        $groupedArticles = $articleModel->getGroupedByCategory(true);
        
        $response = [
            'success' => true,
            'data' => [
                'categories' => $groupedArticles
            ]
        ];
        
        sendResponse($response);
    }
    
    // Route: GET /articles/category/{id}
    elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[0] === 'articles' && $pathParts[1] === 'category') {
        $categoryId = (int)$pathParts[2];
        
        // Vérifier si la catégorie existe
        $category = $categoryModel->getById($categoryId);
        if (!$category) {
            sendResponse([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
            exit();
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $articles = $articleModel->getByCategory($categoryId, $page, $limit, true);
        $totalCount = $articleModel->countByCategory($categoryId, true);
        
        $response = [
            'success' => true,
            'data' => [
                'category' => $category,
                'articles' => $articles,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'pages' => ceil($totalCount / $limit)
                ]
            ]
        ];
        
        sendResponse($response);
    }
    
    // Route: GET /articles/{id}
    elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'articles') {
        $articleId = (int)$pathParts[1];
        
        $article = $articleModel->getById($articleId, true);
        if (!$article) {
            sendResponse([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
            exit();
        }
        
        $response = [
            'success' => true,
            'data' => [
                'article' => $article
            ]
        ];
        
        sendResponse($response);
    }
    
    // Route: GET /categories
    elseif ($method === 'GET' && count($pathParts) === 1 && $pathParts[0] === 'categories') {
        $categories = $categoryModel->getAll();
        
        $response = [
            'success' => true,
            'data' => [
                'categories' => $categories
            ]
        ];
        
        sendResponse($response);
    }
    
    // Route non trouvée
    else {
        sendResponse([
            'success' => false,
            'message' => 'Route non trouvée',
            'path' => $pathParts,
            'available_routes' => [
                'GET /articles' => 'Récupérer tous les articles',
                'GET /articles/grouped' => 'Récupérer les articles groupés par catégorie',
                'GET /articles/category/{id}' => 'Récupérer les articles d\'une catégorie',
                'GET /articles/{id}' => 'Récupérer un article par ID',
                'GET /categories' => 'Récupérer toutes les catégories'
            ]
        ], 404);
    }
    
} catch (Exception $e) {
    error_log("Erreur API REST: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Erreur interne du serveur: ' . $e->getMessage()
    ], 500);
} 