# 🌍 Guide : Déploiement du Backend à Distance (o2switch)

Ce guide explique comment mettre en ligne votre backend Mangathèque sur un hébergement mutualisé classique comme o2switch (qui utilise cPanel et Apache). 

Cela permet d'avoir un serveur backend disponible 24h/24 pour tous les groupes d'élèves simultanément.

## 1. Préparer les fichiers

Le dossier `backend/` de ce dépôt contient tout ce dont vous avez besoin :
- `index.php` (le code métier)
- `.htaccess` (pour rediriger proprement toutes les requêtes vers index.php)
- `data/` (le dossier qui contiendra la base de données)

## 2. Hébergement o2switch (cPanel)

1. Connectez-vous à votre espace cPanel.
2. Allez dans le **Gestionnaire de fichiers** (ou utilisez un client FTP comme FileZilla).
3. Créez un dossier pour votre API (par exemple : `api-mangatheque` à la racine de votre hébergement `public_html/` ou en tant que sous-domaine).
4. Uploadez **tout le contenu** du dossier `backend/` dans ce nouveau dossier distant. N'oubliez pas le fichier caché `.htaccess`.

## 3. Gestion des permissions (Très Important !)

Le backend utilise SQLite, ce qui signifie que la base de données est un simple fichier stocké dans le dossier `data/`.

Pour que PHP puisse créer et modifier ce fichier, le serveur web doit avoir les droits d'écriture sur le dossier `data/`.

1. Dans le Gestionnaire de fichiers cPanel, faites un clic droit sur le dossier `data/` distant.
2. Cliquez sur **Change Permissions** (Modifier les permissions).
3. Assurez-vous que les permissions sont au moins sur `755` (ou `775` voire `777` selon la configuration stricte de votre serveur si vous rencontrez des erreurs de création de base de données).

## 4. Adaptations du code côté Frontend

Une fois le backend en ligne, il possède une URL publique (par exemple : `https://mondomaine.fr/api-mangatheque`).

Vous devez informer les applications (Starter Kit et Solution) de cette nouvelle adresse.

1. Ouvrez les fichiers `eleve/js/api-client.js` et `solution/js/api-client.js`.
2. Modifiez la constante `URL_BACKEND` pour qu'elle pointe vers votre hébergement o2switch.

**Changement à effectuer :**

```javascript
// Remplacez l'URL locale par votre URL en ligne
// const URL_BACKEND = "http://localhost:8080"; 
const URL_BACKEND = "https://mondomaine.fr/api-mangatheque";
```

> **Attention :** Ne mettez **pas de slash `/`** à la fin de l'URL dans la constante.

## 5. Test de fonctionnement

1. Accédez à l'URL de votre backend dans votre navigateur (ex: `https://mondomaine.fr/api-mangatheque`).
2. Vous devriez voir un message JSON d'accueil ("🎌 Bienvenue sur l'API Mangathèque !").
3. Vérifiez ensuite dans cPanel que le fichier `mangatheque.db` s'est bien créé dans le dossier `data/`.
4. Ouvrez la solution (localement sur votre PC), qui pointe désormais vers le backend distant, et essayez d'ajouter un favori.
