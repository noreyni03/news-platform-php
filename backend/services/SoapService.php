<?php
namespace App\Services;

use App\Models\User;
use App\Models\ApiToken;

class SoapService {
    private $userModel;
    private $tokenModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->tokenModel = new ApiToken();
    }
    
    /**
     * Authentifier un utilisateur
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticateUser($username, $password) {
        try {
            $user = $this->userModel->authenticate($username, $password);
            if ($user) {
                // Générer un nouveau jeton d'API pour cet utilisateur (pour simplifier)
                $token = $this->tokenModel->create($user['id']);

                return [
                    'success' => true,
                    'user'    => $user,
                    'token'   => $token,
                    'message' => 'Authentification réussie'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Nom d\'utilisateur ou mot de passe incorrect'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'authentification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lister tous les utilisateurs (nécessite un token admin)
     * @param string $token
     * @return array
     */
    public function listUsers($token) {
        try {
            $tokenData = $this->tokenModel->validateToken($token);
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            if ($tokenData['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Accès refusé: droits administrateur requis'
                ];
            }
            
            $users = $this->userModel->getAll();
            return [
                'success' => true,
                'users' => $users,
                'count' => count($users)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir un utilisateur par ID (nécessite un token admin)
     * @param string $token
     * @param int $userId
     * @return array
     */
    public function getUserById($token, $userId) {
        try {
            $tokenData = $this->tokenModel->validateToken($token);
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            if ($tokenData['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Accès refusé: droits administrateur requis'
                ];
            }
            
            $user = $this->userModel->getById($userId);
            if ($user) {
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Créer un nouvel utilisateur (nécessite un token admin)
     * @param string $token
     * @param array $userData
     * @return array
     */
    public function createUser($token, $userData) {
        try {
            $tokenData = $this->tokenModel->validateToken($token);
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            if ($tokenData['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Accès refusé: droits administrateur requis'
                ];
            }
            
            // Validation des données
            if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
                return [
                    'success' => false,
                    'message' => 'Tous les champs obligatoires doivent être remplis'
                ];
            }
            
            // Vérifier si l'utilisateur existe déjà
            if ($this->userModel->getByUsername($userData['username'])) {
                return [
                    'success' => false,
                    'message' => 'Ce nom d\'utilisateur existe déjà'
                ];
            }
            
            if ($this->userModel->getByEmail($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Cette adresse email existe déjà'
                ];
            }
            
            $result = $this->userModel->create($userData);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'utilisateur'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modifier un utilisateur (nécessite un token admin)
     * @param string $token
     * @param int $userId
     * @param array $userData
     * @return array
     */
    public function updateUser($token, $userId, $userData) {
        try {
            $tokenData = $this->tokenModel->validateToken($token);
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            if ($tokenData['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Accès refusé: droits administrateur requis'
                ];
            }
            
            // Vérifier si l'utilisateur existe
            $existingUser = $this->userModel->getById($userId);
            if (!$existingUser) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ];
            }
            
            $result = $this->userModel->update($userId, $userData);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Utilisateur modifié avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la modification de l\'utilisateur'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification de l\'utilisateur: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un utilisateur (nécessite un token admin)
     * @param string $token
     * @param int $userId
     * @return array
     */
    public function deleteUser($token, $userId) {
        try {
            $tokenData = $this->tokenModel->validateToken($token);
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            if ($tokenData['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Accès refusé: droits administrateur requis'
                ];
            }
            
            // Vérifier si l'utilisateur existe
            $existingUser = $this->userModel->getById($userId);
            if (!$existingUser) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ];
            }
            
            $result = $this->userModel->delete($userId);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Utilisateur supprimé avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression de l\'utilisateur'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage()
            ];
        }
    }
} 