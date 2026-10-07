<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = "Contact & Localisation - ECOB University";
$activeNav = "contact";

$siteEmail = get_setting('site_email', 'ecob@gmail.com');
$sitePhone1 = get_setting('site_phone1', '+223 67 66 66 37');
$sitePhone2 = get_setting('site_phone2', '+223 77 66 66 37');
$siteAdresse = get_setting('site_adresse', 'Sotuba ACI, Bamako - Mali');
$whatsappNum = get_setting('whatsapp_num', '22367666637');

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url('', 'assets/images/photo.jpg') ?>" alt="Contact & Campus ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-envelope-open-text"></i> Restons en Contact</span>
        <h1>Nous Contacter</h1>
        <p>
            Vous avez une question sur nos formations, les frais de scolarité ou les inscriptions ? Notre équipe est à votre entière écoute.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Contact</span>
        </div>
    </div>
</div>

<!-- SECTION CONTACT & INFOS -->
<section class="section">
    <div class="container">
        <div class="grid-2" style="align-items:flex-start;">
            <!-- Colonne 1 : Coordonnées et Horaires -->
            <div>
                <span class="section-tag">Coordonnées Officielles</span>
                <h2 style="font-size:2.2rem; margin-bottom:1.5rem;">Prenez Contact avec ECOB</h2>

                <div class="feature-card" style="margin-bottom:1.5rem;">
                    <div style="display:flex; gap:1.2rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:52px; height:52px; margin-bottom:0; flex-shrink:0;">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Campus Principal</h4>
                            <p style="color:var(--text-muted); font-size:0.95rem;"><?= htmlspecialchars($siteAdresse) ?></p>
                            <p style="color:var(--text-light); font-size:0.85rem; margin-top:0.25rem;">À proximité du fleuve Niger, zone résidentielle et sécurisée.</p>
                        </div>
                    </div>
                </div>

                <div class="feature-card" style="margin-bottom:1.5rem;">
                    <div style="display:flex; gap:1.2rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:52px; height:52px; margin-bottom:0; flex-shrink:0;">
                            <i class="fas fa-phone-volume"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Téléphone & WhatsApp</h4>
                            <p style="font-size:0.95rem; margin-bottom:0.25rem;">
                                <a href="tel:<?= preg_replace('/\s+/', '', $sitePhone1) ?>" style="color:var(--primary); font-weight:700;">
                                    <?= htmlspecialchars($sitePhone1) ?>
                                </a>
                                <span> / </span>
                                <a href="tel:<?= preg_replace('/\s+/', '', $sitePhone2) ?>" style="color:var(--primary); font-weight:700;">
                                    <?= htmlspecialchars($sitePhone2) ?>
                                </a>
                            </p>
                            <a href="https://wa.me/<?= htmlspecialchars($whatsappNum) ?>" target="_blank" class="program-link" style="color:#10b981;">
                                <i class="fab fa-whatsapp"></i> Échanger sur WhatsApp <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="feature-card" style="margin-bottom:1.5rem;">
                    <div style="display:flex; gap:1.2rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:52px; height:52px; margin-bottom:0; flex-shrink:0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Courrier Électronique</h4>
                            <p style="font-size:0.95rem;">
                                <a href="mailto:<?= htmlspecialchars($siteEmail) ?>" style="color:var(--primary); font-weight:600;">
                                    <?= htmlspecialchars($siteEmail) ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="feature-card">
                    <div style="display:flex; gap:1.2rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:52px; height:52px; margin-bottom:0; flex-shrink:0;">
                            <i class="far fa-clock"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Heures d'Ouverture du Secrétariat</h4>
                            <p style="color:var(--text-muted); font-size:0.9rem;">Du Lundi au Vendredi : 08h00 – 17h00</p>
                            <p style="color:var(--text-muted); font-size:0.9rem;">Samedi : 08h30 – 13h00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne 2 : Formulaire de contact -->
            <div class="form-card" style="margin:0; max-width:100%;">
                <div style="margin-bottom:1.75rem;">
                    <h3 style="font-size:1.65rem; color:var(--primary); margin-bottom:0.35rem;">Envoyez-nous un Message</h3>
                    <p style="color:var(--text-muted); font-size:0.92rem;">Remplissez ce formulaire et notre équipe administrative vous répondra sous 24h.</p>
                </div>

                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                        <div><?= htmlspecialchars((string)$_SESSION['contact_success']); unset($_SESSION['contact_success']); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle" style="font-size:1.2rem;"></i>
                        <div><?= htmlspecialchars((string)$_SESSION['contact_error']); unset($_SESSION['contact_error']); ?></div>
                    </div>
                <?php endif; ?>

                <form action="process-contact.php" method="POST">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="nom">Nom complet <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="nom" id="nom" class="form-control" placeholder="Votre nom et prénom" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Adresse Email <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="exemple@domaine.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="adresse">Votre Ville / Adresse (Optionnel)</label>
                        <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Ex: Bamako, Kalaban-Coro...">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mesage">Votre Message <span style="color:#ef4444;">*</span></label>
                        <textarea name="mesage" id="mesage" class="form-control" placeholder="Écrivez-nous votre question ou demande d'information détaillée..." required style="min-height:140px;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:0.95rem; font-size:1rem;">
                        <i class="fas fa-paper-plane"></i> ENVOYER MON MESSAGE
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
