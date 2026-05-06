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
    <title>Analytique - Hybride IO</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-box {
            background: var(--card);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 32px;
            border: 1px solid var(--border);
            margin-bottom: 40px;
            height: 450px;
        }
    </style>


</head>
<body>
    <?php include '../includes/navbar.php'; ?>


    <div class="container">
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <h1>Analyse Temporelle</h1>
            
            <form method="GET" style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Appareil à analyser</label>
                <select name="device" onchange="this.form.submit()" style="background: var(--card); color: #fff; border: 1px solid var(--border); padding: 10px 15px; border-radius: 12px; font-weight: 600; cursor: pointer; outline: none;">
                    <?php
                    $conn_list = getDbConnection();

                    $res_devices = $conn_list->query("SELECT DISTINCT device_id FROM capteurs ORDER BY device_id ASC");
                    
                    $selected_device = isset($_GET['device']) ? $_GET['device'] : '';
                    echo '<option value="">Tous les appareils</option>';
                    while($d = $res_devices->fetch_assoc()) {
                        $sel = ($selected_device == $d['device_id']) ? 'selected' : '';
                        echo "<option value='".$d['device_id']."' $sel>".$d['device_id']."</option>";
                    }
                    $conn_list->close();
                    ?>
                </select>
            </form>
        </header>

        <?php
        $conn = getDbConnection();

        $labels = []; $temp = []; $hum = []; $lm1 = []; $lm2 = [];
        if (!$conn->connect_error) {
            // Logique de filtrage
            $where_clause = "";
            if (!empty($selected_device)) {
                $where_clause = "WHERE device_id = '" . $conn->real_escape_string($selected_device) . "'";
            }

            $sql = "SELECT * FROM (SELECT * FROM capteurs $where_clause ORDER BY date_enregistrement DESC LIMIT 50) AS sub ORDER BY date_enregistrement ASC";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()) {
                $labels[] = date('H:i', strtotime($row['date_enregistrement']));
                $temp[] = $row['temperature']; $hum[] = $row['humidite'];
                $lm1[] = $row['lm1']; $lm2[] = $row['lm2'];
            }
        }
        ?>

        <div class="chart-box">
            <h2>Température (°C) - DHT22</h2>
            <canvas id="tempChart"></canvas>
        </div>

        <div class="chart-box">
            <h2>Humidité (%) - DHT22</h2>
            <canvas id="humChart"></canvas>
        </div>

        <div class="chart-box">
            <h2>Analogique (LM35 x2)</h2>
            <canvas id="lmChart"></canvas>
        </div>
    </div>

    <script>
        const chartStyle = {
            responsive: true, maintainAspectRatio: false,
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b', font: { weight: '600' } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: '600' } } }
            },
            plugins: { legend: { position: 'top', labels: { color: '#f8fafc', font: { family: 'Plus Jakarta Sans', weight: '700' }, usePointStyle: true, pointStyle: 'circle' } } }
        };

        new Chart(document.getElementById('tempChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    { label: 'Température (°C)', data: <?php echo json_encode($temp); ?>, borderColor: '#fb7185', backgroundColor: 'rgba(251, 113, 133, 0.1)', fill: true, tension: 0.4, borderWidth: 4, pointRadius: 0 }
                ]
            },
            options: chartStyle
        });

        new Chart(document.getElementById('humChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    { label: 'Humidité (%)', data: <?php echo json_encode($hum); ?>, borderColor: '#60a5fa', backgroundColor: 'rgba(96, 165, 250, 0.1)', fill: true, tension: 0.4, borderWidth: 4, pointRadius: 0 }
                ]
            },
            options: chartStyle
        });

        new Chart(document.getElementById('lmChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    { label: 'LM35 - A', data: <?php echo json_encode($lm1); ?>, borderColor: '#34d399', tension: 0.4, borderWidth: 4, pointRadius: 0 },
                    { label: 'LM35 - B', data: <?php echo json_encode($lm2); ?>, borderColor: '#a78bfa', tension: 0.4, borderWidth: 4, pointRadius: 0 }
                ]
            },
            options: chartStyle
        });
    </script>
</body>
</html>
