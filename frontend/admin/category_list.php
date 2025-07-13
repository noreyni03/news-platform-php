<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';
use App\Models\Category;

require_role();

$categoryModel = new Category();
$errors = [];

// Suppression
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        if ($categoryModel->delete($id)) {
            flash('success', 'Catégorie supprimée avec succès.');
            header('Location: category_list.php');
            exit();
        }
        $errors[] = "Échec de la suppression. Assurez-vous que la catégorie n'est associée à aucun article.";
    } catch (Exception $e) {
        // Capturer les erreurs de la base de données (ex: contraintes de clé étrangère)
        if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
            $errors[] = "Impossible de supprimer cette catégorie car elle est utilisée par un ou plusieurs articles.";
        } else {
            $errors[] = "Une erreur est survenue lors de la suppression : " . $e->getMessage();
        }
    }
}

$categories = $categoryModel->getAll();
$flash = flash('success');

?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            color: #4b5563; /* text-gray-600 */
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: #eff6ff; /* bg-blue-50 */
            color: #2563eb; /* text-blue-600 */
        }
        .sidebar-link i {
            width: 1.25rem; /* w-5 */
            margin-right: 0.75rem; /* mr-3 */
        }
    </style>
</head>
<body class="h-full">

<div class="flex h-screen bg-gray-100">
    <!-- Barre latérale -->
    <aside class="w-64 bg-white shadow-lg hidden md:block">
        <div class="p-6 flex items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-newspaper mr-3 text-blue-600"></i>
                <span>Admin Panel</span>
            </a>
        </div>
        <nav class="mt-4">
            <a href="dashboard.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <a href="article_list.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> Articles
            </a>
            <a href="category_list.php" class="sidebar-link active">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="user_list.php" class="sidebar-link">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="token_list.php" class="sidebar-link">
                    <i class="fas fa-key"></i> Tokens API
                </a>
            <?php endif; ?>
        </nav>
        <div class="absolute bottom-0 w-full p-4">
             <a href="../index.php" class="sidebar-link text-sm">
                <i class="fas fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Barre de navigation supérieure -->
        <header class="flex justify-between items-center p-4 sm:p-6 bg-white border-b">
            <div class="flex items-center">
                <button id="mobile-menu-button" class="md:hidden mr-4 text-gray-600 hover:text-gray-800">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Catégories</h1>
            </div>
            <div class="flex items-center space-x-4">
                <a href="category_form.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 text-sm font-medium flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Nouvelle Catégorie
                </a>
                 <a href="logout.php" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600 transition duration-300 text-sm font-medium flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span class="hidden sm:inline">Déconnexion</span>
                </a>
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6">
            <?php if ($flash): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                    <p class="font-bold">Succès</p>
                    <p><?= htmlspecialchars($flash) ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                    <p class="font-bold">Erreur</p>
                    <ul class="list-disc list-inside mt-2">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                                        <p class="text-lg">Aucune catégorie trouvée.</p>
                                        <p class="text-sm">Commencez par <a href="category_form.php" class="text-blue-600 hover:underline">créer une nouvelle catégorie</a>.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $c['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($c['name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600 max-w-md truncate"><?= htmlspecialchars($c['description']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="category_form.php?id=<?= $c['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Éditer</a>
                                            <a href="?delete=<?= $c['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.');">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Menu mobile -->
<div id="mobile-menu" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="fixed left-0 top-0 h-full bg-white w-64 shadow-lg p-4 z-40">
        <button id="close-mobile-menu" class="absolute top-4 right-4 text-gray-600 hover:text-gray-800">
            <i class="fas fa-times fa-lg"></i>
        </button>
        <nav class="mt-12">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="article_list.php" class="sidebar-link"><i class="fas fa-file-alt"></i> Articles</a>
            <a href="category_list.php" class="sidebar-link active"><i class="fas fa-tags"></i> Catégories</a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="user_list.php" class="sidebar-link"><i class="fas fa-users"></i> Utilisateurs</a>
                <a href="token_list.php" class="sidebar-link"><i class="fas fa-key"></i> Tokens API</a>
            <?php endif; ?>
            <a href="../index.php" class="sidebar-link mt-8 border-t pt-4"><i class="fas fa-arrow-left"></i> Retour au site</a>
        </nav>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const closeMobileMenuButton = document.getElementById('close-mobile-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.remove('hidden');
            });
        }

        if (closeMobileMenuButton) {
            closeMobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        }
        
        if (mobileMenu) {
            mobileMenu.addEventListener('click', (e) => {
                if (e.target === mobileMenu) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    });
</script>

</body>
</html>
