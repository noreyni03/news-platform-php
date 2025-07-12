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
    <title><?= $editing ? 'Modifier' : 'Nouvelle' ?> catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <h3><?= $editing ? 'Modifier' : 'Créer une' ?> catégorie</h3>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? ($category['name'] ?? '')) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description (optionnelle)</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? ($category['description'] ?? '')) ?></textarea>
        </div>
        <button class="btn btn-success">Enregistrer</button>
        <a href="category_list.php" class="btn btn-secondary ms-2">Annuler</a>
    </form>
</div>
</body>
</html>
