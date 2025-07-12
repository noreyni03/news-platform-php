<?php
require_once __DIR__ . '/../../vendor/autoload.php';

session_start();

// Si l'utilisateur n'est pas connecté, redirige simplement vers la page de connexion
if (!isset($_SESSION['token']) || !isset($_SESSION['user'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$token = $_SESSION['token'];

try {
    // Appel au service SOAP pour révoquer le jeton côté backend
    $soap = new \SoapClient('http://localhost/projet-actualite/backend/api/soap_server.php?wsdl');
    // Le service renvoie un JSON encodé sous forme de string -> on le decode pour vérifier la réponse
    $responseJson = $soap->logoutUser($token);
    $response = is_string($responseJson) ? json_decode($responseJson, true) : (array) $responseJson;
    // Vous pouvez gérer la réponse si nécessaire, mais même en cas d'échec nous détruisons la session côté front
} catch (Exception $e) {
    // Optionnel : journaliser l'erreur ou l'ignorer
}

// Suppression de toutes les données de session côté frontend
session_unset();
session_destroy();

// Redirection vers la page de connexion
header('Location: login.php');
exit();
