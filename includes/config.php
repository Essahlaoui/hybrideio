<?php
/**
 * CONFIGURATION CENTRALE - HYBRIDE IO
 */

// Détection de l'environnement
$is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['SERVER_NAME'] === 'localhost');

if ($is_local) {
    // 1. Paramètres Base de Données (LOCAL XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'arduinodb');
    define('DB_USER', 'root'); // Utilise root en local pour éviter les problèmes de permissions
    define('DB_PASS', '');
} else {
    // 1. Paramètres Base de Données (PRODUCTION)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'arduinodb');
    define('DB_USER', 'Hybride');
    define('DB_PASS', 'st6reLV6aNRhW0uJQK7Q');
}


// 2. Paramètres Authentification Dashboard
define('ADMIN_USER', 'ESTKH');
define('ADMIN_PASS', 'AdminESTKH');

// 3. Paramètres Système
define('BASE_URL', 'http://hybrideio.duckdns.org/');
define('APP_NAME', 'HYBRIDE.IO');
date_default_timezone_set('Africa/Casablanca');

/**
 * Fonction de connexion à la base de données
 */
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }
    // Synchroniser l'heure de MySQL avec le Maroc (UTC+1)
    $conn->query("SET time_zone = '+01:00'");
    return $conn;
}

?>
