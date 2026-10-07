<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_admin_permission('emails');

$pageTitle = "Emails & Notifications";
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../config/mailer.php';

ensure_mail_schema();
$db = get_db();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_smtp') {
            save_setting('smtp_host', clean_input($_POST['smtp_host'] ?? 'smtp.gmail.com'));
            save_setting('smtp_port', (string)(int)($_POST['smtp_port'] ?? 587));
            $secure = in_array($_POST['smtp_secure'] ?? 'tls', ['tls', 'ssl', 'none'], true) ? $_POST['smtp_secure'] : 'tls';
            save_setting('smtp_secure', $secure);
            save_setting('smtp_user', clean_input($_POST['smtp_user'] ?? ''));
            save_setting('smtp_from_name', clean_input($_POST['smtp_from_name'] ?? ''));
            if (trim($_POST['smtp_pass'] ?? '') !== '') {
                save_setting('smtp_pass', str_replace(' ', '', trim($_POST['smtp_pass'])));
            }
            if (isset($_POST['email_directeur']) && filter_var($_POST['email_directeur'], FILTER_VALIDATE_EMAIL)) {
                save_setting('email_directeur', trim($_POST['email_directeur']));
            }
            $success = "Configuration d'envoi enregistrée. Cliquez sur « Envoyer un email de test » pour la vérifier.";
        } elseif ($action === 'test') {
            $dest = fresh_setting('email_directeur', 'chakirbal07@gmail.com');
            $html = build_notification_html('test', 'Test ECOB', 'Email de test', ['Résultat' => "La configuration d'envoi fonctionne.", 'Date' => date('d/m/Y H:i:s')], $dest);
            [$ok, $err] = send_mail_smtp($dest, 'Test d\'envoi - ' . get_setting('site_nom', 'ECOB University'), $html);
            if ($ok) {
                $success = "Email de test envoyé à $dest. Vérifiez la boîte de réception (et les courriers indésirables).";
            } else {
                $error = "Échec de l'envoi de test : $err";
            }
        } elseif ($action === 'import') {
            $n = backfill_previous_requests();
            $success = $n > 0
                ? "$n demande(s) précédente(s) ajoutée(s) à la file d'envoi. Cliquez sur « Envoyer tous les emails en attente »."
                : "Aucune ancienne demande à importer : tout est déjà dans le journal.";
        } elseif ($action === 'send_all') {
            [$sent, $failed, $firstErr] = send_pending_emails();
            if ($sent > 0) {
                $success = "$sent email(s) envoyé(s) au directeur." . ($failed ? " $failed en échec." : '');
            }
            if ($failed > 0 && $sent === 0) {
                $error = "Aucun email n'a pu être envoyé : $firstErr";
            } elseif ($failed > 0) {
                $error = "$failed email(s) en échec : $firstErr";
            } elseif ($sent === 0) {
                $success = "Aucun email en attente.";
            }
        } elseif ($action === 'resend') {
            [$ok, $err] = deliver_log_row((int)($_POST['id'] ?? 0));
            if ($ok) {
                $success = "Email renvoyé avec succès.";
            } else {
                $error = "Échec du renvoi : $err";
            }
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM emails_notifications WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
            $success = "Entrée supprimée du journal.";
        }
    } catch (Throwable $t) {
        $error = "Erreur : " . $t->getMessage();
    }
}

$cfg = smtp_config();
$configured = smtp_is_configured();
$stats = ['envoye' => 0, 'en_attente' => 0, 'echec' => 0];
foreach ($db->query("SELECT statut_envoi, COUNT(*) c FROM emails_notifications GROUP BY statut_envoi")->fetchAll() as $r) {
    $stats[$r['statut_envoi']] = (int)$r['c'];
}
$unlogged = 0;
foreach (['admis', 'contact', 'message'] as $t) {
    $unlogged += (int)$db->query("SELECT COUNT(*) FROM `$t` WHERE id NOT IN (SELECT ref_id FROM emails_notifications WHERE ref_table = '$t' AND ref_id IS NOT NULL)")->fetchColumn();
}
$rows = $db->query("SELECT id, type_notification, destinataire, sujet, statut_envoi, erreur, tentatives, created_at, date_envoi FROM emails_notifications ORDER BY id DESC LIMIT 100")->fetchAll();

