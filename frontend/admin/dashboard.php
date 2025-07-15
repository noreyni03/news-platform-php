<?php
require_once __DIR__ . '/_auth.php';
require_role();

// Données pour les cartes du tableau de bord
$cards = [
    [
        'href' => 'article_list.php',
        'title' => 'Gérer les Articles',
        'description' => 'Créer, modifier et supprimer des articles.',
        'icon' => 'fa-file-alt',
        'color' => 'blue',
        'role' => null
    ],
    [
        'href' => 'category_list.php',
        'title' => 'Gérer les Catégories',
        'description' => 'Organiser les articles par catégories.',
        'icon' => 'fa-tags',
        'color' => 'green',
        'role' => null
    ],
    [
        'href' => 'user_list.php',
        'title' => 'Gérer les Utilisateurs',
        'description' => 'Ajouter ou modifier des comptes utilisateurs.',
        'icon' => 'fa-users',
        'color' => 'purple',
        'role' => 'admin'
    ],
    [
        'href' => 'token_list.php',
        'title' => 'Gérer les Tokens API',
        'description' => 'Générez et révoquez des jetons d\'accès pour l\'API.',
        'icon' => 'fa-key',
        'color' => 'orange',
        'role' => 'admin'
    ]
];

?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
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
    <!-- Barre latérale de navigation -->
    <aside class="w-64 bg-white shadow-lg hidden md:block">
        <div class="p-6 flex items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-newspaper mr-3 text-blue-600"></i>
                <span>Admin Panel</span>
            </a>
        </div>
        <nav class="mt-4">
            <a href="dashboard.php" class="sidebar-link active">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <a href="article_list.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> Articles
            </a>
            <a href="category_list.php" class="sidebar-link">
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
                <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <span class="text-gray-600 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-lg"></i>
                        <span class="hidden sm:inline"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                    </span>
                </div>
                <a href="logout.php" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600 transition duration-300 text-sm font-medium flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span class="hidden sm:inline">Déconnexion</span>
                </a>
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800">Bienvenue, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</h2>
                <p class="mt-1 text-gray-600">Ceci est votre centre de contrôle. Utilisez les cartes ci-dessous ou le menu pour gérer le contenu du site.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($cards as $card): ?>
                    <?php if ($card['role'] === null || ($_SESSION['user']['role'] === $card['role'])): ?>
                        <a href="<?= $card['href'] ?>" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div class="p-3 rounded-full bg-<?= $card['color'] ?>-100 text-<?= $card['color'] ?>-600">
                                    <i class="fas <?= $card['icon'] ?> fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-semibold text-gray-900"><?= $card['title'] ?></h3>
                                <p class="text-gray-600 text-sm mt-1"><?= $card['description'] ?></p>
                            </div>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </main>
    </div>
</div>

<!-- Menu mobile (caché par défaut) -->
<div id="mobile-menu" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="fixed left-0 top-0 h-full bg-white w-64 shadow-lg p-4 z-40">
        <button id="close-mobile-menu" class="absolute top-4 right-4 text-gray-600 hover:text-gray-800">
            <i class="fas fa-times fa-lg"></i>
        </button>
        <nav class="mt-12">
            <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="article_list.php" class="sidebar-link"><i class="fas fa-file-alt"></i> Articles</a>
            <a href="category_list.php" class="sidebar-link"><i class="fas fa-tags"></i> Catégories</a>
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

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden');
        });

        closeMobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
        
        mobileMenu.addEventListener('click', (e) => {
            if (e.target === mobileMenu) {
                mobileMenu.classList.add('hidden');
            }
        });
    });
</script>

</body>
</html>
