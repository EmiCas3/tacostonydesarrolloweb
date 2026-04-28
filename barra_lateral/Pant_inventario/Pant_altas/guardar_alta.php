<?php
include("../../../seguridad_admin.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Altas1.php");
    exit;
}

$nombre   = isset($_POST['nombre'])   ? trim($_POST['nombre'])   : '';
$cantidad = isset($_POST['cantidad']) ? trim($_POST['cantidad']) : '';

if ($nombre === '' || $cantidad === '') {
    echo "<script>alert('Error: datos incompletos.'); window.location.href='Altas1.php';</script>";
    exit;
}

if (!is_numeric($cantidad) || floatval($cantidad) < 0) {
    echo "<script>alert('Cantidad inválida.'); window.location.href='Altas1.php';</script>";
    exit;
}
$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='Altas1.php';</script>";
    exit;
}
$nombre_esc   = mysqli_real_escape_string($link, $nombre);
$cantidad_val = floatval($cantidad);


$checkQuery = "SELECT id FROM t_materiales WHERE LOWER(nombre) = LOWER('$nombre_esc')";
$checkResult = mysqli_query($link, $checkQuery);
if ($checkResult && mysqli_num_rows($checkResult) > 0) {
    mysqli_close($link);
    echo "<script>alert('Este material ya existe en la base de datos.'); window.location.href='Altas1.php';</script>";
    exit;
}

$query = "INSERT INTO t_materiales (nombre, existencias) VALUES ('$nombre_esc', $cantidad_val)";
$result = mysqli_query($link, $query);

mysqli_close($link);

if ($result) {
    echo "<script>alert('Material registrado con éxito.'); window.location.href='../../Inventario.php';</script>";
} else {
    echo "<script>alert('Error al guardar el material. Intente de nuevo.'); window.location.href='Altas1.php';</script>";
}
?>