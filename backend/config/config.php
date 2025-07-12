<?php
// backend/config/config.php
define('BASE_URL', 'http://localhost/projet-actualite/');
define('API_BASE_URL', BASE_URL . 'backend/api/');
define('SOAP_ENDPOINT', API_BASE_URL . 'soap/');
define('REST_ENDPOINT', API_BASE_URL . 'rest/');
define('JWT_SECRET', '8a4ba3dd045d30692cfb06f1d1b93715c70abebda4c061555d9ff5c821a85dac');
define('JWT_ALGORITHM', 'HS256');
define('SESSION_TIMEOUT', 3600); // 1 heure