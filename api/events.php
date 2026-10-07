<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/db.php';

try {
    $db = get_db();
    
    $cat = clean_input($_GET['cat'] ?? '');
    $type = clean_input($_GET['type'] ?? '');
    $q = clean_input($_GET['q'] ?? '');

    $sql = "SELECT id, titre, categorie, type_media, media_url, video_embed, description, contenu, date_evenement, created_at 
            FROM evenements 
            WHERE statut = 'publie'";
    $params = [];

    if (!empty($cat) && $cat !== 'all') {
        $sql .= " AND categorie = ?";
        $params[] = $cat;
    }

    if (!empty($type) && $type !== 'all') {
        $sql .= " AND type_media = ?";
        $params[] = $type;
    }

    if (!empty($q)) {
        $sql .= " AND (titre LIKE ? OR description LIKE ?)";
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }

    $sql .= " ORDER BY date_evenement DESC, id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates & default media
    foreach ($events as &$ev) {
        $ev['date_formatted'] = format_date_fr($ev['date_evenement'] ?? $ev['created_at']);
        $ev['media_url'] = asset_url($ev['media_url'], 'assets/images/img2.jpg');
    }

    // Récupérer les catégories avec compteurs
    $catStats = $db->query("SELECT categorie, COUNT(*) as cnt FROM evenements WHERE statut = 'publie' AND categorie IS NOT NULL GROUP BY categorie")->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode([
        'status' => 'success',
        'total' => count($events),
        'categories' => $catStats,
        'data' => $events
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la récupération des événements.'
    ]);
}
