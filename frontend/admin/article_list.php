<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';
use App\Models\Article;
use App\Models\Category;

require_role();

$articleModel = new Article();
$categoryModel = new Category();

$articles = $articleModel->getAll(1, 100, false); // récupérer tout
$flash = flash('success');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Gestion des articles</h3>
        <a href="article_form.php" class="btn btn-primary">+ Nouvel article</a>
    </div>
    <?php if ($flash): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Publiée</th>
                <th>Créée le</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= htmlspecialchars($a['category_name']) ?></td>
                    <td><?= htmlspecialchars($a['author_name']) ?></td>
                    <td><?= $a['published'] ? 'Oui' : 'Non' ?></td>
                    <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
