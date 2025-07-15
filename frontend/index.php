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
        $articles = $articleModel->getByCategory($categoryId, $page, 6, $publishedOnly);
        $totalCount = $articleModel->countByCategory($categoryId, $publishedOnly);
        $category = $categoryModel->getById($categoryId);
    } else {
        $articles = $articleModel->getAll($page, 6, $publishedOnly);
        $totalCount = $articleModel->count($publishedOnly);
        $category = null;
    }

    // Si aucun article publié, tenter de récupérer tous les articles (brouillons inclus)
    if (empty($articles)) {
        $publishedOnly = false;
        if ($categoryId) {
            $articles = $articleModel->getByCategory($categoryId, $page, 6, $publishedOnly);
            $totalCount = $articleModel->countByCategory($categoryId, $publishedOnly);
        } else {
            $articles = $articleModel->getAll($page, 6, $publishedOnly);
            $totalCount = $articleModel->count($publishedOnly);
        }
    }

    $totalPages = ceil(max(1, $totalCount) / 6);

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
    <title>Actu-Web | Les dernières nouvelles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .modal-bg {
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            max-height: 90vh;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
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
                <a href="admin/login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Connexion</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">

        <!-- Titre et filtres -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Derniers Articles</h1>
            <div class="relative" id="categories">
                <select id="category-filter" class="appearance-none w-48 bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-blue-500 category-select">
                    <option value="all">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= isset($_GET['category']) && $_GET['category']==$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>
        </div>

        <!-- Grille des articles -->
        <div id="articles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($articles)): ?>
                <p class="col-span-full text-center text-gray-500">Aucun article trouvé.</p>
            <?php else: ?>
                <?php 
                // Fonction pour obtenir la couleur selon la catégorie
                function getCategoryColor($categoryName) {
                    $colors = [
                        'Technologie' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'badge' => 'bg-blue-100', 'image' => '3498db'],
                        'Économie' => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'badge' => 'bg-green-100', 'image' => '2ecc71'],
                        'Voyage' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'badge' => 'bg-red-100', 'image' => 'e74c3c'],
                        'Santé' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'badge' => 'bg-purple-100', 'image' => '9b59b6'],
                        'Sport' => ['bg' => 'bg-orange-500', 'text' => 'text-orange-600', 'badge' => 'bg-orange-100', 'image' => 'f39c12'],
                        'Politique' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'badge' => 'bg-indigo-100', 'image' => '6366f1'],
                        'Culture' => ['bg' => 'bg-pink-500', 'text' => 'text-pink-600', 'badge' => 'bg-pink-100', 'image' => 'ec4899'],
                        'Sciences' => ['bg' => 'bg-teal-500', 'text' => 'text-teal-600', 'badge' => 'bg-teal-100', 'image' => '14b8a6'],
                        'Environnement' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'badge' => 'bg-emerald-100', 'image' => '10b981'],
                        'Éducation' => ['bg' => 'bg-cyan-500', 'text' => 'text-cyan-600', 'badge' => 'bg-cyan-100', 'image' => '06b6d4']
                    ];
                    
                    // Couleur par défaut si la catégorie n'est pas définie
                    $default = ['bg' => 'bg-gray-500', 'text' => 'text-gray-600', 'badge' => 'bg-gray-100', 'image' => '6b7280'];
                    
                    return isset($colors[$categoryName]) ? $colors[$categoryName] : $default;
                }
                
                foreach ($articles as $article): 
                    $colorScheme = getCategoryColor($article['category_name'] ?? '');
                ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden article-card cursor-pointer" data-id="<?= $article['id'] ?>">
                        <div class="<?= $colorScheme['bg'] ?> h-48 flex items-center justify-center p-6">
                            <h3 class="text-2xl font-bold text-white text-center leading-tight">
                                <?= htmlspecialchars($article['title']) ?>
                            </h3>
                        </div>
                        <div class="p-6">
                            <span class="text-sm <?= $colorScheme['text'] ?> <?= $colorScheme['badge'] ?> px-2 py-1 rounded-full category-badge"><?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie') ?></span>
                            <p class="mt-4 text-gray-600"><?= htmlspecialchars($article['summary'] ?? substr(strip_tags($article['content']), 0, 150) . '...') ?></p>
                            <div class="mt-4 flex justify-between items-center">
                                <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($article['created_at'])) ?></p>
                                <span class="text-xs text-gray-400">
                                    <i class="fas fa-user mr-1"></i>
                                    <?= htmlspecialchars($article['author_name'] ?? 'Anonyme') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div id="pagination-controls" class="flex justify-center items-center mt-12 space-x-4">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center pagination-btn">
                        <i class="fas fa-arrow-left mr-2"></i> Précédent
                    </a>
                <?php endif; ?>
                <span class="text-gray-700">Page <?= $page ?> sur <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center pagination-btn">
                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </main>

    <!-- Modal pour la consultation détaillée d'un article -->
    <div id="article-modal" class="fixed inset-0 w-full h-full flex items-center justify-center z-50 modal-bg hidden">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 md:w-3/4 lg:w-2/3 modal-content overflow-y-auto fade-in">
            <div class="sticky top-0 bg-white p-4 border-b flex justify-between items-center z-10">
                <h2 id="modal-title" class="text-2xl font-bold"></h2>
                <button id="close-modal" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
            </div>
            <div class="p-6 md:p-8">
                <img id="modal-image" src="" alt="Image de l'article" class="w-full h-64 object-cover rounded-lg mb-6">
                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                    <span id="modal-category" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium"></span>
                    <span id="modal-date"></span>
                </div>
                <div id="modal-content" class="prose max-w-none text-gray-700 leading-relaxed">
                    <!-- Contenu de l'article -->
                </div>
                
                <!-- Gemini Features -->
                <div class="mt-8 border-t pt-6 ai-feature">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-wand-magic-sparkles mr-2 text-blue-500"></i>Fonctionnalités IA</h3>
                    
                    <!-- Summarizer -->
                    <div class="mb-6">
                        <button id="summarize-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center ai-button">
                            ✨ Résumer l'article
                        </button>
                        <div id="summary-loader" class="mt-4 hidden">
                            <p class="text-gray-500 animate-pulse">Génération du résumé...</p>
                        </div>
                        <div id="summary-output" class="mt-4 p-4 bg-gray-100 rounded-lg text-gray-700 hidden"></div>
                    </div>

                    <!-- Related Questions -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Pour aller plus loin...</h4>
                        <div id="questions-loader" class="mt-4">
                            <p class="text-gray-500 animate-pulse">Génération des questions...</p>
                        </div>
                        <div id="questions-output" class="mt-2 text-gray-700 hidden prose max-w-none">
                            <!-- Les questions seront injectées ici -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t">
        <div class="container mx-auto px-6 text-center text-gray-600">
            <p>&copy; 2025 Actu-Web. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const articlesGrid = document.getElementById('articles-grid');
            const categoryFilter = document.getElementById('category-filter');
            const paginationControls = document.getElementById('pagination-controls');
            
            // Modal elements
            const modal = document.getElementById('article-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalImage = document.getElementById('modal-image');
            const modalCategory = document.getElementById('modal-category');
            const modalDate = document.getElementById('modal-date');
            const modalContent = document.getElementById('modal-content');

            // Gemini elements
            const summarizeBtn = document.getElementById('summarize-btn');
            const summaryLoader = document.getElementById('summary-loader');
            const summaryOutput = document.getElementById('summary-output');
            const questionsLoader = document.getElementById('questions-loader');
            const questionsOutput = document.getElementById('questions-output');

            let currentArticleContent = '';

            // --- Fonctions ---

            /**
             * Calls our backend API to generate AI content.
             * @param {string} action The action to perform (summarize, questions, article_ai).
             * @param {object} data The data to send.
             * @returns {Promise<object>} The generated content.
             */
            async function callBackendApi(action, data) {
                try {
                    const response = await fetch(`api/gemini.php?action=${action}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        throw new Error(`API call failed with status: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    if (result.error) {
                        throw new Error(result.error);
                    }
                    
                    return result;
                } catch (error) {
                    console.error("Error calling backend API:", error);
                    return { error: error.message };
                }
            }

            async function openModal(articleId) {
                // Récupérer les données de l'article via AJAX
                try {
                    const response = await fetch(`article.php?id=${articleId}&ajax=1`);
                    const article = await response.json();
                    
                    if (article) {
                        currentArticleContent = article.content.replace(/<[^>]*>?/gm, ' '); // Store clean text
                        modalTitle.textContent = article.title;
                        
                        // Utiliser la couleur de la catégorie pour l'image
                        const categoryColors = {
                            'Technologie': '3498db',
                            'Économie': '2ecc71',
                            'Voyage': 'e74c3c',
                            'Santé': '9b59b6',
                            'Sport': 'f39c12',
                            'Politique': '6366f1',
                            'Culture': 'ec4899',
                            'Sciences': '14b8a6',
                            'Environnement': '10b981',
                            'Éducation': '06b6d4'
                        };
                        const imageColor = categoryColors[article.category_name] || '6b7280';
                        modalImage.src = `https://placehold.co/600x400/${imageColor}/ffffff?text=${encodeURIComponent(article.title)}`;
                        
                        modalCategory.textContent = article.category_name || 'Sans catégorie';
                        modalDate.textContent = new Date(article.created_at).toLocaleDateString('fr-FR');
                        modalContent.innerHTML = article.content;
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';

                        // Trigger Gemini features
                        generateRelatedQuestions();
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement de l\'article:', error);
                    // Fallback: redirection vers la page article
                    window.location.href = `article.php?id=${articleId}`;
                }
            }
            
            function closeModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                // Reset AI content
                summaryOutput.classList.add('hidden');
                summaryOutput.innerHTML = '';
                questionsOutput.classList.add('hidden');
                questionsOutput.innerHTML = '';
                questionsLoader.classList.remove('hidden');
            }

            async function generateSummary() {
                summaryLoader.classList.remove('hidden');
                summaryOutput.classList.add('hidden');
                summarizeBtn.disabled = true;

                const result = await callBackendApi('summarize', { content: currentArticleContent });
                
                if (result.error) {
                    summaryOutput.innerHTML = `<p class="text-red-600">Erreur: ${result.error}</p>`;
                } else {
                    summaryOutput.innerHTML = result.summary;
                }
                
                summaryLoader.classList.add('hidden');
                summaryOutput.classList.remove('hidden');
                summarizeBtn.disabled = false;
            }

            async function generateRelatedQuestions() {
                questionsLoader.classList.remove('hidden');
                questionsOutput.classList.add('hidden');

                const result = await callBackendApi('questions', { content: currentArticleContent });
                
                if (result.error) {
                    questionsOutput.innerHTML = `<p class="text-red-600">Erreur: ${result.error}</p>`;
                } else {
                    // Format questions into an unordered list
                    const questionItems = result.questions.map(q => `<li>${q}</li>`).join('');
                    questionsOutput.innerHTML = `<ul>${questionItems}</ul>`;
                }
                
                questionsLoader.classList.add('hidden');
                questionsOutput.classList.remove('hidden');
            }

            // --- Écouteurs d'événements ---
            categoryFilter.addEventListener('change', (e) => {
                const categoryId = e.target.value;
                if (categoryId === 'all') {
                    window.location.href = 'index.php';
                } else {
                    window.location.href = `index.php?category=${categoryId}`;
                }
            });
            
            closeModalBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });
            summarizeBtn.addEventListener('click', generateSummary);

            // Add click listeners to article cards
            document.querySelectorAll('[data-id]').forEach(card => {
                card.addEventListener('click', () => openModal(card.dataset.id));
            });
        });
    </script>
</body>
</html> 