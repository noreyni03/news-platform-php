# Projet d'Architecture Logicielle - Site d'Actualités

Ce projet met en œuvre les compétences acquises dans le cours d'architecture logicielle. Il est composé de trois parties principales :

1. **Site Web** - Interface utilisateur pour consulter les articles
2. **Services Web** - API SOAP et REST pour l'accès aux données
3. **Application Client Java** - Interface d'administration des utilisateurs

## 🏗️ Architecture du Projet

```
projet-actualite/
├── backend/                 # Backend PHP
│   ├── api/                # Services web (SOAP + REST)
│   ├── config/             # Configuration
│   ├── controllers/        # Contrôleurs
│   ├── models/            # Modèles de données
│   ├── services/          # Services métier
│   └── utils/             # Utilitaires
├── frontend/              # Interface utilisateur
│   ├── views/             # Pages PHP
│   └── assets/            # Ressources statiques
├── database/              # Scripts de base de données
├── java-client/           # Application Java
│   └── src/main/java/     # Code source Java
└── vendor/                # Dépendances PHP
```

## 📋 Prérequis

- **PHP 7.4+** avec extensions : `soap`, `curl`, `json`, `pdo`
- **MySQL 5.7+** ou **MariaDB 10.2+**
- **Apache/Nginx** avec mod_rewrite activé
- **Java 11+** et **Maven 3.6+** (pour l'application Java)
- **Composer** (gestionnaire de dépendances PHP)

## 🚀 Installation

### 1. Configuration de la base de données

```sql
-- Créer la base de données
CREATE DATABASE actualite_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importer le schéma
mysql -u root -p actualite_db < database/schema.sql
```

### 2. Préparation des utilisateurs (optionnel mais recommandé)

Un script pratique permet de créer (ou réinitialiser) le compte **admin / password** :

```bash
php scripts/create_admin.php
```

Vous pouvez l'exécuter à tout moment si vous perdez le mot de passe administrateur.

### 3. Configuration PHP

```bash
# Installer les dépendances PHP
composer install

# Copier et configurer la base de données
cp backend/config/database.php.example backend/config/database.php
# Éditer backend/config/database.php avec vos paramètres
```

### 3. Configuration de l'application Java

```bash
# Aller dans le répertoire Java
cd java-client

# Compiler l'application
mvn clean compile

# Créer l'exécutable JAR
mvn package
```

## 🔧 Configuration

### Base de données (backend/config/database.php)

```php
private const HOST = 'localhost';
private const DB_NAME = 'actualite_db';
private const USERNAME = 'root';
private const PASSWORD = '';
```

### Services Web

- **SOAP** : `http://localhost/projet-actualite/backend/api/soap_server.php`
- **REST** : `http://localhost/projet-actualite/backend/api/rest_api.php`

## 📖 Utilisation

### Site Web

1. **Accéder au site** : `http://localhost/projet-actualite/frontend/`
2. **Navigation** :
   - Page d'accueil avec liste des articles
   - Filtrage par catégorie
   - Pagination (boutons précédent/suivant)
   - Consultation détaillée des articles

### Services Web

#### API SOAP (retours JSON)

Toutes les fonctions SOAP renvoient désormais **une chaîne JSON** (type `xsd:string` dans le WSDL). Le client Java ou tout autre consommateur doit désérialiser cette chaîne pour obtenir un objet/Map.

Exemple de réponse JSON :
```json
{"success":true,"user":{"id":1,"username":"admin","role":"admin"},"token":"..."}
```


**Authentification** :
```xml
<soap:Envelope>
  <soap:Body>
    <authenticateUser>
      <username>admin</username>
      <password>password</password>
    </authenticateUser>
  </soap:Body>
</soap:Envelope>
```

**Gestion des utilisateurs** (nécessite token admin) :
- `listUsers(token)`
- `getUserById(token, userId)`
- `createUser(token, userData)`
- `updateUser(token, userId, userData)`
- `deleteUser(token, userId)`

#### API REST

**Récupération des articles** :
- `GET /articles` - Tous les articles
- `GET /articles/grouped` - Articles groupés par catégorie
- `GET /articles/category/{id}` - Articles d'une catégorie
- `GET /articles/{id}` - Article spécifique

**Formats supportés** :
- JSON : `?format=json` ou `Accept: application/json`
- XML : `?format=xml` ou `Accept: application/xml`

### Application Java

```bash
# Lancer l'application
java -jar java-client/target/user-management-client-1.0.0.jar

# Ou avec Maven
mvn exec:java -Dexec.mainClass="com.actualite.client.UserManagementApp"
```

**Fonctionnalités** :
- Authentification administrateur
- Liste des utilisateurs
- Création/modification/suppression d'utilisateurs
- Interface console interactive

## 👥 Types d'utilisateurs

### 1. Visiteurs simples
- ✅ Consultation des articles
- ✅ Filtrage par catégorie
- ✅ Navigation dans les articles

### 2. Éditeurs
- ✅ Toutes les fonctionnalités des visiteurs
- ✅ Gestion des articles (CRUD)
- ✅ Gestion des catégories (CRUD)

### 3. Administrateurs
- ✅ Toutes les fonctionnalités des éditeurs
- ✅ Gestion des utilisateurs (CRUD)
- ✅ Gestion des jetons d'authentification API

## 🔐 Authentification

### Données de test

```sql
-- Utilisateur admin
Username: admin
Password: password
Role: admin

-- Utilisateur éditeur
Username: editeur1
Password: password
Role: editeur

-- Utilisateur visiteur
Username: user1
Password: password
Role: visiteur
```

### Jetons API

Les administrateurs peuvent générer des jetons d'authentification pour accéder aux services web SOAP depuis l'interface d'administration.

## 🧪 Tests

### Tests PHP

```bash
# Lancer les tests unitaires
./vendor/bin/phpunit tests/
```

### Tests Java

```bash
# Lancer les tests Java
mvn test
```

## 📁 Structure des fichiers

### Backend PHP

- **Models** : `User`, `Article`, `Category`, `ApiToken`
- **Services** : `SoapService` pour les opérations SOAP
- **API** : `soap_server.php` et `rest_api.php`
- **Config** : Configuration base de données

### Frontend

- **index.php** : Page d'accueil avec liste des articles
- **article.php** : Page de détail d'un article
- **admin/** : Interface d'administration

### Application Java

- **UserManagementApp** : Application principale
- **SoapClient** : Client SOAP pour communiquer avec le serveur
- **User** : Modèle utilisateur

## 🔧 Développement

### Ajouter une nouvelle fonctionnalité

1. **Backend** : Créer le modèle et le service
2. **API** : Exposer via SOAP ou REST
3. **Frontend** : Ajouter l'interface utilisateur
4. **Java** : Intégrer dans l'application client si nécessaire

### Logs et débogage

- **PHP** : `error_log()` et logs Apache/Nginx
- **Java** : SLF4J avec configuration simple

## 🚨 Sécurité

- Mots de passe hashés avec `password_hash()`
- Validation des entrées utilisateur
- Protection contre les injections SQL (PDO)
- Authentification par token pour les API
- Contrôle d'accès basé sur les rôles

## 📝 Licence

Ce projet est développé dans le cadre d'un cours d'architecture logicielle.

## 🤝 Contribution

Pour contribuer au projet :

1. Fork le repository
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Créer une Pull Request

## 📞 Support

Pour toute question ou problème :

1. Vérifier la documentation
2. Consulter les logs d'erreur
3. Tester les services web individuellement
4. Vérifier la configuration de la base de données

---

**Note** : Ce projet est une démonstration des concepts d'architecture logicielle et ne doit pas être utilisé en production sans modifications appropriées. 