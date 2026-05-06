<?php
/**
 * Outils d'administration pour le Système Hybride
 */
require_once 'config.php';

$conn = getDbConnection();


$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'generate':
        // Générer des données pour deux appareils différents
        $devices = ["ARD-01", "ARD-02"];
        
        foreach ($devices as $device) {
            for ($i = 0; $i < 30; $i++) {
                // Plages légèrement différentes pour les distinguer
                if ($device == "ARD-01") {
                    $t = 22.0 + (rand(0, 50) / 10.0);
                    $h = 45.0 + (rand(0, 100) / 10.0);
                } else {
                    $t = 18.0 + (rand(0, 50) / 10.0);
                    $h = 35.0 + (rand(0, 100) / 10.0);
                }
                
                $l1 = 18.0 + (rand(0, 150) / 10.0);
                $l2 = 18.0 + (rand(0, 150) / 10.0);
                $date = date('Y-m-d H:i:s', strtotime("-" . ($i * 45) . " minutes"));
                
                $sql = "INSERT INTO capteurs (device_id, temperature, humidite, lm1, lm2, date_enregistrement) 
                        VALUES ('$device', $t, $h, $l1, $l2, '$date')";
                $conn->query($sql);
            }
        }
        header("Location: ../pages/settings.php?msg=Simulation complète générée (2 appareils)");

        break;

    case 'clear':
        // Vérification du mot de passe
        if (isset($_GET['pwd']) && $_GET['pwd'] === ADMIN_PASS) {
            $sql = "TRUNCATE TABLE capteurs";
            $conn->query($sql);
            header("Location: ../pages/dashboard.php?msg=Base vidée avec succès");

        } else {
            header("Location: ../pages/settings.php?msg=Erreur: Mot de passe incorrect");

        }
        break;

    case 'export':
        // Export Excel (CSV)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=donnees_capteurs.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Date', 'Appareil', 'Temp (°C)', 'Hum (%)', 'LM1 (°C)', 'LM2 (°C)'));
        
        $query = "SELECT * FROM capteurs ORDER BY date_enregistrement DESC";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
        break;

    default:
        header("Location: ../pages/dashboard.php");

        break;
}

$conn->close();
?>
