<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';

use App\Models\Article;
use App\Models\Category;

require_role();

$articleModel = new Article();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    http_response_code(400);
    echo 'Identifiant invalide';
    exit();
}

$article = $articleModel->getById($id, false);
if (!$article) {
    http_response_code(404);
    echo 'Article introuvable';
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = $_POST['category_id'] ?? null;
    $published = isset($_POST['published']) ? 1 : 0;

    if (!$title) $errors[] = 'Le titre est obligatoire';
    if (!$content) $errors[] = 'Le contenu est obligatoire';

    if (!$errors) {
        $success = $articleModel->update($id, [
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'category_id' => $categoryId ?: null,
            'published' => $published,
        ]);
        if ($success) {
            flash('success', 'Article mis à jour avec succès.');
            header('Location: article_list.php');
            exit();
        }
        $errors[] = "Erreur lors de la mise à jour.";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <h3>Modifier l'article</h3>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? $article['title']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Résumé (optionnel)</label>
            <textarea name="summary" class="form-control" rows="2"><?= htmlspecialchars($_POST['summary'] ?? $article['summary']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Contenu</label>
            <textarea name="content" class="form-control" rows="10" required><?= htmlspecialchars($_POST['content'] ?? $article['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <select name="category_id" class="form-select">
                <option value="">-- Aucune --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ((($_POST['category_id'] ?? $article['category_id']) == $cat['id']) ? 'selected' : '') ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="published" class="form-check-input" id="published" <?= (($_POST['published'] ?? $article['published']) ? 'checked' : '') ?> />
            <label class="form-check-label" for="published">Publié</label>
        </div>
        <button class="btn btn-success">Mettre à jour</button>
        <a href="article_list.php" class="btn btn-secondary ms-2">Annuler</a>
    </form>
</div>
</body>
</html>
