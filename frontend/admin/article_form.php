<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';
use App\Models\Article;
use App\Models\Category;

require_role();

$articleModel = new Article();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = $_POST['category_id'] ?? null;
    $published = isset($_POST['published']) ? 1 : 0;

    if (!$title) $errors[] = 'Le titre est obligatoire';
    if (!$content) $errors[] = 'Le contenu est obligatoire';

    if (!$errors) {
        $success = $articleModel->create([
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'category_id' => $categoryId ?: null,
            'author_id' => $_SESSION['user']['id'],
            'published' => $published,
        ]);
        if ($success) {
            flash('success', 'Article créé avec succès.');
            header('Location: article_list.php');
            exit();
        }
        $errors[] = 'Erreur lors de la création.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel article - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .fade-in { 
            animation: fadeIn 0.3s ease-out; 
        }
        @keyframes fadeIn { 
            from { 
                opacity: 0; 
                transform: translateY(10px);
            } 
            to {
                opacity: 1; 
                transform: translateY(0);
            } 
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <!-- Header moderne -->
    <header class="bg-white shadow-md sticky top-0 z-40 border-b border-gray-200">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-gray-800 flex items-center hover:text-blue-600 transition-colors">
                <i class="fas fa-newspaper mr-2 text-blue-600"></i>Administration
            </a>
            <div class="flex items-center space-x-6">
                <a href="article_list.php" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center">
                    <i class="fas fa-list mr-2"></i>Articles
                </a>
                <a href="category_list.php" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center">
                    <i class="fas fa-tags mr-2"></i>Catégories
                </a>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="user_list.php" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center">
                        <i class="fas fa-users mr-2"></i>Utilisateurs
                    </a>
                <?php endif; ?>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-600 text-sm">
                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>
                    </span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm transition-colors flex items-center">
                        <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenu principal -->
    <main class="container mx-auto px-6 py-8 fade-in">
        <!-- En-tête de la page -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Créer un nouvel article</h1>
                    <p class="text-gray-600">Rédigez et publiez un nouvel article sur votre site d'actualités</p>
                </div>
                <a href="article_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
                </a>
            </div>
        </div>

        <!-- Messages d'erreur -->
        <?php if ($errors): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 fade-in" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                    <div>
                        <strong class="font-semibold">Erreur de validation</strong>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-edit mr-3"></i>Informations de l'article
                </h2>
            </div>
            
            <form method="post" class="p-6 space-y-6">
                <!-- Titre -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="title">
                        <i class="fas fa-heading mr-2 text-blue-600"></i>Titre de l'article
                    </label>
                    <input 
                        type="text" 
                        name="title" 
                        id="title" 
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" 
                        placeholder="Entrez le titre de votre article..."
                        required 
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                    >
                </div>

                <!-- Résumé -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="summary">
                        <i class="fas fa-align-left mr-2 text-green-600"></i>Résumé (optionnel)
                    </label>
                    <textarea 
                        name="summary" 
                        id="summary" 
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none" 
                        rows="3"
                        placeholder="Un bref résumé de votre article..."
                    ><?= htmlspecialchars($_POST['summary'] ?? '') ?></textarea>
                </div>

                <!-- Contenu -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="content">
                        <i class="fas fa-file-text mr-2 text-purple-600"></i>Contenu de l'article
                    </label>
                    <textarea 
                        name="content" 
                        id="content" 
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none" 
                        rows="12"
                        placeholder="Rédigez le contenu complet de votre article..."
                        required
                    ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>

                <!-- Catégorie -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="category_id">
                        <i class="fas fa-tag mr-2 text-orange-600"></i>Catégorie
                    </label>
                    <select 
                        name="category_id" 
                        id="category_id" 
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                    >
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Statut de publication -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <input 
                            type="checkbox" 
                            name="published" 
                            id="published" 
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" 
                            <?= isset($_POST['published']) ? 'checked' : '' ?>
                        >
                        <label class="text-sm font-semibold text-gray-700 flex items-center" for="published">
                            <i class="fas fa-globe mr-2 text-green-600"></i>Publier immédiatement
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-8">
                        Si coché, l'article sera visible publiquement dès sa création. Sinon, il restera en brouillon.
                    </p>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all flex items-center shadow-lg hover:shadow-xl"
                    >
                        <i class="fas fa-save mr-2"></i>Enregistrer l'article
                    </button>
                    <a 
                        href="article_list.php" 
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t border-gray-200">
        <div class="container mx-auto px-6 text-center text-gray-600">
            <p>&copy; <?= date('Y'); ?> Administration - Site d'Actualités. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
