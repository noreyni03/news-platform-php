<?php
// Inclu depuis des pages admin; suppose session déjà démarrée
?>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin - Actualités</a>
        <ul class="navbar-nav flex-row gap-3">
            <li class="nav-item"><a class="nav-link" href="article_list.php">Articles</a></li>
            <li class="nav-item"><a class="nav-link" href="category_list.php">Catégories</a></li>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="user_list.php">Utilisateurs</a></li>
            <?php endif; ?>
        </ul>
        <div>
            <span class="text-light me-3"><?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>
