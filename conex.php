<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Único usuario MySQL para toda la aplicación
define('DB_USER', 'proydweb_bd2026');
define('DB_PASS', 'DWeb_p2@26');
define('DB_NAME', 'proydweb_p2026');

function Conectarse() {
    $link = @mysqli_connect("localhost", DB_USER, DB_PASS, DB_NAME);
    return $link ? $link : false;
}
?>