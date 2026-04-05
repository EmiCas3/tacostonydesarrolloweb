<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Credenciales de los 2 usuarios MySQL creados con GRANT
define('DB_ADMIN_USER', 'administrador');
define('DB_ADMIN_PASS', 'admin123');
define('DB_EMPLEADO_USER', 'empleado');
define('DB_EMPLEADO_PASS', 'emple456');
define('DB_NAME', '2doAvance');
function Conectarse($user = null, $pass = null) {
    // Si no se pasan credenciales, usamos las de la sesión activa
    if ($user === null && $pass === null) {
        if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
            return false;
        }
        $user = $_SESSION['db_user'];
        $pass = $_SESSION['db_pass'];
    }

    $link = @mysqli_connect("localhost", $user, $pass, DB_NAME);
    return $link ? $link : false;
}
?>