<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/db.php';

try {
    $db = get_db();
    $stmt = $db->query("SELECT id, nom, logo_url, type_partenariat, description, pays, site_web, ordre, statut FROM partenaires WHERE statut = 'actif' ORDER BY ordre ASC, id ASC");
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($partners as &$p) {
        $p['logo_url'] = asset_url($p['logo_url']);
    }

    echo json_encode([
        'status' => 'success',
        'total' => count($partners),
        'data' => $partners
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la récupération des partenaires.'
    ]);
}
