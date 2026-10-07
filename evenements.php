<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = "Activités & Événements - ECOB University";
$activeNav = "evenements";

$initialCat = clean_input($_GET['cat'] ?? 'all');
if (empty($initialCat)) {
    $initialCat = 'all';
}

$events = [];
$catStats = [];

try {
    $db = get_db();
    // Charger tous les événements publiés pour filtrage dynamique instantané sans rechargement
    $stmt = $db->query("SELECT * FROM evenements WHERE statut = 'publie' ORDER BY date_evenement DESC, id DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer les compteurs par catégorie
    foreach ($events as $ev) {
        $c = !empty($ev['categorie']) ? $ev['categorie'] : 'Général';
        $catStats[$c] = ($catStats[$c] ?? 0) + 1;
    }
} catch (Exception $e) {
    error_log("Failed to load evenements: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE PAGE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url('', 'assets/images/img4.jpg') ?>" alt="Vie du Campus & Événements ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-bullhorn"></i> Vie du Campus & Actualités</span>
        <h1>Activités & Événements</h1>
        <p>
            Revivez les moments forts, les cérémonies officielles, les conférences bilingues et les immersions pratiques de nos étudiants.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Activités & Événements</span>
        </div>
    </div>
</div>

<!-- SECTION CONTENU ÉVÉNEMENTS AVEC FILTRAGE SANS RECHARGEMENT -->
<section class="section" id="eventsSection">
    <div class="container">
        
        <!-- BARRE DE FILTRAGE INTERACTIVE EN TEMPS RÉEL (RESTE SUR PLACE SANS ACTUALISER) -->
        <div class="events-filter-container">
            <div class="events-filter-bar">
                
                <!-- Filtres Principaux par Catégorie -->
                <div class="filter-group-primary" id="categoryFilterGroup" role="group" aria-label="Filtrer par catégorie">
                    <button type="button" class="filter-pill <?= $initialCat === 'all' ? 'active' : '' ?>" data-filter="all">
                        <i class="fas fa-th-large"></i> Tous les articles
                        <span class="filter-badge"><?= count($events) ?></span>
                    </button>
                    <?php foreach ($catStats as $catName => $count): ?>
                        <button type="button" class="filter-pill <?= $initialCat === $catName ? 'active' : '' ?>" data-filter="<?= htmlspecialchars($catName) ?>">
                            <?= htmlspecialchars($catName) ?>
                            <span class="filter-badge"><?= $count ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Filtres Secondaires : Type de média & Recherche instantanée -->
                <div class="filter-secondary-bar">
                    <div class="media-type-tabs" role="tablist" aria-label="Type de média">
                        <button type="button" class="media-tab active" data-media="all" role="tab" aria-selected="true">
                            <i class="fas fa-layer-group"></i> Tout
                        </button>
                        <button type="button" class="media-tab" data-media="image" role="tab" aria-selected="false">
                            <i class="fas fa-image"></i> Photos
                        </button>
                        <button type="button" class="media-tab" data-media="video" role="tab" aria-selected="false">
                            <i class="fas fa-video"></i> Vidéos
                        </button>
                    </div>

                    <div class="event-search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="eventSearchInput" class="event-search-input" placeholder="Rechercher par titre ou mot-clé..." autocomplete="off">
                        <button type="button" id="clearSearchBtn" class="clear-search-btn" style="display:none;" aria-label="Effacer la recherche">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Résumé compteur en direct -->
            <div class="filter-results-info">
                <span>
                    Affichage de <strong id="visibleCount" style="color:var(--primary);"><?= count($events) ?></strong> publication(s)
                </span>
                <span id="activeFilterLabel" style="font-size:0.82rem; color:var(--text-light);">
                    Catégorie : <strong>Toutes</strong>
                </span>
            </div>
        </div>

        <!-- GRILLE DES ARTICLES -->
        <?php if (!empty($events)): ?>
            <div class="grid-3" id="eventsGrid">
                <?php foreach ($events as $event): ?>
                    <?php 
                    $mediaImg = !empty($event['media_url']) ? $event['media_url'] : 'assets/images/img2.jpg';
                    $evCat = !empty($event['categorie']) ? $event['categorie'] : 'Général';
                    $evType = !empty($event['type_media']) ? $event['type_media'] : 'image';
                    $searchData = mb_strtolower($event['titre'] . ' ' . $event['description'] . ' ' . $evCat);
                    ?>
                    <div class="event-card" 
                         data-category="<?= htmlspecialchars($evCat) ?>" 
                         data-media="<?= htmlspecialchars($evType) ?>" 
                         data-search="<?= htmlspecialchars($searchData) ?>">
                        
                        <div class="event-media-container">
                            <img src="<?= asset_url($event['media_url'], 'assets/images/img2.jpg') ?>" alt="<?= htmlspecialchars($event['titre']) ?>" loading="lazy">
                            <span class="event-type-badge">
                                <i class="fas <?= $evType === 'video' ? 'fa-video' : 'fa-image' ?>"></i>
                                <?= htmlspecialchars($evCat) ?>
                            </span>

                            <?php if ($evType === 'video' && !empty($event['video_embed'])): ?>
                                <button type="button" class="video-play-overlay" onclick="openVideoModal('<?= htmlspecialchars($event['video_embed']) ?>')" aria-label="Lire la vidéo" style="border:none; cursor:pointer;">
                                    <i class="fas fa-play"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="event-body">
                            <div class="event-meta">
                                <span><i class="far fa-calendar-alt"></i> <?= format_date_fr($event['date_evenement'] ?? $event['created_at']) ?></span>
                                <?php if ($evType === 'video'): ?>
                                    <span style="color:#ef4444; font-weight:600;"><i class="fas fa-play-circle"></i> Vidéo</span>
                                <?php else: ?>
                                    <span style="color:var(--accent); font-weight:600;"><i class="far fa-image"></i> Galerie</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="event-title">
                                <?= htmlspecialchars($event['titre']) ?>
                            </h3>
                            <p class="event-desc">
                                <?= nl2br(htmlspecialchars($event['description'])) ?>
                            </p>
                            
                            <?php if (!empty($event['contenu'])): ?>
                                <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border-color); font-size:0.9rem; color:#475569;">
                                    <?= $event['contenu'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Carte État Vide lorsqu'aucun filtre ne correspond -->
            <div id="eventsEmptyState" style="display:none; text-align:center; padding:4rem 2rem; background:#fff; border-radius:18px; box-shadow:var(--shadow-sm); max-width:600px; margin:2rem auto 0;">
                <i class="fas fa-search" style="font-size:3.5rem; color:#cbd5e1; margin-bottom:1.25rem;"></i>
                <h3 style="margin-bottom:0.5rem; color:var(--primary);">Aucun article correspondant</h3>
                <p class="text-muted" style="margin-bottom:1.5rem;">Aucune publication ne correspond à vos critères de recherche actuels.</p>
                <button type="button" id="resetFiltersBtn" class="btn-primary" style="border:none; cursor:pointer;">
                    <i class="fas fa-rotate-left"></i> Réinitialiser les filtres
                </button>
            </div>

        <?php else: ?>
            <div style="text-align:center; padding:4rem 2rem; background:#fff; border-radius:18px; box-shadow:var(--shadow-sm); max-width:600px; margin:0 auto;">
                <i class="fas fa-folder-open" style="font-size:3.5rem; color:#cbd5e1; margin-bottom:1.25rem;"></i>
                <h3 style="margin-bottom:0.5rem;">Aucune publication pour le moment</h3>
                <p class="text-muted" style="margin-bottom:1.5rem;">De nouvelles activités et actualités seront bientôt publiées.</p>
                <a href="index.php" class="btn-primary">Retour à l'accueil</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- MODAL VIDÉO INTERACTIVE -->
<div id="videoModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.88); z-index:9999; align-items:center; justify-content:center; padding:1.5rem;">
    <div style="position:relative; width:100%; max-width:850px; background:#000; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.6);">
        <button type="button" onclick="closeVideoModal()" style="position:absolute; top:12px; right:12px; z-index:10000; background:rgba(255,255,255,0.25); border:none; color:#fff; width:38px; height:38px; border-radius:50%; cursor:pointer; font-size:1.2rem; display:flex; align-items:center; justify-content:center;" aria-label="Fermer la vidéo">
            <i class="fas fa-times"></i>
        </button>
        <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden;">
            <iframe id="videoIframe" src="" style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;" allowfullscreen allow="autoplay"></iframe>
        </div>
    </div>
</div>

<!-- GESTIONNAIRE JAVASCRIPT DU FILTRAGE SANS AUCUN RECHARGEMENT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterPills = document.querySelectorAll('#categoryFilterGroup .filter-pill');
    const mediaTabs = document.querySelectorAll('.media-tab');
    const searchInput = document.getElementById('eventSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const eventCards = document.querySelectorAll('.event-card');
    const visibleCountSpan = document.getElementById('visibleCount');
    const activeFilterLabel = document.getElementById('activeFilterLabel');
    const emptyState = document.getElementById('eventsEmptyState');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    let currentCategory = <?= json_encode($initialCat) ?>;
    let currentMedia = 'all';
    let currentSearch = '';

    function applyFilters() {
        let visibleCount = 0;

        eventCards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            const cardMedia = card.getAttribute('data-media');
            const cardSearch = card.getAttribute('data-search') || '';

            const matchCat = (currentCategory === 'all' || cardCat.toLowerCase() === currentCategory.toLowerCase());
            const matchMedia = (currentMedia === 'all' || cardMedia === currentMedia);
            const matchSearch = (!currentSearch || cardSearch.includes(currentSearch.toLowerCase()));

            if (matchCat && matchMedia && matchSearch) {
                visibleCount++;
                card.classList.remove('is-hidden');
                // Animation fluide d'apparition
                card.classList.add('is-fading');
                requestAnimationFrame(() => {
                    card.classList.remove('is-fading');
                });
            } else {
                card.classList.add('is-hidden');
            }
        });

        // Mise à jour des compteurs et de l'état vide
        if (visibleCountSpan) {
            visibleCountSpan.textContent = visibleCount;
        }

        if (activeFilterLabel) {
            const catLabel = currentCategory === 'all' ? 'Toutes' : currentCategory;
            activeFilterLabel.innerHTML = 'Catégorie : <strong>' + escapeHtml(catLabel) + '</strong>';
        }

        if (emptyState) {
            emptyState.style.display = (visibleCount === 0 && eventCards.length > 0) ? 'block' : 'none';
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // 1. Clic sur les boutons de catégories
    filterPills.forEach(pill => {
        pill.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            currentCategory = this.getAttribute('data-filter');

            // Mettre à jour l'URL sans recharger la page (très propre & professionnel)
            if (window.history && window.history.replaceState) {
                const newUrl = currentCategory === 'all' 
                    ? window.location.pathname 
                    : window.location.pathname + '?cat=' + encodeURIComponent(currentCategory);
                window.history.replaceState(null, '', newUrl);
            }

            applyFilters();
        });
    });

    // 2. Clic sur les onglets Type de média
    mediaTabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            mediaTabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            currentMedia = this.getAttribute('data-media');
            applyFilters();
        });
    });

    // 3. Recherche instantanée au clavier
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = currentSearch ? 'flex' : 'none';
            }
            applyFilters();
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                currentSearch = '';
                this.style.display = 'none';
                searchInput.focus();
                applyFilters();
            }
        });
    }

    // 4. Bouton de réinitialisation
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            currentCategory = 'all';
            currentMedia = 'all';
            currentSearch = '';

            filterPills.forEach(p => {
                p.classList.toggle('active', p.getAttribute('data-filter') === 'all');
            });

            mediaTabs.forEach(t => {
                const isAll = t.getAttribute('data-media') === 'all';
                t.classList.toggle('active', isAll);
                t.setAttribute('aria-selected', isAll ? 'true' : 'false');
            });

            if (searchInput) {
                searchInput.value = '';
            }
            if (clearSearchBtn) {
                clearSearchBtn.style.display = 'none';
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', window.location.pathname);
            }

            applyFilters();
        });
    }

    // Appliquer le filtre initial si présent
    if (currentCategory !== 'all') {
        applyFilters();
    }
});

// Modal Vidéo
function openVideoModal(url) {
    const modal = document.getElementById('videoModal');
    const iframe = document.getElementById('videoIframe');
    if (modal && iframe) {
        iframe.src = url;
        modal.style.display = 'flex';
    }
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const iframe = document.getElementById('videoIframe');
    if (modal && iframe) {
        iframe.src = '';
        modal.style.display = 'none';
    }
}

// Fermeture au clic à l'extérieur de la vidéo
window.addEventListener('click', function (e) {
    const modal = document.getElementById('videoModal');
    if (modal && e.target === modal) {
        closeVideoModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
