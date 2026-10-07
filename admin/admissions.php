<?php
// Export CSV doit être traité AVANT tout output HTML
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Session + DB uniquement, pas le layout HTML
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_logged_in'])) {
        http_response_code(403);
        exit('Accès refusé.');
    }
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/helpers.php';
    if (!admin_has_permission('admissions')) {
        http_response_code(403);
        exit('Accès refusé : permission préinscriptions requise.');
    }

    $db = get_db();

    // Filtres optionnels transmis depuis la page
    $statut = isset($_GET['statut']) && in_array($_GET['statut'], ['En attente', 'Validé', 'Rejeté'], true)
        ? $_GET['statut'] : null;

    $sql = "SELECT * FROM admis";
    $params = [];
    if ($statut) {
        $sql .= " WHERE statut = ?";
        $params[] = $statut;
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Vider tout buffer de sortie éventuel
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=admissions_ecob_' . date('Y-m-d') . '.csv');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // BOM UTF-8 pour Excel (affichage correct des accents)
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // En-têtes des colonnes
    fputcsv($output, [
        'ID', 'Nom', 'Prénom', 'Email', 'Téléphone',
        'Nationalité', 'Date Naissance', 'Niveau Étude',
        'Formation Souhaitée', 'Statut', 'Date Inscription'
    ], ';');

    $dec = fn($v) => html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    foreach ($rows as $r) {
        fputcsv($output, [
            $r['id'] ?? $r['ID'] ?? '',
            $dec($r['nom'] ?? ''),
            $dec($r['prenom'] ?? ''),
            $r['email'] ?? '',
            $r['phone'] ?? '',
            $dec($r['nationalite'] ?? ''),
            $r['dte'] ?? '',
            $dec($r['niveau_etude'] ?? ''),
            $dec($r['formation'] ?? ''),
            $dec($r['statut'] ?? 'En attente'),
            $r['date_creation'] ?? '',
        ], ';');
    }

    fclose($output);
    exit;
}

// ---- PAGE NORMALE (HTML) ----
require_once __DIR__ . '/../config/helpers.php';
require_admin_permission('admissions');

$pageTitle = "Gestion des Candidatures & Préinscriptions";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

// Changement de statut
if (isset($_GET['status'], $_GET['id'])) {
    $stat     = clean_input($_GET['status']);
    $admisId  = (int)$_GET['id'];
    if (in_array($stat, ['En attente', 'Validé', 'Rejeté'], true) && $admisId > 0) {
        $upd = $db->prepare("UPDATE admis SET statut = ? WHERE id = ? OR ID = ?");
        $upd->execute([$stat, $admisId, $admisId]);
        $msg = "Statut de la candidature #$admisId mis à jour : $stat";
    }
}

// Suppression
if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $stmt = $db->prepare("DELETE FROM admis WHERE id = ? OR ID = ?");
        $stmt->execute([$delId, $delId]);
        $msg = "Candidature supprimée avec succès.";
    }
}

$admissions = $db->query("SELECT * FROM admis ORDER BY id DESC")->fetchAll();

