<?php
$client = new SoapClient('http://localhost/projet-actualite/backend/api/soap_server.php?wsdl');
$response = $client->authenticateUser('admin','password');
var_dump($response);
