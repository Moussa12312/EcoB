<?php
// ========================================================
// SERVICE D'ENVOI DE NOTIFICATIONS EMAIL - ECOB UNIVERSITY
// Envoi réel via SMTP (PHPMailer). Paramètres modifiables dans
// Administration > Emails. Chaque envoi est journalisé en base.
// ========================================================

require_once __DIR__ . '/db.php';

require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/** Enregistre (ou met à jour) un paramètre du site. */
function save_setting(string $key, string $value): void {
    $db = get_db();
    $st = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $st->execute([$key]);
    if ((int)$st->fetchColumn() > 0) {
        $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?")->execute([$value, $key]);
    } else {
        $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)")->execute([$key, $value]);
    }
}

/** Lecture d'un paramètre sans cache (pour que la config SMTP soit toujours à jour). */
function fresh_setting(string $key, string $default = ''): string {
    try {
        $st = get_db()->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $st->execute([$key]);
        $v = $st->fetchColumn();
        return ($v === false || $v === null) ? $default : (string)$v;
    } catch (Throwable $t) {
        return $default;
    }
}

/** Ajoute les colonnes nécessaires au journal des emails (une seule fois). */
function ensure_mail_schema(): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $db = get_db();
        $cols = $db->query("SHOW COLUMNS FROM emails_notifications")->fetchAll(PDO::FETCH_COLUMN);
        $add = [
            'ref_table'  => "VARCHAR(30) NULL",
            'ref_id'     => "INT NULL",
            'erreur'     => "TEXT NULL",
            'reply_to'   => "VARCHAR(190) NULL",
            'tentatives' => "INT NOT NULL DEFAULT 0",
            'date_envoi' => "DATETIME NULL",
        ];
        foreach ($add as $col => $def) {
            if (!in_array($col, $cols, true)) {
                $db->exec("ALTER TABLE emails_notifications ADD COLUMN $col $def");
            }
        }
        $db->exec("ALTER TABLE emails_notifications MODIFY statut_envoi VARCHAR(30) NOT NULL DEFAULT 'en_attente'");
        $db->exec("UPDATE emails_notifications SET statut_envoi = 'en_attente' WHERE statut_envoi = 'enregistre_localement'");
    } catch (Throwable $t) {
        error_log('ensure_mail_schema: ' . $t->getMessage());
    }
}

/** Configuration SMTP courante. */
function smtp_config(): array {
    $user = trim(fresh_setting('smtp_user', ''));
    return [
        'host'      => trim(fresh_setting('smtp_host', 'smtp.gmail.com')) ?: 'smtp.gmail.com',
        'port'      => (int)(fresh_setting('smtp_port', '587') ?: 587),
        'secure'    => fresh_setting('smtp_secure', 'tls'),
        'user'      => $user,
        // Les mots de passe d'application Gmail sont souvent copiés avec des espaces
        'pass'      => str_replace(' ', '', fresh_setting('smtp_pass', '')),
        'from_name' => fresh_setting('smtp_from_name', '') ?: fresh_setting('site_nom', 'ECOB University'),
    ];
}

function smtp_is_configured(): bool {
    $c = smtp_config();
    return $c['user'] !== '' && $c['pass'] !== '';
}

/** URL publique de l'espace admin, basée sur l'hôte courant. */
function admin_url(): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/Siteweb/index.php');
    $base = preg_replace('#/(admin/)?[^/]*$#', '', $script);
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $https . '://' . $host . $base . '/admin/';
}

/**
 * Envoie un email HTML. Retourne [bool succès, string erreur].
 */
