<?php
require_once __DIR__ . '/config/db.php';

$pageRetour = 'index.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $refUrl = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    $baseName = basename($refUrl);
    if (!empty($baseName) && file_exists(__DIR__ . '/' . $baseName)) {
        $pageRetour = $baseName;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $pageRetour");
    exit;
}

$nom = clean_input($_POST['nom'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$texte = clean_input($_POST['texte'] ?? '');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
       || !empty($_POST['ajax']);

if (empty($nom) || !$email || empty($texte)) {
    $err = "Veuillez renseigner votre nom, un email valide et votre message.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $err]);
        exit;
    }
    $_SESSION['comment_error'] = "⚠️ " . $err;
    header("Location: $pageRetour");
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO message (nom, email, texte) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $email, $texte]);
    $newId = (int)$db->lastInsertId();

    $succMsg = "Merci $nom ! Votre message a bien été transmis à la direction.";

    // Notification automatique au Directeur (chakirbal07@gmail.com)
    require_once __DIR__ . '/config/mailer.php';
    envoyer_notification_directeur(
        'commentaire',
        "Nouveau Commentaire / Avis : $nom",
        "Nouveau Commentaire Reçu (Pied de Page)",
        [
            'Auteur'            => $nom,
            'Email'             => $email,
            'Commentaire / Avis' => $texte,
            'Page d\'Origine'   => $pageRetour,
            'Date de Réception' => date('d/m/Y H:i')
        ],
        $email,
        'message',
        $newId
    );

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'message' => $succMsg]);
        exit;
    }

    $_SESSION['comment_success'] = "✅ " . $succMsg;
    header("Location: $pageRetour");
    exit;
} catch (Exception $e) {
    error_log("Error in process-comment.php: " . $e->getMessage());
    $errMsg = "Une erreur technique est survenue. Veuillez réessayer ultérieurement.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
        exit;
    }
    $_SESSION['comment_error'] = "⚠️ " . $errMsg;
    header("Location: $pageRetour");
    exit;
}
