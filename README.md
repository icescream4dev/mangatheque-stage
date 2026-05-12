# 🎌 Mangathèque — Environnement de Stage

> Projet pédagogique pour apprendre HTML & JavaScript en créant une application de collection de mangas/animes.

## 📁 Structure du projet

```
mangatheque-stage/
├── backend/          → API PHP/SQLite (fourni, ne pas modifier)
│   ├── index.php     → Point d'entrée unique (routeur + CORS)
│   ├── .htaccess     → Réécriture Apache (pour o2switch)
│   └── data/         → Base SQLite (créée automatiquement)
│
├── eleve/            → Starter Kit pour les élèves
│   ├── index.html    → Fichier HTML à compléter
│   └── js/
│       └── api-client.js  → Client API simplifié (ne pas modifier)
│
├── solution/         → Application de référence complète
│   ├── index.html    → Version finalisée avec design manga
│   └── js/
│       └── api-client.js
│
├── GUIDE_ENCADRANT.md → Planning des 8 sessions + conseils
└── README.md          → Ce fichier
```

## 🚀 Démarrage rapide

### 1. Lancer le backend
```bash
cd backend
php -S localhost:8080 index.php
```

### 2. Configurer le groupe
Dans `eleve/js/api-client.js`, modifier la variable `ID_GROUPE` :
```js
const ID_GROUPE = "groupe-A";
```

### 3. Ouvrir le starter kit
Ouvrir `eleve/index.html` dans un navigateur (double-clic).

## 🔌 Routes de l'API

| Route | Méthode | Paramètres | Description |
|-------|---------|------------|-------------|
| `recherche_externe` | GET | `q` (texte) | Rechercher un anime via Jikan |
| `ajouter` | POST | `id_jikan`, `titre`, `image_url` | Ajouter à la collection |
| `supprimer` | POST | `id` | Supprimer de la collection |
| `ma_collection` | GET | — | Voir la collection du groupe |

> `id_groupe` est injecté automatiquement par `api-client.js`.

## 🎯 Public cible
- Élèves de 16 ans, débutants complets
- 2 semaines, 8 sessions de 3h
- Voir `GUIDE_ENCADRANT.md` pour le planning détaillé

## 🛠️ Prérequis techniques
- PHP 8+
- Un navigateur moderne (Chrome ou Firefox)
- Un éditeur de texte (VS Code recommandé)
