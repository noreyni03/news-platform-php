<?php
namespace App\Controllers;

use App\Models\ApiToken;
use Exception;

class ApiTokenController
{
    private ApiToken $model;

    public function __construct()
    {
        $this->model = new ApiToken();
    }

    public function index(): array
    {
        try {
            return $this->model->getAll();
        } catch (Exception $e) {
            error_log('ApiTokenController@index: ' . $e->getMessage());
            return [];
        }
    }

    public function store(int $userId, ?string $expiresAt = null): string|false
    {
        try {
            return $this->model->create($userId, null, $expiresAt);
        } catch (Exception $e) {
            error_log('ApiTokenController@store: ' . $e->getMessage());
            return false;
        }
    }

    public function destroyByToken(string $token): bool
    {
        try {
            return $this->model->deleteByToken($token);
        } catch (Exception $e) {
            error_log('ApiTokenController@destroy: ' . $e->getMessage());
            return false;
        }
    }
}