$inputStyle = 'width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem;';
$labelStyle = 'display:block; font-weight:700; margin-bottom:0.35rem; font-size:0.85rem;';
$typeLabels = ['preinscription' => 'Préinscription', 'contact' => 'Contact', 'commentaire' => 'Commentaire', 'test' => 'Test'];
?>

<div style="max-width:1100px; margin:0 auto;">

    <?php if ($success): ?>
        <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.25rem; font-weight:600;"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.25rem; font-weight:600;"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!$configured): ?>
        <div style="background:#fef3c7; color:#92400e; padding:1.1rem 1.25rem; border-radius:12px; margin-bottom:1.25rem; border:1px solid #fcd34d;">
            <strong><i class="fas fa-exclamation-triangle"></i> Les emails ne peuvent pas partir pour l'instant.</strong><br>
            Le serveur local n'a pas de service d'envoi. Renseignez ci-dessous l'adresse Gmail d'envoi et son <em>mot de passe d'application</em>
            (Compte Google → Sécurité → Validation en 2 étapes → Mots de passe des applications). Les demandes reçues sont conservées et pourront être envoyées ensuite.
        </div>
    <?php endif; ?>

    <!-- STATISTIQUES -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card"><div><div class="stat-val" style="color:#15803d;"><?= $stats['envoye'] ?></div><div class="stat-label">Envoyés</div></div><div class="stat-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-check"></i></div></div>
        <div class="stat-card"><div><div class="stat-val" style="color:#b45309;"><?= $stats['en_attente'] ?></div><div class="stat-label">En attente</div></div><div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-clock"></i></div></div>
        <div class="stat-card"><div><div class="stat-val" style="color:#b91c1c;"><?= $stats['echec'] ?></div><div class="stat-label">En échec</div></div><div class="stat-icon" style="background:#fee2e2; color:#b91c1c;"><i class="fas fa-times"></i></div></div>
        <div class="stat-card"><div><div class="stat-val"><?= $unlogged ?></div><div class="stat-label">Anciennes demandes non notifiées</div></div><div class="stat-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-inbox"></i></div></div>
    </div>

    <!-- ACTIONS RAPIDES -->
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1.5rem;">
        <form method="POST"><input type="hidden" name="action" value="import">
            <button class="btn-action" style="background:#e0f2fe; color:#0369a1; padding:0.75rem 1.1rem;"><i class="fas fa-file-import"></i> Importer les demandes précédentes</button></form>
        <form method="POST"><input type="hidden" name="action" value="send_all">
            <button class="btn-action" style="background:#f59e0b; color:#09122c; padding:0.75rem 1.1rem;"><i class="fas fa-paper-plane"></i> Envoyer tous les emails en attente</button></form>
        <form method="POST"><input type="hidden" name="action" value="test">
            <button class="btn-action" style="background:#09122c; color:#fff; padding:0.75rem 1.1rem;"><i class="fas fa-vial"></i> Envoyer un email de test</button></form>
    </div>

    <!-- CONFIGURATION SMTP -->
    <form method="POST" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:1.75rem; margin-bottom:1.5rem;">
        <input type="hidden" name="action" value="save_smtp">
        <h3 style="font-size:1.1rem; color:#09122c; margin-bottom:1rem; border-bottom:1px solid #f1f5f9; padding-bottom:0.6rem;">
            <i class="fas fa-cog" style="color:#f59e0b;"></i> Compte d'envoi (SMTP)
            <span class="badge <?= $configured ? 'badge-success' : 'badge-pending' ?>" style="margin-left:0.5rem;"><?= $configured ? 'Configuré' : 'À configurer' ?></span>
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1rem; margin-bottom:1rem;">
            <div><label style="<?= $labelStyle ?>">Email du directeur (destinataire)</label>
                <input type="email" name="email_directeur" value="<?= e(fresh_setting('email_directeur', 'chakirbal07@gmail.com')) ?>" required style="<?= $inputStyle ?>"></div>
            <div><label style="<?= $labelStyle ?>">Adresse Gmail d'envoi</label>
                <input type="email" name="smtp_user" value="<?= e($cfg['user']) ?>" placeholder="exemple@gmail.com" style="<?= $inputStyle ?>"></div>
            <div><label style="<?= $labelStyle ?>">Mot de passe d'application (16 caractères)</label>
                <input type="password" name="smtp_pass" autocomplete="new-password" placeholder="<?= $cfg['pass'] !== '' ? '•••••••• (déjà enregistré, laisser vide pour conserver)' : 'xxxx xxxx xxxx xxxx' ?>" style="<?= $inputStyle ?>"></div>
            <div><label style="<?= $labelStyle ?>">Nom affiché de l'expéditeur</label>
                <input type="text" name="smtp_from_name" value="<?= e($cfg['from_name']) ?>" style="<?= $inputStyle ?>"></div>
        </div>
        <details style="margin-bottom:1rem;">
            <summary style="cursor:pointer; font-weight:700; font-size:0.85rem; color:#475569;">Réglages avancés du serveur</summary>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-top:0.85rem;">
                <div><label style="<?= $labelStyle ?>">Serveur SMTP</label><input type="text" name="smtp_host" value="<?= e($cfg['host']) ?>" style="<?= $inputStyle ?>"></div>
                <div><label style="<?= $labelStyle ?>">Port</label><input type="number" name="smtp_port" value="<?= (int)$cfg['port'] ?>" style="<?= $inputStyle ?>"></div>
                <div><label style="<?= $labelStyle ?>">Chiffrement</label>
                    <select name="smtp_secure" style="<?= $inputStyle ?>">
                        <?php foreach (['tls' => 'STARTTLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Aucun'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $cfg['secure'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select></div>
            </div>
        </details>
        <button type="submit" class="btn-add" style="border:none; cursor:pointer;"><i class="fas fa-save"></i> Enregistrer la configuration</button>
    </form>

    <!-- JOURNAL -->
    <div class="table-card" style="margin-top:0;">
        <h3 style="font-size:1.1rem; color:#09122c; margin-bottom:1rem;"><i class="fas fa-list" style="color:#f59e0b;"></i> Journal des 100 derniers emails</h3>
        <?php if (!$rows): ?>
            <p style="color:#64748b;">Aucun email dans le journal. Utilisez « Importer les demandes précédentes ».</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Date</th><th>Type</th><th>Sujet</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                    <td><?= e($typeLabels[$r['type_notification']] ?? $r['type_notification']) ?></td>
                    <td>
                        <?= e($r['sujet']) ?>
                        <?php if (!empty($r['erreur']) && $r['statut_envoi'] !== 'envoye'): ?>
                            <div style="font-size:0.75rem; color:#b91c1c; margin-top:0.2rem;"><?= e($r['erreur']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['statut_envoi'] === 'envoye'): ?><span class="badge badge-success">Envoyé</span>
                        <?php elseif ($r['statut_envoi'] === 'echec'): ?><span class="badge badge-danger">Échec</span>
                        <?php else: ?><span class="badge badge-pending">En attente</span><?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <form method="POST" style="display:inline;"><input type="hidden" name="action" value="resend"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn-action btn-edit" title="Envoyer / renvoyer"><i class="fas fa-redo"></i> <?= $r['statut_envoi'] === 'envoye' ? 'Renvoyer' : 'Envoyer' ?></button></form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette entrée du journal ?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn-action btn-del" title="Supprimer"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
