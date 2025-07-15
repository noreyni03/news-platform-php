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
    }

    if (!$errors) {
        $ok = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        if ($ok) {
            $success = 'Utilisateur créé.';
        } else {
            $errors[] = 'Erreur lors de la création.';
        }
    }
}

$users = $userModel->getAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-4">
    <h3 class="mb-4">Gestion des utilisateurs</h3>

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
        <div class="card-header">Nouvel utilisateur</div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="create">
                <div class="col-md-3">
                    <input type="text" name="username" placeholder="Nom d'utilisateur" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" placeholder="Email" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <input type="password" name="password" placeholder="Mot de passe" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="visiteur">Visiteur</option>
                        <option value="editeur">Éditeur</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Créé le</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
