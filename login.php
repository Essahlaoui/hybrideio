<?php
session_start();
require_once 'includes/config.php';

$error = "";

// Identifiants centralisés
$admin_user = ADMIN_USER;
$admin_pass = ADMIN_PASS;


if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['logged_in'] = true;
        header("Location: pages/dashboard.php");

        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Hybride IO</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }

        .login-card {
            background: var(--card);
            backdrop-filter: blur(20px);
            padding: 50px;
            border-radius: 32px;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
            text-align: center;
        }

        .logo { font-weight: 900; font-size: 2rem; letter-spacing: -1.5px; margin-bottom: 30px; }
        .logo span { color: var(--accent); }

        h2 { margin-bottom: 30px; font-weight: 700; color: #94a3b8; }
        .error { color: #fb7185; font-size: 0.9rem; margin-top: 20px; font-weight: 600; }
        .input-group { margin-bottom: 20px; text-align: left; }

        .login-btn {
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }
    </style>




</head>
<body>
    <div class="login-card">
        <div class="logo"><span>HYBRIDE</span>.IO</div>
        <h2>Accès Monitoring</h2>
        
        <form method="POST">
            <div class="input-group">
                <label>Utilisateur</label>
                <input type="text" name="username" required placeholder="admin">
            </div>
            <div class="input-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" name="login" class="login-btn">SE CONNECTER</button>
        </form>

        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
