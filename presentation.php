<?php
require_once __DIR__ . '/config/db.php';

$activeNav = "presentation";

// Récupération dynamique depuis la base de données
$pageData = null;
try {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = 'presentation'");
    $stmt->execute();
    $pageData = $stmt->fetch();
} catch (Exception $e) {
    error_log("Failed to fetch page presentation: " . $e->getMessage());
}

$pageTitle = ($pageData['titre'] ?? "Présentation de l'Institution") . " - ECOB University";
$badgeText = $pageData['badge'] ?? "Institution & Vision";
$sousTitre = $pageData['sous_titre'] ?? "Une institution d'enseignement supérieur d'excellence dédiée à l'émergence des compétences managériales et techniques en Afrique.";
$mainContent = $pageData['contenu_principal'] ?? "";
$imgUrl = !empty($pageData['image_url']) ? $pageData['image_url'] : 'assets/images/img2.jpg';

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE DYNAMIQUE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/img2.jpg') ?>" alt="Présentation ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-university"></i> <?= htmlspecialchars($badgeText) ?></span>
        <h1><?= htmlspecialchars($pageData['titre'] ?? 'Présentation d\'ECOB University') ?></h1>
        <p><?= htmlspecialchars($sousTitre) ?></p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Présentation</span>
        </div>
    </div>
</div>

<!-- SECTION PRÉSENTATION -->
<section class="section">
    <div class="container">
        <div class="grid-2" style="margin-bottom:4.5rem; align-items:flex-start;">
            <div>
                <span class="section-tag">Notre Mission Fondatrice</span>
                <h2 style="font-size:2.2rem; margin-bottom:1.5rem; line-height:1.25;">Former les Leaders Bilingues et Décideurs de Demain</h2>
                
                <div class="page-dynamic-content" style="color:var(--text-main); font-size:1.05rem; line-height:1.8; margin-bottom:2rem;">
                    <?php if (!empty($mainContent)): ?>
                        <?= $mainContent ?>
                    <?php else: ?>
                        <p>Fondée avec l'ambition d'apporter une réponse concrète aux défis d'insertion des diplômés, ECOB University s'est imposée comme une référence de l'enseignement supérieur privé au Mali.</p>
                        <p>Notre modèle pédagogique combine l'assimilation théorique rigoureuse, la pratique en entreprise dès le premier cycle et la maîtrise courante de l'anglais professionnel.</p>
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <a href="admission.php" class="btn-primary">
                        <i class="fas fa-graduation-cap"></i> Déposer ma Candidature
                    </a>
                    <a href="contact.php" class="btn-secondary" style="color:var(--primary); border-color:#cbd5e1; background:#fff;">
                        <i class="fas fa-envelope"></i> Nous Poser une Question
                    </a>
                </div>
            </div>

            <div>
                <div style="border-radius:24px; overflow:hidden; box-shadow:var(--shadow-xl); border:4px solid #fff; position:relative;">
                    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/img2.jpg') ?>" alt="Campus ECOB University" style="width:100%; height:auto; display:block;">
                    <div style="position:absolute; bottom:1.5rem; left:1.5rem; right:1.5rem; background:rgba(9,18,44,0.92); padding:1.25rem; border-radius:16px; border:1px solid rgba(255,255,255,0.2); box-shadow:0 10px 30px rgba(0,0,0,0.35); color:#fff;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:42px; height:42px; border-radius:50%; background:var(--accent); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                <i class="fas fa-award"></i>
                            </div>
                            <div>
                                <h4 style="color:#fff; font-size:1rem; margin-bottom:0.15rem;">Agrément Officiel d'État</h4>
                                <p style="color:#cbd5e1; font-size:0.8rem; margin:0;">Diplômes homologués et conformes au standard LMD</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Piliers Fondamentaux -->
        <div class="section-header" style="margin-bottom:2.5rem;">
            <span class="section-tag">Les Piliers de l'Institution</span>
            <h2>Pourquoi ECOB University est Différente</h2>
        </div>

        <div class="grid-3">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3>Exigence & Éthique</h3>
                <p>
                    Nous inculquons à nos étudiants le sens aigu de la responsabilité, l'intégrité intellectuelle et la rigueur morale indispensables aux futurs dirigeants d'entreprises.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-globe-africa"></i>
                </div>
                <h3>Bilinguisme Opérationnel</h3>
                <p>
                    L'apprentissage de l'anglais des affaires est une pratique quotidienne grâce à des modules immersifs, des conférences internationales et des ateliers de prise de parole.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Insertion Professionnelle</h3>
                <p>
                    Nos partenariats privilégiés avec les banques, sociétés minières et entreprises agroalimentaires garantissent à nos étudiants des stages pratiques et des débouchés concrets.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

