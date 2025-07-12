<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use SoapClient;
use App\Config\DatabaseConfig;

session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        try {
            $soap = new \SoapClient('http://localhost/projet-actualite/backend/api/soap_server.php?wsdl');
            $response = $soap->authenticateUser($username, $password);
            // Le service SOAP peut renvoyer un tableau ou un objet stdClass selon la configuration
            if (is_array($response)) {
                $resp = $response;
            } else {
                $resp = (array)$response;
            }
            if (!empty($resp['success'])) {
                $_SESSION['user'] = (array)($resp['user'] ?? []);
                $_SESSION['token'] = $resp['token'] ?? null;
                header('Location: dashboard.php');
                exit();
            }
            $error = $resp['message'] ?? 'Erreur inconnue';
        } catch (Exception $e) {
            $error = 'Erreur de connexion au service SOAP';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center bg-light" style="height:100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-4 text-center">Connexion Admin</h4>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
