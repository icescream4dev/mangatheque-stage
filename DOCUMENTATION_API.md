# 🎌 Manuel d'utilisation de l'API Mangathèque

Ce guide explique comment utiliser la couche client `api-client.js` pour communiquer avec le serveur Mangathèque. 

Le fichier `api-client.js` encapsule toute la logique réseau (requêtes HTTP, sérialisation des paramètres, gestion du CORS) et expose **une fonction unique** pour interagir avec le backend.

---

## ⚙️ Initialisation et Configuration

Avant de faire appel à l'API, assurez-vous d'avoir configuré les constantes au début du fichier `js/api-client.js` :

```javascript
// L'URL de votre API de production ou de développement local
const URL_BACKEND = "https://api.manga.technivore.fr";

// L'identifiant unique de votre groupe (attribué par l'encadrant)
const ID_GROUPE = "groupe-1";
```

---

## 🔌 Fonction Principale : `appeler_backend`

Toutes les requêtes passent par la fonction asynchrone suivante :

```javascript
const reponse = await appeler_backend(action, parametres);
```

### Paramètres de la fonction :
1. **`action`** (`string`) : Le nom de l'action à exécuter (ex: `"recherche_externe"`).
2. **`parametres`** (`object`, *optionnel*) : Les données à transmettre à l'API sous forme de clé/valeur. L'identifiant du groupe (`id_groupe`) est **automatiquement injecté** dans les paramètres par la fonction.

### Retour :
Retourne une `Promise` qui résout en un **objet JavaScript** (contenant le statut de la réponse et les données demandées).

---

## 🗂️ Actions Disponibles (Routes)

### 1. `recherche_externe` — Rechercher des mangas sur l'API Jikan (MyAnimeList)
Cette action sert à rechercher des mangas dans la base globale de MyAnimeList. Le serveur fait office de proxy pour contourner les blocages réseau.

* **Paramètres à passer :**
  | Clé | Type | Requis ? | Description |
  | :--- | :--- | :---: | :--- |
  | `q` | `string` | **Oui** | Le mot-clé de recherche (ex: `"Naruto"`, `"One Piece"`). |

* **Exemple d'appel :**
  ```javascript
  const reponse = await appeler_backend("recherche_externe", { q: "Death Note" });
  ```

* **Structure de la réponse (succès) :**
  ```json
  {
    "statut": "ok",
    "resultats": [
      {
        "id_jikan": 21,
        "titre": "Death Note",
        "image_url": "https://cdn.myanimelist.net/images/manga/...jpg",
        "synopsis": "Light Yagami is a genius high school student who...",
        "score": 8.62,
        "episodes": 108,
        "type": "Manga",
        "statut": "Finished"
      }
    ]
  }
  ```

---

### 2. `ma_collection` — Afficher la collection du groupe
Récupère tous les mangas actuellement enregistrés dans la collection du groupe spécifié dans `ID_GROUPE`.

