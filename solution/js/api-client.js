/**
 * ============================================================
 *  MANGATHÈQUE — Client API
 * ============================================================
 *
 *  Ce fichier te permet de communiquer avec le backend
 *  sans te soucier des détails techniques (fetch, JSON, etc.)
 *
 *  Tu as UNE SEULE fonction à retenir :
 *
 *    appeler_backend(route, params)
 *
 *  Exemples :
 *    // Chercher un manga
 *    const resultats = await appeler_backend("recherche_externe", { q: "Naruto" });
 *
 *    // Voir ma collection
 *    const maListe = await appeler_backend("ma_collection");
 *
 *    // Ajouter un manga
 *    await appeler_backend("ajouter", { titre: "Naruto", id_jikan: 20, image_url: "..." });
 *
 *    // Supprimer un manga
 *    await appeler_backend("supprimer", { id: 5 });
 *
 * ============================================================
 */

// ─────────────────────────────────────────────
//  CONFIGURATION — À modifier si nécessaire
// ─────────────────────────────────────────────

// L'adresse du serveur backend.
// En local avec PHP : php -S localhost:8080 backend/index.php
const URL_BACKEND = "https://api.manga.technivore.fr";

// L'identifiant de ton groupe.
// Chaque groupe a sa propre collection, isolée des autres.
// Ton encadrant te donnera le nom de ton groupe.
const ID_GROUPE = "groupe-1";

// ─────────────────────────────────────────────
//  FONCTION PRINCIPALE — appeler_backend()
// ─────────────────────────────────────────────

/**
 * Envoie une requête au backend et retourne la réponse.
 *
 * @param {string} route - Le nom de l'action :
 *   "recherche_externe", "ajouter", "supprimer", "ma_collection"
 *
 * @param {object} params - Les paramètres à envoyer (optionnel).
 *   Exemples : { q: "One Piece" } ou { id: 3 }
 *
 * @returns {Promise<object>} La réponse du serveur sous forme d'objet JavaScript.
 */
async function appeler_backend(route, params = {}) {

    // On ajoute automatiquement l'identifiant du groupe
    params.id_groupe = ID_GROUPE;

    // Pour éviter les blocages de pare-feu et de sécurité (comme les cookies anti-DDoS o2switch
    // ou les restrictions CORS strictes sur le protocole file://), nous utilisons GET pour toutes les requêtes.
    const methode = "GET";

    let url = URL_BACKEND + "?route=" + route;
    let options = {};

    if (methode === "GET") {
        // Pour GET, on met les paramètres dans l'URL
        for (const [cle, valeur] of Object.entries(params)) {
            url += "&" + encodeURIComponent(cle) + "=" + encodeURIComponent(valeur);
        }
        options = { method: "GET" };
    } else {
        // Pour POST, on envoie les paramètres dans le corps de la requête
        options = {
            method: "POST",
            headers: { "Content-Type": "text/plain" },
            body: JSON.stringify(params)
        };
    }

    try {
        console.log(`📡 Appel API : ${route}`, params);

        const reponse = await fetch(url, options);
        const donnees = await reponse.json();

        if (donnees.statut === "erreur") {
            console.error(`❌ Erreur du serveur : ${donnees.message}`);
        } else {
            console.log(`✅ Réponse reçue pour : ${route}`, donnees);
        }

        return donnees;

    } catch (erreur) {
        console.error("🔴 Impossible de contacter le serveur !", erreur.message);
        console.error("💡 Vérifie que le backend est lancé : php -S localhost:8080 backend/index.php");

        return {
            statut: "erreur",
            message: "Impossible de contacter le serveur. Est-il bien lancé ?"
        };
    }
}
