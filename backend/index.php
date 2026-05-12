<?php
/**
 * ============================================================
 *  MANGATHÈQUE — Backend API (PHP 8 / SQLite)
 * ============================================================
 *
 *  Point d'entrée unique. Ce fichier gère :
 *    1. Les headers CORS (pour autoriser localhost et file://)
 *    2. La création automatique de la base SQLite
 *    3. Le routage vers les 4 actions disponibles :
 *       - recherche_externe  →  chercher un manga via Jikan (MyAnimeList)
 *       - ajouter            →  ajouter un manga à la collection du groupe
 *       - supprimer          →  retirer un manga de la collection du groupe
 *       - ma_collection      →  afficher la collection du groupe
 *
 *  Utilisation :
 *    php -S localhost:8080 index.php
 *
 *  Pensé pour o2switch (Apache) avec le .htaccess fourni.
 * ============================================================
 */

// ─────────────────────────────────────────────
//  1. HEADERS CORS — On autorise tout le monde
// ─────────────────────────────────────────────

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

// Les navigateurs envoient d'abord une requête OPTIONS (preflight).
// On répond immédiatement avec un statut 200 et on s'arrête.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─────────────────────────────────────────────
//  2. BASE DE DONNÉES — Création automatique
// ─────────────────────────────────────────────

$cheminBase = __DIR__ . '/data/mangatheque.db';

try {
    $db = new PDO("sqlite:$cheminBase");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer la table si elle n'existe pas encore
    $db->exec("
        CREATE TABLE IF NOT EXISTS collection (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            id_groupe   TEXT    NOT NULL,
            id_jikan    INTEGER NOT NULL,
            titre       TEXT    NOT NULL,
            image_url   TEXT    DEFAULT '',
            synopsis    TEXT    DEFAULT '',
            score       REAL    DEFAULT 0,
            episodes    INTEGER DEFAULT 0,
            date_ajout  DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(id_groupe, id_jikan)
        )
    ");
} catch (PDOException $e) {
    repondre_erreur("Impossible de se connecter à la base de données : " . $e->getMessage());
}

// ─────────────────────────────────────────────
//  3. ROUTAGE — Quelle action demande le client ?
// ─────────────────────────────────────────────

$route = $_GET['route'] ?? '';

// Récupérer les paramètres (GET ou POST en JSON)
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $corps = file_get_contents('php://input');
    $params = json_decode($corps, true) ?? [];
} else {
    $params = $_GET;
}

// Aiguillage vers la bonne fonction
switch ($route) {
    case 'recherche_externe':
        recherche_externe($params);
        break;

    case 'ajouter':
        ajouter($db, $params);
        break;

    case 'supprimer':
        supprimer($db, $params);
        break;

    case 'ma_collection':
        ma_collection($db, $params);
        break;

    default:
        repondre_json([
            "statut"  => "ok",
            "message" => "🎌 Bienvenue sur l'API Mangathèque !",
            "routes"  => [
                "recherche_externe" => "Chercher un manga (param: q)",
                "ajouter"           => "Ajouter à ma collection (params: id_jikan, titre, image_url, id_groupe)",
                "supprimer"         => "Supprimer de ma collection (params: id, id_groupe)",
                "ma_collection"     => "Voir ma collection (param: id_groupe)"
            ]
        ]);
}

// ─────────────────────────────────────────────
//  4. FONCTIONS — Le cœur de l'API
// ─────────────────────────────────────────────

/**
 * Recherche un manga via l'API Jikan (MyAnimeList).
 * On fait office de « proxy » pour éviter les problèmes CORS côté client.
 */
