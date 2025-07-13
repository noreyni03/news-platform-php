<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';
use App\Models\Category;

require_role();

$categoryModel = new Category();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editing = $id !== null;
$category = $editing ? $categoryModel->getById($id) : null;
if ($editing && !$category) {
    http_response_code(404);
    echo 'Catégorie introuvable';
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$name) $errors[] = 'Le nom est obligatoire';

    if (!$errors) {
        if ($editing) {
            $success = $categoryModel->update($id, [
                'name' => $name,
                'description' => $description,
            ]);
            $msg = 'Catégorie mise à jour avec succès.';
        } else {
            $success = $categoryModel->create([
                'name' => $name,
                'description' => $description,
            ]);
            $msg = 'Catégorie créée avec succès.';
        }
        if ($success) {
            flash('success', $msg);
            header('Location: category_list.php');
            exit();
        }
        $errors[] = 'Une erreur est survenue.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Modifier' : 'Nouvelle' ?> catégorie - Administration</title>
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
                <a href="category_list.php" class="text-blue-600 font-semibold flex items-center">
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?= $editing ? 'Modifier la catégorie' : 'Créer une nouvelle catégorie' ?>
                    </h1>
                    <p class="text-gray-600">
                        <?= $editing ? 'Modifiez les informations de cette catégorie' : 'Ajoutez une nouvelle catégorie pour organiser vos articles' ?>
                    </p>
                </div>
                <a href="category_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux catégories
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
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden max-w-2xl mx-auto">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-tag mr-3"></i>Informations de la catégorie
                </h2>
            </div>
            
            <form method="post" class="p-6 space-y-6">
                <!-- Nom de la catégorie -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="name">
                        <i class="fas fa-tag mr-2 text-green-600"></i>Nom de la catégorie
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" 
                        placeholder="Ex: Technologie, Économie, Sport..."
                        required 
                        value="<?= htmlspecialchars($_POST['name'] ?? ($category['name'] ?? '')) ?>"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        Le nom de la catégorie sera affiché publiquement sur le site
                    </p>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700" for="description">
                        <i class="fas fa-align-left mr-2 text-blue-600"></i>Description (optionnelle)
                    </label>
                    <textarea 
                        name="description" 
                        id="description"
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none" 
                        rows="4"
                        placeholder="Décrivez brièvement le contenu de cette catégorie..."
                    ><?= htmlspecialchars($_POST['description'] ?? ($category['description'] ?? '')) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        Cette description peut être utilisée pour expliquer le contenu de la catégorie
                    </p>
                </div>

                <!-- Informations supplémentaires -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 mb-1">À propos des catégories</h4>
                            <p class="text-xs text-blue-700">
                                Les catégories permettent d'organiser vos articles par thème. 
                                <?= $editing ? 'Modifier une catégorie existante n\'affectera pas les articles déjà publiés.' : 'Une fois créée, vous pourrez assigner cette catégorie à vos articles.' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button 
                        type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all flex items-center shadow-lg hover:shadow-xl"
                    >
                        <i class="fas fa-save mr-2"></i>
                        <?= $editing ? 'Mettre à jour' : 'Créer' ?> la catégorie
                    </button>
                    <a 
                        href="category_list.php" 
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Aperçu de la catégorie (si en mode édition) -->
        <?php if ($editing && $category): ?>
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-eye mr-3"></i>Aperçu de la catégorie
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-purple-600 bg-purple-100 px-3 py-1 rounded-full text-sm font-medium">
                                <?= htmlspecialchars($category['name']) ?>
                            </span>
                            <span class="text-gray-500 text-sm">
                                <i class="fas fa-calendar mr-1"></i>Modifiée le <?= date('d/m/Y', strtotime($category['updated_at'] ?? $category['created_at'])) ?>
                            </span>
                        </div>
                        <?php if ($category['description']): ?>
                            <p class="text-gray-700 text-sm">
                                <?= htmlspecialchars($category['description']) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm italic">Aucune description</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t border-gray-200">
        <div class="container mx-auto px-6 text-center text-gray-600">
            <p>&copy; <?= date('Y'); ?> Administration - Site d'Actualités. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
