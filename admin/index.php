<?php
$pageTitle = "Tableau de Bord";
require_once __DIR__ . '/layout-header.php';

$db = get_db();

// Comptages réels
$cntAdmis = $db->query("SELECT COUNT(*) FROM admis")->fetchColumn();
$cntEvenements = $db->query("SELECT COUNT(*) FROM evenements")->fetchColumn();
$cntContact = $db->query("SELECT COUNT(*) FROM contact")->fetchColumn();
$cntMessage = $db->query("SELECT COUNT(*) FROM message")->fetchColumn();
$cntFilieres = $db->query("SELECT COUNT(*) FROM filieres")->fetchColumn();
$cntEquipe = $db->query("SELECT COUNT(*) FROM equipe_direction")->fetchColumn();
$cntPartenaires = $db->query("SELECT COUNT(*) FROM partenaires")->fetchColumn();

// Dernières préinscriptions
$recentAdmis = $db->query("SELECT * FROM admis ORDER BY id DESC LIMIT 5")->fetchAll();

// Derniers messages
$recentContacts = $db->query("SELECT * FROM contact ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- STATS CARDS -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
    <?php if (admin_has_permission('admissions')): ?>
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntAdmis ?></div>
            <div class="stat-label">Préinscriptions</div>
        </div>
        <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>
    <?php else: ?>
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntPartenaires ?></div>
            <div class="stat-label">Partenaires Actifs</div>
        </div>
        <div class="stat-icon" style="background:#f1f5f9; color:#475569;">
            <i class="fas fa-handshake"></i>
        </div>
    </div>
    <?php endif; ?>

    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntFilieres ?></div>
            <div class="stat-label">Filières Actives</div>
        </div>
        <div class="stat-icon" style="background:#fef3c7; color:#d97706;">
            <i class="fas fa-graduation-cap"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntEvenements ?></div>
            <div class="stat-label">Actualités & Vidéos</div>
        </div>
        <div class="stat-icon" style="background:#f3e8ff; color:#9333ea;">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>

    <?php if (admin_has_permission('contacts')): ?>
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntContact + $cntMessage ?></div>
            <div class="stat-label">Messages & Avis</div>
        </div>
        <div class="stat-icon" style="background:#dcfce7; color:#16a34a;">
            <i class="fas fa-envelope-open-text"></i>
        </div>
    </div>
    <?php else: ?>
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $cntEquipe ?></div>
            <div class="stat-label">Équipe & Staff</div>
        </div>
        <div class="stat-icon" style="background:#dcfce7; color:#16a34a;">
            <i class="fas fa-users-cog"></i>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODULES DE GESTION INTÉGRALE DU SITE -->
<div style="margin-bottom:2.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <h2 style="font-size:1.3rem; font-weight:800; font-family:'Outfit',sans-serif; color:var(--primary);">
                <i class="fas fa-th-large" style="color:var(--accent); margin-right:8px;"></i> Gestion Complète du Site Web
            </h2>
            <p style="color:#64748b; font-size:0.88rem;">Tous les contenus, textes, images, programmes et dirigeants sont modifiables sans aucun code :</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
        <!-- Slider & Bannières -->
        <a href="settings.php#slider" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(245,158,11,0.15); color:var(--accent-hover); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-images"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Slider & Bannières</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Changer les 3 photos, titres et boutons de la page d'accueil</p>
            </div>
        </a>

        <!-- Filières & Formations -->
        <a href="filieres.php" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Formations (<?= $cntFilieres ?>)</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Ajouter ou modifier les filières Gestion, Agro et Mines</p>
            </div>
        </a>

        <!-- Pages Institutionnelles -->
        <a href="pages.php" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Pages du Site</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Modifier Présentation, À Propos, Organisation et Règlement</p>
            </div>
        </a>

        <!-- Équipe Dirigeante -->
        <a href="equipe.php" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:#fef3c7; color:#b45309; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-users-cog"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Équipe Dirigeante (<?= $cntEquipe ?>)</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Gérer les photos, postes et bios des directeurs et fondateurs</p>
            </div>
        </a>

        <!-- Partenaires Professionnels -->
        <a href="partenaires.php" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:#f1f5f9; color:#475569; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Partenaires & Conventions (<?= $cntPartenaires ?>)</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Gérer les logos, domaines et détails de collaboration</p>
            </div>
        </a>

        <!-- Coordonnées & Réseaux Sociaux -->
        <a href="settings.php" style="background:#fff; border-radius:14px; padding:1.25rem; border:1px solid #e2e8f0; text-decoration:none; color:inherit; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s; display:flex; gap:1rem; align-items:center;">
            <div style="width:48px; height:48px; border-radius:12px; background:#f3e8ff; color:#9333ea; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:0.2rem;">Contacts & Réseaux</h4>
                <p style="font-size:0.8rem; color:#64748b; margin:0;">Modifier téléphones, WhatsApp, emails et adresse physique</p>
            </div>
        </a>
    </div>
</div>

<?php if (admin_has_permission('admissions')): ?>
<!-- ACTIONS RAPIDES & DERNIÈRES ADMISSIONS -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <h2 style="font-size:1.3rem; font-weight:800; font-family:'Outfit',sans-serif;">Dernières Préinscriptions d'Étudiants</h2>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a href="evenement-form.php" class="btn-add">
            <i class="fas fa-plus-circle"></i> Publier une actualité / vidéo
        </a>
        <a href="admissions.php" class="btn-action btn-edit" style="padding:0.65rem 1rem;">
            Voir toutes les admissions (<?= $cntAdmis ?>)
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Candidat</th>
                <th>Contact</th>
                <th>Filière Souhaitée</th>
                <th>Niveau</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recentAdmis)): ?>
                <?php foreach ($recentAdmis as $a): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($a['nationalite'] ?? 'Malienne') ?></div>
                        </td>
                        <td>
                            <div><i class="fas fa-phone" style="font-size:0.75rem; color:#64748b;"></i> <?= htmlspecialchars($a['phone']) ?></div>
                            <div style="font-size:0.8rem; color:#64748b;"><i class="fas fa-envelope" style="font-size:0.75rem;"></i> <?= htmlspecialchars($a['email']) ?></div>
                        </td>
                        <td>
                            <span style="font-weight:600; color:var(--primary);"><?= htmlspecialchars($a['formation']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($a['niveau_etude']) ?></td>
                        <td>
                            <span class="badge badge-pending"><?= htmlspecialchars($a['statut'] ?? 'En attente') ?></span>
                        </td>
                        <td>
                            <a href="admissions.php" class="btn-action btn-edit">Gérer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucune préinscription enregistrée pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<!-- TABLEAU DERNIÈRES ACTUALITÉS & MÉDIAS (ESPACE MARKETING) -->
<?php
$recentEvts = $db->query("SELECT * FROM evenements ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 style="font-size:1.3rem; font-weight:800; font-family:'Outfit',sans-serif;">Dernières Actualités & Médias Publiés</h2>
        <p style="font-size:0.85rem; color:#64748b; margin:0;">Section dédiée au suivi et à la mise à jour des contenus du campus</p>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="evenement-form.php" class="btn-add">
            <i class="fas fa-plus-circle"></i> Ajouter un nouvel événement / média
        </a>
    </div>
</div>

<div class="table-card" style="margin-top:0;">
    <table>
        <thead>
            <tr>
                <th>Titre de l'Actualité</th>
                <th>Type</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recentEvts)): ?>
                <?php foreach ($recentEvts as $ev): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($ev['titre']) ?></strong>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars(substr($ev['description'] ?? '', 0, 70)) ?>...</div>
                        </td>
                        <td>
                            <span class="badge" style="background:#e0e7ff; color:#4338ca; text-transform:uppercase;">
                                <?= htmlspecialchars($ev['type_media'] ?? 'photo') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($ev['date_evenement'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= ($ev['statut'] ?? '') === 'publie' ? 'badge-success' : 'badge-pending' ?>">
                                <?= htmlspecialchars($ev['statut'] ?? 'brouillon') ?>
                            </span>
                        </td>
                        <td>
                            <a href="evenement-form.php?id=<?= $ev['id'] ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i> Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:2rem; color:#64748b;">
                        Aucun événement publié pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
