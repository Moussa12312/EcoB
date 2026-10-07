<?php
$pageTitle = "Gestion des Pages du Site";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$pages = $db->query("SELECT * FROM pages_contenu ORDER BY slug ASC")->fetchAll();
?>

<div style="margin-bottom:1.5rem;">
    <p style="color:#64748b; font-size:0.92rem;">
        Modifiez le contenu textuel et les images d'en-tête de toutes les pages d'information de l'université.
    </p>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Page</th>
                <th>Titre Principal</th>
                <th>Dernière mise à jour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $slugMap = [
                'presentation' => '../presentation.php',
                'a-propos'     => '../a-propos.php',
                'organisation' => '../organisa.php',
                'reglement'    => '../regle.php'
            ];
            foreach ($pages as $p): 
                $pubUrl = $slugMap[$p['slug']] ?? ('../' . $p['slug'] . '.php');
            ?>
                <tr>
                    <td>
                        <strong style="color:var(--primary); font-size:1rem; text-transform:capitalize;">
                            <i class="fas fa-file-alt" style="color:var(--accent); margin-right:6px;"></i> <?= htmlspecialchars($p['slug']) ?>
                        </strong>
                        <div style="font-size:0.8rem; color:#64748b;">
                            Fichier : <?= basename($pubUrl) ?> 
                            <a href="<?= $pubUrl ?>" target="_blank" style="color:#0284c7; margin-left:6px; font-weight:600;"><i class="fas fa-external-link-alt"></i> Voir</a>
                        </div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($p['titre']) ?></strong>
                        <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars(mb_strimwidth($p['sous_titre'] ?? '', 0, 75, '...')) ?></div>
                    </td>
                    <td>
                        <small style="color:#64748b;"><?= date('d/m/Y H:i', strtotime($p['updated_at'])) ?></small>
                    </td>
                    <td>
                        <a href="page-edit.php?slug=<?= urlencode($p['slug']) ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Modifier le texte & l'image
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
