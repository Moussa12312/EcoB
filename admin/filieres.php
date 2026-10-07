<?php
$pageTitle = "Gestion des Filières & Formations";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $db->prepare("DELETE FROM filieres WHERE id = ?")->execute([$delId]);
        $msg = "Filière supprimée avec succès.";
    }
}

$filieres = $db->query("SELECT * FROM filieres ORDER BY departement ASC, ordre ASC, id DESC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;" id="deptFilterButtons">
        <button type="button" class="btn-action active-filter-btn" data-dept="all" style="cursor:pointer; background:#09122c; color:#fff; border:1px solid #09122c;">Toutes les filières</button>
        <button type="button" class="btn-action" data-dept="gestion" style="cursor:pointer; background:#fff; color:#0f172a; border:1px solid #cbd5e1;">Gestion</button>
        <button type="button" class="btn-action" data-dept="agro" style="cursor:pointer; background:#fff; color:#0f172a; border:1px solid #cbd5e1;">Agro-Business</button>
        <button type="button" class="btn-action" data-dept="mine" style="cursor:pointer; background:#fff; color:#0f172a; border:1px solid #cbd5e1;">Mines & Géologie</button>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <input type="text" id="adminFiliereSearch" placeholder="Rechercher une filière..." style="padding:0.6rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; min-width:200px;">
        <a href="filiere-form.php" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter une Filière
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Image / Icône</th>
                <th>Nom de la Filière</th>
                <th>Département</th>
                <th>Diplôme & Durée</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($filieres)): ?>
                <?php foreach ($filieres as $f): ?>
                    <tr class="filiere-row" data-dept="<?= htmlspecialchars($f['departement']) ?>" data-name="<?= htmlspecialchars(mb_strtolower($f['nom'] . ' ' . $f['diplome'])) ?>">
                        <td style="width:80px;">
                            <img src="<?= asset_url($f['image_url'], 'assets/images/img2.jpg', '../') ?>" alt="" style="width:65px; height:45px; object-fit:cover; border-radius:8px;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($f['nom']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars(mb_strimwidth($f['description'], 0, 70, '...')) ?></div>
                        </td>
                        <td>
                            <?php
                            $deptLabels = ['gestion' => 'Sciences de Gestion', 'agro' => 'Agro-Business', 'mine' => 'Mines & Géologie'];
                            $deptColors = ['gestion' => '#0284c7', 'agro' => '#10b981', 'mine' => '#d97706'];
                            ?>
                            <span class="badge" style="background:<?= $deptColors[$f['departement']] ?>15; color:<?= $deptColors[$f['departement']] ?>;">
                                <?= $deptLabels[$f['departement']] ?? $f['departement'] ?>
                            </span>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($f['diplome']) ?></div>
                            <small style="color:#64748b;"><?= htmlspecialchars($f['duree']) ?></small>
                        </td>
                        <td>
                            <?php if (($f['statut'] ?? 'actif') === 'actif'): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="filiere-form.php?id=<?= $f['id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="filieres.php?del=<?= $f['id'] ?>" class="btn-action btn-del" onclick="return confirm('Supprimer cette filière ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucune filière trouvée dans cette catégorie.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('#deptFilterButtons button');
    const searchInput = document.getElementById('adminFiliereSearch');
    const rows = document.querySelectorAll('.filiere-row');

    let currentDept = 'all';

    function filterRows() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

        rows.forEach(row => {
            const dept = row.getAttribute('data-dept');
            const name = row.getAttribute('data-name');

            const matchDept = (currentDept === 'all' || dept === currentDept);
            const matchQuery = (!query || name.includes(query));

            row.style.display = (matchDept && matchQuery) ? '' : 'none';
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => {
                b.style.background = '#fff';
                b.style.color = '#0f172a';
                b.style.borderColor = '#cbd5e1';
            });
            this.style.background = '#09122c';
            this.style.color = '#fff';
            this.style.borderColor = '#09122c';

            currentDept = this.getAttribute('data-dept');
            filterRows();
        });
    });

    if (searchInput) searchInput.addEventListener('input', filterRows);
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
