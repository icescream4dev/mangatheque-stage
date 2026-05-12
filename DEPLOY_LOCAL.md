# 🚀 Guide : Déploiement du Backend en Local (Test)

Ce guide explique comment lancer le backend Mangathèque sur votre propre ordinateur (idéal pour le développement et les tests avant le stage).

## 1. Prérequis

Vous avez besoin de PHP et de l'extension SQLite3 installés sur votre machine.

**Sur Ubuntu / Debian :**
```bash
sudo apt-get update
sudo apt-get install -y php-cli php-sqlite3
```

**Sur Windows :**
Téléchargez et installez XAMPP ou WampServer, ou téléchargez directement les binaires PHP depuis le site officiel.

**Sur macOS :**
PHP est souvent préinstallé, ou vous pouvez l'installer via Homebrew :
```bash
brew install php
```

## 2. Démarrer le serveur PHP intégré

Le backend Mangathèque est conçu pour fonctionner sans serveur web complexe (comme Apache ou Nginx) lors des tests locaux grâce au serveur de développement intégré de PHP.

Ouvrez un terminal, placez-vous dans le dossier de votre projet, et lancez la commande suivante :

```bash
cd chemin/vers/mangatheque-stage/backend
php -S localhost:8080 index.php
```

Vous devriez voir un message indiquant que le serveur écoute sur `http://localhost:8080`.

> **Note :** La base de données SQLite `mangatheque.db` sera créée automatiquement dans le dossier `backend/data/` lors de la première requête.

## 3. Configuration du client (Frontend)

Pour que l'application "Élève" et la "Solution" puissent communiquer avec ce backend local, vérifiez que l'URL est bien configurée.

Ouvrez le fichier `eleve/js/api-client.js` (et `solution/js/api-client.js`) et assurez-vous que la constante `URL_BACKEND` pointe vers votre serveur local :

```javascript
// L'adresse du serveur backend local
const URL_BACKEND = "http://localhost:8080";
```

## 4. Tester

Ouvrez simplement le fichier `eleve/index.html` ou `solution/index.html` dans votre navigateur web (via un double-clic sur le fichier). Le frontend communiquera avec le backend tournant en arrière-plan.
