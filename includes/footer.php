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
?>

<!-- BANNIÈRE PRÉINSCRIPTION RAPIDE (AVEC VRAIE IMAGE PRINCIPALE DU CAMPUS) -->
<section class="footer-cta-banner">
    <img src="<?= asset_url('', 'assets/images/img2.jpg') ?>" alt="Campus ECOB University" class="footer-cta-bg">
    <div class="footer-cta-overlay"></div>
    <div class="container footer-cta-content">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <span class="footer-cta-tag"><i class="fas fa-graduation-cap"></i> Rejoignez l'élite bilingue</span>
                <h3 style="color:#fff; font-size:1.95rem; margin-top:0.35rem; font-weight:800;">Préparez votre avenir académique et professionnel dès aujourd'hui</h3>
            </div>
            <div>
                <a href="admission.php" class="btn-primary" style="padding:1rem 2.2rem; font-size:1.05rem;">
                    <i class="fas fa-file-signature"></i> ME PRÉINSCRIRE MAINTENANT
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER PRINCIPAL -->
<footer class="site-footer">
    <div class="container">
        <!-- BANDEAU COORDONNÉES OFFICIELLES, LIEU & RÉSEAUX EN PIED DE PAGE -->
        <div class="footer-info-bar">
            <div class="footer-info-items">
                <div class="footer-info-item">
                    <div class="footer-info-icon"><i class="fa-regular fa-envelope"></i></div>
                    <div>
                        <span class="footer-info-label">Email Officiel</span>
                        <a href="mailto:<?= htmlspecialchars($siteEmail) ?>"><?= htmlspecialchars($siteEmail) ?></a>
                    </div>
                </div>
                <div class="footer-info-item">
                    <div class="footer-info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <span class="footer-info-label">Lignes Directes</span>
                        <a href="tel:<?= preg_replace('/\s+/', '', $sitePhone1) ?>"><?= htmlspecialchars($sitePhone1) ?></a>
                        <span style="color:rgba(255,255,255,0.4); margin:0 4px;">/</span>
                        <a href="tel:<?= preg_replace('/\s+/', '', $sitePhone2) ?>"><?= htmlspecialchars($sitePhone2) ?></a>
                    </div>
                </div>
                <div class="footer-info-item">
                    <div class="footer-info-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <span class="footer-info-label">Localisation</span>
                        <span><?= htmlspecialchars($siteAdresse) ?></span>
                    </div>
                </div>
            </div>
            <div class="footer-info-actions">
                <div class="footer-socials">
                    <a href="<?= htmlspecialchars($facebookUrl) ?>" target="_blank" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="<?= htmlspecialchars($instagramUrl) ?>" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsappNum) ?>" target="_blank" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-grid">
            <!-- Col 1: À propos -->
            <div class="footer-col footer-about">
                <div class="brand-logo" style="margin-bottom:1.25rem; display:flex; align-items:center; gap:0.85rem;">
                    <img src="<?= site_logo_url() ?>" alt="Logo ECOB" style="height:62px; width:auto; background:#ffffff; padding:4px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.25);">
                    <div class="brand-text-wrap">
                        <span class="brand-main-name" style="color:#ffffff; font-size:1.4rem; font-weight:800; display:block; line-height:1.1;">ECOB</span>
                        <span class="brand-sub-name" style="color:var(--accent-gold); font-size:0.75rem; letter-spacing:0.8px; font-weight:700;">UNIVERSITÉ BILINGUE</span>
                    </div>
                </div>
                <p>
                    École de Business Bilingue de référence à Bamako. Formations professionnelles d'excellence en Gestion d'Entreprise, Agro-Business et Mines & Géologie avec un accompagnement pédagogique personnalisé.
                </p>
            </div>

            <!-- Col 2: Informations Universitaires -->
            <div class="footer-col">
                <h4>Informations</h4>
                <ul class="footer-links">
                    <li><a href="presentation.php"><i class="fas fa-university"></i> Présentation Générale</a></li>
                    <li><a href="organisa.php"><i class="fas fa-sitemap"></i> Organisation & Gouvernance</a></li>
                    <li><a href="regle.php"><i class="fas fa-balance-scale"></i> Règlement Intérieur</a></li>
                    <li><a href="a-propos.php"><i class="fas fa-landmark"></i> À Propos de Nous</a></li>
                    <li><a href="admission.php"><i class="fas fa-user-graduate"></i> Modalités d'Admission</a></li>
                    <li><a href="contact.php"><i class="fas fa-map-marker-alt"></i> Contact & Accès Campus</a></li>
                </ul>
            </div>

            <!-- Col 3: Nos Formations -->
            <div class="footer-col">
                <h4>Nos Filières</h4>
                <ul class="footer-links">
                    <li><a href="gestion.php"><i class="fas fa-angle-right"></i> Sciences de Gestion</a></li>
                    <li><a href="agro.php"><i class="fas fa-angle-right"></i> Agro-Business</a></li>
                    <li><a href="mine.php"><i class="fas fa-angle-right"></i> Mines & Géologie</a></li>
                    <li><a href="evenements.php"><i class="fas fa-angle-right"></i> Vie du Campus & Médias</a></li>
                    <li><a href="admission.php"><i class="fas fa-angle-right"></i> Préinscription en Ligne</a></li>
                </ul>
            </div>

            <!-- Col 4: Laisser un message / Commentaire rapide -->
            <div class="footer-col">
                <h4>Nous Écrire</h4>
                <form action="process-comment.php" method="POST">
                    <?php if (isset($_SESSION['comment_success'])): ?>
                        <div style="background:#ecfdf5;color:#065f46;padding:8px 12px;border-radius:6px;font-size:0.85rem;margin-bottom:10px;">
                            <?= htmlspecialchars((string)$_SESSION['comment_success']); unset($_SESSION['comment_success']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['comment_error'])): ?>
                        <div style="background:#fef2f2;color:#991b1b;padding:8px 12px;border-radius:6px;font-size:0.85rem;margin-bottom:10px;">
                            <?= htmlspecialchars((string)$_SESSION['comment_error']); unset($_SESSION['comment_error']); ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom:10px;">
                        <input type="text" name="nom" class="form-control" placeholder="Votre nom" required style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.15);color:#fff;padding:0.65rem 0.95rem;font-size:0.88rem;">
                    </div>
                    <div style="margin-bottom:10px;">
                        <input type="email" name="email" class="form-control" placeholder="Votre email" required style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.15);color:#fff;padding:0.65rem 0.95rem;font-size:0.88rem;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <textarea name="texte" class="form-control" placeholder="Votre avis ou question..." required style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.15);color:#fff;padding:0.65rem 0.95rem;font-size:0.88rem;min-height:75px;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.65rem;font-size:0.9rem;">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- BARRE DU BAS -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= htmlspecialchars($siteName) ?></strong>. Tous droits réservés.
                </div>
                <div style="display:flex;gap:1.5rem;align-items:center;">
                    <span><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> <?= htmlspecialchars($siteAdresse) ?></span>
                    <a href="admin/login.php" class="discreet-admin-link" title="Administration">
                        <i class="fas fa-lock" style="font-size:0.7rem;"></i> Administration
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- SCRIPT GLOBAL -->
<script src="assets/js/main.js"></script>
</body>
</html>