function send_mail_smtp(string $to, string $subject, string $html, ?string $replyTo = null): array {
    $cfg = smtp_config();

    if (!smtp_is_configured()) {
        return [false, "SMTP non configuré : renseignez l'adresse Gmail et le mot de passe d'application dans Administration > Emails."];
    }

    $attempt = function (bool $relaxTls) use ($cfg, $to, $subject, $html, $replyTo): array {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->Port       = $cfg['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['user'];
            $mail->Password   = $cfg['pass'];
            $mail->Timeout    = 10;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPSecure = $cfg['secure'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : ($cfg['secure'] === 'none' ? '' : PHPMailer::ENCRYPTION_STARTTLS);
            if ($cfg['secure'] === 'none') {
                $mail->SMTPAutoTLS = false;
            }
            if ($relaxTls) {
                $mail->SMTPOptions = ['ssl' => [
                    'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true,
                ]];
            }
            $mail->setFrom($cfg['user'], $cfg['from_name']);
            $mail->addAddress($to);
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = trim(html_entity_decode(strip_tags(preg_replace('#<(br|/tr|/p|/h\d)[^>]*>#i', "\n", $html)), ENT_QUOTES, 'UTF-8'));
            $mail->send();
            return [true, ''];
        } catch (Throwable $t) {
            return [false, $mail->ErrorInfo ?: $t->getMessage()];
        }
    };

    [$ok, $err] = $attempt(false);
    // Sous WAMP, le magasin de certificats est souvent absent : on réessaie sans vérification stricte
    if (!$ok && preg_match('/certificate|SSL|TLS|stream_socket_enable_crypto/i', $err)) {
        [$ok, $err] = $attempt(true);
    }
    if (!$ok) {
        if (stripos($err, 'Could not authenticate') !== false || stripos($err, 'Username and Password not accepted') !== false) {
            $err .= " — Gmail refuse l'authentification : utilisez un « mot de passe d'application » (validation en 2 étapes requise), pas le mot de passe habituel.";
        } elseif (stripos($err, 'Could not connect') !== false || stripos($err, 'Connection') !== false) {
            $err .= " — Connexion à " . $cfg['host'] . ":" . $cfg['port'] . " impossible (pare-feu / antivirus / pas d'Internet).";
        }
    }
    return [$ok, $err];
}

/** Construit le HTML de l'email (couleurs : blanc, bleu nuit, orange). */
function build_notification_html(string $type, string $sujet, string $titre, array $champs, string $destinataire, ?string $dateRef = null): string {
    $siteNom = get_setting('site_nom', 'ECOB University');
    $dateEnvoi = $dateRef ?: date('d/m/Y à H:i');

    $badgeText = 'NOTIFICATION';
    if ($type === 'preinscription') {
        $badgeText = "NOUVELLE PRÉINSCRIPTION";
    } elseif ($type === 'contact') {
        $badgeText = 'NOUVEAU MESSAGE DE CONTACT';
    } elseif ($type === 'commentaire') {
        $badgeText = 'NOUVEAU COMMENTAIRE';
    }

    $rowsHtml = '';
    foreach ($champs as $label => $valeur) {
        $rowsHtml .= '<tr>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#09122c;width:35%;vertical-align:top;font-size:14px;background:#f8fafc;">' . e($label) . '</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:14px;line-height:1.5;">' . nl2br(e($valeur)) . '</td>'
            . '</tr>';
    }

    return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>' . e($sujet) . '</title></head>'
        . '<body style="margin:0;padding:20px;background:#f1f5f9;font-family:Segoe UI,Arial,sans-serif;">'
        . '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #cbd5e1;">'
        . '<tr><td style="background:#09122c;padding:24px 20px;text-align:center;border-bottom:4px solid #f59e0b;">'
        . '<h1 style="color:#ffffff;font-size:20px;margin:0 0 4px 0;">' . e($siteNom) . '</h1>'
        . '<p style="color:#cbd5e1;font-size:12px;margin:0;text-transform:uppercase;letter-spacing:1px;">Notification de la direction</p>'
        . '</td></tr>'
        . '<tr><td style="padding:25px 30px;">'
        . '<div style="display:inline-block;background:#f59e0b;color:#09122c;font-size:11px;font-weight:800;padding:4px 12px;border-radius:20px;letter-spacing:.5px;margin-bottom:15px;">' . e($badgeText) . '</div>'
        . '<h2 style="color:#09122c;font-size:18px;margin:0 0 15px 0;">' . e($titre) . '</h2>'
        . '<p style="color:#475569;font-size:14px;line-height:1.5;margin:0 0 20px 0;">Une nouvelle interaction a été enregistrée sur le site de l\'université. Voici les informations transmises :</p>'
        . '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:1px solid #e2e8f0;margin-bottom:25px;">' . $rowsHtml . '</table>'
        . '<div style="text-align:center;margin:25px 0 10px 0;"><a href="' . e(admin_url()) . '" style="background:#f59e0b;color:#09122c;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:700;font-size:14px;display:inline-block;">Ouvrir l\'espace administrateur</a></div>'
        . '</td></tr>'
        . '<tr><td style="background:#f8fafc;padding:15px 20px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;">'
        . 'Message destiné à <strong>' . e($destinataire) . '</strong> — reçu le ' . e($dateEnvoi) . '.<br>&copy; ' . date('Y') . ' ' . e($siteNom)
        . '</td></tr></table></body></html>';
}

/** Tente d'envoyer une ligne du journal et met à jour son statut. */
function deliver_log_row(int $id): array {
    ensure_mail_schema();
    $db = get_db();
    $st = $db->prepare("SELECT * FROM emails_notifications WHERE id = ?");
    $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) {
        return [false, 'Email introuvable.'];
    }
    $replyTo = $row['reply_to'] ?? null;
    [$ok, $err] = send_mail_smtp($row['destinataire'], $row['sujet'], $row['contenu_html'], $replyTo);
    $up = $db->prepare("UPDATE emails_notifications SET statut_envoi = ?, erreur = ?, tentatives = tentatives + 1, date_envoi = ? WHERE id = ?");
    $up->execute([
        $ok ? 'envoye' : (smtp_is_configured() ? 'echec' : 'en_attente'),
        $ok ? null : $err,
        $ok ? date('Y-m-d H:i:s') : null,
        $id,
    ]);
    return [$ok, $err];
}

