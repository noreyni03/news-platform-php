<?php
require_once __DIR__ . '/_auth.php';
require_role();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin - Actualités</a>
        <span class="navbar-text me-3">Connecté en tant que <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>
<div class="container my-4">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="list-group">
                <a href="article_list.php" class="list-group-item list-group-item-action">Articles</a>
                <a href="category_list.php" class="list-group-item list-group-item-action">Catégories</a>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="user_list.php" class="list-group-item list-group-item-action">Utilisateurs</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-9">
            <h3>Bienvenue sur le tableau de bord</h3>
            <p>Choisissez une section dans le menu pour commencer.</p>
        </div>
    </div>
</div>
</body>
</html>
