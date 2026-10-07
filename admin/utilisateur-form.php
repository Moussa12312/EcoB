<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin_permission('utilisateurs');

$db = get_db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;

if ($id > 0) {
    $st = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $st->execute([$id]);
    $user = $st->fetch();
    if (!$user) {
        $_SESSION['admin_flash_error'] = "Utilisateur introuvable.";
        header("Location: utilisateurs.php");
        exit;
    }
}

$pageTitle = $id > 0 ? "Modifier l'utilisateur : " . htmlspecialchars($user['nom_complet']) : "Créer un nouveau Marketeur / Utilisateur";

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $nomComplet = clean_input($_POST['nom_complet'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $telephone = clean_input($_POST['telephone'] ?? '');
    $role = clean_input($_POST['role'] ?? 'marketeur');
    $statut = clean_input($_POST['statut'] ?? 'actif');
    $password = $_POST['password'] ?? '';

    // Permissions
    $permsPosted = $_POST['perms'] ?? [];
    if ($role === 'superadmin' || $role === 'admin') {
        $permissionsJson = 'all';
    } else {
        $permissionsJson = json_encode(array_values($permsPosted));
    }

    if (empty($username) || empty($nomComplet)) {
        $error = "Le nom d'utilisateur et le nom complet sont obligatoires.";
    } elseif ($id === 0 && empty($password)) {
        $error = "Veuillez définir un mot de passe initial pour ce nouvel utilisateur.";
    } else {
        try {
            // Vérifier unicité username
            $stU = $db->prepare("SELECT id FROM admin_users WHERE username = ? AND id <> ?");
            $stU->execute([$username, $id]);
            if ($stU->fetch()) {
                $error = "Ce nom d'utilisateur est déjà utilisé par un autre compte.";
            } else {
                if ($id > 0) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $up = $db->prepare("UPDATE admin_users SET username = ?, nom_complet = ?, email = ?, telephone = ?, role = ?, permissions = ?, statut = ?, password = ? WHERE id = ?");
                        $up->execute([$username, $nomComplet, $email, $telephone, $role, $permissionsJson, $statut, $hash, $id]);
                    } else {
                        $up = $db->prepare("UPDATE admin_users SET username = ?, nom_complet = ?, email = ?, telephone = ?, role = ?, permissions = ?, statut = ? WHERE id = ?");
                        $up->execute([$username, $nomComplet, $email, $telephone, $role, $permissionsJson, $statut, $id]);
                    }
                    $_SESSION['admin_flash_success'] = "Utilisateur « $nomComplet » mis à jour avec succès.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $db->prepare("INSERT INTO admin_users (username, password, nom_complet, email, telephone, role, permissions, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$username, $hash, $nomComplet, $email, $telephone, $role, $permissionsJson, $statut]);
                    $_SESSION['admin_flash_success'] = "Nouveau compte « $nomComplet » ($role) créé avec succès.";
                }
                header("Location: utilisateurs.php");
                exit;
            }
        } catch (Exception $e) {
            $error = "Erreur SQL : " . $e->getMessage();
        }
    }
}

// Permissions actuelles
$currentPerms = ['events', 'partenaires', 'pages', 'filieres', 'settings', 'equipe'];
if ($user) {
    $rawP = $user['permissions'] ?? '';
    if ($rawP === 'all') {
        $currentPerms = ['events', 'partenaires', 'pages', 'filieres', 'settings', 'equipe', 'contacts', 'admissions'];
    } elseif (!empty($rawP)) {
        $dec = json_decode($rawP, true);
        if (is_array($dec)) {
            $currentPerms = $dec;
        }
    }
}

require_once __DIR__ . '/layout-header.php';
?>

