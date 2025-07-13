# Architecture du projet

Le projet est structuré selon une architecture MVC (Modèle-Vue-Contrôleur) :

* **backend/** : Contient toute la logique métier, les modèles, contrôleurs et services.
* **frontend/** : Fichiers PHP destinés à l’affichage et à l’interface d’administration / utilisateur.
* **database/** : Scripts SQL pour la création et le remplissage de la base de données.
* **vendor/** : Dépendances gérées par Composer.
* **tests/** : Tests unitaires et d’intégration basés sur PHPUnit.
* **docs/** : Cette documentation.

## Flux de données

```
Navigateur → frontend (Vue) → Controllers backend (API REST) → Models → Base de données
```

## Configuration

La connexion à la base de données est paramétrée dans `backend/config/DatabaseConfig.php`.

## Déploiement

1. Installer les dépendances : `composer install`
2. Configurer la base de données.
3. Lancer le serveur local : `php -S localhost:8000 -t frontend`.
4. Exécuter les tests : `vendor\bin\phpunit`.
