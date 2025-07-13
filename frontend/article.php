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

// Si c'est une requête AJAX, retourner les données en JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode($article);
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
    <title><?= htmlspecialchars($article['title']) ?> - Actu-Web</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .prose {
            color: #374151;
        }
        .prose p {
            margin-bottom: 1em;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-40">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-gray-800">
                <i class="fas fa-newspaper mr-2 text-blue-600"></i>Actu-Web
            </a>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-gray-600 hover:text-blue-600">Accueil</a>
                <?php if ($category): ?>
                    <a href="index.php?category=<?= $category['id'] ?>" class="text-gray-600 hover:text-blue-600"><?= htmlspecialchars($category['name']) ?></a>
                <?php endif; ?>
                <a href="admin/login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Connexion</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="index.php" class="hover:text-blue-600">Accueil</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <?php if ($category): ?>
                    <li><a href="index.php?category=<?= $category['id'] ?>" class="hover:text-blue-600"><?= htmlspecialchars($category['name']) ?></a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                <?php endif; ?>
                <li class="text-gray-900"><?= htmlspecialchars($article['title']) ?></li>
            </ol>
        </nav>

        <!-- Article Content -->
        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Article Header -->
            <?php
            // Fonction pour obtenir la couleur selon la catégorie
            function getCategoryColor($categoryName) {
                $colors = [
                    'Technologie' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'badge' => 'bg-blue-100'],
                    'Économie' => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'badge' => 'bg-green-100'],
                    'Voyage' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'badge' => 'bg-red-100'],
                    'Santé' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'badge' => 'bg-purple-100'],
                    'Sport' => ['bg' => 'bg-orange-500', 'text' => 'text-orange-600', 'badge' => 'bg-orange-100'],
                    'Politique' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'badge' => 'bg-indigo-100'],
                    'Culture' => ['bg' => 'bg-pink-500', 'text' => 'text-pink-600', 'badge' => 'bg-pink-100'],
                    'Sciences' => ['bg' => 'bg-teal-500', 'text' => 'text-teal-600', 'badge' => 'bg-teal-100'],
                    'Environnement' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'badge' => 'bg-emerald-100'],
                    'Éducation' => ['bg' => 'bg-cyan-500', 'text' => 'text-cyan-600', 'badge' => 'bg-cyan-100']
                ];
                
                $default = ['bg' => 'bg-gray-500', 'text' => 'text-gray-600', 'badge' => 'bg-gray-100'];
                return isset($colors[$categoryName]) ? $colors[$categoryName] : $default;
            }
            
            $colorScheme = getCategoryColor($article['category_name'] ?? '');
            ?>
            <div class="<?= $colorScheme['bg'] ?> p-8 text-white">
                <div class="flex items-center space-x-4 mb-4">
                    <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm">
                        <?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie') ?>
                    </span>
                    <span class="text-white text-opacity-80 text-sm">
                        <i class="fas fa-calendar mr-1"></i>
                        <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    </span>
                    <span class="text-white text-opacity-80 text-sm">
                        <i class="fas fa-user mr-1"></i>
                        <?= htmlspecialchars($article['author_name'] ?? 'Anonyme') ?>
                    </span>
                </div>
                
                <h1 class="text-4xl font-bold text-white mb-4"><?= htmlspecialchars($article['title']) ?></h1>
                
                <?php if ($article['summary']): ?>
                    <p class="text-xl text-white text-opacity-90 leading-relaxed"><?= htmlspecialchars($article['summary']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Article Body -->
            <div class="p-8">
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </div>
            </div>
        </article>

        <!-- Related Articles -->
        <?php if (!empty($relatedArticles)): ?>
            <section class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Articles similaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($relatedArticles as $rel): ?>
                        <?php if ($rel['id'] == $article['id']) continue; ?>
                        <?php $relColorScheme = getCategoryColor($rel['category_name'] ?? ''); ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="<?= $relColorScheme['bg'] ?> h-32 flex items-center justify-center p-4">
                                <h3 class="text-lg font-semibold text-white text-center leading-tight">
                                    <a href="article.php?id=<?= $rel['id'] ?>" class="hover:text-white hover:opacity-80">
                                        <?= htmlspecialchars($rel['title']) ?>
                                    </a>
                                </h3>
                            </div>
                            <div class="p-6">
                                <span class="text-sm <?= $relColorScheme['text'] ?> <?= $relColorScheme['badge'] ?> px-2 py-1 rounded-full">
                                    <?= htmlspecialchars($rel['category_name'] ?? 'Sans catégorie') ?>
                                </span>
                                <p class="mt-3 text-sm text-gray-600">
                                    <?= htmlspecialchars(substr(strip_tags($rel['content']), 0, 100) . '...') ?>
                                </p>
                                <div class="mt-4 flex justify-between items-center text-xs text-gray-500">
                                    <span><?= date('d/m/Y', strtotime($rel['created_at'])) ?></span>
                                    <a href="article.php?id=<?= $rel['id'] ?>" class="<?= $relColorScheme['text'] ?> hover:underline">
                                        Lire la suite <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t">
        <div class="container mx-auto px-6 text-center text-gray-600">
            <p>&copy; 2025 Actu-Web. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
