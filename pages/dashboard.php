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
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 50px;
        }

        .page-title h1 {
            font-size: 3rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -2px;
            background: linear-gradient(to right, #fff, #64748b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid rgba(34, 197, 94, 0.2);
            margin-top: 15px;
        }

        .pulse {
            width: 8px; height: 8px; background: #4ade80;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
            100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 25px;
            display: block;
        }

        .card-label {
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .card-value {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 10px 0;
            display: flex;
            align-items: baseline;
        }

        .card-unit {
            font-size: 1.2rem;
            color: #64748b;
            margin-left: 8px;
            font-weight: 500;
        }

        .id-badge {
            background: rgba(56, 189, 248, 0.1);
            color: var(--accent);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            header { flex-direction: column; align-items: flex-start; gap: 20px; }
            .page-title h1 { font-size: 2.2rem; }
        }
    </style>


</head>
<body>
    <?php include '../includes/navbar.php'; ?>



    <div class="container">
        <?php
        $conn = getDbConnection();

        
        $selected_device = isset($_GET['device']) ? $_GET['device'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        if (!$conn->connect_error) {
            $where_clause = "";
            if (!empty($selected_device)) {
                $where_clause = "WHERE device_id = '" . $conn->real_escape_string($selected_device) . "'";
            }

            // Récupérer le dernier enregistrement pour le header et les cartes
            $sql_latest = "SELECT * FROM capteurs $where_clause ORDER BY date_enregistrement DESC LIMIT 1";
            $result_latest = $conn->query($sql_latest);
            $latest = $result_latest->fetch_assoc();
            
            $history_where = "";
            if ($latest) {
                $current_device = $latest['device_id'];
                $history_where = empty($selected_device) ? "" : "WHERE device_id = '$current_device'";
            }
        ?>

        <header>
            <div class="page-title">
                <h1>Dashboard Live</h1>
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 15px;">
                    <div class="status-badge">
                        <div class="pulse"></div>
                        Flux actif
                    </div>
                    <?php if ($latest): ?>
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 600; background: rgba(255,255,255,0.05); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border);">
                        <span style="color: var(--accent);">ID:</span> <?php echo $latest['device_id']; ?> 
                        <span style="margin: 0 10px; opacity: 0.3;">|</span>
                        <span style="color: var(--accent);">Reçu à:</span> <?php echo date('H:i:s', strtotime($latest['date_enregistrement'])); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; align-items: flex-end;">
                <form method="GET" style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Filtrer par Appareil</label>
                    <select name="device" onchange="this.form.submit()" style="background: var(--card); color: #fff; border: 1px solid var(--border); padding: 10px 15px; border-radius: 12px; font-weight: 600; cursor: pointer; outline: none;">
                        <?php
                        $res_devices = $conn->query("SELECT DISTINCT device_id FROM capteurs ORDER BY device_id ASC");
                        echo '<option value="">Tous les appareils</option>';
                        while($d = $res_devices->fetch_assoc()) {
                            $sel = ($selected_device == $d['device_id']) ? 'selected' : '';
                            echo "<option value='".$d['device_id']."' $sel>".$d['device_id']."</option>";
                        }
                        ?>
                    </select>
                </form>
                <button class="sync-btn" onclick="location.reload()">
                    <span style="font-size: 1.2rem;">🔄</span> SYNC
                </button>
            </div>
        </header>

        <?php
            if ($latest) {
        ?>
        <div class="stats-grid">
            <div class="card">
                <span class="card-icon">🌡️</span>
                <div class="card-label">Température DHT</div>
                <div class="card-value" style="color: var(--temp);">
                    <?php echo number_format($latest['temperature'], 1); ?>
                    <span class="card-unit">°C</span>
                </div>
            </div>
            <div class="card">
                <span class="card-icon">💧</span>
                <div class="card-label">Humidité DHT</div>
                <div class="card-value" style="color: var(--hum);">
                    <?php echo number_format($latest['humidite'], 1); ?>
                    <span class="card-unit">%</span>
                </div>
            </div>
            <div class="card">
                <span class="card-icon">🏠</span>
                <div class="card-label">LM35 Intérieur</div>
                <div class="card-value" style="color: var(--lm);">
                    <?php echo number_format($latest['lm1'], 1); ?>
                    <span class="card-unit">°C</span>
                </div>
            </div>
            <div class="card">
                <span class="card-icon">🌳</span>
                <div class="card-label">LM35 Extérieur</div>
                <div class="card-value" style="color: #a78bfa;">
                    <?php echo number_format($latest['lm2'], 1); ?>
                    <span class="card-unit">°C</span>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Journal d'activité</h2>
                    <p style="color: #64748b; margin: 5px 0 0 0;">Historique complet des relevés.</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <?php
                    // Calcul pagination
                    $sql_count = "SELECT COUNT(*) as total FROM capteurs $history_where";
                    $total_res = $conn->query($sql_count);
                    $total_rows = $total_res->fetch_assoc()['total'];
                    $total_pages = ceil($total_rows / $limit);
                    
                    $base_url = "dashboard.php?device=" . urlencode($selected_device);
                    ?>
                    <a href="<?php echo $base_url . "&page=" . ($page - 1); ?>" class="sync-btn" style="padding: 8px 16px; background: <?php echo ($page <= 1) ? '#1e293b' : 'var(--accent)'; ?>; pointer-events: <?php echo ($page <= 1) ? 'none' : 'auto'; ?>; opacity: <?php echo ($page <= 1) ? '0.5' : '1'; ?>; text-decoration: none; color: #000; font-size: 0.8rem;">⬅️ Précédent</a>
                    <span style="align-self: center; font-weight: 700; font-size: 0.9rem; margin: 0 10px;"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
                    <a href="<?php echo $base_url . "&page=" . ($page + 1); ?>" class="sync-btn" style="padding: 8px 16px; background: <?php echo ($page >= $total_pages) ? '#1e293b' : 'var(--accent)'; ?>; pointer-events: <?php echo ($page >= $total_pages) ? 'none' : 'auto'; ?>; opacity: <?php echo ($page >= $total_pages) ? '0.5' : '1'; ?>; text-decoration: none; color: #000; font-size: 0.8rem;">Suivant ➡️</a>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Horodatage</th>
                        <th>Device</th>
                        <th>Température</th>
                        <th>Humidité</th>
                        <th>Capteur A</th>
                        <th>Capteur B</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_history = "SELECT * FROM capteurs $history_where ORDER BY date_enregistrement DESC LIMIT $limit OFFSET $offset";
                    $result_history = $conn->query($sql_history);
                    while($row = $result_history->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #94a3b8;'>" . date('d/m H:i:s', strtotime($row['date_enregistrement'])) . "</td>";
                        echo "<td><span class='id-badge'>" . $row['device_id'] . "</span></td>";
                        echo "<td style='color: var(--temp); font-weight: 700;'>" . number_format($row['temperature'], 1) . "°C</td>";
                        echo "<td style='color: var(--hum); font-weight: 700;'>" . number_format($row['humidite'], 1) . "%</td>";
                        echo "<td>" . number_format($row['lm1'], 1) . "°C</td>";
                        echo "<td>" . number_format($row['lm2'], 1) . "°C</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
            } else {
                echo "<div class='card' style='text-align: center; padding: 100px;'>
                        <span style='font-size: 4rem;'>📡</span>
                        <h2>En attente de données...</h2>
                        <p>Le système est prêt. Branchez l'émetteur pour commencer la réception.</p>
                      </div>";
            }
            $conn->close();
        }
        ?>
    </div>
</body>
</html>
