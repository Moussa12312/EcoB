<?php
$pageTitle = "Formulaire de Publication";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
$event = [
    'titre' => '',
    'categorie' => 'Actualité',
    'description' => '',
    'contenu' => '',
    'type_media' => 'image',
    'media_url' => '',
    'video_embed' => '',
    'date_evenement' => date('Y-m-d'),
    'statut' => 'publie'
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $event = $found;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre          = clean_input($_POST['titre'] ?? '');
    $categorie      = clean_input($_POST['categorie'] ?? 'Actualité');
    $description    = clean_input($_POST['description'] ?? '');
    $contenu        = trim($_POST['contenu'] ?? '');
    $type_media     = clean_input($_POST['type_media'] ?? 'image');
    $video_embed    = clean_input($_POST['video_embed'] ?? '');
    $date_evenement = clean_input($_POST['date_evenement'] ?? date('Y-m-d'));
    $statut         = clean_input($_POST['statut'] ?? 'publie');
    $media_url      = $event['media_url'];

    $uploadErr = null;
    $newMedia = null;

    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (" . ini_get('post_max_size') . "). Aucune modification enregistrée : réduisez la taille du fichier.";
    } elseif (empty($titre) || empty($description)) {
        $error = "Veuillez remplir au moins le titre et la description.";
    } else {
        $newMedia = save_uploaded_image('image_file', 'event', $uploadErr);
        if ($uploadErr) {
            $error = "Photo non enregistrée : " . $uploadErr;
        } else {
            if ($newMedia) {
                $media_url = $newMedia;
            }
            try {
                if ($id > 0) {
                    $upd = $db->prepare("UPDATE evenements SET titre = ?, categorie = ?, description = ?, contenu = ?, type_media = ?, media_url = ?, video_embed = ?, date_evenement = ?, statut = ? WHERE id = ?");
                    $upd->execute([$titre, $categorie, $description, $contenu, $type_media, $media_url, $video_embed, $date_evenement, $statut, $id]);
                    $success = "Publication mise à jour avec succès !";
                } else {
                    $ins = $db->prepare("INSERT INTO evenements (titre, categorie, description, contenu, type_media, media_url, video_embed, date_evenement, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$titre, $categorie, $description, $contenu, $type_media, $media_url, $video_embed, $date_evenement, $statut]);
                    $id = (int)$db->lastInsertId();
                    $success = "Publication créée avec succès !";
                }

                if ($newMedia && !empty($event['media_url']) && $event['media_url'] !== $newMedia) {
                    delete_uploaded_file($event['media_url']);
                }

                $stmt = $db->prepare("SELECT * FROM evenements WHERE id = ?");
                $stmt->execute([$id]);
                $event = $stmt->fetch();
            } catch (Exception $e) {
                $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width:800px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:800; font-family:'Outfit',sans-serif;">
            <?= $id > 0 ? "Modifier la Publication #$id" : "Créer une Nouvelle Publication" ?>
        </h2>
        <a href="evenements.php" style="color:#64748b; text-decoration:none; font-size:0.9rem; font-weight:600;">
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
        <form method="POST" action="evenement-form.php<?= $id > 0 ? '?id='.$id : '' ?>" enctype="multipart/form-data">
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Titre de la Publication *</label>
                <input type="text" name="titre" value="<?= e($event['titre']) ?>" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; outline:none;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Catégorie *</label>
                    <select name="categorie" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                        <option value="Actualité" <?= $event['categorie'] === 'Actualité' ? 'selected' : '' ?>>Actualité</option>
                        <option value="Événement" <?= $event['categorie'] === 'Événement' ? 'selected' : '' ?>>Événement</option>
                        <option value="Formation" <?= $event['categorie'] === 'Formation' ? 'selected' : '' ?>>Formation</option>
                        <option value="Conférence" <?= $event['categorie'] === 'Conférence' ? 'selected' : '' ?>>Conférence</option>
                        <option value="Activités" <?= $event['categorie'] === 'Activités' ? 'selected' : '' ?>>Activités Étudiantes</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Date de l'événement *</label>
                    <input type="date" name="date_evenement" value="<?= e($event['date_evenement'] ?? date('Y-m-d')) ?>" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Type de Média</label>
                    <select name="type_media" id="type_media" onchange="toggleMediaType()" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                        <option value="image" <?= $event['type_media'] === 'image' ? 'selected' : '' ?>>Image / Photo</option>
                        <option value="video" <?= $event['type_media'] === 'video' ? 'selected' : '' ?>>Vidéo (YouTube, embed)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Statut de publication</label>
                    <select name="statut" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                        <option value="publie" <?= $event['statut'] === 'publie' ? 'selected' : '' ?>>Publié sur le site</option>
                        <option value="brouillon" <?= $event['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon (Non visible)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Photo d'illustration</label>
                <?php if (!empty($event['media_url'])): ?>
                    <div style="margin-bottom:0.6rem; display:flex; align-items:center; gap:0.75rem;">
                        <img src="<?= asset_url($event['media_url'], 'assets/images/img2.jpg', '../') ?>" alt="" style="width:80px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b;">Image actuelle : <?= e($event['media_url']) ?></span>
                    </div>
                <?php endif; ?>
                <input type="file" name="image_file" accept="image/*" style="width:100%; padding:0.6rem; border-radius:8px; border:1.5px dashed #cbd5e1;">
                <small style="color:#64748b;">Laissez vide si vous souhaitez conserver l'image existante.</small>
            </div>

            <div id="video_group" style="margin-bottom:1.25rem; display:<?= $event['type_media'] === 'video' ? 'block' : 'none' ?>;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Lien Embed Vidéo (YouTube / Vimeo)</label>
                <input type="text" name="video_embed" value="<?= e($event['video_embed'] ?? '') ?>" placeholder="https://www.youtube.com/embed/..." style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;">
                <small style="color:#64748b;">Exemple : https://www.youtube.com/embed/votre_code_video</small>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Description courte (Résumé pour la carte) *</label>
                <textarea name="description" required style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; min-height:80px;"><?= e($event['description']) ?></textarea>
            </div>

            <div style="margin-bottom:1.75rem;">
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.9rem;">Contenu détaillé (Texte complet de l'article)</label>
                <textarea name="contenu" style="width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem; min-height:140px;"><?= e($event['contenu'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-add" style="width:100%; justify-content:center; padding:1rem; font-size:1rem; border:none; cursor:pointer;">
                <i class="fas fa-save"></i> <?= $id > 0 ? "Enregistrer les modifications" : "Publier maintenant" ?>
            </button>
        </form>
    </div>
</div>

<script>
function toggleMediaType() {
    var val = document.getElementById('type_media').value;
    var group = document.getElementById('video_group');
    if (group) {
        group.style.display = (val === 'video') ? 'block' : 'none';
    }
}
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
