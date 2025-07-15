<?php
session_start();

// Vérifie qu'un utilisateur est connecté et qu'il a le rôle adéquat
function require_role(array $allowedRoles = ['admin', 'editeur']) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
        header('Location: login.php');
        exit();
    }

    if (!in_array($_SESSION['user']['role'], $allowedRoles, true)) {
        http_response_code(403);
        echo 'Accès refusé.';
        exit();
    }
}

function flash(string $key, ?string $value = null) {
    if (!isset($_SESSION['__flash'])) {
        $_SESSION['__flash'] = [];
    }
    if ($value === null) {
        if (isset($_SESSION['__flash'][$key])) {
            $val = $_SESSION['__flash'][$key];
            unset($_SESSION['__flash'][$key]);
            return $val;
        }
        return null;
    }
    $_SESSION['__flash'][$key] = $value;
}
