<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = "Formations en Agro-Business - ECOB University";
$activeNav = "agro";

$db = get_db();
$filieres = $db->query("SELECT * FROM filieres WHERE departement = 'agro' AND statut = 'actif' ORDER BY ordre ASC, id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url('', 'assets/images/ima5.jpg') ?>" alt="Agro-Business & Filières Agricoles ECOB" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-seedling"></i> Filière Stratégique d'Avenir</span>
        <h1>Formations en Agro-Business</h1>
        <p>
            Valorisez le potentiel agricole de notre pays par le management, la technologie et l'entrepreneuriat agroalimentaire moderne.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Agro-Business</span>
        </div>
    </div>
</div>

<!-- SECTION INTRO -->
<section class="section" style="padding-bottom:2rem;">
    <div class="container">
        <div class="grid-2">
            <div>
                <span class="section-tag">Pôle d'Impact Économique</span>
                <h2 style="font-size:2.2rem; margin-bottom:1.25rem;">L'Agro-Business au Cœur de l'Entrepreneuriat</h2>
                <p style="color:var(--text-muted); font-size:1.05rem; margin-bottom:1.25rem;">
                    L'agriculture et l'agroalimentaire constituent le poumon économique du Mali et de la sous-région ouest-africaine. ECOB forme des managers et des agronomes entrepreneurs capables de transformer les récoltes, de gérer des exploitations modernes et d'exporter des produits à forte valeur ajoutée.
                </p>
                <div class="feature-card" style="padding:1.25rem; margin-bottom:1rem;">
                    <div style="display:flex; gap:0.75rem; align-items:center;">
                        <i class="fas fa-check-circle" style="color:#10b981; font-size:1.25rem;"></i>
                        <span style="font-weight:600;">Stages pratiques sur exploitations et usines agroalimentaires</span>
                    </div>
                </div>
                <div class="feature-card" style="padding:1.25rem;">
                    <div style="display:flex; gap:0.75rem; align-items:center;">
                        <i class="fas fa-check-circle" style="color:#10b981; font-size:1.25rem;"></i>
                        <span style="font-weight:600;">Accompagnement pour le financement de projets d'entrepreneuriat jeune</span>
                    </div>
                </div>
            </div>
            <div>
                <div style="border-radius:20px; overflow:hidden; box-shadow:var(--shadow-xl);">
                    <img src="<?= asset_url(get_setting('pole_agro_img', 'assets/images/img1.jpg'), 'assets/images/img1.jpg') ?>" alt="Agro-Business ECOB" style="width:100%; height:auto;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION FILIÈRES AGRO (100% DYNAMIQUE DU CMS) -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Nos Spécialités</span>
            <h2>Filières en Agro-Business</h2>
            <p>Des programmes alliant sciences du vivant, technologies modernes et gestion commerciale.</p>
        </div>

        <!-- Filtres Diplômes en place (Sans rechargement) -->
        <div class="diploma-filter-wrap" style="display:flex; justify-content:center; gap:0.6rem; flex-wrap:wrap; margin-bottom:2.5rem;">
            <button type="button" class="filter-pill active" data-diploma="all">
                <i class="fas fa-layer-group"></i> Toutes les filières
            </button>
            <button type="button" class="filter-pill" data-diploma="dut">
                <i class="fas fa-certificate"></i> DUT (Bac+2)
            </button>
            <button type="button" class="filter-pill" data-diploma="licence">
                <i class="fas fa-graduation-cap"></i> Licence (Bac+3)
            </button>
            <button type="button" class="filter-pill" data-diploma="master">
                <i class="fas fa-user-tie"></i> Master (Bac+5)
            </button>
        </div>

        <div class="grid-3" id="filieresGrid">
            <?php if (!empty($filieres)): ?>
                <?php foreach ($filieres as $f): ?>
                    <div class="program-card" data-diploma="<?= htmlspecialchars(mb_strtolower($f['diplome'])) ?>">
                        <div class="program-card-img">
                            <img src="<?= asset_url($f['image_url'], 'assets/images/img1.jpg') ?>" alt="<?= htmlspecialchars($f['nom']) ?>">
                            <span class="program-badge"><?= htmlspecialchars($f['diplome']) ?></span>
                        </div>
                        <div class="program-card-body">
                            <div style="font-size:1.8rem; margin-bottom:0.4rem;"><?= htmlspecialchars($f['icon']) ?></div>
                            <h3><?= htmlspecialchars($f['nom']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($f['description'])) ?></p>
                            
                            <?php if (!empty($f['debouches'])): ?>
                                <div style="background:#f8fafc; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:1.25rem; font-size:0.85rem; color:#475569;">
                                    <strong style="color:var(--primary); display:block; margin-bottom:0.25rem;">
                                        <i class="fas fa-briefcase" style="color:var(--accent);"></i> Débouchés professionnels :
                                    </strong>
                                    <?= htmlspecialchars($f['debouches']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="program-card-footer">
                                <span class="text-muted" style="font-size:0.85rem;"><i class="far fa-clock"></i> <?= htmlspecialchars($f['duree']) ?></span>
                                <a href="admission.php" class="btn-primary" style="padding:0.55rem 1.15rem; font-size:0.85rem;">
                                    Choisir cette filière
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:3rem; grid-column:span 3;">
                    <p>Aucune filière configurée actuellement.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('.diploma-filter-wrap button');
    const cards = document.querySelectorAll('#filieresGrid .program-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const diploma = this.getAttribute('data-diploma');
            cards.forEach(card => {
                const cardDiplome = (card.getAttribute('data-diploma') || '').toLowerCase();
                const match = (diploma === 'all' || cardDiplome.includes(diploma));
                card.style.display = match ? '' : 'none';
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
