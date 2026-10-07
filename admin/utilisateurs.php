<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin_permission('utilisateurs');

$pageTitle = "Comptes & Utilisateurs (Admins & Marketeurs)";
$db = get_db();
$msg = $_SESSION['admin_flash_success'] ?? '';
$err = $_SESSION['admin_flash_error'] ?? '';
unset($_SESSION['admin_flash_success'], $_SESSION['admin_flash_error']);

// Action : Activer / Désactiver ou Supprimer
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $userId = (int)$_GET['id'];
    $currentId = (int)($_SESSION['admin_user_id'] ?? 0);

    if ($action === 'toggle' && $userId !== $currentId) {
        $st = $db->prepare("SELECT statut FROM admin_users WHERE id = ?");
        $st->execute([$userId]);
        $curStat = $st->fetchColumn();
        $newStat = ($curStat === 'actif') ? 'inactif' : 'actif';
        $db->prepare("UPDATE admin_users SET statut = ? WHERE id = ?")->execute([$newStat, $userId]);
        $_SESSION['admin_flash_success'] = "Statut de l'utilisateur mis à jour ($newStat).";
        header("Location: utilisateurs.php");
        exit;
    } elseif ($action === 'delete' && $userId !== $currentId) {
        $db->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$userId]);
        $_SESSION['admin_flash_success'] = "Compte utilisateur supprimé avec succès.";
        header("Location: utilisateurs.php");
        exit;
    }
}

