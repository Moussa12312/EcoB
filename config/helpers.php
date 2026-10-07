<?php
// ========================================================
// FONCTIONS UTILITAIRES COMMUNES - ECOB UNIVERSITY
// Uploads d'images sécurisés, URLs anti-cache, dates FR
// ========================================================

if (!defined('UPLOAD_DIR_REL')) {
    define('UPLOAD_DIR_REL', 'assets/uploads/');
}
if (!defined('UPLOAD_MAX_BYTES')) {
    define('UPLOAD_MAX_BYTES', 20 * 1024 * 1024); // 20 Mo
}
if (!defined('IMAGE_MAX_DIMENSION')) {
    define('IMAGE_MAX_DIMENSION', 1800); // px, les photos plus grandes sont réduites
}

/** Échappement HTML court pour l'affichage. */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Message d'erreur lisible pour un code d'erreur d'upload PHP. */
function upload_error_message(int $code): string {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "La photo est trop volumineuse pour la configuration actuelle du serveur (limite : " . ini_get('upload_max_filesize') . "). Réduisez sa taille ou augmentez upload_max_filesize dans php.ini.";
        case UPLOAD_ERR_PARTIAL:
            return "Le téléversement a été interrompu. Veuillez réessayer.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Dossier temporaire du serveur introuvable.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Impossible d'écrire le fichier sur le disque du serveur.";
        case UPLOAD_ERR_EXTENSION:
            return "Le téléversement a été bloqué par une extension PHP.";
        default:
            return "Erreur inconnue lors du téléversement (code $code).";
    }
}

/**
 * Détecte le cas où le navigateur a envoyé un fichier mais où PHP a tout rejeté
 * (post_max_size dépassé : $_POST et $_FILES arrivent alors vides).
 */
function upload_post_too_large(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && empty($_POST) && empty($_FILES)
        && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

/**
 * Enregistre une image téléversée de façon sûre.
 *
 * @return string|null Chemin relatif (assets/uploads/xxx) ; null si aucun fichier envoyé ou en cas d'erreur
 *                     (dans ce cas $error contient le message à afficher).
 */
function save_uploaded_image(string $fileKey, string $prefix, ?string &$error = null, bool $allowSvg = false): ?string {
    $error = null;

    if (upload_post_too_large()) {
        $error = "Le formulaire envoyé dépasse la taille maximale acceptée par le serveur (post_max_size = " . ini_get('post_max_size') . "). Aucune modification n'a été enregistrée : réduisez la taille des photos.";
        return null;
    }

    if (empty($_FILES[$fileKey]) || !is_array($_FILES[$fileKey]) || ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null; // Aucun fichier choisi : on conserve l'image existante
    }

    $file = $_FILES[$fileKey];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = upload_error_message((int)$file['error']);
        return null;
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        $error = "La photo dépasse " . (UPLOAD_MAX_BYTES / 1024 / 1024) . " Mo.";
        return null;
    }

    $tmp = $file['tmp_name'];
    if (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp)) {
        $error = "Fichier invalide.";
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if ($allowSvg) {
        $allowed[] = 'svg';
    }
    if (!in_array($ext, $allowed, true)) {
        $error = "Format non accepté (" . strtoupper(implode(', ', $allowed)) . " uniquement).";
        return null;
    }

    $isSvg = ($ext === 'svg');
    if (!$isSvg) {
        $info = @getimagesize($tmp);
        if ($info === false) {
            $error = "Le fichier envoyé n'est pas une image valide.";
            return null;
        }
    } else {
        $head = @file_get_contents($tmp, false, null, 0, 4096);
        if ($head === false || stripos($head, '<svg') === false) {
            $error = "Le fichier SVG n'est pas valide.";
            return null;
        }
        $svgFull = (string)file_get_contents($tmp);
        if (preg_match('/<script|onload\s*=|onerror\s*=|javascript:/i', $svgFull)) {
            $error = "Ce fichier SVG contient du code non autorisé.";
            return null;
        }
    }

    $dirAbs = __DIR__ . '/../' . UPLOAD_DIR_REL;
    if (!is_dir($dirAbs) && !@mkdir($dirAbs, 0775, true)) {
        $error = "Dossier de destination introuvable.";
        return null;
    }
    if (!is_writable($dirAbs)) {
        $error = "Le dossier assets/uploads n'est pas accessible en écriture.";
        return null;
    }

    $safePrefix = preg_replace('/[^a-z0-9_]/i', '', $prefix) ?: 'img';
    $name = $safePrefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $targetAbs = $dirAbs . $name;

    $done = false;
    if (!$isSvg && extension_loaded('gd') && $ext !== 'gif') {
        $done = resize_and_store_image($tmp, $targetAbs, $ext);
    }
    if (!$done) {
        $done = (PHP_SAPI === 'cli') ? @copy($tmp, $targetAbs) : @move_uploaded_file($tmp, $targetAbs);
    }
    if (!$done) {
        $error = "Échec de l'enregistrement de la photo sur le serveur.";
        return null;
    }
    @chmod($targetAbs, 0644);

    return UPLOAD_DIR_REL . $name;
}

