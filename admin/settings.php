<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_admin_permission('settings');

$pageTitle = "Configuration & Modification des Textes & Images";
require_once __DIR__ . '/layout-header.php';

$db = get_db();
$success = '';
$error = '';
$warnings = [];

// Images gérables : clé du paramètre => [champ fichier, libellé, image par défaut, préfixe fichier, SVG autorisé]
$imageFields = [
    'site_logo'          => ['file_site_logo',     "Logo de l'université (en-tête, pied de page, administration)", 'assets/images/logo.png', 'logo', true],
    'slider_img1'        => ['slider_file_1',      "Image du Slide 1", 'assets/images/img2.jpg', 'slider1', false],
    'slider_img2'        => ['slider_file_2',      "Image du Slide 2", 'assets/images/img1.jpg', 'slider2', false],
    'slider_img3'        => ['slider_file_3',      "Image du Slide 3", 'assets/images/img3.jpg', 'slider3', false],
    'cycle_dut_img'      => ['file_cycle_dut',     "Photo du cycle DUT (accueil)", 'assets/images/img2.jpg', 'cycle_dut', false],
    'cycle_licence_img'  => ['file_cycle_licence', "Photo du cycle Licence (accueil)", 'assets/images/img1.jpg', 'cycle_licence', false],
    'cycle_master_img'   => ['file_cycle_master',  "Photo du cycle Master (accueil)", 'assets/images/img4.jpg', 'cycle_master', false],
    'pole_gestion_img'   => ['file_pole_gestion',  "Image du pôle Gestion & Management", 'assets/images/img4.jpg', 'pole_gestion', false],
    'pole_agro_img'      => ['file_pole_agro',     "Image du pôle Agro-industrie", 'assets/images/img1.jpg', 'pole_agro', false],
    'pole_mine_img'      => ['file_pole_mine',     "Image du pôle Mines & Géologie", 'assets/images/img3.jpg', 'pole_mine', false],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (upload_post_too_large()) {
        $error = "Le formulaire dépasse la taille maximale acceptée par le serveur (post_max_size = " . ini_get('post_max_size') . "). Rien n'a été enregistré : envoyez moins de photos à la fois ou réduisez leur taille.";
    } else {
        try {
            $fields = [
                'site_nom', 'site_slogan', 'site_email', 'site_phone1', 'site_phone2',
                'site_adresse', 'site_anneescolaire', 'email_directeur',
                'facebook_url', 'instagram_url', 'whatsapp_num', 'linkedin_url',
                'slider_titre1', 'slider_desc1',
                'slider_titre2', 'slider_desc2',
                'slider_titre3', 'slider_desc3'
            ];
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    save_setting($field, clean_input($_POST[$field]));
                }
            }

            $uploaded = 0;
            $current = [];
            foreach ($db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll() as $r) {
                $current[$r['setting_key']] = $r['setting_value'];
            }
            foreach ($imageFields as $key => [$fileKey, $label, $default, $prefix, $svg]) {
                $err = null;
                $path = save_uploaded_image($fileKey, $prefix, $err, $svg);
                if ($err) {
                    $warnings[] = "$label : $err";
                } elseif ($path) {
                    delete_uploaded_file($current[$key] ?? '');
                    save_setting($key, $path);
                    $uploaded++;
                }
            }

            $success = "Paramètres enregistrés" . ($uploaded ? " ($uploaded image" . ($uploaded > 1 ? 's' : '') . " mise" . ($uploaded > 1 ? 's' : '') . " à jour)" : '') . ". Les modifications sont immédiatement visibles sur le site public.";
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Recharger les paramètres (lecture fraîche après enregistrement)
$settings = [];
foreach ($db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll() as $r) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$inputStyle = 'width:100%; padding:0.8rem 1rem; border-radius:8px; border:1.5px solid #cbd5e1; font-size:0.95rem;';
$cardStyle  = 'background:#fff; border-radius:16px; padding:2rem; border:1px solid #e2e8f0; margin-bottom:1.75rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);';
$h3Style    = 'font-size:1.15rem; color:#09122c; margin-bottom:1.25rem; border-bottom:1px solid #f1f5f9; padding-bottom:0.6rem;';

/** Bloc d'upload d'image avec aperçu. */
function image_field(string $key, array $def, array $settings, string $label = ''): void {
    [$fileKey, $defLabel, $default, , $svg] = $def;
    $current = $settings[$key] ?? '';
    $src = asset_url($current, $default, '../');
    ?>
    <div style="display:flex; gap:1.25rem; align-items:center; flex-wrap:wrap; margin-bottom:1rem;">
        <img src="<?= $src ?>" alt="<?= e($label ?: $defLabel) ?>" style="width:140px; height:85px; object-fit:cover; border-radius:8px; border:2px solid #cbd5e1; background:#f8fafc;">
        <div style="flex:1; min-width:240px;">
            <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.3rem;"><?= e($label ?: $defLabel) ?></label>
            <input type="file" name="<?= e($fileKey) ?>" accept="<?= $svg ? 'image/*,.svg' : 'image/*' ?>" style="width:100%; font-size:0.85rem; padding:0.4rem; border:1.5px dashed #cbd5e1; border-radius:8px; background:#fff;">
            <small style="color:#64748b;">Image actuelle : <?= e($current ?: $default . ' (par défaut)') ?></small>
        </div>
    </div>
    <?php
}
?>

<div style="max-width:900px; margin:0 auto;">
    <div style="margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:800; font-family:'Outfit',sans-serif;">Modifier les Textes, Coordonnées & Images du Site</h2>
        <p style="color:#64748b; font-size:0.92rem;">
            Changez le logo, les photos du site, les numéros, les adresses et les textes sans toucher à aucune ligne de code. Formats acceptés : JPG, PNG, WEBP (jusqu'à 20 Mo, redimensionnées automatiquement).
        </p>
    </div>

    <?php if (!empty($success)): ?>
        <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.25rem; font-weight:600; display:flex; align-items:center; gap:0.75rem;">
            <i class="fas fa-check-circle" style="font-size:1.25rem;"></i>
            <div><?= e($success) ?></div>
        </div>
    <?php endif; ?>

    <?php foreach ($warnings as $w): ?>
        <div style="background:#fef3c7; color:#92400e; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1rem; font-weight:600; display:flex; align-items:center; gap:0.75rem;">
            <i class="fas fa-exclamation-triangle" style="font-size:1.25rem;"></i>
            <div><?= e($w) ?></div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($error)): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.25rem; font-weight:600; display:flex; align-items:center; gap:0.75rem;">
            <i class="fas fa-exclamation-circle" style="font-size:1.25rem;"></i>
            <div><?= e($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="settings.php" enctype="multipart/form-data">

        <!-- BLOC LOGO -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-certificate" style="color:#f59e0b;"></i> Logo de l'Université</h3>
            <?php image_field('site_logo', $imageFields['site_logo'], $settings); ?>
        </div>

        <!-- BLOC SLIDER -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-images" style="color:#f59e0b;"></i> Images et Textes du Grand Slider d'Accueil</h3>
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem;">
                    <strong style="color:#09122c; font-size:1rem; display:block; margin-bottom:0.75rem;">
                        <i class="fas fa-layer-group" style="color:#f59e0b;"></i> Slide <?= $i ?>
                    </strong>
                    <?php image_field("slider_img$i", $imageFields["slider_img$i"], $settings, "Remplacer l'image du Slide $i"); ?>
                    <div style="margin-bottom:0.75rem;">
                        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">Titre du Slide <?= $i ?></label>
                        <input type="text" name="slider_titre<?= $i ?>" value="<?= e($settings["slider_titre$i"] ?? '') ?>" style="<?= $inputStyle ?>">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">Description du Slide <?= $i ?></label>
                        <textarea name="slider_desc<?= $i ?>" style="<?= $inputStyle ?> min-height:60px;"><?= e($settings["slider_desc$i"] ?? '') ?></textarea>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- BLOC CYCLES & PÔLES -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-graduation-cap" style="color:#f59e0b;"></i> Photos des Cycles (page d'accueil) & des Pôles</h3>
            <?php foreach (['cycle_dut_img', 'cycle_licence_img', 'cycle_master_img', 'pole_gestion_img', 'pole_agro_img', 'pole_mine_img'] as $k): ?>
                <?php image_field($k, $imageFields[$k], $settings); ?>
            <?php endforeach; ?>
            <small style="color:#64748b;">Les photos des filières se modifient dans « Filières & Formations », celles de l'équipe dans « Équipe Dirigeante ».</small>
        </div>

        <!-- BLOC IDENTITÉ -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-university" style="color:#f59e0b;"></i> Informations Générales & Slogan</h3>
            <div class="grid-2" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Nom de l'Université</label>
                    <input type="text" name="site_nom" value="<?= e($settings['site_nom'] ?? 'ECOB University') ?>" style="<?= $inputStyle ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Année Académique Active</label>
                    <input type="text" name="site_anneescolaire" value="<?= e($settings['site_anneescolaire'] ?? '2025 - 2026') ?>" style="<?= $inputStyle ?>">
                </div>
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Slogan Officiel</label>
                <input type="text" name="site_slogan" value="<?= e($settings['site_slogan'] ?? "École de Business Bilingue - La Marche Vers L'Excellence") ?>" style="<?= $inputStyle ?>">
            </div>
        </div>

        <!-- BLOC COORDONNÉES -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-address-book" style="color:#0284c7;"></i> Téléphones, Email & Adresse</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Téléphone 1 (Standard / Accueil)</label>
                    <input type="text" name="site_phone1" value="<?= e($settings['site_phone1'] ?? '+223 67 66 66 37') ?>" style="<?= $inputStyle ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Téléphone 2 (Service Scolarité)</label>
                    <input type="text" name="site_phone2" value="<?= e($settings['site_phone2'] ?? '+223 77 66 66 37') ?>" style="<?= $inputStyle ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Adresse Email Publique</label>
                    <input type="email" name="site_email" value="<?= e($settings['site_email'] ?? 'ecob@gmail.com') ?>" style="<?= $inputStyle ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Numéro WhatsApp (sans espace ni +)</label>
                    <input type="text" name="whatsapp_num" value="<?= e($settings['whatsapp_num'] ?? '22367666637') ?>" placeholder="22367666637" style="<?= $inputStyle ?>">
                </div>
            </div>

            <div style="background:rgba(245,158,11,0.08); border:1.5px solid rgba(245,158,11,0.4); border-radius:10px; padding:1.2rem; margin-bottom:1rem;">
                <label style="display:block; font-weight:800; margin-bottom:0.35rem; font-size:0.92rem; color:#09122c;">
                    <i class="fas fa-bell" style="color:#d97706; margin-right:6px;"></i> Email de Notification du Directeur (préinscriptions, contacts, commentaires) *
                </label>
                <input type="email" name="email_directeur" value="<?= e($settings['email_directeur'] ?? 'chakirbal07@gmail.com') ?>" required style="<?= $inputStyle ?> background:#fff; font-weight:600; color:#09122c;">
                <small style="color:#64748b; font-size:0.8rem; display:block; margin-top:0.35rem;">
                    Toutes les nouvelles demandes sont envoyées à cette adresse. Pour que l'envoi fonctionne, configurez le compte d'envoi dans
                    <a href="emails.php" style="color:#d97706; font-weight:700;">Emails &amp; Notifications</a>.
                </small>
            </div>

            <div>
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Adresse Géographique du Campus</label>
                <input type="text" name="site_adresse" value="<?= e($settings['site_adresse'] ?? 'Sotuba ACI, Bamako - Mali') ?>" style="<?= $inputStyle ?>">
            </div>
        </div>

        <!-- BLOC RÉSEAUX -->
        <div style="<?= $cardStyle ?>">
            <h3 style="<?= $h3Style ?>"><i class="fas fa-share-alt" style="color:#10b981;"></i> Liens des Réseaux Sociaux</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Lien Page Facebook</label>
                    <input type="text" name="facebook_url" value="<?= e($settings['facebook_url'] ?? 'https://www.facebook.com/ecob.debamako') ?>" style="<?= $inputStyle ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Lien Profil Instagram</label>
                    <input type="text" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>" style="<?= $inputStyle ?>">
                </div>
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:0.4rem; font-size:0.88rem;">Lien Page LinkedIn</label>
                <input type="text" name="linkedin_url" value="<?= e($settings['linkedin_url'] ?? '') ?>" style="<?= $inputStyle ?>">
            </div>
        </div>

        <button type="submit" class="btn-add" style="width:100%; justify-content:center; padding:1.15rem; font-size:1.05rem; border:none; cursor:pointer;">
            <i class="fas fa-save"></i> Enregistrer Tous les Paramètres & Images
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
