<?php
/**
 * SCRIPT D'INSTALLATION LOCALE - HYBRIDE IO
 * Ce script crée la base de données et la table nécessaires.
 */

$servername = "localhost";
$username = "root"; // Utilisateur par défaut XAMPP
$password = "";     // Mot de passe par défaut XAMPP

// 1. Connexion sans base de données
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// 2. Création de la base de données
$sql = "CREATE DATABASE IF NOT EXISTS arduinodb";
if ($conn->query($sql) === TRUE) {
    echo "Base de données 'arduinodb' créée ou déjà existante.<br>";
} else {
    echo "Erreur lors de la création de la base de données : " . $conn->error . "<br>";
}

// 3. Sélection de la base
$conn->select_db("arduinodb");

// 4. Création de la table
$sql = "CREATE TABLE IF NOT EXISTS capteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50),
    temperature FLOAT,
    humidite FLOAT,
    lm1 FLOAT,
    lm2 FLOAT,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'capteurs' prête.<br>";
} else {
    echo "Erreur lors de la création de la table : " . $conn->error . "<br>";
}

// 5. Création de l'utilisateur 'Hybride' s'il n'existe pas (pour correspondre à config.php)
$sql_user = "CREATE USER IF NOT EXISTS 'Hybride'@'localhost' IDENTIFIED BY 'st6reLV6aNRhW0uJQK7Q'";
$conn->query($sql_user);
$sql_grant = "GRANT ALL PRIVILEGES ON arduinodb.* TO 'Hybride'@'localhost'";
$conn->query($sql_grant);
$conn->query("FLUSH PRIVILEGES");

echo "Utilisateur 'Hybride' configuré avec les privilèges nécessaires.<br>";
echo "<br><b>Installation terminée !</b> Vous pouvez maintenant utiliser le Dashboard localement.";

$conn->close();
?>
