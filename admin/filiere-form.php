<?php
$pageTitle = "Formulaire de Filière";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
$filiere = [
    'departement' => 'gestion',
    'nom' => '',
    'diplome' => 'DUT & Licence',
    'duree' => '2 à 3 ans',
    'icon' => '🏛️',
    'description' => '',
    'debouches' => '',
    'image_url' => 'assets/images/img2.jpg',
    'ordre' => 0,
    'statut' => 'actif'
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM filieres WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $filiere = $found;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departement = clean_input($_POST['departement'] ?? 'gestion');
    $nom         = clean_input($_POST['nom'] ?? '');
    $diplome     = clean_input($_POST['diplome'] ?? 'DUT & Licence');
    $duree       = clean_input($_POST['duree'] ?? '2 à 3 ans');
    $icon        = clean_input($_POST['icon'] ?? '🏛️');
    $description = clean_input($_POST['description'] ?? '');
    $debouches   = clean_input($_POST['debouches'] ?? '');
    $ordre       = (int)($_POST['ordre'] ?? 0);
    $statut      = clean_input($_POST['statut'] ?? 'actif');
    $image_url   = $filiere['image_url'];

    $uploadErr = null;
    $newImg = null;

    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (" . ini_get('post_max_size') . "). Aucune modification enregistrée : réduisez la taille de l'image.";
    } elseif (empty($nom) || empty($description)) {
        $error = "Veuillez renseigner au moins le nom et la description de la filière.";
    } else {
        $newImg = save_uploaded_image('image_file', 'filiere', $uploadErr);
        if ($uploadErr) {
            $error = "Image non enregistrée : " . $uploadErr;
        } else {
            if ($newImg) {
                $image_url = $newImg;
            }
            try {
                if ($id > 0) {
                    $upd = $db->prepare("UPDATE filieres SET departement = ?, nom = ?, diplome = ?, duree = ?, icon = ?, description = ?, debouches = ?, image_url = ?, ordre = ?, statut = ? WHERE id = ?");
                    $upd->execute([$departement, $nom, $diplome, $duree, $icon, $description, $debouches, $image_url, $ordre, $statut, $id]);
                    $success = "Filière mise à jour avec succès !";
                } else {
                    $ins = $db->prepare("INSERT INTO filieres (departement, nom, diplome, duree, icon, description, debouches, image_url, ordre, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$departement, $nom, $diplome, $duree, $icon, $description, $debouches, $image_url, $ordre, $statut]);
                    $id = (int)$db->lastInsertId();
                    $success = "Filière ajoutée avec succès !";
                }

                if ($newImg && !empty($filiere['image_url']) && $filiere['image_url'] !== $newImg) {
                    delete_uploaded_file($filiere['image_url']);
                }

                $stmt = $db->prepare("SELECT * FROM filieres WHERE id = ?");
                $stmt->execute([$id]);
                $filiere = $stmt->fetch();
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width:800px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:800; font-family:'Outfit',sans-serif;">
            <?= $id > 0 ? "Modifier la Filière #$id" : "Créer une Nouvelle Filière" ?>
        </h2>
        <a href="filieres.php" style="color:#64748b; text-decoration:none; font-size:0.9rem; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div style="background:#dcfce7; color:#15803d; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <i class="fas fa-check-circle"></i> <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:0.9rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:16px; padding:2rem; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <form method="POST" action="filiere-form.php<?= $id > 0 ? '?id='.$id : '' ?>" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Département *</label>
                    <select name="departement" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                        <option value="gestion" <?= $filiere['departement'] === 'gestion' ? 'selected' : '' ?>>Sciences de Gestion</option>
                        <option value="agro" <?= $filiere['departement'] === 'agro' ? 'selected' : '' ?>>Agro-Business</option>
                        <option value="mine" <?= $filiere['departement'] === 'mine' ? 'selected' : '' ?>>Mines & Géologie</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Diplômes préparés *</label>
                    <input type="text" name="diplome" value="<?= e($filiere['diplome']) ?>" placeholder="Ex: DUT & Licence" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Nom de la Filière *</label>
                <input type="text" name="nom" value="<?= e($filiere['nom']) ?>" required placeholder="Ex: Finance, Banque & Assurance" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Durée des Études</label>
                    <input type="text" name="duree" value="<?= e($filiere['duree']) ?>" placeholder="Ex: 2 à 3 ans" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Icône / Émoji</label>
                    <input type="text" name="icon" value="<?= e($filiere['icon']) ?>" placeholder="Ex: 📊 ou 🏛️" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Statut</label>
                    <select name="statut" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                        <option value="actif" <?= ($filiere['statut'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif (Visible)</option>
                        <option value="inactif" <?= ($filiere['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif (Masqué)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Photo de la Filière</label>
                <?php if (!empty($filiere['image_url'])): ?>
                    <div style="margin-bottom:0.6rem; display:flex; align-items:center; gap:0.75rem;">
                        <img src="<?= asset_url($filiere['image_url'], 'assets/images/img2.jpg', '../') ?>" alt="" style="width:80px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b;">Image actuelle : <?= e($filiere['image_url']) ?></span>
                    </div>
                <?php endif; ?>
                <input type="file" name="image_file" accept="image/*" style="width:100%; padding:0.6rem; border-radius:8px; border:1.5px dashed #cbd5e1;">
                <small style="color:#64748b;">Laissez vide pour conserver l'image actuelle.</small>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Description complète *</label>
                <textarea name="description" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; min-height:90px;"><?= e($filiere['description']) ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns:3fr 1fr; gap:1.25rem; margin-bottom:1.75rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Débouchés professionnels (Métiers visés)</label>
                    <textarea name="debouches" placeholder="Ex: Analyste financier, Chef comptable, Auditeur..." style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; min-height:75px;"><?= e($filiere['debouches'] ?? '') ?></textarea>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Ordre d'affichage</label>
                    <input type="number" name="ordre" value="<?= (int)($filiere['ordre'] ?? 0) ?>" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <button type="submit" class="btn-add" style="width:100%; justify-content:center; padding:1rem; font-size:1rem; border:none; cursor:pointer;">
                <i class="fas fa-save"></i> <?= $id > 0 ? "Enregistrer les modifications" : "Créer la filière" ?>
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
