# Actu-Web - Site d'Actualités Moderne

Un site d'actualités moderne avec design Tailwind CSS et fonctionnalités IA intégrées.

## 🎨 Design Moderne

Le projet a été entièrement refait avec un design moderne utilisant :

- **Tailwind CSS** pour un design responsive et moderne
- **Font Inter** pour une typographie élégante
- **Animations fluides** et transitions
- **Modal interactif** pour la lecture d'articles
- **Fonctionnalités IA** intégrées (résumé et questions)

## 🚀 Fonctionnalités

### Interface Utilisateur
- ✅ Design responsive moderne
- ✅ Navigation fluide
- ✅ Filtrage par catégories
- ✅ Pagination élégante
- ✅ Modal pour lecture d'articles
- ✅ Animations et transitions

### Fonctionnalités IA (Gemini)
- ✅ Résumé automatique d'articles
- ✅ Génération de questions liées
- ✅ API backend sécurisée
- ✅ Gestion d'erreurs robuste

## 🛠️ Installation

### 1. Prérequis
- PHP 7.4+
- MySQL/MariaDB
- Composer
- Serveur web (Apache/Nginx)

### 2. Configuration de la base de données
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE actu_web;
```

### 3. Installation des dépendances
```bash
composer install
```

### 4. Configuration
Copiez le fichier de configuration :
```bash
cp frontend/config/gemini.php.example frontend/config/gemini.php
```

### 5. Configuration de l'API Gemini (Optionnel)

Pour utiliser les fonctionnalités IA :

1. Obtenez une clé API depuis [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Configurez la clé dans `frontend/config/gemini.php` :

```php
return [
    'api_key' => 'VOTRE_CLE_API_GEMINI',
    'model' => 'gemini-2.0-flash',
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
    'max_tokens' => 1000,
    'temperature' => 0.7,
];
```

Ou utilisez une variable d'environnement :
```bash
export GEMINI_API_KEY="votre_cle_api"
```

## 📁 Structure du Projet

```
projet-actualite/
├── frontend/
│   ├── index.php              # Page d'accueil moderne
│   ├── article.php            # Page d'article
│   ├── api/
│   │   └── gemini.php         # API backend pour l'IA
│   └── config/
│       └── gemini.php         # Configuration Gemini
├── backend/
│   └── services/
│       └── GeminiService.php  # Service IA
└── README_MODERN.md           # Ce fichier
```

## 🎯 Utilisation

### Navigation
- **Accueil** : Liste des derniers articles
- **Filtrage** : Sélecteur de catégories
- **Lecture** : Clic sur une carte d'article pour ouvrir le modal

### Fonctionnalités IA
1. Ouvrir un article dans le modal
2. Les questions liées se génèrent automatiquement
3. Cliquer sur "✨ Résumer l'article" pour un résumé IA

## 🔧 Configuration Avancée

### Personnalisation du Design
Le design utilise Tailwind CSS. Vous pouvez personnaliser :

- **Couleurs** : Modifiez les classes `bg-blue-600`, `text-gray-800`, etc.
- **Typographie** : Changez la police dans le CSS
- **Animations** : Ajustez les durées dans les classes `transition-*`

### API Gemini
Le service IA est configurable via `GeminiService.php` :

- **Modèle** : Changez le modèle Gemini utilisé
- **Paramètres** : Ajustez `max_tokens` et `temperature`
- **Prompts** : Personnalisez les prompts pour les résumés et questions

## 🐛 Dépannage

### Problèmes courants

1. **API Gemini non configurée**
   - Vérifiez que la clé API est correcte
   - Testez l'API : `curl -X GET "frontend/api/gemini.php?action=status"`

2. **Erreurs de base de données**
   - Vérifiez la connexion MySQL
   - Exécutez `php install.php` pour réinstaller

3. **Problèmes d'affichage**
   - Vérifiez que Tailwind CSS se charge
   - Inspectez la console pour les erreurs JavaScript

## 🔒 Sécurité

- Les clés API sont stockées côté serveur
- Validation des entrées utilisateur
- Protection contre les injections SQL
- Headers CORS configurés

## 📱 Responsive Design

Le site est entièrement responsive :
- **Mobile** : 1 colonne d'articles
- **Tablet** : 2 colonnes d'articles  
- **Desktop** : 3 colonnes d'articles

## 🎨 Personnalisation

### Couleurs par catégorie
Les catégories ont des couleurs automatiques :
- Technologie : Bleu
- Économie : Vert
- Voyage : Rouge
- Santé : Violet
- Sport : Orange

### Ajouter de nouvelles catégories
Modifiez la fonction `categoryColor()` dans `index.php` pour ajouter de nouvelles couleurs.

## 📈 Performance

- Images optimisées avec placeholders
- Chargement asynchrone des fonctionnalités IA
- Cache des requêtes API
- Compression CSS/JS

## 🤝 Contribution

1. Fork le projet
2. Créez une branche feature
3. Committez vos changements
4. Poussez vers la branche
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

---

**Actu-Web** - Un site d'actualités moderne avec IA intégrée ✨ 