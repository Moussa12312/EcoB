<?php
// ========================================================
// CONFIGURATION DE LA BASE DE DONNÉES - ECOB UNIVERSITY
// ========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'ecob');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root');

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Fallback en cas de mot de passe vide
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e2) {
                error_log("Database connection failed: " . $e2->getMessage());
                die("<div style='font-family:sans-serif;padding:30px;background:#fff5f5;border:1px solid #feb2b2;color:#9b2c2c;margin:40px auto;max-width:600px;border-radius:12px;text-align:center;'>
                    <h2>⚠️ Connexion à la base de données impossible</h2>
                    <p>Veuillez vérifier que le service MySQL de WampServer est bien démarré sur le port " . DB_PORT . ".</p>
                </div>");
            }
        }
    }
    return $pdo;
}

// Fonction pour récupérer les paramètres dynamiques du site
function get_settings(): array {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $db = get_db();
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error fetching settings: " . $e->getMessage());
        }
    }
    return $settings;
}

function get_setting(string $key, string $default = ''): string {
    $settings = get_settings();
    return $settings[$key] ?? $default;
}

// Nettoyage des entrées avant stockage.
// IMPORTANT : on stocke le texte brut (trim uniquement). L'échappement HTML se fait
// UNIQUEMENT à l'affichage (htmlspecialchars / e()), sinon les apostrophes et les "&"
// sont enregistrés encodés puis ré-encodés (ex: L&#039;excellence) et les liens sont cassés.
function clean_input($data): string {
    if (is_null($data)) return '';
    $value = trim((string)$data);
    return str_replace("\0", '', $value);
}

require_once __DIR__ . '/helpers.php';
