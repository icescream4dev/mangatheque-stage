# 📋 Guide de l'Encadrant — Mangathèque

> Stage de découverte HTML/JavaScript — 2 semaines, 8 sessions de 3h
> Public : élèves de 16 ans, débutants complets

---

## 🗺️ Vue d'ensemble

Les élèves construisent **leur propre application web** de collection de mangas/animes.
Ils partent d'un fichier HTML quasi-vide et le complètent session après session.

Le backend (PHP/SQLite) et le fichier `api-client.js` sont **fournis** : les élèves n'y touchent pas. Ils se concentrent uniquement sur le HTML et le JavaScript côté navigateur.

Chaque groupe possède un `id_groupe` distinct qui isole sa collection.

---

## 🔧 Mise en place technique (avant le stage)

### Lancer le backend en local
```bash
cd backend
php -S localhost:8080 index.php
```
> La base SQLite se crée automatiquement dans `backend/data/`.

### Configurer les groupes
Dans `eleve/js/api-client.js`, modifier `ID_GROUPE` pour chaque groupe :
```js
const ID_GROUPE = "groupe-A"; // ou "groupe-B", "groupe-C"...
```

### Déploiement sur o2switch (optionnel)
1. Uploader le dossier `backend/` dans un sous-domaine (ex: `api-manga.mondomaine.fr`)
2. Modifier `URL_BACKEND` dans `api-client.js` pour pointer vers ce sous-domaine
3. Vérifier que le dossier `data/` est accessible en écriture (chmod 755)

---

## 📅 Planning des 8 Sessions

---

### Session 1 — Découverte du HTML 🏗️
**Durée : 3h | Objectif : comprendre la structure d'une page web**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Accueil, présentation du projet, démo de la solution finale |
| 0:30-1:00 | Qu'est-ce qu'un site web ? HTML = squelette, CSS = habits, JS = cerveau |
| 1:00-1:45 | Pratique : ouvrir `eleve/index.html`, modifier le titre, ajouter du texte |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:40 | Les balises essentielles : `<h1>` à `<h6>`, `<p>`, `<img>`, `<a>`, `<ul>/<li>` |
| 2:40-3:00 | Défi : personnaliser la page avec son propre texte et une image |

**Points de vigilance :**
- Montrer comment ouvrir le fichier dans le navigateur (double-clic ou clic droit > Ouvrir avec)
- Insister sur la sauvegarde (Ctrl+S) puis rafraîchissement du navigateur (F5)
- Ne PAS parler de CSS ni JavaScript encore

**Erreurs fréquentes :**
- Balises non fermées (`<h1>` sans `</h1>`)
- Confusion entre attributs et contenu

---

### Session 2 — Premiers pas en JavaScript 🧠
**Durée : 3h | Objectif : variables, fonctions, console**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Récap session 1, introduction à JavaScript |
| 0:30-1:15 | La console (F12), `console.log()`, `alert()` |
| 1:15-1:45 | Variables : `let`, `const` — stocker des informations |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:30 | Fonctions : créer et appeler des fonctions simples |
| 2:30-3:00 | Défi : créer une fonction qui affiche un message personnalisé |

**Conseils pédagogiques :**
- Utiliser des analogies : variable = boîte avec une étiquette, fonction = recette de cuisine
- Tout faire dans la console d'abord, puis dans le fichier HTML entre `<script>` et `</script>`
- Ne pas introduire `async/await` maintenant

---

