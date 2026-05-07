<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
// Récupérer la liste des nodes uniques
$nodes_query = "SELECT DISTINCT device_id FROM capteurs ORDER BY device_id ASC";
$nodes_result = $conn->query($nodes_query);
$nodes = [];
if ($nodes_result) {
    while ($row = $nodes_result->fetch_assoc()) {
        $nodes[] = $row['device_id'];
    }
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

        .node-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: #fff;
            padding: 12px;
            border-radius: 12px;
            font-family: inherit;
            margin-right: 10px;
            min-width: 150px;
        }

        .alert-msg {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 25px;
            border: 1px solid rgba(74, 222, 128, 0.2);
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .config-card { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-outline, .btn-accent, .btn-danger, .node-select { width: 100%; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h1>Panneau de Contrôle</h1>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-msg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

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

        <!-- NOUVELLE SECTION : Suppression par Node -->
        <div class="config-card">
            <div class="config-info">
                <h2>Gestion des Nodes</h2>
                <p>Supprimer l'historique de données pour un node spécifique. Cette action nécessite une confirmation.</p>
            </div>
            <div class="node-action-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select id="nodeSelect" class="node-select">
                    <option value="">Sélectionner un Node</option>
                    <?php foreach($nodes as $node): ?>
                        <option value="<?php echo htmlspecialchars($node); ?>"><?php echo htmlspecialchars($node); ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="confirmNodeDelete()" class="btn btn-danger" style="background: rgba(251, 113, 133, 0.05);">Effacer Node</button>
            </div>
        </div>

        <div class="config-card" style="border-color: rgba(251, 113, 133, 0.3);">
            <div class="config-info">
                <h2 style="color: var(--temp); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; font-weight: 800;">Zone Critique</h2>
                <p>Réinitialisez le système en effaçant toutes les données enregistrées. Cette action est irréversible.</p>
            </div>

            <button onclick="confirmClear()" class="btn btn-danger">Réinitialiser Tout</button>
        </div>
    </div>

    <script>
        function confirmNodeDelete() {
            const node = document.getElementById('nodeSelect').value;
            if (!node) {
                alert("Veuillez sélectionner un node.");
                return;
            }
            const pwd = prompt("Suppression ciblée : Veuillez entrer le mot de passe administrateur pour effacer les données de " + node + " :");
            if (pwd === "<?php echo ADMIN_PASS; ?>") {
                window.location.href = "../includes/tools.php?action=delete_node&node=" + encodeURIComponent(node) + "&pwd=" + encodeURIComponent(pwd);
            } else if (pwd !== null) {
                alert("Mot de passe incorrect.");
            }
        }

        function confirmClear() {
            const pwd = prompt("Action critique ! Veuillez entrer le mot de passe administrateur pour confirmer la suppression TOTALE :");
            if (pwd === "<?php echo ADMIN_PASS; ?>") {
                window.location.href = "../includes/tools.php?action=clear&pwd=" + encodeURIComponent(pwd);
            } else if (pwd !== null) {
                alert("Mot de passe incorrect. Action annulée.");
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
