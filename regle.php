<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$activeNav = "reglement";

$pageData = null;
try {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM pages_contenu WHERE slug = 'reglement'");
    $stmt->execute();
    $pageData = $stmt->fetch();
} catch (Exception $e) {
    error_log("Failed to fetch page reglement: " . $e->getMessage());
}

$pageTitle = ($pageData['titre'] ?? "Règlement Intérieur & Charte d'Éthique") . " - ECOB University";
$badgeText = $pageData['badge'] ?? "Vie Universitaire";
$sousTitre = $pageData['sous_titre'] ?? "Les règles de vie commune garantissant un climat d'études propice à l'excellence, au respect mutuel et à l'épanouissement intellectuel.";
$mainContent = $pageData['contenu_principal'] ?? "";

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE DYNAMIQUE ULTRA-STYILISÉE AVEC VRAIE IMAGE PRINCIPALE -->
<div class="page-banner">
    <img src="<?= asset_url($pageData['image_url'] ?? '', 'assets/images/sur.jpg') ?>" alt="Règlement Intérieur - ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge bouncy-badge"><i class="fas fa-balance-scale"></i> <?= htmlspecialchars($badgeText) ?></span>
        <h1 class="bounce-in-title"><?= htmlspecialchars($pageData['titre'] ?? 'Règlement Intérieur & Charte d\'Éthique') ?></h1>
        <p><?= htmlspecialchars($sousTitre) ?></p>
        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <a href="presentation.php">Information</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Règlement Intérieur</span>
        </div>
    </div>
</div>

