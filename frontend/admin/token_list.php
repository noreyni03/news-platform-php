<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/_auth.php';

use App\Models\ApiToken;
use App\Models\User;

require_role(['admin']);

$tokenModel = new ApiToken();
$userModel = new User();
$errors = [];
$success = null;

// creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $expires = $_POST['expires_at'] ?? null;

    if (!$userId) {
        $errors[] = 'Utilisateur obligatoire.';
    }
    if (!$errors) {
        $token = $tokenModel->create($userId, null, $expires ?: null);
        if ($token) {
            $success = 'Jeton créé : ' . $token;
        } else {
            $errors[] = 'Erreur lors de la création.';
        }
    }
}

// delete
if (isset($_GET['delete'])) {
    $token = $_GET['delete'];
    $tokenModel->deleteByToken($token);
    header('Location: token_list.php');
    exit();
}

$tokens = $tokenModel->getAll();
$users  = $userModel->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jetons API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <h3 class="mb-4">Gestion des jetons API</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Nouveau jeton</div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="create">
                <div class="col-md-4">
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Utilisateur --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= $u['role'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="datetime-local" name="expires_at" class="form-control" placeholder="Expiration (optionnelle)">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Générer</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Token</th>
                <th>Utilisateur</th>
                <th>Expire le</th>
                <th>Créé le</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tokens as $t): ?>
                <tr>
                    <td class="text-break" style="max-width: 300px;"><?= htmlspecialchars($t['token']) ?></td>
                    <td><?= htmlspecialchars($t['username']) ?></td>
                    <td><?= $t['expires_at'] ? date('d/m/Y H:i', strtotime($t['expires_at'])) : '—' ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td>
                        <a href="token_list.php?delete=<?= urlencode($t['token']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce jeton ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
