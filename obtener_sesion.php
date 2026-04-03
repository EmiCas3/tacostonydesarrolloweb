<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (isset($_SESSION['id_empleado'])) {
    echo json_encode([
        "logeado" => true,
        "id" => $_SESSION['id_empleado'],
        "nombre" => $_SESSION['nombre_empleado'],
        "correo" => $_SESSION['correo_empleado'],
        "rol" => $_SESSION['rol']
    ]);
} else {
    echo json_encode(["logeado" => false]);
}
?>
