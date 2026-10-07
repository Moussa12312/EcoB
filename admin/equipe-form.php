<?php
$pageTitle = "Formulaire Dirigeant / Administration";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
$membre = [
    'nom' => '',
    'poste' => '',
    'departement' => 'Administration Générale',
    'photo_url' => 'assets/images/dre.jpg',
    'biographie' => '',
    'ordre' => 0
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM equipe_direction WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $membre = $found;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = clean_input($_POST['nom'] ?? '');
    $poste       = clean_input($_POST['poste'] ?? '');
    $departement = clean_input($_POST['departement'] ?? 'Administration Générale');
    $biographie  = clean_input($_POST['biographie'] ?? '');
    $ordre       = (int)($_POST['ordre'] ?? 0);
    $photo_url   = $membre['photo_url'];

    $uploadErr = null;
    $newPhoto = null;
    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (" . ini_get('post_max_size') . "). Aucune modification enregistrée : réduisez la taille de la photo.";
    } elseif (empty($nom) || empty($poste)) {
        $error = "Veuillez renseigner le nom et le poste du dirigeant.";
    } else {
        $newPhoto = save_uploaded_image('photo_file', 'staff', $uploadErr);
        if ($uploadErr) {
            $error = "Photo non enregistrée : " . $uploadErr;
        } else {
            if ($newPhoto) {
                $photo_url = $newPhoto;
            }
        try {
            if ($id > 0) {
                $upd = $db->prepare("UPDATE equipe_direction SET nom = ?, poste = ?, departement = ?, photo_url = ?, biographie = ?, ordre = ? WHERE id = ?");
                $upd->execute([$nom, $poste, $departement, $photo_url, $biographie, $ordre, $id]);
                $success = "Membre mis à jour avec succès !";
            } else {
                $ins = $db->prepare("INSERT INTO equipe_direction (nom, poste, departement, photo_url, biographie, ordre) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([$nom, $poste, $departement, $photo_url, $biographie, $ordre]);
                $id = (int)$db->lastInsertId();
                $success = "Nouveau membre ajouté avec succès !";
            }
            if ($newPhoto && !empty($membre['photo_url']) && $membre['photo_url'] !== $newPhoto) {
                delete_uploaded_file($membre['photo_url']);
            }
            $stmt = $db->prepare("SELECT * FROM equipe_direction WHERE id = ?");
            $stmt->execute([$id]);
            $membre = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
        }
    }
}
?>

<div style="max-width:700px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:800; font-family:'Outfit',sans-serif;">
            <?= $id > 0 ? "Modifier le Membre #$id" : "Ajouter un Membre de la Direction" ?>
        </h2>
        <a href="equipe.php" style="color:#64748b; text-decoration:none; font-size:0.9rem; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Retour
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
        <form method="POST" action="equipe-form.php<?= $id > 0 ? '?id='.$id : '' ?>" enctype="multipart/form-data">
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Nom complet & Titre (ex: Dr. Mamadou Diallo) *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($membre['nom']) ?>" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Poste / Fonction *</label>
                    <input type="text" name="poste" value="<?= htmlspecialchars($membre['poste']) ?>" placeholder="Ex: Directeur Général" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Département / Pôle</label>
                    <input type="text" name="departement" value="<?= htmlspecialchars($membre['departement']) ?>" placeholder="Ex: Direction Académique" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Photo de profil</label>
                <?php if (!empty($membre['photo_url'])): ?>
                    <div style="margin-bottom:0.6rem; display:flex; align-items:center; gap:0.75rem;">
                        <img src="<?= asset_url($membre['photo_url'], '', '../') ?>" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:2px solid #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b;">Photo actuelle</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="photo_file" accept="image/*" style="width:100%; padding:0.6rem; border-radius:8px; border:1.5px dashed #cbd5e1;">
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Ordre d'affichage (1, 2, 3...)</label>
                <input type="number" name="ordre" value="<?= htmlspecialchars($membre['ordre']) ?>" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
            </div>

            <button type="submit" class="btn-add" style="width:100%; justify-content:center; padding:1rem; font-size:1rem; border:none; cursor:pointer;">
                <i class="fas fa-save"></i> <?= $id > 0 ? "Enregistrer les modifications" : "Ajouter le membre" ?>
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
