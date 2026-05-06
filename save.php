<?php
require_once 'includes/config.php';


// Créer la connexion
$conn = getDbConnection();


// Récupérer les données de la requête (Supporte GET et POST)
if (isset($_REQUEST['data'])) {
    $raw_data = $_REQUEST['data'];
    // Extraire les valeurs (ID peut maintenant être du texte)
    if (preg_match('/I:([a-zA-Z0-9_-]+)/', $raw_data, $m)) $_REQUEST['id'] = $m[1];
    if (preg_match('/T:([\d.-]+)/', $raw_data, $m)) $_REQUEST['t'] = $m[1];
    if (preg_match('/H:([\d.]+)/', $raw_data, $m)) $_REQUEST['h'] = $m[1];
    if (preg_match('/1:([\d.-]+)/', $raw_data, $m)) $_REQUEST['l1'] = $m[1];
    if (preg_match('/2:([\d.-]+)/', $raw_data, $m)) $_REQUEST['l2'] = $m[1];
}

$id_param = isset($_REQUEST['id']) ? $_REQUEST['id'] : (isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : null);
$t_param = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
$h_param = isset($_REQUEST['h']) ? $_REQUEST['h'] : null;
$l1_param = isset($_REQUEST['l1']) ? $_REQUEST['l1'] : 0;
$l2_param = isset($_REQUEST['l2']) ? $_REQUEST['l2'] : 0;

if ($id_param !== null && $t_param !== null && $h_param !== null) {
    $device_id = $conn->real_escape_string($id_param);
    $t = (float)$t_param;
    $h = (float)$h_param;
    $l1 = (float)$l1_param;
    $l2 = (float)$l2_param;

    $sql = "INSERT INTO capteurs (device_id, temperature, humidite, lm1, lm2) VALUES ('$device_id', $t, $h, $l1, $l2)";

    if ($conn->query($sql) === TRUE) {
        echo "Données enregistrées avec succès";
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Paramètres manquants";
}


$conn->close();
?>

/* --- SCHEMA SQL À JOUR ---
CREATE DATABASE arduinodb;
USE arduinodb;

CREATE TABLE capteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50),
    temperature FLOAT,
    humidite FLOAT,
    lm1 FLOAT,
    lm2 FLOAT,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
*/