/** Réduit (si besoin), corrige l'orientation EXIF et enregistre l'image. */
function resize_and_store_image(string $src, string $dest, string $ext): bool {
    try {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $img = @imagecreatefromjpeg($src);
                break;
            case 'png':
                $img = @imagecreatefrompng($src);
                break;
            case 'webp':
                $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false;
                break;
            default:
                return false;
        }
        if (!$img) {
            return false;
        }

        // Orientation EXIF (photos de téléphone)
        if (in_array($ext, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $orientation = $exif['Orientation'] ?? 1;
            if ($orientation == 3) {
                $img = imagerotate($img, 180, 0);
            } elseif ($orientation == 6) {
                $img = imagerotate($img, -90, 0);
            } elseif ($orientation == 8) {
                $img = imagerotate($img, 90, 0);
            }
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $max = IMAGE_MAX_DIMENSION;
        if ($w > $max || $h > $max) {
            $ratio = min($max / $w, $max / $h);
            $nw = max(1, (int)round($w * $ratio));
            $nh = max(1, (int)round($h * $ratio));
            $resized = imagecreatetruecolor($nw, $nh);
            if ($ext === 'png' || $ext === 'webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        } elseif ($ext === 'png' || $ext === 'webp') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        switch ($ext) {
            case 'png':
                $ok = imagepng($img, $dest, 6);
                break;
            case 'webp':
                $ok = imagewebp($img, $dest, 85);
                break;
            default:
                $ok = imagejpeg($img, $dest, 85);
        }
        imagedestroy($img);
        return (bool)$ok;
    } catch (Throwable $t) {
        error_log('resize_and_store_image: ' . $t->getMessage());
        return false;
    }
}

/** Supprime l'ancienne photo téléversée (uniquement dans assets/uploads). */
function delete_uploaded_file(?string $relPath): void {
    if (!$relPath || strpos($relPath, UPLOAD_DIR_REL) !== 0 || strpos($relPath, '..') !== false) {
        return;
    }
    $abs = __DIR__ . '/../' . $relPath;
    if (is_file($abs)) {
        @unlink($abs);
    }
}

/**
 * URL d'une image avec empreinte de version : le navigateur recharge
 * automatiquement l'image dès qu'elle est modifiée par l'administrateur.
 */
function asset_url(?string $path, string $fallback = '', string $prefix = ''): string {
    $path = trim((string)$path);
    if ($path === '') {
        $path = $fallback;
    }
    if ($path === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $path)) {
        return e($path);
    }
    $abs = __DIR__ . '/../' . $path;
    if (!is_file($abs) && $fallback !== '' && $path !== $fallback) {
        $path = $fallback;
        $abs = __DIR__ . '/../' . $path;
    }
    $version = is_file($abs) ? filemtime($abs) : time();
    return e($prefix . $path) . '?v=' . $version;
}

/** Logo du site (modifiable depuis l'administration). */
function site_logo_url(string $prefix = ''): string {
    return asset_url(get_setting('site_logo', ''), 'assets/images/logo.png', $prefix);
}

/** Date en français : 15 octobre 2025 */
function format_date_fr(?string $date): string {
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return e($date);
    }
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return (int)date('j', $ts) . ' ' . $mois[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * =========================================================================
 * GESTION DES RÔLES & RESTRICTIONS (SUPERADMIN, ADMIN, MARKETEUR)
 * =========================================================================
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

function current_admin_role(): string {
    return $_SESSION['admin_role'] ?? 'invite';
}

function is_superadmin(): bool {
    return current_admin_role() === 'superadmin';
}

function is_marketeur(): bool {
    return current_admin_role() === 'marketeur';
}

/**
 * Vérifie si l'utilisateur possède une permission spécifique.
 * Permissions types :
 *  - 'events'       : Actualités et événements
 *  - 'partenaires'  : Partenaires et sponsors
 *  - 'pages'        : Contenus éditoriaux des pages
 *  - 'filieres'     : Formations
 *  - 'settings'     : Visuels, sliders et textes généraux
 *  - 'equipe'       : Membres de l'équipe
 *  - 'contacts'     : Messages reçus (CONFIDENTIEL)
 *  - 'admissions'   : Préinscriptions et dossiers (CONFIDENTIEL)
 *  - 'emails'       : Journal des notifications et SMTP (CONFIDENTIEL)
 *  - 'utilisateurs' : Gestion des comptes admins et marketeurs (CONFIDENTIEL)
 */
function admin_has_permission(string $perm): bool {
    if (!is_admin_logged_in()) {
        return false;
    }
    $role = current_admin_role();
    if ($role === 'superadmin' || $role === 'admin') {
        return true;
    }
    
    $permsRaw = $_SESSION['admin_permissions'] ?? null;
    if ($permsRaw === 'all') {
        return true;
    }
    
    $permsList = [];
    if (is_string($permsRaw) && !empty($permsRaw)) {
        $decoded = json_decode($permsRaw, true);
        $permsList = is_array($decoded) ? $decoded : array_map('trim', explode(',', $permsRaw));
    } elseif (is_array($permsRaw)) {
        $permsList = $permsRaw;
    } else {
        // Permissions par défaut d'un profil Marketeur
        $permsList = ['events', 'partenaires', 'pages', 'filieres', 'settings', 'equipe'];
    }
    
    return in_array($perm, $permsList, true);
}

function require_admin_permission(string $perm): void {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit;
    }
    if (!admin_has_permission($perm)) {
        $_SESSION['admin_flash_error'] = "⛔ Accès Restreint : Votre profil ne dispose pas de l'autorisation d'accès aux informations de cette section confidentielle.";
        header("Location: index.php");
        exit;
    }
}