<div style="max-width:850px; margin:0 auto;">
    <div style="margin-bottom:1.5rem;">
        <a href="utilisateurs.php" style="color:#64748b; text-decoration:none; font-size:0.9rem; font-weight:600; display:inline-flex; align-items:center; gap:0.4rem;">
            <i class="fas fa-arrow-left"></i> Retour à la liste des utilisateurs
        </a>
    </div>

    <?php if ($error): ?>
        <div style="background:#fef2f2; color:#991b1b; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600; border:1px solid #fecaca;">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:18px; padding:2.5rem; border:1px solid #e2e8f0; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
        <form action="" method="POST">
            <h3 style="font-size:1.3rem; color:#09122c; margin-bottom:1.5rem; padding-bottom:0.75rem; border-bottom:1px solid #f1f5f9; font-weight:800;">
                <i class="fas fa-id-card" style="color:#f59e0b;"></i> Informations du Compte
            </h3>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Nom Complet <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="nom_complet" value="<?= htmlspecialchars($_POST['nom_complet'] ?? $user['nom_complet'] ?? '') ?>" required placeholder="Ex: Moussa Coulibaly" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Identifiant de connexion (Username) <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? $user['username'] ?? '') ?>" required placeholder="Ex: moussa_marketing" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Email Professionnel
                    </label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>" placeholder="Ex: contact@ecob.edu.ml" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Numéro Téléphone / WhatsApp
                    </label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? $user['telephone'] ?? '') ?>" placeholder="Ex: +223 70 00 00 00" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Rôle du Compte <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="role" id="roleSelect" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem; background:#fff; font-weight:600;">
                        <?php $currR = $_POST['role'] ?? $user['role'] ?? 'marketeur'; ?>
                        <option value="marketeur" <?= $currR === 'marketeur' ? 'selected' : '' ?>>🎯 Marketeur / Chargé de Communication</option>
                        <option value="admin" <?= $currR === 'admin' ? 'selected' : '' ?>>🛡️ Administrateur</option>
                        <option value="superadmin" <?= $currR === 'superadmin' ? 'selected' : '' ?>>👑 Super Administrateur (Direction)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                        Statut du Compte
                    </label>
                    <select name="statut" style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem; background:#fff;">
                        <?php $currS = $_POST['statut'] ?? $user['statut'] ?? 'actif'; ?>
                        <option value="actif" <?= $currS === 'actif' ? 'selected' : '' ?>>✅ Compte Actif</option>
                        <option value="inactif" <?= $currS === 'inactif' ? 'selected' : '' ?>>⛔ Compte Suspendu / Inactif</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:2rem;">
                <label style="display:block; font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.4rem;">
                    Mot de passe <?= $id > 0 ? '<span style="font-weight:normal; color:#64748b;">(laisser vide pour ne pas modifier)</span>' : '<span style="color:#ef4444;">*</span>' ?>
                </label>
                <input type="password" name="password" placeholder="<?= $id > 0 ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe sécurisé' ?>" <?= $id === 0 ? 'required' : '' ?> style="width:100%; padding:0.75rem 1rem; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem;">
            </div>

            <!-- BLOC PERMISSIONS & RESTRICTIONS PROFESSIONNELLES -->
            <div id="permissionsBlock" style="background:#f8fafc; border-radius:14px; padding:1.5rem; border:1px solid #e2e8f0; margin-bottom:2rem;">
                <h4 style="font-size:1.05rem; color:#09122c; font-weight:800; margin-bottom:0.4rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-user-lock" style="color:#0ea5e9;"></i> Permissions & Restrictions Professionnelles
                </h4>
                <p style="font-size:0.85rem; color:#64748b; margin-bottom:1.25rem;">
                    Définissez précisément les modules accessibles par ce collaborateur marketing :
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.9rem;">
                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="events" <?= in_array('events', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-calendar-alt" style="color:#f59e0b;"></i> Événements & Actualités</div>
                            <div style="font-size:0.78rem; color:#64748b;">Création, modification des photos d'activités</div>
                        </div>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="settings" <?= in_array('settings', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-sliders-h" style="color:#0ea5e9;"></i> Slider & Médias Visuels</div>
                            <div style="font-size:0.78rem; color:#64748b;">Mise à jour des images d'accueil & textes d'accroche</div>
                        </div>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="partenaires" <?= in_array('partenaires', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-handshake" style="color:#10b981;"></i> Logos Partenaires</div>
                            <div style="font-size:0.78rem; color:#64748b;">Ajout et mise en avant des entreprises partenaires</div>
                        </div>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="pages" <?= in_array('pages', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-file-alt" style="color:#6366f1;"></i> Contenu des Pages</div>
                            <div style="font-size:0.78rem; color:#64748b;">Édition des présentations et textes institutionnels</div>
                        </div>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="filieres" <?= in_array('filieres', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-graduation-cap" style="color:#8b5cf6;"></i> Filières & Formations</div>
                            <div style="font-size:0.78rem; color:#64748b;">Descriptions des filières de formation</div>
                        </div>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                        <input type="checkbox" name="perms[]" value="equipe" <?= in_array('equipe', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#0f172a;"><i class="fas fa-users" style="color:#ec4899;"></i> Équipe Dirigeante</div>
                            <div style="font-size:0.78rem; color:#64748b;">Photos et biographies des dirigeants</div>
                        </div>
                    </label>
                </div>

                <!-- SECTIONS CONFIDENTIELLES -->
                <div style="margin-top:1.25rem; padding-top:1rem; border-top:1px dashed #cbd5e1;">
                    <div style="font-size:0.8rem; font-weight:800; color:#b91c1c; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                        <i class="fas fa-user-shield"></i> Données Confidentielles (Restreintes par défaut)
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.9rem;">
                        <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #fecaca; cursor:pointer;">
                            <input type="checkbox" name="perms[]" value="contacts" <?= in_array('contacts', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                            <div>
                                <div style="font-weight:700; font-size:0.88rem; color:#991b1b;"><i class="fas fa-envelope"></i> Consulter les Messages Reçus</div>
                                <div style="font-size:0.78rem; color:#64748b;">Messages de contact des visiteurs et parents</div>
                            </div>
                        </label>

                        <label style="display:flex; align-items:flex-start; gap:0.6rem; background:#fff; padding:0.85rem; border-radius:8px; border:1px solid #fecaca; cursor:pointer;">
                            <input type="checkbox" name="perms[]" value="admissions" <?= in_array('admissions', $currentPerms) ? 'checked' : '' ?> style="margin-top:3px;">
                            <div>
                                <div style="font-weight:700; font-size:0.88rem; color:#991b1b;"><i class="fas fa-user-graduate"></i> Consulter les Préinscriptions</div>
                                <div style="font-size:0.78rem; color:#64748b;">Dossiers personnels et coordonnées d'étudiants</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:1rem; align-items:center;">
                <button type="submit" class="btn-add" style="background:#09122c; border:none; padding:0.85rem 1.8rem; font-size:1rem; cursor:pointer;">
                    <i class="fas fa-save"></i> <?= $id > 0 ? "Enregistrer les modifications" : "Créer le compte" ?>
                </button>
                <a href="utilisateurs.php" style="color:#64748b; text-decoration:none; font-weight:600; font-size:0.95rem;">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const roleSelect = document.getElementById('roleSelect');
const permsBlock = document.getElementById('permissionsBlock');

function updatePermsVisibility() {
    if (roleSelect.value === 'superadmin' || roleSelect.value === 'admin') {
        permsBlock.style.opacity = '0.5';
        permsBlock.style.pointerEvents = 'none';
    } else {
        permsBlock.style.opacity = '1';
        permsBlock.style.pointerEvents = 'auto';
    }
}

roleSelect.addEventListener('change', updatePermsVisibility);
updatePermsVisibility();
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