### Session 3 — Le DOM : manipuler la page 🎨
**Durée : 3h | Objectif : querySelector, textContent, événements**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Récap, introduction au DOM (la page est un arbre d'objets) |
| 0:30-1:15 | `document.querySelector()` — sélectionner un élément |
| 1:15-1:45 | Modifier le contenu : `.textContent`, `.innerHTML` |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:30 | Événements : `onclick`, `addEventListener` |
| 2:30-3:00 | Défi : créer un bouton qui change le titre de la page au clic |

**Activité guidée :**
```html
<button id="mon-bouton">Clique ici</button>
<p id="message"></p>

<script>
  const bouton = document.querySelector("#mon-bouton");
  bouton.addEventListener("click", function() {
    document.querySelector("#message").textContent = "Bravo, ça marche !";
  });
</script>
```

---

### Session 4 — Connexion à l'API 🔌
**Durée : 3h | Objectif : comprendre api-client.js, première recherche**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Récap, vérifier que le backend tourne |
| 0:30-1:00 | Explication simplifiée de `appeler_backend()` — c'est comme envoyer un SMS au serveur |
| 1:00-1:30 | Tester dans la console : `appeler_backend("recherche_externe", { q: "Naruto" })` |
| 1:30-1:45 | ☕ Pause |
| 1:45-2:30 | Créer la barre de recherche (un `<input>` + un `<button>`) |
| 2:30-3:00 | Connecter le bouton à `appeler_backend` et afficher le résultat dans la console |

**Point crucial :**
- Les élèves doivent comprendre `async/await` de manière intuitive :
  > « `await` veut dire : attends la réponse avant de continuer. Comme quand tu envoies un message et tu attends la réponse. »
- Ne pas rentrer dans les détails de `fetch`, `Promise`, etc.

---

### Session 5 — Affichage dynamique 📋
**Durée : 3h | Objectif : boucles, createElement, innerHTML**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Récap, montrer les données reçues dans la console |
| 0:30-1:15 | La boucle `for...of` pour parcourir les résultats |
| 1:15-1:45 | `innerHTML` : construire du HTML avec JavaScript |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:45 | Créer une carte pour chaque manga (image + titre) |
| 2:45-3:00 | Défi : ajouter le score et le nombre d'épisodes |

**Exemple guidé :**
```js
async function chercher() {
  const recherche = document.querySelector("#champ-recherche").value;
  const reponse = await appeler_backend("recherche_externe", { q: recherche });

  let html = "";
  for (const manga of reponse.resultats) {
    html += "<div>";
    html += "<img src='" + manga.image_url + "'>";
    html += "<h3>" + manga.titre + "</h3>";
    html += "</div>";
  }
  document.querySelector("#resultats").innerHTML = html;
}
```

---

### Session 6 — Gestion des favoris ⭐
**Durée : 3h | Objectif : ajouter/supprimer, état de l'interface**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Récap, présentation de la fonctionnalité favoris |
| 0:30-1:15 | Ajouter un bouton "Ajouter" sur chaque carte |
| 1:15-1:45 | Fonction `ajouterFavori()` avec `appeler_backend("ajouter", ...)` |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:30 | Afficher "Ma Collection" avec `appeler_backend("ma_collection")` |
| 2:30-3:00 | Bouton "Supprimer" et rafraîchissement de la liste |

**Points de vigilance :**
- Les élèves doivent passer les bonnes données (id_jikan, titre, image_url)
- Penser à rafraîchir l'affichage après ajout/suppression
- C'est la session la plus complexe, prévoir du temps pour l'aide individuelle

---

### Session 7 — Design et finitions 🎨
**Durée : 3h | Objectif : CSS, animations, responsive**

| Temps | Activité |
|-------|----------|
| 0:00-0:30 | Introduction au CSS : propriétés de base (color, background, font-size) |
| 0:30-1:15 | Styliser la page : `<style>` dans le `<head>` |
| 1:15-1:45 | Mise en page : flexbox ou grid pour les cartes |
| 1:45-2:00 | ☕ Pause |
| 2:00-2:30 | Effets visuels : hover, border-radius, box-shadow |
| 2:30-3:00 | Personnalisation libre : chaque groupe crée son identité visuelle |

**Conseil :**
- Montrer la solution finale comme source d'inspiration (pas de copie !)
- Encourager la créativité : couleurs, polices, mise en page différente
- Montrer comment inspecter le CSS d'un site avec F12

---

### Session 8 — Présentation finale 🎤
**Durée : 3h | Objectif : démo de chaque groupe, retours**

| Temps | Activité |
|-------|----------|
| 0:00-1:00 | Temps de finition et préparation de la présentation |
| 1:00-1:15 | ☕ Pause |
| 1:15-2:30 | Présentations : chaque groupe montre son application (5-10 min par groupe) |
| 2:30-3:00 | Retours, discussion sur ce qu'ils ont appris, prochaines étapes |

**Format de présentation suggéré :**
1. Montrer l'application en action (recherche + ajout favori)
2. Expliquer une partie du code dont ils sont fiers
3. Dire ce qui a été le plus difficile et le plus gratifiant

---

## 💡 Conseils Généraux

### Rythme
- Les sessions 4-6 sont les plus denses. Prévoir du temps d'aide individuelle.
- Ne jamais hésiter à ralentir si un concept n'est pas compris.
- La session 7 (CSS) est volontairement légère en nouveau contenu pour permettre de rattraper le retard.

### Outils recommandés
- **Éditeur** : Visual Studio Code (gratuit, coloration syntaxique)
- **Navigateur** : Chrome ou Firefox (meilleurs DevTools)
- **Terminal** : Le terminal intégré de VS Code

### Gestion des groupes
- Attribuer un `ID_GROUPE` unique à chaque binôme/trinôme
- Les encourager à comparer leurs collections entre groupes
- Organiser un petit concours : qui a la plus belle collection ?

### En cas de blocage
1. Vérifier la console du navigateur (F12) : les erreurs y sont affichées
2. Vérifier que le backend tourne (`php -S localhost:8080`)
3. Vérifier la sauvegarde du fichier (Ctrl+S)
4. Vérifier l'indentation et les accolades/parenthèses

### Extensions possibles (pour les plus rapides)
- Ajouter une note personnelle à chaque favori
- Trier la collection par score ou par date
- Créer un système de tags/catégories
- Ajouter une recherche dans la collection locale
