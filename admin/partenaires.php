<?php
$pageTitle = "Gestion des Partenaires & Conventions";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

// Suppression d'un partenaire
if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $db->prepare("DELETE FROM partenaires WHERE id = ?")->execute([$delId]);
        $msg = "Partenaire supprimé avec succès.";
    }
}

// Basculer le statut rapidement
if (isset($_GET['toggle_status'], $_GET['id'])) {
    $pId = (int)$_GET['id'];
    $newStat = $_GET['toggle_status'] === 'actif' ? 'inactif' : 'actif';
    if ($pId > 0) {
        $db->prepare("UPDATE partenaires SET statut = ? WHERE id = ?")->execute([$newStat, $pId]);
        $msg = "Statut du partenaire mis à jour.";
    }
}

$partenaires = $db->query("SELECT * FROM partenaires ORDER BY ordre ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.6rem;">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($msg) ?></span>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.75rem;">
    <div>
        <p style="color:#64748b; font-size:0.92rem; margin:0;">
            Gérez les entreprises, institutions et universités partenaires affichées sur la page d'accueil d'ECOB.
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <input type="text" id="partnerSearchInput" placeholder="Rechercher par nom ou domaine..." style="padding:0.6rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; min-width:240px;">
        <a href="partenaire-form.php" class="btn-add">
            <i class="fas fa-plus-circle"></i> Nouveau Partenaire
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th style="width:130px;">Logo</th>
                <th>Partenaire & Domaine</th>
                <th>Détails de la Collaboration</th>
                <th>Localisation</th>
                <th>Ordre</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($partenaires)): ?>
                <?php foreach ($partenaires as $p): ?>
                    <tr class="partner-row" 
                        data-search="<?= htmlspecialchars(mb_strtolower($p['nom'] . ' ' . ($p['type_partenariat'] ?? '') . ' ' . ($p['description'] ?? ''))) ?>">
                        <td>
                            <div style="background:#ffffff; padding:8px 12px; border-radius:10px; border:1px solid #e2e8f0; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 1px 4px rgba(0,0,0,0.04); width:110px; height:65px;">
                                <img src="<?= asset_url($p['logo_url'], '', '../') ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>" style="max-height:50px; max-width:95px; object-fit:contain;">
                            </div>
                        </td>
                        <td>
                            <strong style="color:var(--primary); font-size:0.95rem; display:block; margin-bottom:0.25rem;">
                                <?= htmlspecialchars($p['nom']) ?>
                            </strong>
                            <?php if (!empty($p['type_partenariat'])): ?>
                                <span class="badge" style="background:rgba(245, 158, 11, 0.15); color:var(--accent-hover); font-size:0.75rem;">
                                    <i class="fas fa-handshake"></i> <?= htmlspecialchars($p['type_partenariat']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['site_web'])): ?>
                                <div style="margin-top:0.35rem;">
                                    <a href="<?= htmlspecialchars($p['site_web']) ?>" target="_blank" style="color:#0284c7; font-size:0.78rem; text-decoration:none;">
                                        <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(parse_url($p['site_web'], PHP_URL_HOST) ?? $p['site_web']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:280px;">
                            <p style="font-size:0.85rem; color:#475569; line-height:1.5; margin:0;">
                                <?= htmlspecialchars(mb_strimwidth($p['description'] ?? 'Convention active.', 0, 110, '...')) ?>
                            </p>
                        </td>
                        <td>
                            <span style="font-size:0.85rem; color:#64748b;">
                                <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> <?= htmlspecialchars($p['pays'] ?? 'Mali') ?>
                            </span>
                        </td>
                        <td style="font-weight:700; color:var(--primary); text-align:center;">
                            <?= (int)$p['ordre'] ?>
                        </td>
                        <td>
                            <?php if (($p['statut'] ?? 'actif') === 'actif'): ?>
                                <a href="partenaires.php?toggle_status=actif&id=<?= $p['id'] ?>" title="Cliquer pour masquer" style="text-decoration:none;">
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Actif</span>
                                </a>
                            <?php else: ?>
                                <a href="partenaires.php?toggle_status=inactif&id=<?= $p['id'] ?>" title="Cliquer pour afficher" style="text-decoration:none;">
                                    <span class="badge badge-pending"><i class="fas fa-eye-slash"></i> Inactif</span>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="partenaire-form.php?id=<?= $p['id'] ?>" class="btn-action btn-edit" title="Modifier les détails">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="partenaires.php?del=<?= $p['id'] ?>" class="btn-action btn-del" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?');" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:3rem; color:#64748b;">
                        <i class="fas fa-handshake" style="font-size:2.5rem; color:#cbd5e1; margin-bottom:1rem; display:block;"></i>
                        Aucun partenaire enregistré. Cliquez sur « Nouveau Partenaire » pour en ajouter un.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('partnerSearchInput');
    const rows = document.querySelectorAll('.partner-row');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
