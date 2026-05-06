<?php
include 'includes/config.php';
$sql = "INSERT IGNORE INTO devices (device_id, device_name, location) VALUES (0, 'Gateway ESP32', 'Poste de Contrôle')";
if($conn->query($sql)) {
    echo "ID 0 enregistré avec succès !";
} else {
    echo "Erreur : " . $conn->error;
}
?>
