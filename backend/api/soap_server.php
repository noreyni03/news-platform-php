<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\SoapService;

// Configuration des en-têtes CORS pour permettre l'accès depuis l'application Java
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration du serveur SOAP
$options = [
    'uri' => 'http://localhost/projet-actualite/backend/api/soap_server.php',
    'soap_version' => SOAP_1_2,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'features' => SOAP_SINGLE_ELEMENT_ARRAYS
];

// Si l'on ne demande pas simplement le WSDL, on instancie réellement le service SOAP
if (!isset($_GET['wsdl'])) {
    // Création du serveur SOAP
    $server = new SoapServer(null, $options);

    // Enregistrement des fonctions du service
    $soapService = new SoapService();


}

// Définition des fonctions SOAP
function authenticateUser($username, $password) {
    global $soapService;
    return json_encode($soapService->authenticateUser($username, $password));
}

function listUsers($token) {
    global $soapService;
    return json_encode($soapService->listUsers($token));
}

function getUserById($token, $userId) {
    global $soapService;
    return json_encode($soapService->getUserById($token, $userId));
}

function createUser($token, $userData) {
    global $soapService;
    return json_encode($soapService->createUser($token, $userData));
}

function updateUser($token, $userId, $userData) {
    global $soapService;
    return json_encode($soapService->updateUser($token, $userId, $userData));
}

function deleteUser($token, $userId) {
    global $soapService;
    return json_encode($soapService->deleteUser($token, $userId));
}

function listApiTokens($token) {
    global $soapService;
    return json_encode($soapService->listApiTokens($token));
}

function generateApiToken($adminTok, $id, $exp = null) {
    global $soapService;
    return json_encode($soapService->generateApiToken($adminTok, $id, $exp));
}

if (!function_exists('revokeApiToken')) {
    function revokeApiToken($adminTok, $tok) {
        global $soapService;
        return json_encode($soapService->revokeApiToken($adminTok, $tok));
    }
}

function logoutUser($token) {
    global $soapService;
    return json_encode($soapService->logoutUser($token));
}

// Maintenant que toutes les fonctions sont déclarées, nous pouvons les enregistrer auprès du serveur SOAP
if (!isset($_GET['wsdl'])) {
    $server->addFunction([
        'authenticateUser',
        'listUsers',
        'getUserById',
        'createUser',
        'updateUser',
        'deleteUser',
        'listApiTokens',
        'generateApiToken',
        'revokeApiToken',
        'logoutUser'
    ]);
}


try {
    if (isset($_GET['wsdl'])) {
        // Génération du WSDL
        $wsdl = '<?xml version="1.0" encoding="UTF-8"?>
<definitions name="ActualiteService" 
             targetNamespace="http://localhost/projet-actualite/backend/api/soap_server.php"
             xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:tns="http://localhost/projet-actualite/backend/api/soap_server.php"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema">

    <message name="AuthenticateUserRequest">
        <part name="username" type="xsd:string"/>
        <part name="password" type="xsd:string"/>
    </message>
    
    <message name="AuthenticateUserResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="ListUsersRequest">
        <part name="token" type="xsd:string"/>
    </message>
    
    <message name="ListUsersResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="GetUserByIdRequest">
        <part name="token" type="xsd:string"/>
        <part name="userId" type="xsd:int"/>
    </message>
    
    <message name="GetUserByIdResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="CreateUserRequest">
        <part name="token" type="xsd:string"/>
        <part name="userData" type="xsd:string"/>
    </message>
    
    <message name="CreateUserResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="UpdateUserRequest">
        <part name="token" type="xsd:string"/>
        <part name="userId" type="xsd:int"/>
        <part name="userData" type="xsd:string"/>
    </message>
    
    <message name="UpdateUserResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="DeleteUserRequest">
        <part name="token" type="xsd:string"/>
        <part name="userId" type="xsd:int"/>
    </message>
    
    <message name="DeleteUserResponse">
        <part name="result" type="xsd:string"/>
    </message>
    
    <message name="LogoutUserRequest">
        <part name="token" type="xsd:string"/>
    </message>
    
    <message name="LogoutUserResponse">
        <part name="result" type="xsd:string"/>
    </message>

    <portType name="ActualitePortType">
        <operation name="authenticateUser">
            <input message="tns:AuthenticateUserRequest"/>
            <output message="tns:AuthenticateUserResponse"/>
        </operation>
        
        <operation name="listUsers">
            <input message="tns:ListUsersRequest"/>
            <output message="tns:ListUsersResponse"/>
        </operation>
        
        <operation name="getUserById">
            <input message="tns:GetUserByIdRequest"/>
            <output message="tns:GetUserByIdResponse"/>
        </operation>
        
        <operation name="createUser">
            <input message="tns:CreateUserRequest"/>
            <output message="tns:CreateUserResponse"/>
        </operation>
        
        <operation name="updateUser">
            <input message="tns:UpdateUserRequest"/>
            <output message="tns:UpdateUserResponse"/>
        </operation>
        
        <operation name="deleteUser">
            <input message="tns:DeleteUserRequest"/>
            <output message="tns:DeleteUserResponse"/>
        </operation>
        
        <operation name="logoutUser">
            <input message="tns:LogoutUserRequest"/>
            <output message="tns:LogoutUserResponse"/>
        </operation>
    </portType>

    <binding name="ActualiteBinding" type="tns:ActualitePortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        
        <operation name="authenticateUser">
            <soap:operation soapAction="authenticateUser"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="listUsers">
            <soap:operation soapAction="listUsers"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="getUserById">
            <soap:operation soapAction="getUserById"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="createUser">
            <soap:operation soapAction="createUser"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="updateUser">
            <soap:operation soapAction="updateUser"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="deleteUser">
            <soap:operation soapAction="deleteUser"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
        
        <operation name="logoutUser">
            <soap:operation soapAction="logoutUser"/>
            <input><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input>
            <output><soap:body use="encoded" namespace="http://localhost/projet-actualite/backend/api/soap_server.php" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output>
        </operation>
    </binding>

    <service name="ActualiteService">
        <port name="ActualitePort" binding="tns:ActualiteBinding">
            <soap:address location="http://localhost/projet-actualite/backend/api/soap_server.php"/>
        </port>
    </service>
</definitions>';
        
        header('Content-Type: application/xml');
        echo $wsdl;
    } else {
        // Traitement des requêtes SOAP
        $server->handle();
    }
} catch (Exception $e) {
    error_log("Erreur SOAP: " . $e->getMessage());
    http_response_code(500);
    echo "Erreur interne du serveur";
} 