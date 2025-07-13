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
        $errors[] = "Échec de la suppression.";
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$categories = $categoryModel->getAll();
$flash = flash('success');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Gestion des catégories</h3>
        <a href="category_form.php" class="btn btn-primary">+ Nouvelle catégorie</a>
    </div>
    <?php if ($flash): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th width="120">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['description']) ?></td>
                    <td>
                        <a href="category_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Éditer</a>
                        <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