// Statistiques rapides
$stats = ['total' => count($admissions), 'valide' => 0, 'attente' => 0, 'rejete' => 0];
foreach ($admissions as $a) {
    $s = $a['statut'] ?? 'En attente';
    if ($s === 'Validé')   $stats['valide']++;
    elseif ($s === 'Rejeté') $stats['rejete']++;
    else                    $stats['attente']++;
}
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Statistiques -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="stat-card">
        <div><div class="stat-val"><?= $stats['total'] ?></div><div class="stat-label">Total candidatures</div></div>
        <div class="stat-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-users"></i></div>
    </div>
    <div class="stat-card">
        <div><div class="stat-val" style="color:#15803d;"><?= $stats['valide'] ?></div><div class="stat-label">Validées</div></div>
        <div class="stat-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-card">
        <div><div class="stat-val" style="color:#b45309;"><?= $stats['attente'] ?></div><div class="stat-label">En attente</div></div>
        <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div><div class="stat-val" style="color:#b91c1c;"><?= $stats['rejete'] ?></div><div class="stat-label">Rejetées</div></div>
        <div class="stat-icon" style="background:#fee2e2; color:#b91c1c;"><i class="fas fa-times-circle"></i></div>
    </div>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <p style="color:#64748b; font-size:0.92rem; margin:0;">Consultez et traitez les dossiers des étudiants inscrits en ligne.</p>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <input type="text" id="adminAdmisSearch" placeholder="Rechercher candidat, formation..."
               style="padding:0.6rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; min-width:220px;">
        <select id="adminStatusFilter" style="padding:0.6rem 0.8rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; background:#fff; outline:none;">
            <option value="all">Tous statuts</option>
            <option value="En attente">En attente</option>
            <option value="Validé">Validé</option>
            <option value="Rejeté">Rejeté</option>
        </select>
        <a href="admissions.php?export=csv" class="btn-action"
           style="background:#10b981; color:#fff; padding:0.65rem 1.25rem; border-radius:10px; font-weight:700; text-decoration:none;">
            <i class="fas fa-file-excel"></i> Exporter CSV
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Candidat</th>
                <th>Contacts</th>
                <th>Formation Demandée</th>
                <th>Niveau</th>
                <th>Date Naissance</th>
                <th>Statut</th>
                <th style="white-space:nowrap;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($admissions)): ?>
                <?php foreach ($admissions as $a):
                    $rowId    = $a['id'] ?? $a['ID'];
                    $curStatut = $a['statut'] ?? 'En attente';
                ?>
                    <tr class="admis-row"
                        data-search="<?= htmlspecialchars(mb_strtolower($a['prenom'] . ' ' . $a['nom'] . ' ' . $a['email'] . ' ' . $a['formation'])) ?>"
                        data-status="<?= htmlspecialchars($curStatut) ?>">
                        <td>
                            <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;">Nationalité : <?= htmlspecialchars($a['nationalite']) ?></div>
                            <?php if (!empty($a['date_creation'])): ?>
                                <div style="font-size:0.78rem; color:#94a3b8;">Inscrit le <?= date('d/m/Y', strtotime($a['date_creation'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <a href="tel:<?= preg_replace('/\s+/', '', $a['phone']) ?>"
                                   style="color:#0b1736; font-weight:600; text-decoration:none;">
                                    <i class="fas fa-phone-alt" style="color:#10b981; font-size:0.8rem;"></i>
                                    <?= htmlspecialchars($a['phone']) ?>
                                </a>
                            </div>
                            <div style="font-size:0.82rem; color:#64748b;">
                                <a href="mailto:<?= htmlspecialchars($a['email']) ?>"
                                   style="color:#64748b; text-decoration:none;">
                                    <i class="fas fa-envelope" style="font-size:0.8rem;"></i>
                                    <?= htmlspecialchars($a['email']) ?>
                                </a>
                            </div>
                        </td>
                        <td><strong style="color:var(--primary);"><?= htmlspecialchars($a['formation']) ?></strong></td>
                        <td><?= htmlspecialchars($a['niveau_etude']) ?></td>
                        <td><?= date('d/m/Y', strtotime($a['dte'])) ?></td>
                        <td>
                            <?php if ($curStatut === 'Validé'): ?>
                                <span class="badge badge-success">Validé</span>
                            <?php elseif ($curStatut === 'Rejeté'): ?>
                                <span class="badge badge-danger">Rejeté</span>
                            <?php else: ?>
                                <span class="badge badge-pending">En attente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                <?php if ($curStatut !== 'Validé'): ?>
                                    <a href="admissions.php?status=Validé&id=<?= $rowId ?>"
                                       class="btn-action" style="background:#dcfce7; color:#15803d;" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($curStatut !== 'Rejeté'): ?>
                                    <a href="admissions.php?status=Rejeté&id=<?= $rowId ?>"
                                       class="btn-action" style="background:#fee2e2; color:#b91c1c;" title="Refuser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="admissions.php?del=<?= $rowId ?>"
                                   class="btn-action btn-del"
                                   onclick="return confirm('Supprimer définitivement ce dossier ?');"
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:#64748b;">
                        <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem; color:#cbd5e1;"></i>
                        Aucune candidature enregistrée.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput  = document.getElementById('adminAdmisSearch');
    const statusFilter = document.getElementById('adminStatusFilter');
    const rows         = document.querySelectorAll('.admis-row');

    function filterAdmissions() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const stat  = statusFilter ? statusFilter.value : 'all';

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus  = row.getAttribute('data-status') || '';
            const matchQuery  = !query || searchData.includes(query);
            const matchStatus = (stat === 'all' || rowStatus === stat);
            row.style.display = (matchQuery && matchStatus) ? '' : 'none';
        });
    }

    if (searchInput)  searchInput.addEventListener('input',  filterAdmissions);
    if (statusFilter) statusFilter.addEventListener('change', filterAdmissions);
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
