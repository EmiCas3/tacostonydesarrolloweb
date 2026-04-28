<?php
include("../../seguridad_admin.php");
include("../../conex.php");
$link = Conectarse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasena  = isset($_POST['password']) ? $_POST['password'] : '';
    $correo      = isset($_POST['correo'])   ? trim($_POST['correo']) : '';
    $id_empleado = isset($_SESSION['id_empleado']) ? intval($_SESSION['id_empleado']) : 0;

    if ($id_empleado <= 0) {
        echo "<script>alert('Sesión inválida. Por favor, inicie sesión de nuevo.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (empty($correo)) {
        echo "<script>alert('El correo no puede estar vacío.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Ingrese un correo electrónico válido.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (!empty($contrasena) && strlen($contrasena) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    $correo_esc = mysqli_real_escape_string($link, $correo);

    $check = mysqli_query($link, "SELECT id FROM t_empleados WHERE correo = '$correo_esc' AND id != $id_empleado LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Ese correo ya está en uso por otro usuario.'); window.location.href='../Configuracion.php';</script>";
        exit;
    }

    if (!empty($contrasena)) {
        $contrasena_esc = mysqli_real_escape_string($link, $contrasena);
        $query = "UPDATE t_empleados SET correo = '$correo_esc', contrasena = SHA2('$contrasena_esc', 256) WHERE id = $id_empleado";
    } else {
        $query = "UPDATE t_empleados SET correo = '$correo_esc' WHERE id = $id_empleado";
    }

    if (mysqli_query($link, $query)) {
        $_SESSION['correo_empleado'] = $correo;
        echo "<script>alert('Cambios guardados exitosamente.'); window.location.href='../Configuracion.php';</script>";
    } else {
        echo "<script>alert('Error al guardar: " . mysqli_error($link) . "'); window.location.href='../Configuracion.php';</script>";
    }
} else {
    header("Location: ../Configuracion.php");
    exit;
}
?>
