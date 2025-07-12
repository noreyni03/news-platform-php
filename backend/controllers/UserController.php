<?php
namespace App\Controllers;

use App\Models\User;
use Exception;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Retourne la liste des utilisateurs
     * @return array
     */
    public function index(): array
    {
        try {
            return $this->userModel->getAll();
        } catch (Exception $e) {
            error_log('UserController@index: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée un nouvel utilisateur
     * @param array $data
     * @return bool
     */
    public function store(array $data): bool
    {
        try {
            return $this->userModel->create($data);
        } catch (Exception $e) {
            error_log('UserController@store: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un utilisateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            return $this->userModel->update($id, $data);
        } catch (Exception $e) {
            error_log('UserController@update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un utilisateur
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        try {
            return $this->userModel->delete($id);
        } catch (Exception $e) {
            error_log('UserController@destroy: ' . $e->getMessage());
            return false;
        }
    }
}
