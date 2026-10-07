<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = "ECOB University - École de Business Bilingue à Bamako";
$activeNav = "accueil";

$settings = get_settings();

// Récupérations dynamiques depuis la base de données
$recentEvents = [];
$partenaires = [];
$equipeMembres = [];
try {
    $db = get_db();
    
    // 3 derniers événements
    $stmt = $db->query("SELECT * FROM evenements WHERE statut = 'publie' ORDER BY date_evenement DESC, id DESC LIMIT 3");
    $recentEvents = $stmt->fetchAll();

    // Partenaires professionnels
    $stmtP = $db->query("SELECT * FROM partenaires WHERE statut = 'actif' ORDER BY ordre ASC, id ASC");
    $partenaires = $stmtP->fetchAll();

    // Membres de direction
    $stmtE = $db->query("SELECT * FROM equipe_direction ORDER BY ordre ASC, id ASC LIMIT 8");
    $equipeMembres = $stmtE->fetchAll();
} catch (Exception $e) {
    error_log("Failed to fetch index dynamic data: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER SECTION ULTRA-MODERNE -->
<div class="hero-slider-wrapper">
    <!-- Slide 1 -->
    <div class="hero-slide active">
        <img src="<?= asset_url($settings['slider_img1'] ?? 'assets/images/img2.jpg', 'assets/images/img2.jpg') ?>" alt="Campus ECOB University" class="hero-slide-bg">
        <div class="hero-slide-overlay"></div>
        <div class="container hero-slide-container">
            <span class="hero-tag">
                <span class="pulse-dot"></span>
                <i class="fas fa-certificate"></i> ÉCOLE DE BUSINESS BILINGUE
            </span>
            <h1 class="hero-title">
                <?= htmlspecialchars($settings['slider_titre1'] ?? 'La Marche Vers L\'Excellence Académique') ?>
            </h1>
            <p class="hero-desc">
                <?= htmlspecialchars($settings['slider_desc1'] ?? 'Rejoignez une institution universitaire moderne qui forme les leaders et décideurs africains de demain grâce à une pédagogie bilingue de standard mondial.') ?>
            </p>
            <div class="hero-btns">
                <a href="admission.php" class="btn-primary">
                    <i class="fas fa-user-graduate"></i> Poursuivre mon Cursus
                </a>
                <a href="#formations" class="btn-secondary">
                    <i class="fas fa-book-open"></i> Découvrir les Filières
                </a>
            </div>
        </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide">
        <img src="<?= asset_url($settings['slider_img2'] ?? 'assets/images/img1.jpg', 'assets/images/img1.jpg') ?>" alt="Étudiants ECOB" class="hero-slide-bg">
        <div class="hero-slide-overlay"></div>
        <div class="container hero-slide-container">
            <span class="hero-tag">
                <span class="pulse-dot"></span>
                <i class="fas fa-globe"></i> BILINGUISME & DIPLOMATIE ÉCONOMIQUE
            </span>
            <h1 class="hero-title">
                <?= htmlspecialchars($settings['slider_titre2'] ?? 'Des Compétences Internationales Prêtes à l\'Emploi') ?>
            </h1>
            <p class="hero-desc">
                <?= htmlspecialchars($settings['slider_desc2'] ?? 'Des programmes académiques rigoureux, des stages garantis en entreprise et un réseau d\'anciens diplômés actif à travers la sous-région.') ?>
            </p>
            <div class="hero-btns">
                <a href="admission.php" class="btn-primary">
                    <i class="fas fa-file-alt"></i> Déposer ma Candidature
                </a>
                <a href="a-propos.php" class="btn-secondary">
                    <i class="fas fa-university"></i> À Propos d'ECOB
                </a>
            </div>
        </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide">
        <img src="<?= asset_url($settings['slider_img3'] ?? 'assets/images/img3.jpg', 'assets/images/img3.jpg') ?>" alt="Formations Stratégiques" class="hero-slide-bg">
        <div class="hero-slide-overlay"></div>
        <div class="container hero-slide-container">
            <span class="hero-tag">
                <span class="pulse-dot"></span>
                <i class="fas fa-layer-group"></i> NOUVELLES FILIÈRES <?= htmlspecialchars($settings['site_anneescolaire'] ?? '2025 - 2026') ?>
            </span>
            <h1 class="hero-title">
                <?= htmlspecialchars($settings['slider_titre3'] ?? 'Mines & Géologie & Agro-Business') ?>
            </h1>
            <p class="hero-desc">
                <?= htmlspecialchars($settings['slider_desc3'] ?? 'Répondez aux opportunités économiques majeures du Mali grâce à des formations spécialisées, alliant théorie sur campus et pratique intensive sur le terrain.') ?>
            </p>
            <div class="hero-btns">
                <a href="admission.php" class="btn-primary">
                    <i class="fas fa-check-circle"></i> S'inscrire Maintenant
                </a>
                <a href="evenements.php" class="btn-secondary">
                    <i class="fas fa-calendar-alt"></i> Voir la Vie du Campus
                </a>
            </div>
        </div>
    </div>

    <!-- Contrôles et Indicateurs du Slider -->
    <div class="slider-controls-wrap">
        <div class="slider-dots">
            <button type="button" class="slider-dot active" data-slide="0" aria-label="Aller au slide 1"></button>
            <button type="button" class="slider-dot" data-slide="1" aria-label="Aller au slide 2"></button>
            <button type="button" class="slider-dot" data-slide="2" aria-label="Aller au slide 3"></button>
        </div>
        <div class="slider-arrows">
            <button type="button" class="slider-arrow prev" aria-label="Slide précédent"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="slider-arrow next" aria-label="Slide suivant"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<!-- SECTION CHIFFRES CLÉS / STATS RAPIDES -->
<section class="stats-strip">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-caption">Cursus Bilingue Français / Anglais</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3 Pôles</div>
                <div class="stat-caption">Sciences de Gestion • Agro-Business • Mines</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3 Cycles</div>
                <div class="stat-caption">DUT • Licence Professionnelle • Master</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">Sotuba ACI</div>
                <div class="stat-caption">Campus Moderne Sécurisé à Bamako</div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION POURQUOI CHOISIR ECOB -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Une Pédagogie de Haut Niveau</span>
            <h2>Pourquoi Opter pour ECOB University ?</h2>
            <p>
                EcoB est bien plus qu'un établissement : c'est un tremplin pour vos ambitions, vos compétences et votre réussite professionnelle.
            </p>
        </div>

        <div class="grid-4">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Excellence Académique</h3>
                <p>
                    Des maquettes pédagogiques exigeantes et actualisées, dispensées par des enseignants universitaires chevronnés et des professionnels en activité.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-language"></i>
                </div>
                <h3>Approche Bilingue</h3>
                <p>
                    Une immersion progressive en anglais des affaires pour communiquer avec aisance et briguer des carrières internationales.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Innovation & Pratique</h3>
                <p>
                    Des études de cas réelles, des simulations d'entreprises, des visites d'usines et des sorties de terrain régulières.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Réseau & Communauté</h3>
                <p>
                    Un environnement inclusif et solidaire, un bureau d'orientation professionnelle et des partenariats pour vos stages.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION CYCLES & FORMATIONS -->
<section id="formations" class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Diplômes & Parcours</span>
            <h2>Nos Cycles d'Enseignement</h2>
            <p>
                Du diplôme d'accès rapide au Master de spécialisation approfondie, construisez un parcours sur-mesure.
            </p>
        </div>

        <div class="grid-3">
            <!-- DUT -->
            <div class="program-card">
                <div class="program-card-img">
                    <img src="<?= asset_url(get_setting('cycle_dut_img', 'assets/images/img2.jpg'), 'assets/images/img2.jpg') ?>" alt="Diplôme Universitaire de Technologie">
                    <span class="program-badge">Bac + 2</span>
                </div>
                <div class="program-card-body">
                    <h3>DUT Professionnel</h3>
                    <p>
                        Le DUT vous prépare rapidement à la vie active avec des compétences pratiques, techniques et managériales immédiatement mobilisables en entreprise.
                    </p>
                    <div class="program-card-footer">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="far fa-clock"></i> 2 Années d'études</span>
                        <a href="gestion.php" class="program-link">En savoir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- LICENCE -->
            <div class="program-card">
                <div class="program-card-img">
                    <img src="<?= asset_url(get_setting('cycle_licence_img', 'assets/images/img1.jpg'), 'assets/images/img1.jpg') ?>" alt="Licence Professionnelle">
                    <span class="program-badge">Bac + 3</span>
                </div>
                <div class="program-card-body">
                    <h3>Licence Professionnelle</h3>
                    <p>
                        Une formation de référence pour maîtriser les fondamentaux stratégiques en Gestion d'Entreprise, Finance, Agro-Business ou Mines & Géologie.
                    </p>
                    <div class="program-card-footer">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="far fa-clock"></i> 3 Années d'études</span>
                        <a href="gestion.php" class="program-link">En savoir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- MASTER -->
            <div class="program-card">
                <div class="program-card-img">
                    <img src="<?= asset_url(get_setting('cycle_master_img', 'assets/images/img4.jpg'), 'assets/images/img4.jpg') ?>" alt="Master Universitaire">
                    <span class="program-badge">Bac + 5</span>
                </div>
                <div class="program-card-body">
                    <h3>Master Professionnel</h3>
                    <p>
                        Approfondissez votre expertise managériale pour occuper des postes de direction, piloter des projets d'envergure ou lancer votre propre entreprise.
                    </p>
                    <div class="program-card-footer">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="far fa-clock"></i> 2 Années de Master</span>
                        <a href="gestion.php" class="program-link">En savoir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION ACTIVITÉS & ÉVÉNEMENTS (DYNAMIQUE) -->
<section class="section">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:3rem; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <span class="section-tag">Vie Universitaire</span>
                <h2 style="font-size:2.35rem; margin-top:0.3rem;">Activités & Événements Récents</h2>
                <p class="text-muted" style="font-size:1.05rem;">Suivez les actualités, conférences, cérémonies et immersions terrain d'ECOB.</p>
            </div>
            <div>
                <a href="evenements.php" class="btn-primary" style="padding:0.75rem 1.6rem; font-size:0.92rem;">
                    <i class="fas fa-newspaper"></i> Voir Tout l'Espace Actualités
                </a>
            </div>
        </div>

        <?php if (!empty($recentEvents)): ?>
            <div class="grid-3">
                <?php foreach ($recentEvents as $event): ?>
                    <div class="event-card">
                        <div class="event-media-container">
                            <img src="<?= asset_url($event['media_url'], 'assets/images/img2.jpg') ?>" alt="<?= htmlspecialchars($event['titre']) ?>">
                            <span class="event-type-badge">
                                <i class="fas <?= $event['type_media'] === 'video' ? 'fa-play-circle' : 'fa-camera' ?>"></i>
                                <?= htmlspecialchars($event['categorie']) ?>
                            </span>
                            <?php if ($event['type_media'] === 'video'): ?>
                                <a href="evenements.php" class="video-play-overlay">
                                    <i class="fas fa-play"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="event-body">
                            <div class="event-meta">
                                <span><i class="far fa-calendar-alt"></i> <?= format_date_fr($event['date_evenement'] ?? $event['created_at']) ?></span>
                            </div>
                            <h3 class="event-title">
                                <a href="evenements.php"><?= htmlspecialchars($event['titre']) ?></a>
                            </h3>
                            <p class="event-desc">
                                <?= htmlspecialchars(mb_strimwidth($event['description'], 0, 130, '...')) ?>
                            </p>
                            <div>
                                <a href="evenements.php" class="program-link" style="color:var(--accent);">
                                    Lire l'article complet <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:3rem; background:#fff; border-radius:12px; border:1px dashed #cbd5e1;">
                <i class="fas fa-calendar-alt" style="font-size:3rem; color:#94a3b8; margin-bottom:1rem;"></i>
                <p style="color:#64748b;">De nouvelles activités et photos de promotions seront publiées très prochainement.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- SECTION PARTENAIRES (DYNAMIQUE) -->
<section class="section section-alt" style="padding: 4rem 0;">
    <div class="container">
        <div class="section-header" style="margin-bottom:2.25rem;">
            <span class="section-tag">Réseau & Synergies</span>
            <h2 style="font-size:2rem;">Nos Partenaires d'Insertion Professionnelle</h2>
            <p>Des conventions privilégiées avec le tissu économique malien et sous-régional.</p>
        </div>
        
        <?php if (!empty($partenaires)): ?>
            <div class="partners-grid">
                <?php foreach ($partenaires as $part): ?>
                    <div class="partner-card">
                        <div class="partner-logo-box">
                            <img src="<?= asset_url($part['logo_url'], 'assets/images/logoo1.jpg') ?>" alt="Logo <?= htmlspecialchars($part['nom']) ?>" class="partner-logo-img">
                        </div>
                        <?php if (!empty($part['type_partenariat'])): ?>
                            <span class="partner-domain-badge">
                                <i class="fas fa-handshake"></i> <?= htmlspecialchars($part['type_partenariat']) ?>
                            </span>
                        <?php endif; ?>
                        <h3 class="partner-name"><?= htmlspecialchars($part['nom']) ?></h3>
                        <p class="partner-details">
                            <?= nl2br(htmlspecialchars($part['description'] ?? 'Convention de stage et de collaboration avec l\'Université ECOB.')) ?>
                        </p>
                        <div class="partner-card-footer">
                            <span class="partner-location">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($part['pays'] ?? 'Mali') ?>
                            </span>
                            <?php if (!empty($part['site_web'])): ?>
                                <a href="<?= htmlspecialchars($part['site_web']) ?>" target="_blank" rel="noopener noreferrer" class="partner-link">
                                    Site web <i class="fas fa-arrow-up-right-from-square"></i>
                                </a>
                            <?php else: ?>
                                <span style="color:var(--text-light); font-size:0.8rem;"><i class="fas fa-check-circle" style="color:var(--success);"></i> Convention active</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:3rem; background:#fff; border-radius:18px; box-shadow:var(--shadow-sm); max-width:600px; margin:0 auto;">
                <i class="fas fa-building" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
                <h3 style="color:var(--primary); margin-bottom:0.5rem;">Partenaires Institutionnels</h3>
                <p class="text-muted">Les conventions de stages et de partenariats sont en cours de mise à jour.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- SECTION RENCONTRER L'ADMINISTRATION (DYNAMIQUE) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Gouvernance</span>
            <h2>L'Équipe Dirigeante</h2>
            <p>Une administration engagée au quotidien pour la réussite de chaque étudiant.</p>
        </div>

        <div class="grid-4">
            <?php if (!empty($equipeMembres)): ?>
                <?php foreach ($equipeMembres as $em): ?>
                    <div class="feature-card text-center" style="padding:2.25rem 1.5rem; display:flex; flex-direction:column; align-items:center;">
                        <div style="width:115px; height:115px; border-radius:50%; overflow:hidden; margin-bottom:1.25rem; border:3px solid var(--accent); box-shadow:var(--shadow-md);">
                            <img src="<?= asset_url($em['photo_url'], 'assets/images/dre.jpg') ?>" alt="<?= htmlspecialchars($em['nom']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <h3 style="font-size:1.15rem; margin-bottom:0.25rem; color:var(--primary); font-family:'Outfit',sans-serif;"><?= htmlspecialchars($em['nom']) ?></h3>
                        <p style="color:var(--accent-hover); font-weight:700; font-size:0.88rem; margin-bottom:0.35rem;"><?= htmlspecialchars($em['poste']) ?></p>
                        <span style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($em['departement'] ?? 'Direction') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="feature-card text-center" style="padding:2rem 1.5rem;">
                    <div style="width:110px; height:110px; border-radius:50%; overflow:hidden; margin:0 auto 1.25rem auto; border:3px solid var(--accent); box-shadow:var(--shadow-md);">
                        <img src="assets/images/images.jpg" alt="Promoteur ECOB" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 style="font-size:1.15rem; margin-bottom:0.25rem;">Promoteur</h3>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Présidence de l'Université</p>
                </div>

                <div class="feature-card text-center" style="padding:2rem 1.5rem;">
                    <div style="width:110px; height:110px; border-radius:50%; overflow:hidden; margin:0 auto 1.25rem auto; border:3px solid var(--primary); box-shadow:var(--shadow-md);">
                        <img src="assets/images/dre.jpg" alt="Directeur Général ECOB" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 style="font-size:1.15rem; margin-bottom:0.25rem;">Directeur Général</h3>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Direction Académique</p>
                </div>

                <div class="feature-card text-center" style="padding:2rem 1.5rem;">
                    <div style="width:110px; height:110px; border-radius:50%; overflow:hidden; margin:0 auto 1.25rem auto; border:3px solid var(--accent); box-shadow:var(--shadow-md);">
                        <img src="assets/images/sec.jpg" alt="Secrétariat Principal" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 style="font-size:1.15rem; margin-bottom:0.25rem;">Secrétariat Général</h3>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Admissions & Scolarité</p>
                </div>

                <div class="feature-card text-center" style="padding:2rem 1.5rem;">
                    <div style="width:110px; height:110px; border-radius:50%; overflow:hidden; margin:0 auto 1.25rem auto; border:3px solid var(--primary); box-shadow:var(--shadow-md);">
                        <img src="assets/images/surv.jpg" alt="Surveillance Générale" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 style="font-size:1.15rem; margin-bottom:0.25rem;">Surveillance Générale</h3>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Discipline & Vie Étudiante</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="text-align:center; margin-top:2.5rem;">
            <a href="organisa.php" class="btn-secondary" style="color:var(--primary); border-color:#cbd5e1; background:#fff;">
                <i class="fas fa-sitemap"></i> Voir l'organigramme complet & la gouvernance
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
