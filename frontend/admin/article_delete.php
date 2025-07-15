<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';

use App\Models\Article;

require_role();

$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    http_response_code(400);
    echo 'Identifiant invalide';
    exit();
}

$articleModel = new Article();
$deleted = $articleModel->delete($id);

if ($deleted) {
    flash('success', 'Article supprimé avec succès.');
} else {
    flash('success', "Impossible de supprimer l'article.");
}

header('Location: article_list.php');
exit();
