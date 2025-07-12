<?php
// Vue Tailwind de la page d'accueil. Les variables suivantes doivent être disponibles :
// $articles, $categories, $totalPages, $page, $category
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site d'Actualités</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Animation d'apparition */
        .fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95);} to {opacity: 1; transform: scale(1);} }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header / Navigation -->
<header class="bg-white shadow-md sticky top-0 z-40">
    <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-newspaper mr-2 text-blue-600"></i>Actualités
        </a>
        <div class="flex items-center space-x-4 relative">
            <a href="index.php" class="text-gray-600 hover:text-blue-600 hidden sm:inline">Accueil</a>
            <!-- Menu Catégories -->
            <div class="relative">
                <button id="category-menu-btn" class="text-gray-600 hover:text-blue-600 flex items-center focus:outline-none">
                    Catégories <i class="fas fa-chevron-down ml-1 text-xs"></i>
                </button>
                <ul id="category-menu" class="absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg hidden z-50">
                    <li><a href="index.php" class="block px-4 py-2 hover:bg-gray-100">Toutes les catégories</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="index.php?category=<?= $cat['id'] ?>" class="block px-4 py-2 hover:bg-gray-100"><?= htmlspecialchars($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="admin/login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center text-sm">
                <i class="fas fa-sign-in-alt mr-2"></i>Administration
            </a>
        </div>
    </nav>
</header>

<!-- Hero section -->
<section class="hidden"></section>

<!-- Contenu principal -->
<main class="container mx-auto px-6 py-10">

    <!-- Titre et filtres -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Derniers Articles</h1>
        <form method="get" class="relative w-60">
            <select name="category" onchange="this.form.submit()" class="appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= isset($_GET['category']) && $_GET['category']==$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </form>
    </div>

    <?php
        /**
         * Retourne une classe Tailwind bg-* en fonction de la catégorie (ou d'un hash fallback).
         */
        function categoryColor(string $catName): string
        {
            $map = [
                'Technologie' => 'bg-blue-500',
                'Économie'     => 'bg-green-500',
                'Voyage'       => 'bg-red-500',
                'Santé'        => 'bg-purple-600',
                'Sport'        => 'bg-orange-500',
            ];
            if (isset($map[$catName])) {
                return $map[$catName];
            }
            // fallback : génération pseudo-aléatoire basée sur un hash du nom
            $colors = ['bg-rose-500','bg-fuchsia-500','bg-amber-500','bg-teal-500','bg-cyan-600'];
            return $colors[crc32($catName) % count($colors)];
        }
    ?>

    <!-- Liste des articles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($articles)): ?>
            <p class="col-span-full text-center text-gray-500">Aucun article trouvé.</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <?php $colorClass = categoryColor($article['category_name'] ?? ''); ?>
                <div class="rounded-lg shadow hover:-translate-y-1 hover:shadow-xl transition-transform duration-300 overflow-hidden flex flex-col">
                    <!-- Header coloré avec le titre -->
                    <div class="<?php echo $colorClass; ?> px-4 py-10 flex items-center justify-center">
                        <h3 class="text-4xl font-bold text-center text-white leading-tight">
                            <?= htmlspecialchars($article['title']) ?>
                        </h3>
                    </div>

                    <!-- Corps blanc -->
                    <div class="bg-white p-6 flex flex-col flex-grow">
                        <div class="flex justify-between items-center mb-3 text-sm">
                            <span class="text-blue-600 bg-blue-100 px-2 py-1 rounded-full text-sm font-medium">
                                <?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie') ?>
                            </span>
                            <span class="text-gray-500"><i class="fas fa-calendar mr-1"></i><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                        </div>
                        <p class="text-gray-700 mb-4 line-clamp-3 flex-grow">
                            <?= htmlspecialchars($article['summary'] ?? substr(strip_tags($article['content']), 0, 150) . '...') ?>
                        </p>
                        <div class="flex justify-between items-center mt-auto text-sm">
                            <span class="text-gray-500"><i class="fas fa-user mr-1"></i><?= htmlspecialchars($article['author_name'] ?? 'Anonyme') ?></span>
                            <a href="article.php?id=<?= $article['id'] ?>" class="text-blue-600 hover:underline flex items-center">
                                Lire la suite <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center mt-12 space-x-4">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center">
                    <i class="fas fa-chevron-left mr-2"></i> Précédent
                </a>
            <?php endif; ?>
            <span class="text-gray-700">Page <?= $page ?> sur <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center">
                    Suivant <i class="fas fa-chevron-right ml-2"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<!-- Footer -->
<footer class="bg-white mt-12 py-6 border-t">
    <div class="container mx-auto px-6 text-center text-gray-600">
        <p>&copy; <?= date('Y'); ?> Site d'Actualités. Tous droits réservés.</p>
    </div>
</footer>

<!-- Scripts -->
<script>
// Gestion de l'ouverture/fermeture du menu Catégories
const btn = document.getElementById('category-menu-btn');
const menu = document.getElementById('category-menu');
if (btn && menu) {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
}
</script>

</body>
</html>
