<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

function getTimeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return "$diff sec";
    if ($diff < 3600) return round($diff / 60) . " min";
    if ($diff < 86400) return round($diff / 3600) . " h";
    return round($diff / 86400) . " jours";
}

function getStatusColor($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 300) return "#4ade80"; // < 5 min - Online
    if ($diff < 3600) return "#fbbf24"; // < 1h - Idle
    return "#fb7185"; // Offline
}

function getDeviceName($id) {
    switch($id) {
        case '0':
        case 'GATEWAY': return "Gateway Hybride (Poste 0)";
        case '109':
        case 'NODE_109': return "Node Externe (PFE 109)";
        case '101': return "Node Externe (PFE 101)";
        default: return "Capteur Distant " . $id;
    }
}

// Logic for fetching data (shared between full page and AJAX)
function renderNodesContent() {
    $conn = getDbConnection();
    if ($conn->connect_error) {
        return "<p>Erreur de connexion</p>";
    }

    $sql = "SELECT device_id, MAX(date_enregistrement) as last_seen, COUNT(*) as data_count, 
                   AVG(temperature) as avg_temp, AVG(humidite) as avg_hum
            FROM capteurs 
            GROUP BY device_id 
            ORDER BY last_seen DESC";
    $result = $conn->query($sql);
    
    $total_nodes = $result->num_rows;
    $active_nodes = 0;
    $nodes_data = [];
    
    while($row = $result->fetch_assoc()) {
        $nodes_data[] = $row;
        $diff = time() - strtotime($row['last_seen']);
        if ($diff < 300) $active_nodes++;
    }

    ob_start();
    ?>
    <div class="stats-summary">
        <div class="stat-item"><span>Total Nodes</span><b><?php echo $total_nodes; ?></b></div>
        <div class="stat-item"><span style="color: #4ade80;">En Ligne</span><b><?php echo $active_nodes; ?></b></div>
        <div class="stat-item"><span style="color: #fb7185;">Hors Ligne</span><b><?php echo ($total_nodes - $active_nodes); ?></b></div>
    </div>

    <div class="nodes-grid">
        <?php foreach($nodes_data as $node): 
            $color = getStatusColor($node['last_seen']);
            $time_ago = getTimeAgo($node['last_seen']);
        ?>
            <div class="node-card" <?php echo ($node['device_id'] == 0) ? 'style="border: 2px solid var(--accent);"' : ''; ?>>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div>
                        <span class="node-id" style="margin-bottom: 0;"><?php echo getDeviceName($node['device_id']); ?></span>
                        <span style="font-size: 0.7rem; color: #64748b; font-weight: 700;">ID Technique: <?php echo htmlspecialchars($node['device_id']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div class="status-indicator" style="background: <?php echo $color; ?>; box-shadow: 0 0 10px <?php echo $color; ?>;"></div>
                        <span style="font-size: 0.7rem; font-weight: 800; color: <?php echo $color; ?>; text-transform: uppercase;">
                            <?php 
                                $diff = time() - strtotime($node['last_seen']);
                                if ($diff < 300) echo "En Ligne";
                                elseif ($diff < 3600) echo "Inactif";
                                else echo "Hors Ligne";
                            ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <span class="label">Dernier Contact</span>
                    <span class="time-ago">il y a <?php echo $time_ago; ?></span>
                </div>

                <div class="info-row">
                    <span class="label">Date Précise</span>
                    <span class="value"><?php echo date('d/m/Y H:i:s', strtotime($node['last_seen'])); ?></span>
                </div>

                <div class="info-row" style="border: none;">
                    <span class="label">Total Données</span>
                    <span class="value"><?php echo $node['data_count']; ?> relevés</span>
                </div>

                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="dashboard.php?device=<?php echo urlencode($node['device_id']); ?>" style="text-align: center; background: var(--glass); color: #fff; text-decoration: none; padding: 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; border: 1px solid var(--border);">Voir Dashboard</a>
                    <a href="charts.php?device=<?php echo urlencode($node['device_id']); ?>" style="text-align: center; background: var(--accent); color: #000; text-decoration: none; padding: 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">Analytique</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    $conn->close();
    return ob_get_clean();
}

// Handle AJAX Request
if (isset($_GET['ajax'])) {
    echo renderNodesContent();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État des Nodes - Hybride IO</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nodes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
        
        .status-indicator {
            width: 12px; height: 12px; border-radius: 50%;
            display: inline-block; margin-right: 10px;
        }

        .node-id { font-size: 1.5rem; font-weight: 800; margin-bottom: 15px; display: block; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--glass); }
        .label { color: #64748b; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }
        .value { font-weight: 600; font-size: 0.95rem; }

        .time-ago { font-size: 1.2rem; font-weight: 800; color: var(--accent); }

        .stats-summary { display: flex; gap: 20px; margin-bottom: 40px; }
        .stat-item { background: var(--glass); padding: 15px 25px; border-radius: 20px; border: 1px solid var(--border); }
        .stat-item span { display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .stat-item b { font-size: 1.5rem; color: #fff; }

        .live-badge {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(74, 222, 128, 0.2);
            width: fit-content;
            margin-bottom: 20px;
        }
        
        .pulse {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }

        #dynamic-content {
            transition: opacity 0.3s ease;
        }
        
        .refreshing {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h1 style="margin-bottom: 0;">Moniteur des Nodes</h1>
            <div class="live-badge">
                <div class="pulse"></div>
                En Direct
            </div>
        </div>
        <p style="color: #64748b; margin-bottom: 30px; font-weight: 500;">Mise à jour automatique toutes les 10 secondes.</p>

        <div id="dynamic-content">
            <?php echo renderNodesContent(); ?>
        </div>
    </div>

    <script>
        function refreshNodes() {
            const container = document.getElementById('dynamic-content');
            // Subtle indicator that it's refreshing
            container.classList.add('refreshing');
            
            fetch(window.location.href + '?ajax=1')
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    container.classList.remove('refreshing');
                })
                .catch(err => {
                    console.error('Erreur de rafraîchissement:', err);
                    container.classList.remove('refreshing');
                });
        }

        // Refresh every 10 seconds
        setInterval(refreshNodes, 10000);
    </script>
</body>
</html>

