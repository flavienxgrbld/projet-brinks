<?php
// config.php
// Mettre ici les infos réelles : IP_SERVEUR_BDD, DB_USER, DB_PASS
define('DB_HOST', '192.168.1.55');   // ex: '192.168.1.10'
define('DB_USER', 'root');
define('DB_PASS', 'V@%y!2Nvqx&2t7');
define('DB_NAME', 'brinks');

// Options session

ini_set('session.cookie_httponly', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();