function recherche_externe(array $params): void {
    $recherche = trim($params['q'] ?? '');

    if ($recherche === '') {
        repondre_erreur("Le paramètre 'q' (recherche) est requis.");
        return;
    }

    $url = "https://api.jikan.moe/v4/anime?" . http_build_query([
        'q'     => $recherche,
        'limit' => 12,
        'sfw'   => true
    ]);

    $contexte = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header'  => "User-Agent: Mangatheque-Stage/1.0\r\n"
        ]
    ]);

    $reponse = @file_get_contents($url, false, $contexte);

    if ($reponse === false) {
        repondre_erreur("Impossible de contacter l'API Jikan. Réessaie dans quelques secondes.");
        return;
    }

    $donnees = json_decode($reponse, true);

    // On simplifie les données pour les élèves
    $resultats = [];
    foreach (($donnees['data'] ?? []) as $anime) {
        $resultats[] = [
            'id_jikan'  => $anime['mal_id'] ?? 0,
            'titre'     => $anime['title'] ?? 'Sans titre',
            'image_url' => $anime['images']['jpg']['image_url'] ?? '',
            'synopsis'  => mb_substr($anime['synopsis'] ?? '', 0, 200),
            'score'     => $anime['score'] ?? 0,
            'episodes'  => $anime['episodes'] ?? 0,
            'type'      => $anime['type'] ?? '',
            'statut'    => $anime['status'] ?? '',
        ];
    }

    repondre_json([
        "statut"    => "ok",
        "resultats" => $resultats
    ]);
}

/**
 * Ajoute un manga à la collection d'un groupe.
 */
function ajouter(PDO $db, array $params): void {
    $id_groupe = $params['id_groupe'] ?? '';
    $id_jikan  = intval($params['id_jikan'] ?? 0);
    $titre     = trim($params['titre'] ?? '');
    $image_url = trim($params['image_url'] ?? '');
    $synopsis  = trim($params['synopsis'] ?? '');
    $score     = floatval($params['score'] ?? 0);
    $episodes  = intval($params['episodes'] ?? 0);

    if ($id_groupe === '' || $id_jikan === 0 || $titre === '') {
        repondre_erreur("Paramètres manquants. Il faut : id_groupe, id_jikan, titre.");
        return;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO collection (id_groupe, id_jikan, titre, image_url, synopsis, score, episodes)
            VALUES (:id_groupe, :id_jikan, :titre, :image_url, :synopsis, :score, :episodes)
        ");
        $stmt->execute([
            ':id_groupe' => $id_groupe,
            ':id_jikan'  => $id_jikan,
            ':titre'     => $titre,
            ':image_url' => $image_url,
            ':synopsis'  => $synopsis,
            ':score'     => $score,
            ':episodes'  => $episodes,
        ]);

        repondre_json([
            "statut"  => "ok",
            "message" => "✅ \"$titre\" ajouté à la collection !"
        ]);
    } catch (PDOException $e) {
        // UNIQUE constraint = déjà dans la collection
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            repondre_erreur("\"$titre\" est déjà dans ta collection !");
        } else {
            repondre_erreur("Erreur base de données : " . $e->getMessage());
        }
    }
}

/**
 * Supprime un manga de la collection d'un groupe.
 */
function supprimer(PDO $db, array $params): void {
    $id        = intval($params['id'] ?? 0);
    $id_groupe = $params['id_groupe'] ?? '';

    if ($id === 0 || $id_groupe === '') {
        repondre_erreur("Paramètres manquants. Il faut : id, id_groupe.");
        return;
    }

    $stmt = $db->prepare("DELETE FROM collection WHERE id = :id AND id_groupe = :id_groupe");
    $stmt->execute([':id' => $id, ':id_groupe' => $id_groupe]);

    if ($stmt->rowCount() > 0) {
        repondre_json([
            "statut"  => "ok",
            "message" => "🗑️ Manga supprimé de la collection."
        ]);
    } else {
        repondre_erreur("Aucun manga trouvé avec cet identifiant dans ton groupe.");
    }
}

/**
 * Récupère la collection complète d'un groupe.
 */
function ma_collection(PDO $db, array $params): void {
    $id_groupe = $params['id_groupe'] ?? '';

    if ($id_groupe === '') {
        repondre_erreur("Le paramètre 'id_groupe' est requis.");
        return;
    }

    $stmt = $db->prepare("SELECT * FROM collection WHERE id_groupe = :id_groupe ORDER BY date_ajout DESC");
    $stmt->execute([':id_groupe' => $id_groupe]);
    $collection = $stmt->fetchAll(PDO::FETCH_ASSOC);

    repondre_json([
        "statut"     => "ok",
        "id_groupe"  => $id_groupe,
        "total"      => count($collection),
        "collection" => $collection
    ]);
}

// ─────────────────────────────────────────────
//  5. UTILITAIRES
// ─────────────────────────────────────────────

function repondre_json(array $donnees): void {
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function repondre_erreur(string $message): void {
    http_response_code(400);
    repondre_json([
        "statut"  => "erreur",
        "message" => $message
    ]);
}
