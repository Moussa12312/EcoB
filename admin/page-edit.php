<?php
$pageTitle = "Modifier le Contenu de la Page";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$slug = clean_input($_GET['slug'] ?? 'presentation');

$stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = ?");
$stmt->execute([$slug]);
$pageData = $stmt->fetch();

if (!$pageData) {
    echo "<p>Page introuvable.</p>";
    require_once __DIR__ . '/layout-footer.php';
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre             = clean_input($_POST['titre'] ?? '');
    $sous_titre        = clean_input($_POST['sous_titre'] ?? '');
    $badge             = clean_input($_POST['badge'] ?? '');
    $contenu_principal = trim($_POST['contenu_principal'] ?? '');
    $image_url         = $pageData['image_url'];

    $uploadErr = null;
    $newImg = null;

    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (" . ini_get('post_max_size') . "). Aucune modification enregistrée : réduisez la taille de l'image.";
    } else {
        $newImg = save_uploaded_image('image_file', 'page_' . $slug, $uploadErr);
        if ($uploadErr) {
            $error = "Image non enregistrée : " . $uploadErr;
        } else {
            if ($newImg) {
                $image_url = $newImg;
            }

            try {
                $upd = $db->prepare("UPDATE pages_contenu SET titre = ?, sous_titre = ?, badge = ?, contenu_principal = ?, image_url = ? WHERE slug = ?");
                $upd->execute([$titre, $sous_titre, $badge, $contenu_principal, $image_url, $slug]);
                $success = "La page a été mise à jour avec succès !";

                if ($newImg && !empty($pageData['image_url']) && $pageData['image_url'] !== $newImg) {
                    delete_uploaded_file($pageData['image_url']);
                }

                $stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = ?");
                $stmt->execute([$slug]);
                $pageData = $stmt->fetch();
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width:850px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:800; font-family:'Outfit',sans-serif;">
            Modifier la page : <span style="color:var(--accent); text-transform:capitalize;"><?= htmlspecialchars($slug) ?></span>
        </h2>
        <a href="pages.php" style="color:#64748b; text-decoration:none; font-size:0.9rem; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Retour aux pages
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:16px; padding:2rem; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <form method="POST" action="page-edit.php?slug=<?= urlencode($slug) ?>" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Titre de la Bannière *</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($pageData['titre']) ?>" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Badge Supérieur</label>
                    <input type="text" name="badge" value="<?= htmlspecialchars($pageData['badge'] ?? '') ?>" placeholder="Ex: Histoire & Vision" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Sous-titre / Accroche</label>
                <input type="text" name="sous_titre" value="<?= htmlspecialchars($pageData['sous_titre'] ?? '') ?>" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Image d'illustration de la page</label>
                <?php if (!empty($pageData['image_url'])): ?>
                    <div style="margin-bottom:0.6rem; display:flex; align-items:center; gap:0.75rem;">
                        <img src="<?= asset_url($pageData['image_url'], '', '../') ?>" alt="" style="width:90px; height:55px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b;">Image actuelle</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="image_file" accept="image/*" style="width:100%; padding:0.6rem; border-radius:8px; border:1.5px dashed #cbd5e1;">
            </div>

            <div style="margin-bottom:1.75rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Texte & Paragraphes Principaux (Supporte les balises HTML)</label>
                <textarea name="contenu_principal" required style="width:100%; padding:0.9rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; min-height:220px; line-height:1.6; font-family:inherit;"><?= htmlspecialchars($pageData['contenu_principal']) ?></textarea>
                <small style="color:#64748b;">Vous pouvez utiliser des paragraphes simples ou des balises HTML comme &lt;p&gt;, &lt;h3&gt;, &lt;strong&gt;, etc.</small>
            </div>

            <button type="submit" class="btn-add" style="width:100%; justify-content:center; padding:1.1rem; font-size:1.05rem; border:none; cursor:pointer;">
                <i class="fas fa-save"></i> Enregistrer les Modifications de la Page
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
