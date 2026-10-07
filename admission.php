<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = "Préinscription en Ligne - ECOB University";
$activeNav = "admission";

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNIÈRE (VRAIE IMAGE PRINCIPALE) -->
<div class="page-banner">
    <img src="<?= asset_url('', 'assets/images/img1.jpg') ?>" alt="Préinscription & Admission ECOB University" class="page-banner-bg">
    <div class="page-banner-overlay"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-user-graduate"></i> Année Académique 2025 - 2026</span>
        <h1>Candidature & Préinscription</h1>
        <p>
            Entrez dans une université bilingue d'excellence. Remplissez le formulaire ci-dessous pour réserver votre place.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
            <span>Admission</span>
        </div>
    </div>
</div>

<!-- SECTION FORMULAIRE & INFORMATIONS -->
<section class="section">
    <div class="container">
        <div class="grid-2" style="align-items:flex-start;">
            <!-- Colonne 1 : Conseils et Pièces à fournir -->
            <div>
                <span class="section-tag">Procédure Simplifiée</span>
                <h2 style="font-size:2.2rem; margin-bottom:1.25rem;">Rejoignez la Prochaine Promotion ECOB</h2>
                <p style="color:var(--text-muted); margin-bottom:1.75rem; font-size:1.05rem;">
                    L'admission à ECOB se fait sur étude de dossier et entretien de motivation. La préinscription en ligne vous permet d'obtenir un rendez-vous prioritaire avec la direction des études.
                </p>

                <div class="feature-card" style="margin-bottom:1.5rem;">
                    <div style="display:flex; gap:1rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:48px; height:48px; font-size:1.25rem; margin-bottom:0; flex-shrink:0;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Pièces à fournir lors de l'entretien</h4>
                            <ul style="color:var(--text-muted); font-size:0.9rem; list-style:disc; margin-left:1.2rem; line-height:1.7;">
                                <li>2 Copies certifiées conformes de l'Attestation ou Diplôme (DEF, BAC ou Licence).</li>
                                <li>2 Extraits d'acte de naissance récents.</li>
                                <li>4 Photos d'identité récentes en couleur (fond blanc).</li>
                                <li>1 Copie de la pièce d'identité (Carte NINA, Passeport ou Carte d'identité).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="feature-card">
                    <div style="display:flex; gap:1rem; align-items:flex-start;">
                        <div class="feature-icon-wrapper" style="width:48px; height:48px; font-size:1.25rem; margin-bottom:0; flex-shrink:0;">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem; margin-bottom:0.35rem;">Besoin d'orientation personnalisée ?</h4>
                            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:0.75rem;">
                                Nos conseillers pédagogiques sont disponibles du lundi au samedi sur le campus de Sotuba ACI.
                            </p>
                            <a href="tel:<?= preg_replace('/\s+/', '', get_setting('site_phone1', '+22367666637')) ?>" style="color:var(--primary); font-weight:700; font-size:0.95rem;">
                                <i class="fas fa-phone-alt" style="color:var(--accent);"></i> <?= htmlspecialchars(get_setting('site_phone1', '+223 67 66 66 37')) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne 2 : Formulaire de préinscription connecté -->
            <div class="form-card" style="margin:0; max-width:100%;">
                <div style="margin-bottom:1.75rem; text-align:center;">
                    <h3 style="font-size:1.65rem; color:var(--primary);">Formulaire de Préinscription</h3>
                    <p style="color:var(--text-muted); font-size:0.92rem;">Remplissez vos coordonnées pour valider votre dossier.</p>
                </div>

                <?php if (isset($_SESSION['admission_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                        <div><?= htmlspecialchars((string)$_SESSION['admission_success']); unset($_SESSION['admission_success']); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admission_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle" style="font-size:1.2rem;"></i>
                        <div><?= htmlspecialchars((string)$_SESSION['admission_error']); unset($_SESSION['admission_error']); ?></div>
                    </div>
                <?php endif; ?>

                <form action="process-admission.php" method="POST">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="nom">Nom <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: COULIBALY" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="prenom">Prénom(s) <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Ex: Moussa" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="email">Adresse Email <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="exemple@gmail.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Numéro de Téléphone (WhatsApp) <span style="color:#ef4444;">*</span></label>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="Ex: +223 70 00 00 00" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="nationalite">Nationalité <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="nationalite" id="nationalite" class="form-control" placeholder="Ex: Malienne" value="Malienne" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="dte">Date de Naissance <span style="color:#ef4444;">*</span></label>
                            <input type="date" name="dte" id="dte" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="niveau_etude">Niveau d'étude actuel <span style="color:#ef4444;">*</span></label>
                            <select name="niveau_etude" id="niveau_etude" class="form-control" required>
                                <option value="">-- Sélectionnez votre niveau --</option>
                                <option value="Bachelier / Terminale">Nouveau Bachelier / En classe de Terminale</option>
                                <option value="Bac + 1">Bac + 1 validé</option>
                                <option value="Bac + 2 (DUT, DEUG, BTS)">Bac + 2 (DUT, DEUG, BTS)</option>
                                <option value="Licence (Bac + 3)">Licence Professionnelle (Bac + 3)</option>
                                <option value="Professionnel en activité">Professionnel en activité / Reconversion</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="formation">Formation souhaitée <span style="color:#ef4444;">*</span></label>
                            <select name="formation" id="formation" class="form-control" required>
                                <option value="">-- Choisissez une filière --</option>
                                <optgroup label="Sciences de Gestion">
                                    <option value="Gestion des Entreprises et Administrations">Gestion des Entreprises et Administrations</option>
                                    <option value="Gestion des Ressources Humaines">Gestion des Ressources Humaines</option>
                                    <option value="Finance, Banque & Assurance">Finance, Banque & Assurance</option>
                                    <option value="Comptabilité & Gestion Financière">Comptabilité & Gestion Financière</option>
                                    <option value="Marketing & Commerce International">Marketing & Commerce International</option>
                                    <option value="Logistique & Transport">Logistique & Transport</option>
                                </optgroup>
                                <optgroup label="Agro-Business">
                                    <option value="Agro-business & Management Agricole">Agro-business & Management Agricole</option>
                                    <option value="Hydro-agricole">Hydro-agricole</option>
                                </optgroup>
                                <optgroup label="Mines & Géologie">
                                    <option value="Mines & Exploitation Minière">Mines & Exploitation Minière</option>
                                    <option value="Géologie Appliquée">Géologie Appliquée</option>
                                    <option value="Hydrogéologie">Hydrogéologie</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; margin-top:1rem; padding:1rem; font-size:1.05rem;">
                        <i class="fas fa-paper-plane"></i> ENVOYER MA PRÉINSCRIPTION
                    </button>
                    <p style="text-align:center; color:#94a3b8; font-size:0.82rem; margin-top:0.75rem;">
                        Vos données personnelles sont strictement confidentielles et utilisées uniquement par le service scolarité d'ECOB.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>