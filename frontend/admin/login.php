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

            // La réponse est un JSON encodé sous forme de chaîne (voir soap_server.php)
            if (is_string($response)) {
                $resp = json_decode($response, true);
            } elseif ($response instanceof stdClass) {
                // certains environnements SOAP emballent la chaîne dans un objet
                $resp = json_decode(json_encode($response), true);
            } else {
                $resp = (array)$response;
            }
            if (!is_array($resp)) {
                $resp = [];
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
    <title>Connexion Administration - Site d'Actualités</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Animation d'apparition */
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        
        /* Animation de focus */
        .input-focus:focus-within {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        /* Gradient animé */
        .gradient-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <!-- Fond décoratif -->
    <div class="fixed inset-0 gradient-bg opacity-10"></div>
    
    <!-- Container principal -->
    <div class="relative z-10 w-full max-w-md fade-in">
        <!-- Logo et titre -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4 shadow-lg">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Administration</h1>
            <p class="text-gray-600">Connectez-vous à votre espace de gestion</p>
        </div>

        <!-- Formulaire de connexion -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                    <span class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <!-- Champ nom d'utilisateur -->
                <div class="input-focus">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Nom d'utilisateur
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="username" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Entrez votre nom d'utilisateur"
                            required
                            autocomplete="username"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Champ mot de passe -->
                <div class="input-focus">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Mot de passe
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Entrez votre mot de passe"
                            required
                            autocomplete="current-password"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Bouton de connexion -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition duration-200 flex items-center justify-center group"
                >
                    <i class="fas fa-sign-in-alt mr-2 group-hover:translate-x-1 transition-transform duration-200"></i>
                    Se connecter
                </button>
            </form>

            <!-- Lien retour -->
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-blue-600 hover:text-blue-700 text-sm flex items-center justify-center group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform duration-200"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>&copy; <?= date('Y'); ?> Site d'Actualités. Tous droits réservés.</p>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Animation des champs au focus
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.classList.remove('scale-105');
            });
        });

        // Animation du bouton au clic
        document.querySelector('button[type="submit"]').addEventListener('click', function() {
            this.classList.add('scale-95');
            setTimeout(() => {
                this.classList.remove('scale-95');
            }, 150);
        });
    </script>
</body>
</html>
