<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.php");
    exit;
}

$nom     = clean_input($_POST['nom'] ?? '');
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$adresse = clean_input($_POST['adresse'] ?? '');
$mesage  = clean_input($_POST['mesage'] ?? '');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
       || !empty($_POST['ajax']);

if (empty($nom) || !$email || empty($mesage)) {
    $err = "Veuillez renseigner votre nom, un email valide et votre message.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $err]);
        exit;
    }
    $_SESSION['contact_error'] = "⚠️ " . $err;
    header("Location: contact.php");
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO contact (nom, email, adresse, mesage) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $email, $adresse, $mesage]);
    $newId = (int)$db->lastInsertId();

    $succMsg = "Merci $nom ! Votre message a été transmis avec succès. Notre équipe vous répondra dans les plus brefs délais.";

    // Notification automatique au Directeur (chakirbal07@gmail.com)
    require_once __DIR__ . '/config/mailer.php';
    envoyer_notification_directeur(
        'contact',
        "Nouveau Message de Contact : $nom",
        "Nouveau Message via le Formulaire de Contact",
        [
            'Nom de l\'Expéditeur' => $nom,
            'Email'               => $email,
            'Téléphone / Adresse' => !empty($adresse) ? $adresse : 'Non précisé',
            'Message'             => $mesage,
            'Date de Réception'   => date('d/m/Y H:i')
        ],
        $email,
        'contact',
        $newId
    );

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'message' => $succMsg]);
        exit;
    }

    $_SESSION['contact_success'] = "✅ " . $succMsg;
    header("Location: contact.php");
    exit;
} catch (Exception $e) {
    error_log("Error in process-contact.php: " . $e->getMessage());
    $errMsg = "Une erreur technique est survenue lors de l'envoi de votre message. Veuillez réessayer.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
        exit;
    }
    $_SESSION['contact_error'] = "⚠️ " . $errMsg;
    header("Location: contact.php");
    exit;
}
