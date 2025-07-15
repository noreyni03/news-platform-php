<?php
// Chargement automatique des classes via Composer
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require $autoloadFile;
}

// Vous pouvez ajouter ici une configuration commune pour vos tests, par exemple
// la définition d'une base de données en mémoire ou des constantes globales.