$users = $db->query("SELECT * FROM admin_users ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/layout-header.php';
?>

<?php if ($msg): ?>
    <div style="background:#ecfdf5; color:#065f46; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.6rem; border:1px solid #a7f3d0;">
        <i class="fas fa-check-circle" style="font-size:1.2rem;"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>
<?php if ($err): ?>
    <div style="background:#fef2f2; color:#991b1b; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.6rem; border:1px solid #fecaca;">
        <i class="fas fa-exclamation-triangle" style="font-size:1.2rem;"></i> <?= htmlspecialchars($err) ?>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
    <div>
        <p style="color:#64748b; font-size:0.95rem; margin:0;">
            Gérez les accès de l'équipe administrative et créez des comptes <strong>Marketeur</strong> avec restrictions professionnelles sur les données confidentielles.
        </p>
    </div>
    <a href="utilisateur-form.php" class="btn-add" style="background:#09122c; color:#fff; padding:0.75rem 1.4rem; border-radius:10px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; box-shadow:0 4px 12px rgba(9,18,44,0.15);">
        <i class="fas fa-user-plus"></i> Créer un Marketeur ou Admin
    </a>
</div>

<div class="table-card">
    <div style="margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between;">
        <h3 style="font-size:1.15rem; color:#09122c; font-weight:800;">Liste des Comptes (<?= count($users) ?>)</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Utilisateur / Nom</th>
                <th>Rôle & Titre</th>
                <th>Permissions & Restrictions</th>
                <th>Statut</th>
                <th>Créé le</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                $isSelf = ((int)$u['id'] === (int)($_SESSION['admin_user_id'] ?? 0));
                $uRole = $u['role'] ?? 'admin';
                $rawPerms = $u['permissions'] ?? '';
                $permsArr = [];
                if ($rawPerms === 'all' || in_array($uRole, ['superadmin', 'admin'])) {
                    $permsArr = ['all'];
                } elseif (!empty($rawPerms)) {
                    $decoded = json_decode($rawPerms, true);
                    $permsArr = is_array($decoded) ? $decoded : explode(',', $rawPerms);
                }
                ?>
                <tr>
                    <td>
                        <div style="font-weight:700; color:#09122c; font-size:0.95rem;">
                            <?= htmlspecialchars($u['nom_complet']) ?>
                            <?php if ($isSelf): ?>
                                <span style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; padding:0.15rem 0.45rem; border-radius:6px; margin-left:0.3rem;">Vous</span>
                            <?php endif; ?>
                        </div>
                        <div style="color:#64748b; font-size:0.82rem; display:flex; align-items:center; gap:0.4rem; margin-top:2px;">
                            <i class="fas fa-user" style="font-size:0.75rem;"></i> <?= htmlspecialchars($u['username']) ?>
                            <?php if (!empty($u['email'])): ?>
                                <span>&bull;</span> <span><?= htmlspecialchars($u['email']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($uRole === 'superadmin'): ?>
                            <span class="badge" style="background:#09122c; color:#f59e0b; border:1px solid rgba(245,158,11,0.3); font-weight:800;">
                                <i class="fas fa-crown"></i> SUPER ADMIN
                            </span>
                        <?php elseif ($uRole === 'admin'): ?>
                            <span class="badge" style="background:#1e293b; color:#fff; font-weight:700;">
                                <i class="fas fa-user-shield"></i> ADMINISTRATEUR
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background:rgba(238,41,41,0.12); color:#dc2626; border:1px solid rgba(238,41,41,0.3); font-weight:800;">
                                <i class="fas fa-bullhorn"></i> MARKETEUR
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array('all', $permsArr) || $uRole === 'superadmin'): ?>
                            <span style="display:inline-flex; align-items:center; gap:0.3rem; font-size:0.82rem; color:#15803d; font-weight:700; background:#dcfce7; padding:0.25rem 0.65rem; border-radius:6px;">
                                <i class="fas fa-check-double"></i> Accès Total & Illimité
                            </span>
                        <?php else: ?>
                            <div style="display:flex; flex-wrap:wrap; gap:0.35rem; max-width:380px;">
                                <span style="font-size:0.72rem; background:#f1f5f9; color:#0f172a; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;">
                                    <i class="fas fa-images"></i> Médias & Événements
                                </span>
                                <span style="font-size:0.72rem; background:#f1f5f9; color:#0f172a; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;">
                                    <i class="fas fa-sliders-h"></i> Sliders
                                </span>
                                <span style="font-size:0.72rem; background:#f1f5f9; color:#0f172a; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;">
                                    <i class="fas fa-handshake"></i> Partenaires
                                </span>
                                <?php if (in_array('contacts', $permsArr)): ?>
                                    <span style="font-size:0.72rem; background:#dbeafe; color:#1e40af; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;">
                                        <i class="fas fa-envelope"></i> Messages (Autorisé)
                                    </span>
                                <?php else: ?>
                                    <span style="font-size:0.72rem; background:#fee2e2; color:#991b1b; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;" title="Ne peut pas consulter les messages reçus">
                                        <i class="fas fa-lock"></i> Messages (Restreint)
                                    </span>
                                <?php endif; ?>

                                <?php if (in_array('admissions', $permsArr)): ?>
                                    <span style="font-size:0.72rem; background:#dbeafe; color:#1e40af; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;">
                                        <i class="fas fa-user-graduate"></i> Préinscriptions (Autorisé)
                                    </span>
                                <?php else: ?>
                                    <span style="font-size:0.72rem; background:#fee2e2; color:#991b1b; padding:0.15rem 0.45rem; border-radius:4px; font-weight:600;" title="Ne peut pas consulter les préinscriptions d'étudiants">
                                        <i class="fas fa-lock"></i> Préinscriptions (Restreint)
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($u['statut'] ?? 'actif') === 'actif'): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Actif</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Suspendu</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#64748b; font-size:0.82rem; white-space:nowrap;">
                        <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="utilisateur-form.php?id=<?= $u['id'] ?>" class="btn-action btn-edit" title="Modifier">
                            <i class="fas fa-pen"></i> Modifier
                        </a>
                        <?php if (!$isSelf): ?>
                            <a href="utilisateurs.php?action=toggle&id=<?= $u['id'] ?>" class="btn-action" style="background:#f1f5f9; color:#475569;" title="<?= ($u['statut'] ?? 'actif') === 'actif' ? 'Désactiver le compte' : 'Activer le compte' ?>">
                                <i class="fas fa-power-off"></i>
                            </a>
                            <a href="utilisateurs.php?action=delete&id=<?= $u['id'] ?>" class="btn-action btn-del" onclick="return confirm('Confirmer la suppression définitive de cet utilisateur ?');" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
