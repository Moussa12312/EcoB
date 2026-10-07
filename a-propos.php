<?php
require_once __DIR__ . '/config/db.php';

$activeNav = "a-propos";

$pageData = null;
try {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = 'a-propos'");
    $stmt->execute();
    $pageData = $stmt->fetch();
} catch (Exception $e) {
    error_log("Failed to fetch page a-propos: " . $e->getMessage());
}

$pageTitle = ($pageData['titre'] ?? "À Propos de Nous") . " - ECOB University";
$badgeText = $pageData['badge'] ?? "Histoire & Vision";
$sousTitre = $pageData['sous_titre'] ?? "Découvrez l'histoire, la vocation et les valeurs qui animent notre communauté académique au quotidien.";
$mainContent = $pageData['contenu_principal'] ?? "";
$imgUrl = !empty($pageData['image_url']) ? $pageData['image_url'] : 'assets/images/img3.jpg';

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE DYNAMIQUE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/photo.jpg') ?>" alt="À Propos d'ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-landmark"></i> <?= htmlspecialchars($badgeText) ?></span>
        <h1><?= htmlspecialchars($pageData['titre'] ?? 'À Propos d\'ECOB University') ?></h1>
        <p><?= htmlspecialchars($sousTitre) ?></p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>À Propos</span>
        </div>
    </div>
</div>

<!-- SECTION HISTOIRE & VALEURS -->
<section class="section">
    <div class="container">
        <div class="grid-2" style="margin-bottom:4.5rem; align-items:flex-start;">
            <div>
                <span class="section-tag">Genèse & Ambition</span>
                <h2 style="font-size:2.2rem; margin-bottom:1.25rem;">Une Réponse Audacieuse aux Exigences du Marché</h2>
                
                <div class="page-dynamic-content" style="color:var(--text-main); font-size:1.05rem; line-height:1.8; margin-bottom:1.5rem;">
                    <?php if (!empty($mainContent)): ?>
                        <?= $mainContent ?>
                    <?php else: ?>
                        <p>Fondée à Bamako par un collège d'universitaires et d'acteurs économiques de premier plan, <strong>ECOB (École Centrale d'Optique & de Business)</strong> est née du constat que les jeunes diplômés manquaient souvent de deux atouts décisifs : la maîtrise pratique du monde des affaires et le bilinguisme français-anglais.</p>
                        <p>En alliant des maquettes académiques rigoureuses à des stages obligatoires en immersion professionnelle, notre université a rapidement forgé une réputation d'excellence auprès des recruteurs au Mali et dans la sous-région UEMOA.</p>
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:2rem; background:#fff; padding:1.25rem 1.75rem; border-radius:16px; border:1px solid #e2e8f0; box-shadow:var(--shadow-sm); flex-wrap:wrap;">
                    <div>
                        <strong style="font-size:2rem; color:var(--primary); font-family:'Outfit',sans-serif;">100%</strong>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">Stages assurés</p>
                    </div>
                    <div style="border-left:1px solid #e2e8f0; padding-left:1.5rem;">
                        <strong style="font-size:2rem; color:var(--accent); font-family:'Outfit',sans-serif;">30+</strong>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">Partenaires industriels</p>
                    </div>
                    <div style="border-left:1px solid #e2e8f0; padding-left:1.5rem;">
                        <strong style="font-size:2rem; color:var(--primary-light); font-family:'Outfit',sans-serif;">3 Pôles</strong>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">Gestion • Agro • Mines</p>
                    </div>
                </div>
            </div>
            <div>
                <div style="border-radius:24px; overflow:hidden; box-shadow:var(--shadow-xl); border:4px solid #fff;">
                    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/img3.jpg') ?>" alt="Campus Moderne ECOB" style="width:100%; height:auto; display:block;">
                </div>
            </div>
        </div>

        <!-- 4 Valeurs Piliers -->
        <div class="section-header">
            <span class="section-tag">L'Esprit ECOB</span>
            <h2>Nos Valeurs Cardinales</h2>
            <p>Ce qui guide notre engagement pédagogique et institutionnel.</p>
        </div>

        <div class="grid-4">
            <div class="feature-card text-center">
                <div class="feature-icon-wrapper" style="margin:0 auto 1.25rem auto;">
                    <i class="fas fa-star"></i>
                </div>
                <h3>L'Excellence</h3>
                <p>Cultiver le dépassement de soi et viser la qualité absolue dans les études et la pratique professionnelle.</p>
            </div>
            <div class="feature-card text-center">
                <div class="feature-icon-wrapper" style="margin:0 auto 1.25rem auto;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3>L'Intégrité</h3>
                <p>Former des citoyens et des cadres honnêtes, guidés par la probité intellectuelle et la transparence.</p>
            </div>
            <div class="feature-card text-center">
                <div class="feature-icon-wrapper" style="margin:0 auto 1.25rem auto;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>L'Innovation</h3>
                <p>Encourager l'esprit critique, la créativité entrepreneuriale et l'utilisation des technologies actuelles.</p>
            </div>
            <div class="feature-card text-center">
                <div class="feature-icon-wrapper" style="margin:0 auto 1.25rem auto;">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>L'Ouverture</h3>
                <p>Préparer nos étudiants à évoluer avec fluidité dans des contextes multiculturels et mondialisés.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

