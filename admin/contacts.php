<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_admin_permission('contacts');

$pageTitle = "Messages & Commentaires Reçus";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$msg = '';

// Suppression contact
if (isset($_GET['del_contact'])) {
    $cid = (int)$_GET['del_contact'];
    if ($cid > 0) {
        $db->prepare("DELETE FROM contact WHERE id = ? OR ID = ?")->execute([$cid, $cid]);
        $msg = "Message de contact supprimé.";
    }
}

// Suppression message footer
if (isset($_GET['del_message'])) {
    $mid = (int)$_GET['del_message'];
    if ($mid > 0) {
        $db->prepare("DELETE FROM message WHERE id = ? OR ID = ?")->execute([$mid, $mid]);
        $msg = "Commentaire supprimé.";
    }
}

$contacts = $db->query("SELECT * FROM contact ORDER BY id DESC")->fetchAll();
$messages = $db->query("SELECT * FROM message ORDER BY id DESC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- ONGLETS / SECTIONS -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
    <h2 style="font-size:1.3rem; font-weight:800; margin-bottom:0; font-family:'Outfit',sans-serif;">
        <i class="fas fa-inbox" style="color:var(--accent);"></i> Messages de la Page Contact (<?= count($contacts) ?>)
    </h2>
    <input type="text" id="contactSearch" placeholder="Filtrer les messages..." style="padding:0.55rem 0.95rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; min-width:220px;">
</div>

<div class="table-card" style="margin-top:0; margin-bottom:3rem;">
    <table>
        <thead>
            <tr>
                <th>Expéditeur</th>
                <th>Adresse / Ville</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contacts)): ?>
                <?php foreach ($contacts as $c): $cid = $c['id'] ?? $c['ID']; ?>
                    <tr class="contact-row" data-search="<?= htmlspecialchars(mb_strtolower($c['nom'] . ' ' . $c['email'] . ' ' . ($c['adresse'] ?? '') . ' ' . $c['mesage'])) ?>">
                        <td style="white-space:nowrap;">
                            <strong><?= htmlspecialchars($c['nom']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;">
                                <a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="color:#0284c7; text-decoration:none;">
                                    <?= htmlspecialchars($c['email']) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($c['adresse'] ?? 'Non spécifiée') ?></td>
                        <td>
                            <p style="font-size:0.9rem; color:#334155; line-height:1.5;">
                                <?= nl2br(htmlspecialchars($c['mesage'])) ?>
                            </p>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="btn-action btn-edit" title="Répondre par email">
                                <i class="fas fa-reply"></i> Répondre
                            </a>
                            <a href="contacts.php?del_contact=<?= $cid ?>" class="btn-action btn-del" onclick="return confirm('Supprimer ce message ?');" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucun message reçu via le formulaire de contact.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
    <h2 style="font-size:1.3rem; font-weight:800; margin-bottom:0; font-family:'Outfit',sans-serif;">
        <i class="fas fa-comments" style="color:#0284c7;"></i> Commentaires & Avis du Pied de Page (<?= count($messages) ?>)
    </h2>
    <input type="text" id="commentSearch" placeholder="Filtrer les commentaires..." style="padding:0.55rem 0.95rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; min-width:220px;">
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Avis / Commentaire</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $m): $mid = $m['id'] ?? $m['ID']; ?>
                    <tr class="comment-row" data-search="<?= htmlspecialchars(mb_strtolower($m['nom'] . ' ' . $m['email'] . ' ' . $m['texte'])) ?>">
                        <td><strong><?= htmlspecialchars($m['nom']) ?></strong></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color:#0284c7; text-decoration:none;">
                                <?= htmlspecialchars($m['email']) ?>
                            </a>
                        </td>
                        <td>
                            <p style="font-size:0.9rem; color:#334155; line-height:1.5;">
                                <?= nl2br(htmlspecialchars($m['texte'])) ?>
                            </p>
                        </td>
                        <td>
                            <a href="contacts.php?del_message=<?= $mid ?>" class="btn-action btn-del" onclick="return confirm('Supprimer ce commentaire ?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucun commentaire du pied de page reçu pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contactSearch = document.getElementById('contactSearch');
    const commentSearch = document.getElementById('commentSearch');

    if (contactSearch) {
        contactSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.contact-row').forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }

    if (commentSearch) {
        commentSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.comment-row').forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