* **Paramètres à passer :** Aucun (l'identifiant `id_groupe` est fourni automatiquement par le client).
* **Exemple d'appel :**
  ```javascript
  const reponse = await appeler_backend("ma_collection");
  ```

* **Structure de la réponse (succès) :**
  ```json
  {
    "statut": "ok",
    "id_groupe": "groupe-1",
    "total": 1,
    "collection": [
      {
        "id": 12,
        "id_groupe": "groupe-1",
        "id_jikan": 21,
        "titre": "Death Note",
        "image_url": "https://cdn.myanimelist.net/images/manga/...jpg",
        "synopsis": "Light Yagami is a genius high school student who...",
        "score": 8.62,
        "episodes": 108,
        "tome_possede": 5,
        "tome_lu": 3,
        "date_ajout": "2026-05-22 14:30:00"
      }
    ]
  }
  ```

---

### 3. `ajouter` — Ajouter un manga à la collection
Enregistre un manga de la recherche externe dans la base de données SQLite du groupe.

* **Paramètres à passer :**
  | Clé | Type | Requis ? | Description |
  | :--- | :--- | :---: | :--- |
  | `id_jikan` | `integer` | **Oui** | L'identifiant unique du manga sur MyAnimeList. |
  | `titre` | `string` | **Oui** | Le titre du manga. |
  | `image_url` | `string` | Non | L'URL de l'image de couverture. |
  | `synopsis` | `string` | Non | Le résumé simplifié du manga. |
  | `score` | `number` | Non | La note moyenne (ex: `8.5`). |
  | `episodes` | `integer` | Non | Le nombre total de tomes ou volumes (renommé `episodes` par simplicité). |
  | `tome_possede`| `integer` | Non | Nombre de tomes possédés physiquement (défaut: `0`). |
  | `tome_lu` | `integer` | Non | Nombre de tomes lus (défaut: `0`). |

* **Exemple d'appel :**
  ```javascript
  const reponse = await appeler_backend("ajouter", {
    id_jikan: 21,
    titre: "Death Note",
    image_url: "https://cdn.myanimelist.net/images/manga/...jpg",
    synopsis: "Un étudiant trouve un carnet de la mort...",
    score: 8.62,
    episodes: 108
  });
  ```

* **Structure de la réponse (succès) :**
  ```json
  {
    "statut": "ok",
    "message": "✅ \"Death Note\" ajouté à la collection !"
  }
  ```

---

### 4. `supprimer` — Retirer un manga de la collection
Supprime un manga de la collection du groupe à partir de son identifiant de base de données local.

* **Paramètres à passer :**
  | Clé | Type | Requis ? | Description |
  | :--- | :--- | :---: | :--- |
  | `id` | `integer` | **Oui** | L'identifiant de la ligne dans la table SQL (l'attribut `id` dans l'objet de `ma_collection`, **et non** `id_jikan`). |

* **Exemple d'appel :**
  ```javascript
  const reponse = await appeler_backend("supprimer", { id: 12 });
  ```

* **Structure de la réponse (succès) :**
  ```json
  {
    "statut": "ok",
    "message": "🗑️ Manga supprimé de la collection."
  }
  ```

---

### 5. `maj_progression` — Modifier la progression de lecture/possession
Met à jour le nombre de tomes possédés et/ou lus pour un manga donné.

* **Paramètres à passer :**
  | Clé | Type | Requis ? | Description |
  | :--- | :--- | :---: | :--- |
  | `id` | `integer` | **Oui** | L'identifiant SQL du manga dans la collection (attribut `id`). |
  | `tome_possede`| `integer` | **Oui** | Le nouveau nombre de tomes possédés. |
  | `tome_lu` | `integer` | **Oui** | Le nouveau nombre de tomes lus. |

* **Exemple d'appel :**
  ```javascript
  const reponse = await appeler_backend("maj_progression", {
    id: 12,
    tome_possede: 12,
    tome_lu: 6
  });
  ```

* **Structure de la réponse (succès) :**
  ```json
  {
    "statut": "ok",
    "message": "Progression sauvegardée !"
  }
  ```

---

## ⚠️ Gestion des Erreurs

Si un appel échoue (paramètre obligatoire manquant, manga déjà présent dans la collection, erreur serveur), l'objet retourné contiendra les champs suivants :
* `statut` : Contient la valeur `"erreur"`.
* `message` : Une chaîne de caractères décrivant l'erreur (idéale pour afficher à l'utilisateur dans une alerte).

### Exemple de traitement de la réponse :

```javascript
try {
  const reponse = await appeler_backend("ajouter", { id_jikan: 21, titre: "Death Note" });

  if (reponse.statut === "erreur") {
    alert("Impossible d'ajouter le manga : " + reponse.message);
  } else {
    alert("Succès : " + reponse.message);
  }
} catch (error) {
  console.error("Erreur réseau globale", error);
}
```
