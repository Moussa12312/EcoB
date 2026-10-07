<?php
$pageTitle = "Gestion de l'Équipe Dirigeante";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $db->prepare("DELETE FROM equipe_direction WHERE id = ?")->execute([$delId]);
        $msg = "Membre de l'administration supprimé.";
    }
}

$equipe = $db->query("SELECT * FROM equipe_direction ORDER BY ordre ASC, id ASC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <p style="color:#64748b; font-size:0.92rem;">Gérez les photos, noms et fonctions des membres de la direction affichés sur le site.</p>
    </div>
    <a href="equipe-form.php" class="btn-add">
        <i class="fas fa-user-plus"></i> Ajouter un Dirigeant
    </a>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Nom & Prénom</th>
                <th>Fonction / Poste</th>
                <th>Pôle / Département</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($equipe)): ?>
                <?php foreach ($equipe as $m): ?>
                    <tr>
                        <td style="width:70px;">
                            <img src="<?= asset_url($m['photo_url'], 'assets/images/dre.jpg', '../') ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #cbd5e1;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['nom']) ?></strong>
                        </td>
                        <td>
                            <span class="badge" style="background:#e0f2fe; color:#0369a1;"><?= htmlspecialchars($m['poste']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($m['departement']) ?></td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="equipe-form.php?id=<?= $m['id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="equipe.php?del=<?= $m['id'] ?>" class="btn-action btn-del" onclick="return confirm('Supprimer ce membre ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucun membre enregistré.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
