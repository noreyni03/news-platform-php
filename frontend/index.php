<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Article;
use App\Models\Category;

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Récupération des paramètres
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

    // Initialisation des modèles
    $articleModel = new Article();
    $categoryModel = new Category();

    // Récupération des données
    $categories = $categoryModel->getAll();

    $publishedOnly = true;
    if ($categoryId) {
        $articles = $articleModel->getByCategory($categoryId, $page, 5, $publishedOnly);
        $totalCount = $articleModel->countByCategory($categoryId, $publishedOnly);
        $category = $categoryModel->getById($categoryId);
    } else {
        $articles = $articleModel->getAll($page, 5, $publishedOnly);
        $totalCount = $articleModel->count($publishedOnly);
        $category = null;
    }

    // Si aucun article publié, tenter de récupérer tous les articles (brouillons inclus)
    if (empty($articles)) {
        $publishedOnly = false;
        if ($categoryId) {
            $articles = $articleModel->getByCategory($categoryId, $page, 5, $publishedOnly);
            $totalCount = $articleModel->countByCategory($categoryId, $publishedOnly);
        } else {
            $articles = $articleModel->getAll($page, 5, $publishedOnly);
            $totalCount = $articleModel->count($publishedOnly);
        }
    }

    $totalPages = ceil(max(1, $totalCount) / 5);

    // Utilise la nouvelle vue Tailwind pour l'affichage
    require __DIR__ . '/views/public/home_tailwind.php';
    // Arrête l'exécution pour éviter que l'ancien template Bootstrap ne s'affiche encore
    exit;
    
} catch (Exception $e) {
    // En cas d'erreur, afficher une page d'erreur simple
    $error = $e->getMessage();
    $articles = [];
    $categories = [];
    $totalPages = 0;
    $category = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site d'Actualités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Fallback minimal CSS if CDN assets fail -->
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        .article-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .article-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .category-badge {
            font-size: 0.8em;
        }
        .pagination .page-link {
            color: #007bff;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-newspaper me-2"></i>Actualités
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Catégories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php">Toutes les catégories</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ($categories as $cat): ?>
                                <li><a class="dropdown-item" href="index.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Administration
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Bienvenue sur notre site d'actualités</h1>
            <p class="lead">Découvrez les dernières nouvelles et informations importantes</p>
            <?php if ($category): ?>
                <div class="mt-3">
                    <span class="badge bg-light text-dark fs-6">Catégorie: <?= htmlspecialchars($category['name']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contenu principal -->
    <div class="container my-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <h4>Erreur de connexion à la base de données</h4>
                <p><?= htmlspecialchars($error) ?></p>
                <p>Veuillez vérifier la configuration de votre base de données.</p>
            </div>
        <?php else: ?>
            <!-- Filtres et navigation -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                            <?php if ($category): ?>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Liste des articles -->
            <div class="row">
                <?php if (empty($articles)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun article trouvé.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card article-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary category-badge">
                                            <?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie') ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                        </small>
                                    </div>
                                    
                                    <h5 class="card-title">
                                        <a href="article.php?id=<?= $article['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars($article['summary'] ?? substr($article['content'], 0, 150) . '...') ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($article['author_name'] ?? 'Anonyme') ?>
                                        </small>
                                        <a href="article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            Lire la suite <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navigation des articles" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <!-- Bouton précédent -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </span>
                            </li>
                        <?php endif; ?>

                        <!-- Pages -->
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Bouton suivant -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Site d'Actualités</h5>
                    <p class="mb-0">Votre source d'informations fiables et à jour.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-code me-1"></i>
                        Projet d'architecture logicielle
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 