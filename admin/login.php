<?php
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if (($user['statut'] ?? 'actif') === 'inactif') {
                    $error = "Ce compte utilisateur a été désactivé par l'administrateur.";
                } else {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_nom'] = $user['nom_complet'];
                    $_SESSION['admin_role'] = $user['role'] ?? 'admin';
                    $_SESSION['admin_permissions'] = $user['permissions'] ?? '';
                    header("Location: index.php");
                    exit;
                }
            } else {
                $error = "Identifiants invalides. Veuillez réessayer.";
            }
        } catch (Exception $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration - ECOB University</title>
    <link rel="icon" type="image/x-icon" href="<?= site_logo_url('../') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #060c1d 0%, #0b1736 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }
        .logo-box {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-icon {
            width: 60px;
            height: 60px;
            background: #0b1736;
            color: #f59e0b;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        h2 { font-size: 1.6rem; color: #0b1736; font-weight: 700; margin-bottom: 0.35rem; }
        p { color: #64748b; font-size: 0.92rem; }
        .form-group { margin-bottom: 1.35rem; }
        label { display: block; font-size: 0.88rem; font-weight: 600; color: #0f172a; margin-bottom: 0.45rem; }
        input {
            width: 100%;
            padding: 0.85rem 1.15rem;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }
        input:focus { border-color: #0b1736; box-shadow: 0 0 0 3px rgba(11,23,54,0.1); }
        .btn-submit {
            width: 100%;
            background: #0b1736;
            color: #ffffff;
            border: none;
            padding: 0.95rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }
        .btn-submit:hover { background: #162a5a; }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s;
        }
        .back-link a:hover { color: #0b1736; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-box">
        <img src="<?= site_logo_url('../') ?>" alt="Logo Officiel ECOB" style="height:90px; width:auto; margin-bottom:1rem; filter:drop-shadow(0 4px 10px rgba(0,0,0,0.15));">
        <h2>Espace Administration</h2>
        <p>Direction & Pilotage d'ECOB University</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Identifiant</label>
            <input type="text" name="username" id="username" placeholder="admin" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">
            Se Connecter <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
        </button>
    </form>

    <div class="back-link">
        <a href="../index.php"><i class="fas fa-arrow-left"></i> Retourner sur le site public</a>
    </div>
</div>

</body>
</html>
