<?php
require_once __DIR__ . '/config/db.php';

$activeNav = "organisation";

$pageData = null;
$membres = [];
try {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = 'organisation'");
    $stmt->execute();
    $pageData = $stmt->fetch();

    $stmtM = $db->query("SELECT * FROM equipe_direction ORDER BY ordre ASC, id ASC");
    $membres = $stmtM->fetchAll();
} catch (Exception $e) {
    error_log("Failed to fetch organisation data: " . $e->getMessage());
}

$pageTitle = ($pageData['titre'] ?? "Organisation & Gouvernance") . " - ECOB University";
$badgeText = $pageData['badge'] ?? "Gouvernance";
$sousTitre = $pageData['sous_titre'] ?? "Une structure administrative moderne, réactive et orientée vers la réussite et l'épanouissement de la communauté estudiantine.";
$mainContent = $pageData['contenu_principal'] ?? "";

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE DYNAMIQUE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/ima3.jpg') ?>" alt="Organisation & Gouvernance ECOB" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-sitemap"></i> <?= htmlspecialchars($badgeText) ?></span>
        <h1><?= htmlspecialchars($pageData['titre'] ?? 'Organisation & Gouvernance') ?></h1>
        <p><?= htmlspecialchars($sousTitre) ?></p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Organisation</span>
        </div>
    </div>
</div>

<!-- SECTION ORGANIGRAMME & MEMBRES DE DIRECTION -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Structure & Organigramme</span>
            <h2>Gouvernance Académique et Administrative</h2>
            <p>Une coordination fluide entre la présidence, les directions de pôles et les services aux étudiants.</p>
        </div>

        <?php if (!empty($mainContent)): ?>
            <div style="max-width:850px; margin:0 auto 3rem auto; background:#fff; padding:2rem; border-radius:16px; border:1px solid #e2e8f0; box-shadow:var(--shadow-sm); line-height:1.8; color:var(--text-main); font-size:1.05rem;">
                <?= $mainContent ?>
            </div>
        <?php endif; ?>

        <!-- Grille Dynamique des Membres de Direction -->
        <div style="margin-bottom:4rem;">
            <div style="text-align:center; margin-bottom:2.5rem;">
                <h3 style="font-size:1.8rem; font-family:'Outfit',sans-serif; color:var(--primary);">Membres du Directoire</h3>
                <p style="color:var(--text-muted); font-size:0.95rem;">Les femmes et les hommes qui pilotent la vision d'ECOB University</p>
            </div>

            <div class="grid-4">
                <?php if (!empty($membres)): ?>
                    <?php foreach ($membres as $m): ?>
                        <div class="feature-card text-center" style="padding:2.25rem 1.5rem; display:flex; flex-direction:column; align-items:center;">
                            <div style="width:120px; height:120px; border-radius:50%; overflow:hidden; margin-bottom:1.25rem; border:3px solid var(--accent); box-shadow:var(--shadow-md); position:relative;">
                                <img src="<?= asset_url($m['photo_url'], 'assets/images/images.jpg') ?>" alt="<?= htmlspecialchars($m['nom']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <span style="display:inline-block; background:rgba(9,18,44,0.06); color:var(--primary); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; padding:0.25rem 0.75rem; border-radius:9999px; margin-bottom:0.6rem;">
                                <?= htmlspecialchars($m['departement'] ?? 'Direction') ?>
                            </span>
                            <h3 style="font-size:1.2rem; margin-bottom:0.25rem; color:var(--primary); font-family:'Outfit',sans-serif;"><?= htmlspecialchars($m['nom']) ?></h3>
                            <div style="color:var(--accent-hover); font-weight:700; font-size:0.9rem; margin-bottom:0.75rem;"><?= htmlspecialchars($m['poste']) ?></div>
                            <?php if (!empty($m['biographie'])): ?>
                                <p style="color:var(--text-muted); font-size:0.86rem; line-height:1.5; margin:0;"><?= htmlspecialchars($m['biographie']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column:1/-1; text-align:center; padding:2rem; color:#64748b;">
                        Les membres de la direction sont en cours de mise à jour.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Schéma Hiérarchique des Services -->
        <div style="max-width:920px; margin:0 auto;">
            <div class="organigramme-head-card">
                <div class="organigramme-head-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h3>Présidence du Conseil d'Administration</h3>
                <p>
                    Définit les orientations stratégiques d'ECOB University, veille à la conformité institutionnelle et supervise le développement des partenariats internationaux.
                </p>
            </div>

            <div class="organigramme-level-2">
                <div class="organigramme-card organigramme-card-primary">
                    <div class="organigramme-icon-wrap icon-primary">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4>Direction Générale</h4>
                    <p>
                        Pilotage opérationnel de l'établissement, coordination inter-départements et relations avec les institutions de tutelle.
                    </p>
                </div>

                <div class="organigramme-card organigramme-card-accent">
                    <div class="organigramme-icon-wrap icon-accent">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h4>Direction des Études</h4>
                    <p>
                        Gestion des maquettes pédagogiques LMD, recrutement du corps professoral, organisation des contrôles et suivi des stages.
                    </p>
                </div>
            </div>

            <!-- Niveau 3 : Services d'Appui & Départements -->
            <div class="grid-3">
                <div class="feature-card" style="padding:1.6rem; text-align:center;">
                    <div style="font-size:1.8rem; color:var(--primary); margin-bottom:0.6rem;"><i class="fas fa-file-invoice"></i></div>
                    <h4 style="font-size:1.1rem; margin-bottom:0.35rem;">Scolarité & Admissions</h4>
                    <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.5;">Traitement des candidatures, inscriptions, relevés de notes et attestations de réussite.</p>
                </div>
                <div class="feature-card" style="padding:1.6rem; text-align:center;">
                    <div style="font-size:1.8rem; color:var(--accent-hover); margin-bottom:0.6rem;"><i class="fas fa-coins"></i></div>
                    <h4 style="font-size:1.1rem; margin-bottom:0.35rem;">Finances & Comptabilité</h4>
                    <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.5;">Gestion des règlements de scolarité, facturation et plans d'échelonnement.</p>
                </div>
                <div class="feature-card" style="padding:1.6rem; text-align:center;">
                    <div style="font-size:1.8rem; color:var(--primary); margin-bottom:0.6rem;"><i class="fas fa-shield-alt"></i></div>
                    <h4 style="font-size:1.1rem; margin-bottom:0.35rem;">Vie Scolaire & Discipline</h4>
                    <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.5;">Contrôle de l'assiduité, respect du règlement, associations étudiantes et campus sécurisé.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

