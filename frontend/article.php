<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Article;
use App\Models\Category;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupération de l'ID d'article
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$articleId) {
    header('Location: index.php');
    exit();
}

$articleModel = new Article();
$categoryModel = new Category();

$article = $articleModel->getById($articleId, true);
if (!$article) {
    header('HTTP/1.0 404 Not Found');
    echo 'Article non trouvé';
    exit();
}

$relatedArticles = $articleModel->getByCategory($article['category_id'], 1, 3, true);
$category = $categoryModel->getById($article['category_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> - Actualités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Fallback minimal CSS -->
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-newspaper me-2"></i>Actualités</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <?php if ($category): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?category=<?= $category['id'] ?>">Catégorie : <?= htmlspecialchars($category['name']) ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>
    <p class="text-muted">Publié le <?= date('d/m/Y', strtotime($article['created_at'])) ?> par <?= htmlspecialchars($article['author_name'] ?? 'Anonyme') ?></p>

    <div class="mb-4">
        <?= nl2br($article['content']) ?>
    </div>

    <?php if (!empty($relatedArticles)): ?>
        <h3>Articles similaires</h3>
        <ul class="list-group list-group-flush">
            <?php foreach ($relatedArticles as $rel): ?>
                <?php if ($rel['id'] == $article['id']) continue; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="article.php?id=<?= $rel['id'] ?>"><?= htmlspecialchars($rel['title']) ?></a>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($rel['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Site d'Actualités</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
