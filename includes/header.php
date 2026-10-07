<?php
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/db.php';
}

$siteName = get_setting('site_nom', 'ECOB University');
$siteEmail = get_setting('site_email', 'ecob@gmail.com');
$sitePhone1 = get_setting('site_phone1', '+223 67 66 66 37');
$sitePhone2 = get_setting('site_phone2', '+223 77 66 66 37');
$siteAdresse = get_setting('site_adresse', 'Sotuba ACI, Bamako - Mali');
$facebookUrl = get_setting('facebook_url', 'https://www.facebook.com/ecob.debamako');
$instagramUrl = get_setting('instagram_url', 'https://www.instagram.com/invites/contact/?i=11phrapkdhqpj');
$whatsappNum = get_setting('whatsapp_num', '22367666637');
$linkedinUrl = get_setting('linkedin_url', '#');

if (!isset($pageTitle)) {
    $pageTitle = 'Accueil - ' . $siteName;
}
if (!isset($activeNav)) {
    $activeNav = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="Université ECOB - École de Business Bilingue à Bamako. Formations en Gestion, Agro-Business et Mines & Géologie.">
    <link rel="icon" type="image/png" href="<?= site_logo_url() ?>">
    <!-- Font Awesome Icons & BoxIcons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Main Modern Design Stylesheet -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- HEADER & NAVIGATION -->
<header class="site-header">
    <div class="container">
        <div class="nav-container">
            <a href="index.php" class="brand-logo" title="Université Bilingue ECOB">
                <img src="<?= site_logo_url() ?>" alt="Logo Officiel ECOB" class="site-header-logo">
                <div class="brand-text-wrap">
                    <span class="brand-main-name">ECOB</span>
                    <span class="brand-sub-name">UNIVERSITÉ BILINGUE</span>
                </div>
            </a>

            <button type="button" class="mobile-toggle" id="mobileMenuBtn" aria-label="Menu de navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Backdrop pour le menu mobile -->
            <div class="nav-backdrop" id="navBackdrop"></div>

            <nav class="main-nav" id="mainNav">
                <div class="mobile-drawer-header">
                    <div class="brand-logo">
                        <img src="<?= site_logo_url() ?>" alt="Logo Officiel ECOB" class="site-header-logo">
                        <div class="brand-text-wrap">
                            <span class="brand-main-name">ECOB</span>
                            <span class="brand-sub-name">UNIVERSITÉ BILINGUE</span>
                        </div>
                    </div>
                    <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="Fermer le menu">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?= $activeNav === 'accueil' ? 'active' : '' ?>">
                            <i class="fas fa-home mobile-only-icon"></i> ACCUEIL
                        </a>
                    </li>
                    <li class="nav-item dropdown-parent">
                        <a href="javascript:void(0);" class="nav-link dropdown-toggle <?= in_array($activeNav, ['presentation', 'organisation', 'reglement']) ? 'active' : '' ?>" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-info-circle mobile-only-icon"></i> INFORMATION <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="presentation.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Présentation Générale</a>
                            <a href="organisa.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Organisation & Gouvernance</a>
                            <a href="regle.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Règlement Intérieur</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown-parent">
                        <a href="javascript:void(0);" class="nav-link dropdown-toggle <?= in_array($activeNav, ['gestion', 'agro', 'mine']) ? 'active' : '' ?>" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-graduation-cap mobile-only-icon"></i> FORMATIONS <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="gestion.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Sciences de Gestion</a>
                            <a href="agro.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Agro-Business</a>
                            <a href="mine.php" class="dropdown-item"><i class="fas fa-angle-right"></i> Mines & Géologie</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="evenements.php" class="nav-link <?= $activeNav === 'evenements' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-check mobile-only-icon"></i> ACTIVITÉS & ÉVÉNEMENTS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="a-propos.php" class="nav-link <?= $activeNav === 'a-propos' ? 'active' : '' ?>">
                            <i class="fas fa-landmark mobile-only-icon"></i> À PROPOS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link <?= $activeNav === 'contact' ? 'active' : '' ?>">
                            <i class="fas fa-phone mobile-only-icon"></i> CONTACT
                        </a>
                    </li>
                    <li class="nav-item mobile-only-item" style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.08);">
                        <a href="admin/login.php" class="discreet-drawer-admin" title="Administration">
                            <i class="fas fa-lock" style="font-size:0.75rem;"></i> Administration
                        </a>
                    </li>
                </ul>

                <a href="admission.php" class="nav-cta-btn" title="Préinscription en Ligne">
                    <i class="fas fa-user-graduate"></i> PRÉINSCRIPTION
                </a>
            </nav>
        </div>
    </div>
</header>
