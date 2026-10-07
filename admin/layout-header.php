<?php
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = get_db();
$adminNom = $_SESSION['admin_nom'] ?? 'Administrateur';
$currentAdminPage = basename($_SERVER['PHP_SELF']);

// Compteurs de notifications en direct
$cntPendingAdmis = 0;
$cntContacts = 0;
$cntEmailsPending = 0;
try {
    $cntPendingAdmis = $db->query("SELECT COUNT(*) FROM admis WHERE statut = 'En attente'")->fetchColumn();
    $cntContacts = $db->query("SELECT COUNT(*) FROM contact")->fetchColumn();
    require_once __DIR__ . '/../config/mailer.php';
    ensure_mail_schema();
    $cntEmailsPending = (int)$db->query("SELECT COUNT(*) FROM emails_notifications WHERE statut_envoi <> 'envoye'")->fetchColumn();
} catch (Throwable $e) {
    // Silencieux
}
$adminLogo = site_logo_url('../');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Administration' ?> - ECOB University</title>
    <link rel="icon" type="image/x-icon" href="<?= $adminLogo ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            display: flex;
            min-height: 100vh;
        }
        /* SIDEBAR */
        .admin-sidebar {
            width: 270px;
            background: #09122c;
            color: #cbd5e1;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            box-shadow: 4px 0 16px rgba(0,0,0,0.15);
        }
        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand-mark {
            width: 40px; height: 40px; background: #f59e0b; color: #09122c;
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem; font-family: 'Outfit', sans-serif;
        }
        .sidebar-brand-title {
            color: #fff; font-weight: 700; font-size: 1.15rem; font-family: 'Outfit', sans-serif;
        }
        .sidebar-brand-title span { color: #f59e0b; }
        .sidebar-menu {
            padding: 1.25rem 0.8rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .sidebar-item a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .sidebar-item-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar-item a:hover, .sidebar-item a.active {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }
        .sidebar-item a.active {
            background: #f59e0b;
            color: #09122c;
        }
        .sidebar-item a i { font-size: 1.1rem; width: 22px; text-align: center; }
        .nav-count-badge {
            background: #ef4444; color: #fff; font-size: 0.72rem; padding: 0.15rem 0.5rem;
            border-radius: 9999px; font-weight: 800;
        }
        .sidebar-item a.active .nav-count-badge {
            background: #09122c; color: #fff;
        }
        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-info { display: flex; align-items: center; gap: 0.65rem; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: #162a5a;
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.9rem;
        }
        .user-name { font-size: 0.85rem; color: #fff; font-weight: 600; }
        .logout-btn {
            color: #ef4444; text-decoration: none; font-size: 1.1rem; padding: 0.35rem;
            transition: color 0.2s;
        }
        .logout-btn:hover { color: #f87171; }

        /* MAIN CONTENT */
        .admin-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .admin-topbar {
            background: #fff;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-topbar h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #09122c;
            font-family: 'Outfit', sans-serif;
        }
        .admin-body {
            padding: 2rem;
            flex-grow: 1;
        }

        /* CARDS & TABLES */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-val { font-size: 2.1rem; font-weight: 800; color: #09122c; line-height: 1; margin-bottom: 0.35rem; font-family:'Outfit',sans-serif; }
        .stat-label { font-size: 0.82rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        }
        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-top: 1.5rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }
        th {
            background: #f8fafc;
            color: #475569;
            padding: 0.85rem 1rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }
        tr:hover td { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-edit:hover { background: #bae6fd; }
        .btn-del { background: #fee2e2; color: #b91c1c; }
        .btn-del:hover { background: #fecaca; }
        .btn-add {
            background: #09122c;
            color: #fff;
            padding: 0.65rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        .btn-add:hover { background: #152759; }

        @media (max-width: 900px) {
            body { flex-direction: column; }
            .admin-sidebar { width: 100%; }
            .sidebar-menu { flex-direction: row; flex-wrap: wrap; }
            .admin-body { padding: 1.25rem; }
            .admin-topbar { padding: 1rem 1.25rem; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="admin-sidebar">
    <div class="sidebar-brand" style="display:flex; align-items:center; gap:0.75rem; padding:1.25rem 1.5rem;">
        <img src="<?= $adminLogo ?>" alt="Logo ECOB" style="height:48px; width:auto; background:#ffffff; padding:3px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
        <div class="sidebar-brand-title">
            ECOB <span>Admin</span>
            <div style="font-size:0.68rem; color:#94a3b8; font-weight:500;">Université Bilingue</div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="index.php" class="<?= $currentAdminPage === 'index.php' ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-chart-pie"></i> <span>Tableau de Bord</span>
                </div>
            </a>
        </li>
        <?php if (admin_has_permission('admissions')): ?>
        <li class="sidebar-item">
            <a href="admissions.php" class="<?= $currentAdminPage === 'admissions.php' ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-user-graduate"></i> <span>Préinscriptions</span>
                </div>
                <?php if ($cntPendingAdmis > 0): ?>
                    <span class="nav-count-badge" title="<?= $cntPendingAdmis ?> nouvelle(s) demande(s) en attente"><?= $cntPendingAdmis ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('filieres')): ?>
        <li class="sidebar-item">
            <a href="filieres.php" class="<?= in_array($currentAdminPage, ['filieres.php', 'filiere-form.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-graduation-cap"></i> <span>Filières & Formations</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('equipe')): ?>
        <li class="sidebar-item">
            <a href="equipe.php" class="<?= in_array($currentAdminPage, ['equipe.php', 'equipe-form.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-users-cog"></i> <span>Équipe Dirigeante</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('pages')): ?>
        <li class="sidebar-item">
            <a href="pages.php" class="<?= in_array($currentAdminPage, ['pages.php', 'page-edit.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-file-alt"></i> <span>Pages du Site</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('events')): ?>
        <li class="sidebar-item">
            <a href="evenements.php" class="<?= in_array($currentAdminPage, ['evenements.php', 'evenement-form.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-calendar-alt"></i> <span>Événements & Médias</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('partenaires')): ?>
        <li class="sidebar-item">
            <a href="partenaires.php" class="<?= in_array($currentAdminPage, ['partenaires.php', 'partenaire-form.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-handshake"></i> <span>Partenaires</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('contacts')): ?>
        <li class="sidebar-item">
            <a href="contacts.php" class="<?= $currentAdminPage === 'contacts.php' ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-envelope"></i> <span>Messages Reçus</span>
                </div>
                <?php if ($cntContacts > 0): ?>
                    <span class="nav-count-badge" style="background:#0284c7;"><?= $cntContacts ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('emails')): ?>
        <li class="sidebar-item">
            <a href="emails.php" class="<?= $currentAdminPage === 'emails.php' ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-paper-plane"></i> <span>Emails &amp; Notifications</span>
                </div>
                <?php if ($cntEmailsPending > 0): ?>
                    <span class="nav-count-badge" title="<?= $cntEmailsPending ?> email(s) non envoyé(s)"><?= $cntEmailsPending ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('settings')): ?>
        <li class="sidebar-item">
            <a href="settings.php" class="<?= $currentAdminPage === 'settings.php' ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-sliders-h"></i> <span>Paramètres & Visuels</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
        <?php if (admin_has_permission('utilisateurs')): ?>
        <li class="sidebar-item">
            <a href="utilisateurs.php" class="<?= in_array($currentAdminPage, ['utilisateurs.php', 'utilisateur-form.php']) ? 'active' : '' ?>">
                <div class="sidebar-item-content">
                    <i class="fas fa-user-shield"></i> <span>Comptes & Marketeurs</span>
                </div>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-info">
                <div class="user-avatar" style="background:<?= is_marketeur() ? '#dc2626' : '#162a5a' ?>;">
                    <i class="<?= is_marketeur() ? 'fas fa-bullhorn' : 'fas fa-user-shield' ?>"></i>
                </div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($adminNom) ?></div>
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:600;">
                        <?php if (is_superadmin()): ?>
                            <span style="color:#f59e0b;"><i class="fas fa-crown"></i> Super Admin</span>
                        <?php elseif (is_marketeur()): ?>
                            <span style="color:#f87171;"><i class="fas fa-bullhorn"></i> Marketeur</span>
                        <?php else: ?>
                            <span><i class="fas fa-user-tie"></i> Administrateur</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn" title="Déconnexion"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</aside>

<!-- MAIN WRAPPER -->
<div class="admin-main">
    <div class="admin-topbar">
        <div>
            <h1><?= $pageTitle ?? 'Administration' ?></h1>
            <?php if (is_marketeur()): ?>
                <div style="font-size:0.8rem; color:#64748b; margin-top:2px;">
                    <span style="background:rgba(238,41,41,0.1); color:#dc2626; padding:0.15rem 0.5rem; border-radius:4px; font-weight:700;"><i class="fas fa-shield-alt"></i> Espace Marketing & Visuels</span> &bull; Données confidentielles protégées
                </div>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <a href="../index.php" target="_blank" style="display:inline-flex; align-items:center; gap:0.4rem; color:#09122c; text-decoration:none; font-weight:700; font-size:0.85rem; background:#f1f5f9; padding:0.5rem 0.95rem; border-radius:8px; border:1px solid #e2e8f0;">
                <i class="fas fa-external-link-alt"></i> Voir le site public
            </a>
            <a href="logout.php" style="display:inline-flex; align-items:center; gap:0.4rem; color:#ef4444; text-decoration:none; font-weight:700; font-size:0.85rem; background:#fee2e2; padding:0.5rem 0.95rem; border-radius:8px;">
                <i class="fas fa-power-off"></i> Quitter
            </a>
        </div>
    </div>
    <div class="admin-body">
        <?php if (!empty($_SESSION['admin_flash_error'])): ?>
            <div style="background:#fef2f2; color:#991b1b; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.75rem; border:1px solid #fecaca; box-shadow:0 2px 8px rgba(239,68,68,0.1);">
                <i class="fas fa-lock" style="font-size:1.3rem; color:#dc2626;"></i>
                <div><?= htmlspecialchars($_SESSION['admin_flash_error']); unset($_SESSION['admin_flash_error']); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['admin_flash_success'])): ?>
            <div style="background:#ecfdf5; color:#065f46; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600; display:flex; align-items:center; gap:0.75rem; border:1px solid #a7f3d0;">
                <i class="fas fa-check-circle" style="font-size:1.3rem; color:#10b981;"></i>
                <div><?= htmlspecialchars($_SESSION['admin_flash_success']); unset($_SESSION['admin_flash_success']); ?></div>
            </div>
        <?php endif; ?>
