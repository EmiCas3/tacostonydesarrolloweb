<?php
include("../../../seguridad_admin.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Proveedor.php");
    exit;
}

$nombre          = isset($_POST['nombre'])          ? trim($_POST['nombre'])          : '';
$numero_telefono = isset($_POST['numero_telefono']) ? trim($_POST['numero_telefono']) : '';
$correo          = isset($_POST['correo'])          ? trim($_POST['correo'])          : '';

if ($nombre === '' || $numero_telefono === '' || $correo === '') {
    echo "<script>alert('Error: todos los campos son obligatorios.'); window.location.href='Proveedor.php';</script>";
    exit;
}

if (strlen($numero_telefono) != 10 || !ctype_digit($numero_telefono)) {
    echo "<script>alert('Número de teléfono no válido (debe tener 10 dígitos).'); window.location.href='Proveedor.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='Proveedor.php';</script>";
    exit;
}

$nombre_esc          = mysqli_real_escape_string($link, $nombre);
$numero_telefono_esc = mysqli_real_escape_string($link, $numero_telefono);
$correo_esc          = mysqli_real_escape_string($link, $correo);

$checkTel = mysqli_query($link, "SELECT id FROM t_proovedores WHERE numero_telefono = '$numero_telefono_esc'");
if ($checkTel && mysqli_num_rows($checkTel) > 0) {
    mysqli_close($link);
    echo "<script>alert('El número de teléfono ya está registrado en la base de datos.'); window.location.href='Proveedor.php';</script>";
    exit;
}

$checkCor = mysqli_query($link, "SELECT id FROM t_proovedores WHERE LOWER(correo) = LOWER('$correo_esc')");
if ($checkCor && mysqli_num_rows($checkCor) > 0) {
    mysqli_close($link);
    echo "<script>alert('El correo electrónico ya está registrado en la base de datos.'); window.location.href='Proveedor.php';</script>";
    exit;
}

$query = "INSERT INTO t_proovedores (nombre, numero_telefono, correo)
          VALUES ('$nombre_esc', '$numero_telefono_esc', '$correo_esc')";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Proveedor registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar el proveedor. Intente de nuevo.'); window.location.href='Proveedor.php';</script>";
}
?>