<!-- SECTION RÈGLEMENT INTÉRIEUR COMPLET -->
<section class="section reglement-page-section">
    <div class="container" style="max-width:1080px;">

        <!-- EN-TÊTE DU CODE DE CONDUITE -->
        <div class="reglement-header-card bouncy-hover">
            <div class="reglement-header-left">
                <div class="reglement-icon-box pulse-glow">
                    <i class="fas fa-gavel"></i>
                </div>
                <div>
                    <span class="reglement-mini-badge"><i class="fas fa-shield-alt"></i> DOCUMENT OFFICIEL ECOB</span>
                    <h2>Code de Conduite & Règlement Intérieur Général</h2>
                    <p>Applicable à l'ensemble des étudiants, auditeurs, promotions et partenaires d'ECOB University</p>
                </div>
            </div>
            <div class="reglement-header-right">
                <a href="#der-banner" class="btn-reglement-quick">
                    <i class="fas fa-file-signature"></i> Je m'engage
                </a>
            </div>
        </div>

        <!-- SOMMAIRE RAPIDE INTERACTIF -->
        <div class="reglement-nav-chips">
            <a href="#chap1" class="chip-item"><i class="fas fa-landmark"></i> Chapitre 1 : Dispositions</a>
            <a href="#chap2" class="chip-item"><i class="fas fa-user-check"></i> Chapitre 2 : Admission</a>
            <a href="#chap3" class="chip-item"><i class="fas fa-graduation-cap"></i> Chapitre 3 : Vie Académique</a>
            <a href="#chap4" class="chip-item"><i class="fas fa-shield-alt"></i> Chapitre 4 : Discipline</a>
            <a href="#chap5" class="chip-item"><i class="fas fa-users"></i> Chapitre 5 : Campus</a>
            <a href="#chap6" class="chip-item"><i class="fas fa-gavel"></i> Chapitre 6 : Sanctions</a>
            <a href="#annexes" class="chip-item"><i class="fas fa-scroll"></i> Annexes</a>
        </div>

        <!-- CONTENU DES ARTICLES DU RÈGLEMENT -->
        <div class="reglement-content-card">
            <div class="page-dynamic-content reglement-full-articles">
                <?php if (!empty($mainContent) && strpos($mainContent, 'Chapitre') !== false): ?>
                    <?= $mainContent ?>
                <?php else: ?>

                    <!-- CHAPITRE 1 -->
                    <div class="chapitre" id="chap1">
                        <h4><i class="fas fa-landmark"></i> Chapitre 1 : Dispositions Générales</h4>
                        <div class="article">
                            <h5>Article 1 : Objet et Champ d'Application</h5>
                            <p>Le présent règlement intérieur a pour objet de définir les règles de vie et de fonctionnement au sein de l'Université ECOB, École de Business Bilingue Mali. Il s'applique à l'ensemble des étudiants, enseignants, et personnel administratif.</p>
                        </div>
                        <div class="article">
                            <h5>Article 2 : Valeurs et Principes</h5>
                            <p>L'Université ECOB promeut les valeurs de respect, d'intégrité, de responsabilité et de diversité. Les étudiants sont tenus de respecter ces valeurs dans toutes leurs activités académiques et extrascolaires.</p>
                        </div>
                    </div>

                    <!-- CHAPITRE 2 -->
                    <div class="chapitre" id="chap2">
                        <h4><i class="fas fa-user-check"></i> Chapitre 2 : Admission et Inscription</h4>
                        <div class="article">
                            <h5>Article 3 : Conditions d'Admission</h5>
                            <p>L'admission à l'Université ECOB est conditionnée par la soumission des documents requis et le paiement des frais de scolarité selon les modalités fixées par l'administration.</p>
                        </div>
                        <div class="article">
                            <h5>Article 4 : Inscription Administrative et Pédagogique</h5>
                            <p>Les étudiants doivent compléter leur inscription administrative et pédagogique à chaque début de semestre. L'inscription est validée après le paiement des frais de scolarité et donne droit à la carte d'étudiant.</p>
                        </div>
                    </div>

                    <!-- CHAPITRE 3 -->
                    <div class="chapitre" id="chap3">
                        <h4><i class="fas fa-graduation-cap"></i> Chapitre 3 : Vie Académique</h4>
                        <div class="article">
                            <h5>Article 5 : Assiduité et Ponctualité</h5>
                            <p>Les étudiants doivent assister régulièrement et ponctuellement aux cours, travaux dirigés (TD) et pratiques (TP). Tout retard supérieur à 15 minutes entraîne le refus d'accès. Toute absence doit être justifiée par un document officiel sous 48h.</p>
                        </div>
                        <div class="article">
                            <h5>Article 6 : Évaluations et Examens</h5>
                            <p>Les évaluations se déroulent conformément au calendrier académique officiel. Toute fraude ou tentative de fraude lors des examens entraînera des sanctions disciplinaires sévères, pouvant aller jusqu'à l'exclusion définitive.</p>
                        </div>
                    </div>

                    <!-- CHAPITRE 4 -->
                    <div class="chapitre" id="chap4">
                        <h4><i class="fas fa-shield-alt"></i> Chapitre 4 : Comportement et Discipline</h4>
                        <div class="article">
                            <h5>Article 7 : Respect et Comportement</h5>
                            <p>Les étudiants doivent faire preuve de respect envers leurs camarades, les enseignants et le personnel administratif. Toute forme de harcèlement, discrimination ou violence verbale ou physique est strictement interdite.</p>
                        </div>
                        <div class="article">
                            <h5>Article 8 : Utilisation des Installations</h5>
                            <p>Les installations de l'université (amphithéâtres, salles informatiques, bibliothèque) doivent être utilisées de manière responsable. Toute dégradation volontaire des biens de l'université sera sanctionnée financièrement et disciplinairement.</p>
                        </div>
                    </div>

                    <!-- CHAPITRE 5 -->
                    <div class="chapitre" id="chap5">
                        <h4><i class="fas fa-users"></i> Chapitre 5 : Activités Extrascolaires</h4>
                        <div class="article">
                            <h5>Article 9 : Clubs et Associations</h5>
                            <p>Les étudiants sont encouragés à participer aux clubs et associations de l'université (Club d'Anglais, Club Débat, Bureau des Étudiants). Ces activités doivent être en conformité avec les valeurs de l'Université ECOB.</p>
                        </div>
                        <div class="article">
                            <h5>Article 10 : Événements et Manifestations</h5>
                            <p>L'organisation d'événements et de manifestations sur le campus doit être approuvée préalablement par l'administration. Les activités doivent impérativement respecter les règles de sécurité et de quiétude du campus.</p>
                        </div>
                    </div>

                    <!-- CHAPITRE 6 -->
                    <div class="chapitre" id="chap6">
                        <h4><i class="fas fa-gavel"></i> Chapitre 6 : Dispositions Finales</h4>
                        <div class="article">
                            <h5>Article 11 : Sanctions Disciplinaires</h5>
                            <p>Toute infraction au présent règlement peut entraîner des sanctions disciplinaires : avertissement, blâme avec mention au dossier, suspension temporaire, ou exclusion définitive prononcée par le Conseil de Discipline.</p>
                        </div>
                        <div class="article">
                            <h5>Article 12 : Modification du Règlement</h5>
                            <p>Le présent règlement peut être modifié par l'administration de l'université. Toute modification sera communiquée aux étudiants et affichée sur les panneaux d'information officiels et le site web de l'université.</p>
                        </div>
                    </div>

                    <!-- ANNEXES -->
                    <div class="annexes" id="annexes">
                        <h4><i class="fas fa-scroll"></i> Annexes Officielles</h4>
                        <div class="annexe">
                            <h5>Annexe 1 : Charte de l'Étudiant</h5>
                            <p>L'Université ECOB met en avant une charte de l'étudiant, rappelant les droits et devoirs des étudiants pour assurer une cohabitation harmonieuse, stimulante et productive sur le campus.</p>
                        </div>
                        <div class="annexe">
                            <h5>Annexe 2 : Procédure de Plainte et de Médiation</h5>
                            <p>Les étudiants peuvent déposer une plainte en cas de problème non résolu par les moyens habituels. Une procédure de médiation est mise en place pour traiter les différends de manière équitable et transparente.</p>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION ICONIQUE : BANNIÈRE D'ENGAGEMENT AVEC BOUTON ANIMÉ ET IMAGE ORIGINALE -->
        <div class="der bouncy-card" id="der-banner">
            <div class="der2">
                <img src="<?= asset_url('assets/images/sur.jpg', 'assets/images/img4.jpg') ?>" alt="Étudiants ECOB University Excellence">
            </div>
            <div class="der1">
                <div class="der-badge"><i class="fas fa-star"></i> DEVENIR ÉTUDIANT ECOB</div>
                <h3 class="der-title">ECOB « La marche vers l'excellence »</h3>
                <p class="clo">Rejoignez une communauté universitaire d'élite et préparez un avenir d'envergure internationale.</p>

                <!-- Bouton animé culte avec rebond & cercle expansif -->
                <a href="admission.php" class="animated-button">
                    <svg viewBox="0 0 24 24" class="arr-2" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"></path>
                    </svg>
                    <span class="text">Préinscrivez-vous ici...</span>
                    <span class="circle"></span>
                    <svg viewBox="0 0 24 24" class="arr-1" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"></path>
                    </svg>
                </a>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
