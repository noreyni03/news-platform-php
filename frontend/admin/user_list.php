<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';

use App\Models\User;

require_role(['admin']);

$userModel = new User();
$errors = [];
$success = null;

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'visiteur';

    if (!$username || !$email || !$password) {
        $errors[] = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    if (empty($errors)) {
        try {
            $userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);
            flash('success', "L'utilisateur '" . htmlspecialchars($username) . "' a été créé avec succès.");
            header('Location: user_list.php');
            exit();
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                 $errors[] = 'Ce nom d\'utilisateur ou cet email existe déjà.';
            } else {
                $errors[] = 'Erreur lors de la création de l\'utilisateur.';
            }
        }
    }
}

$users = $userModel->getAll();
$flash = flash('success');

function getRoleBadge($role) {
    $colors = [
        'admin' => 'bg-red-100 text-red-800',
        'editeur' => 'bg-yellow-100 text-yellow-800',
        'visiteur' => 'bg-blue-100 text-blue-800',
    ];
    $color = $colors[$role] ?? 'bg-gray-100 text-gray-800';
    return "<span class='px-2 py-1 font-semibold leading-tight text-xs rounded-full {$color}'>" . htmlspecialchars(ucfirst($role)) . "</span>";
}

?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
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
            <a href="category_list.php" class="sidebar-link">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="user_list.php" class="sidebar-link active">
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
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
            </div>
            <div class="flex items-center space-x-4">
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
                    <p class="font-bold">Erreur de validation</p>
                    <ul class="list-disc list-inside mt-2">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire de création -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ajouter un nouvel utilisateur</h2>
                <form method="post" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                        <input type="text" name="username" id="username" placeholder="ex: jeandupont" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" placeholder="ex: jean.dupont@email.com" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" name="password" id="password" placeholder="Min. 8 caractères" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select name="role" id="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="visiteur">Visiteur</option>
                            <option value="editeur">Éditeur</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="lg:col-span-1">
                        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                 <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom d'utilisateur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $u['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['username']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= getRoleBadge($u['role']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
            <a href="category_list.php" class="sidebar-link"><i class="fas fa-tags"></i> Catégories</a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="user_list.php" class="sidebar-link active"><i class="fas fa-users"></i> Utilisateurs</a>
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
