<?php
include("../../seguridad.php");
include("../../conex.php");
$link = Conectarse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasena = isset($_POST['password']) ? $_POST['password'] : '';
    $id_empleado = isset($_SESSION['id_empleado']) ? intval($_SESSION['id_empleado']) : 0;

    // Validaciones
    if ($id_empleado <= 0) {
        echo "<script>alert('Sesión inválida. Por favor, inicie sesión de nuevo.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (empty($contrasena)) {
        echo "<script>alert('La contraseña no puede estar vacía.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (strlen($contrasena) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    // Saneamiento de datos
    $contrasena_esc = mysqli_real_escape_string($link, $contrasena);

    // Actualización de la contraseña con SHA2-256
    $query = "UPDATE t_empleados SET contrasena = SHA2('$contrasena_esc', 256) WHERE id = $id_empleado";
    
    if (mysqli_query($link, $query)) {
        echo "<script>alert('Contraseña actualizada exitosamente.'); window.location.href='../Configuracion.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar la contraseña: " . mysqli_error($link) . "'); window.location.href='../Configuracion.php';</script>";
    }
} else {
    header("Location: ../Configuracion.php");
    exit;
}
?>
