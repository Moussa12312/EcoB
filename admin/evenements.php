<?php
$pageTitle = "Gestion des Événements & Publications";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

// Suppression d'un événement
if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $stmt = $db->prepare("DELETE FROM evenements WHERE id = ?");
        $stmt->execute([$delId]);
        $msg = "Publication supprimée avec succès.";
    }
}

$events = $db->query("SELECT * FROM evenements ORDER BY id DESC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
    <div>
        <p style="color:#64748b; font-size:0.92rem;">Publiez vos photos, vidéos, conférences et annonces universitaires.</p>
    </div>
    <?php
    $cats = $db->query("SELECT DISTINCT categorie FROM evenements WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY categorie ASC")->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <input type="text" id="adminEventSearch" placeholder="Rechercher..." style="padding:0.6rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none;">
        <select id="adminCatFilter" style="padding:0.6rem 0.8rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; background:#fff; outline:none;">
            <option value="all">Toutes catégories</option>
            <?php foreach ($cats as $catItem): ?>
                <option value="<?= htmlspecialchars($catItem) ?>"><?= htmlspecialchars($catItem) ?></option>
            <?php endforeach; ?>
        </select>
        <a href="evenement-form.php" class="btn-add">
            <i class="fas fa-plus"></i> Nouvelle Publication
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Média</th>
                <th>Titre & Catégorie</th>
                <th>Type</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $ev): ?>
                    <tr class="event-row" data-title="<?= htmlspecialchars(mb_strtolower($ev['titre'])) ?>" data-cat="<?= htmlspecialchars($ev['categorie']) ?>">
                        <td style="width:70px;">
                            <img src="<?= asset_url($ev['media_url'], 'assets/images/img2.jpg', '../') ?>" alt="" style="width:60px; height:45px; object-fit:cover; border-radius:6px;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($ev['titre']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($ev['categorie']) ?></div>
                        </td>
                        <td>
                            <?php if ($ev['type_media'] === 'video'): ?>
                                <span class="badge" style="background:#fee2e2; color:#b91c1c;"><i class="fas fa-video"></i> Vidéo</span>
                            <?php else: ?>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-image"></i> Photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($ev['date_evenement'] ?? $ev['created_at'])) ?>
                        </td>
                        <td>
                            <?php if ($ev['statut'] === 'publie'): ?>
                                <span class="badge badge-success">Publié</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Brouillon</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="evenement-form.php?id=<?= $ev['id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="evenements.php?del=<?= $ev['id'] ?>" class="btn-action btn-del" onclick="return confirm('Confirmer la suppression de cette publication ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucun événement ou média publié pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('adminEventSearch');
    const catFilter = document.getElementById('adminCatFilter');
    const rows = document.querySelectorAll('.event-row');

    function filterAdminRows() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const cat = (catFilter ? catFilter.value : 'all');

        rows.forEach(row => {
            const title = row.getAttribute('data-title') || '';
            const rowCat = row.getAttribute('data-cat') || '';

            const matchQuery = !query || title.includes(query);
            const matchCat = (cat === 'all' || rowCat === cat);

            row.style.display = (matchQuery && matchCat) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterAdminRows);
    if (catFilter) catFilter.addEventListener('change', filterAdminRows);
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
