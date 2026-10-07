<?php
$pageTitle = "Formulaire Partenaire";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
$partner = [
    'nom' => '',
    'logo_url' => '',
    'type_partenariat' => 'Stages & Insertion Professionnelle',
    'description' => '',
    'pays' => 'Bamako, Mali',
    'site_web' => '',
    'ordre' => 0,
    'statut' => 'actif'
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM partenaires WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $partner = $found;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom              = clean_input($_POST['nom'] ?? '');
    $type_partenariat = clean_input($_POST['type_partenariat'] ?? 'Stages & Insertion');
    $description      = clean_input($_POST['description'] ?? '');
    $pays             = clean_input($_POST['pays'] ?? 'Mali');
    $site_web         = clean_input($_POST['site_web'] ?? '');
    $ordre            = (int)($_POST['ordre'] ?? 0);
    $statut           = clean_input($_POST['statut'] ?? 'actif');
    $logo_url         = $partner['logo_url']; // Conserver existant par défaut

    $uploadErr = null;
    $newLogo = null;

    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (" . ini_get('post_max_size') . "). Aucune modification enregistrée : réduisez la taille du logo.";
    } elseif (empty($nom)) {
        $error = "Le nom de l'entreprise ou institution partenaire est obligatoire.";
    } else {
        $newLogo = save_uploaded_image('logo_file', 'partner', $uploadErr, true);
        if ($uploadErr) {
            $error = "Logo non enregistré : " . $uploadErr;
        } else {
            if ($newLogo) {
                $logo_url = $newLogo;
            }

            try {
                if ($id > 0) {
                    // Modification
                    $stmt = $db->prepare("UPDATE partenaires SET nom = ?, type_partenariat = ?, description = ?, pays = ?, site_web = ?, ordre = ?, statut = ?, logo_url = ? WHERE id = ?");
                    $stmt->execute([
                        $nom,
                        $type_partenariat,
                        $description,
                        $pays,
                        $site_web,
                        $ordre,
                        $statut,
                        $logo_url,
                        $id
                    ]);
                    $success = "Partenaire mis à jour avec succès !";

                    if ($newLogo && !empty($partner['logo_url']) && $partner['logo_url'] !== $newLogo) {
                        delete_uploaded_file($partner['logo_url']);
                    }

                    $partner = array_merge($partner, [
                        'nom' => $nom,
                        'type_partenariat' => $type_partenariat,
                        'description' => $description,
                        'pays' => $pays,
                        'site_web' => $site_web,
                        'ordre' => $ordre,
                        'statut' => $statut,
                        'logo_url' => $logo_url
                    ]);
                } else {
                    // Création
                    if (empty($logo_url)) {
                        $logo_url = 'assets/images/logoo1.jpg'; // Défaut
                    }
                    $stmt = $db->prepare("INSERT INTO partenaires (nom, type_partenariat, description, pays, site_web, ordre, statut, logo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $nom,
                        $type_partenariat,
                        $description,
                        $pays,
                        $site_web,
                        $ordre,
                        $statut,
                        $logo_url
                    ]);
                    $id = $db->lastInsertId();
                    $success = "Nouveau partenaire ajouté avec succès !";
                }
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.4rem; font-family:'Outfit',sans-serif; color:var(--primary);">
            <?= $id > 0 ? "Modifier le Partenaire : " . htmlspecialchars($partner['nom']) : "Ajouter un Nouveau Partenaire" ?>
        </h2>
        <p style="color:#64748b; font-size:0.9rem;">Gérez le logo, les détails de collaboration et l'affichage sur la page d'accueil.</p>
    </div>
    <a href="partenaires.php" class="btn-action" style="background:#e2e8f0; color:#334155;">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
</div>

<?php if (!empty($success)): ?>
    <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.6rem;">
        <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.6rem;">
        <i class="fas fa-exclamation-circle" style="font-size:1.2rem;"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div style="background:#fff; border-radius:16px; padding:2.25rem; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(0,0,0,0.06); max-width:850px;">
    <form method="POST" action="partenaire-form.php<?= $id > 0 ? '?id=' . $id : '' ?>" enctype="multipart/form-data">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Nom de l'Entreprise / Institution *
                </label>
                <input type="text" name="nom" required value="<?= htmlspecialchars($partner['nom']) ?>" placeholder="Ex: Barrick Gold Corporation" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none;">
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Domaine / Type de Partenariat *
                </label>
                <input type="text" name="type_partenariat" list="domainesList" required value="<?= htmlspecialchars($partner['type_partenariat']) ?>" placeholder="Ex: Mines & Géologie" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none;">
                <datalist id="domainesList">
                    <option value="Mines & Géologie">
                    <option value="Sciences de Gestion & Finance">
                    <option value="Agro-Business & Filières Vertes">
                    <option value="Stages & Insertion Professionnelle">
                    <option value="Mobilité & Coopération Internationale">
                    <option value="Technologie & Transformation Digitale">
                </datalist>
            </div>
        </div>

        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                Détails & Nature de la Collaboration (Visible par les visiteurs) *
            </label>
            <textarea name="description" rows="4" placeholder="Décrivez les apports du partenariat : accueil en stage, visites de terrain, bourses d'excellence, certifications professionnelles..." style="width:100%; padding:0.85rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none; font-family:inherit; line-height:1.6;"><?= htmlspecialchars($partner['description']) ?></textarea>
            <small style="color:#64748b;">Ce texte explique concrètement ce que ce partenariat apporte aux étudiants d'ECOB.</small>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Ville / Pays de Localisation
                </label>
                <input type="text" name="pays" value="<?= htmlspecialchars($partner['pays']) ?>" placeholder="Ex: Bamako, Mali ou International" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none;">
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Site Web Officiel (URL)
                </label>
                <input type="url" name="site_web" value="<?= htmlspecialchars($partner['site_web']) ?>" placeholder="https://www.exemple.com" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none;">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Ordre d'Affichage (Position)
                </label>
                <input type="number" name="ordre" value="<?= htmlspecialchars($partner['ordre']) ?>" min="0" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none;">
                <small style="color:#64748b;">Plus le chiffre est petit, plus le partenaire s'affiche en premier.</small>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:0.88rem; margin-bottom:0.4rem; color:#1e293b;">
                    Statut de Publication
                </label>
                <select name="statut" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.92rem; outline:none; background:#fff;">
                    <option value="actif" <?= $partner['statut'] === 'actif' ? 'selected' : '' ?>>Actif (Visible sur le site)</option>
                    <option value="inactif" <?= $partner['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif (Masqué)</option>
                </select>
            </div>
        </div>

        <!-- Section Logo Agrandie et Aperçu -->
        <div style="background:#f8fafc; padding:1.5rem; border-radius:12px; border:1.5px dashed #cbd5e1; margin-bottom:2.25rem;">
            <label style="display:block; font-weight:700; font-size:0.92rem; margin-bottom:0.5rem; color:#1e293b;">
                Logo Haute Résolution du Partenaire
            </label>
            <p style="color:#64748b; font-size:0.85rem; margin-bottom:1rem;">
                Le logo s'affichera agrandi sur fond blanc dans la galerie des partenaires. Format PNG avec fond transparent ou JPG propre recommandé.
            </p>

            <div style="display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
                <?php if (!empty($partner['logo_url'])): ?>
                    <div style="text-align:center;">
                        <span style="display:block; font-size:0.75rem; color:#64748b; margin-bottom:0.3rem;">Logo Actuel :</span>
                        <div style="background:#fff; padding:12px 20px; border-radius:12px; border:1px solid #e2e8f0; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.05); min-width:140px; height:85px;">
                            <img src="<?= asset_url($partner['logo_url'], '', '../') ?>" alt="Aperçu logo" style="max-height:65px; max-width:140px; object-fit:contain;">
                        </div>
                    </div>
                <?php endif; ?>

                <div style="flex-grow:1;">
                    <input type="file" name="logo_file" accept="image/*" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.88rem; background:#fff;">
                    <small style="color:#64748b; display:block; margin-top:0.35rem;">Laisser vide pour conserver le logo existant.</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-add" style="padding:0.95rem 2rem; font-size:1rem; border:none; cursor:pointer;">
            <i class="fas fa-save"></i> <?= $id > 0 ? "Enregistrer les modifications" : "Créer le partenaire" ?>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
