<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admission.php");
    exit;
}

$nom          = clean_input($_POST['nom'] ?? '');
$prenom       = clean_input($_POST['prenom'] ?? '');
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone        = clean_input($_POST['phone'] ?? '');
$nationalite  = clean_input($_POST['nationalite'] ?? '');
$dte          = clean_input($_POST['dte'] ?? '');
$niveau_etude = clean_input($_POST['niveau_etude'] ?? '');
$formation    = clean_input($_POST['formation'] ?? '');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
       || !empty($_POST['ajax']);

if (empty($nom) || empty($prenom) || !$email || empty($phone) || empty($dte) || empty($formation)) {
    $err = "Veuillez renseigner tous les champs obligatoires avec des informations valides.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $err]);
        exit;
    }
    $_SESSION['admission_error'] = "⚠️ " . $err;
    header("Location: admission.php");
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO admis (nom, prenom, email, phone, nationalite, dte, niveau_etude, formation, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'En attente')");
    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $phone,
        $nationalite,
        $dte,
        $niveau_etude,
        $formation
    ]);
    $newId = (int)$db->lastInsertId();

    $_SESSION['admission_success'] = "🎉 Félicitations $prenom ! Votre dossier de préinscription pour la filière « $formation » a été enregistré avec succès. Le service de scolarité vous contactera sous 48h au $phone.";

    // Notification automatique au Directeur (chakirbal07@gmail.com)
    require_once __DIR__ . '/config/mailer.php';
    envoyer_notification_directeur(
        'preinscription',
        "Nouvelle Préinscription : $prenom $nom - $formation",
        "Nouvelle Préinscription Reçue",
        [
            'Nom & Prénom'       => "$prenom $nom",
            'Filière Souhaitée'  => $formation,
            'Cycle / Niveau'     => $niveau_etude,
            'Téléphone'          => $phone,
            'Adresse Email'      => $email,
            'Date de Naissance'  => $dte,
            'Nationalité'        => $nationalite,
            'Date d\'Enregistrement' => date('d/m/Y H:i')
        ],
        $email,
        'admis',
        $newId
    );

    $succMsg = "Félicitations $prenom ! Votre dossier de préinscription pour la filière « $formation » a été enregistré avec succès. Le service de scolarité vous contactera sous 48h au $phone.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'message' => $succMsg]);
        exit;
    }

    $_SESSION['admission_success'] = "🎉 " . $succMsg;
    header("Location: admission.php");
    exit;
} catch (Exception $e) {
    error_log("Error in process-admission.php: " . $e->getMessage());
    $errMsg = "Une erreur est survenue lors de l'enregistrement de votre candidature. Veuillez réessayer.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
        exit;
    }
    $_SESSION['admission_error'] = "⚠️ " . $errMsg;
    header("Location: admission.php");
    exit;
}