/**
 * Enregistre la notification dans le journal puis tente de l'envoyer.
 * Ne bloque jamais l'enregistrement de la demande du visiteur.
 */
function envoyer_notification_directeur(string $type, string $sujet, string $titre, array $champs, ?string $replyTo = null, ?string $refTable = null, ?int $refId = null, bool $sendNow = true): bool {
    ensure_mail_schema();
    $destinataire = fresh_setting('email_directeur', 'chakirbal07@gmail.com') ?: 'chakirbal07@gmail.com';
    $html = build_notification_html($type, $sujet, $titre, $champs, $destinataire);

    $logId = 0;
    try {
        $db = get_db();
        $meta = ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) ? $replyTo : null;
        $db->prepare("INSERT INTO emails_notifications (type_notification, destinataire, sujet, contenu_html, statut_envoi, reply_to, ref_table, ref_id) VALUES (?, ?, ?, ?, 'en_attente', ?, ?, ?)")
           ->execute([$type, $destinataire, $sujet, $html, $meta, $refTable, $refId]);
        $logId = (int)$db->lastInsertId();
    } catch (Throwable $e) {
        error_log('Journal email: ' . $e->getMessage());
    }

    if (!$sendNow || $logId === 0) {
        return $logId > 0;
    }

    try {
        [$ok, $err] = deliver_log_row($logId);
        return $ok;
    } catch (Throwable $t) {
        error_log('Envoi email: ' . $t->getMessage());
        return false;
    }
}

/** Contenu d'un email à partir d'une ligne existante (admis / contact / message). */
function notification_payload(string $table, array $r): array {
    if ($table === 'admis') {
        return [
            'preinscription',
            "Nouvelle Préinscription : {$r['prenom']} {$r['nom']} - {$r['formation']}",
            'Nouvelle Préinscription Reçue',
            [
                'Nom & Prénom'      => $r['prenom'] . ' ' . $r['nom'],
                'Filière Souhaitée' => $r['formation'],
                'Cycle / Niveau'    => $r['niveau_etude'],
                'Téléphone'         => $r['phone'],
                'Adresse Email'     => $r['email'],
                'Date de Naissance' => $r['dte'],
                'Nationalité'       => $r['nationalite'],
                'Date d\'Enregistrement' => $r['date_creation'] ?? '',
            ],
            $r['email'],
        ];
    }
    if ($table === 'contact') {
        return [
            'contact',
            "Nouveau Message de Contact : {$r['nom']}",
            'Nouveau Message via le Formulaire de Contact',
            [
                'Nom de l\'Expéditeur' => $r['nom'],
                'Email'                => $r['email'],
                'Téléphone / Adresse'  => $r['adresse'] ?: 'Non précisé',
                'Message'              => $r['mesage'],
            ],
            $r['email'],
        ];
    }
    return [
        'commentaire',
        "Nouveau Commentaire / Avis : {$r['nom']}",
        'Nouveau Commentaire Reçu (Pied de Page)',
        [
            'Auteur'             => $r['nom'],
            'Email'              => $r['email'],
            'Commentaire / Avis' => $r['texte'],
        ],
        $r['email'],
    ];
}

/**
 * Met en file d'attente les demandes (préinscriptions, contacts, commentaires)
 * qui n'ont jamais généré d'email. Retourne le nombre d'emails créés.
 */
function backfill_previous_requests(): int {
    ensure_mail_schema();
    $db = get_db();
    $created = 0;
    foreach (['admis', 'contact', 'message'] as $table) {
        $rows = $db->query("SELECT * FROM `$table` ORDER BY id ASC")->fetchAll();
        $logged = $db->prepare("SELECT COUNT(*) FROM emails_notifications WHERE ref_table = ? AND ref_id = ?");
        foreach ($rows as $r) {
            $recId = (int)($r['ID'] ?? $r['id'] ?? 0);
            $logged->execute([$table, $recId]);
            if ((int)$logged->fetchColumn() > 0) {
                continue;
            }
            [$type, $sujet, $titre, $champs, $reply] = notification_payload($table, $r);
            envoyer_notification_directeur($type, $sujet, $titre, $champs, $reply, $table, $recId, false);
            $created++;
        }
    }
    return $created;
}

/** Envoie tous les emails en attente ou en échec. Retourne [envoyés, échecs, première erreur]. */
function send_pending_emails(int $limit = 40): array {
    ensure_mail_schema();
    $ids = get_db()->query("SELECT id FROM emails_notifications WHERE statut_envoi IN ('en_attente','echec') ORDER BY id ASC LIMIT " . (int)$limit)->fetchAll(PDO::FETCH_COLUMN);
    $sent = 0; $failed = 0; $firstErr = '';
    foreach ($ids as $id) {
        [$ok, $err] = deliver_log_row((int)$id);
        if ($ok) {
            $sent++;
        } else {
            $failed++;
            if ($firstErr === '') {
                $firstErr = $err;
            }
            if (!smtp_is_configured()) {
                break; // inutile d'insister
            }
        }
    }
    return [$sent, $failed, $firstErr];
}
