<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - Hybride IO</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn-danger { 
            background: rgba(251, 113, 133, 0.1); 
            color: #fb7185; 
            border: 1px solid rgba(251, 113, 133, 0.2);
            padding: 14px 28px;
            border-radius: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-danger:hover { background: #fb7185; color: #000; }
        
        .btn-outline {
            background: transparent;
            color: #fff;
            border: 1px solid var(--border);
            padding: 14px 28px;
            border-radius: 16px;
            font-weight: 800;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-outline:hover { background: #fff; color: #000; }

        @media (max-width: 900px) {
            .config-card { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-outline, .btn-accent, .btn-danger { width: 100%; }
        }
    </style>

</head>
<body>
    <?php include '../includes/navbar.php'; ?>



    <div class="container">
        <h1>Panneau de Contrôle</h1>

        <div class="config-card">
            <div class="config-info">
                <h2>Simulation Lab</h2>
                <p>Besoin de tester l'interface ? Générez 50 relevés fictifs pour peupler vos graphiques instantanément.</p>
            </div>
            <a href="../includes/tools.php?action=generate" class="btn btn-outline">Générer Test</a>
        </div>

        <div class="config-card">
            <div class="config-info">
                <h2>Rapport de données</h2>
                <p>Exportez l'intégralité de votre historique au format CSV universel pour une analyse sous Excel.</p>
            </div>
            <a href="../includes/tools.php?action=export" class="btn btn-accent">Télécharger .CSV</a>
        </div>

        <div class="config-card" style="border-color: rgba(251, 113, 133, 0.3);">
            <div class="config-info">
                <h2 style="color: var(--temp); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; font-weight: 800;">Zone Critique</h2>
                <p>Réinitialisez le système en effaçant toutes les données enregistrées. Cette action est irréversible.</p>
            </div>

            <script>
                function confirmClear() {
                    const pwd = prompt("Action critique ! Veuillez entrer le mot de passe administrateur pour confirmer la suppression totale :");
                    if (pwd === "<?php echo ADMIN_PASS; ?>") {
                        window.location.href = "../includes/tools.php?action=clear&pwd=" + encodeURIComponent(pwd);
                    } else if (pwd !== null) {
                        alert("Mot de passe incorrect. Action annulée.");
                    }
                }
            </script>
            <button onclick="confirmClear()" class="btn btn-danger">Réinitialiser</button>
        </div>
    </div>
</body>
</html>
