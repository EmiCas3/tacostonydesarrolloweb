<?php
include("../../../seguridad.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Colaboradores1.php");
    exit;
}

$nombre    = isset($_POST['nombre'])    ? trim($_POST['nombre'])    : '';
$correo    = isset($_POST['correo'])    ? trim($_POST['correo'])    : '';
$salario   = isset($_POST['salario'])   ? trim($_POST['salario'])   : '';
$contrasena= isset($_POST['contrasena'])? trim($_POST['contrasena']): '';

if ($nombre === '' || $correo === '' || $salario === '' || $contrasena === '') {
    echo "<script>alert('Error: todos los campos son obligatorios.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

if (!is_numeric($salario) || floatval($salario) < 0) {
    echo "<script>alert('Salario inválido.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$nombre_esc     = mysqli_real_escape_string($link, $nombre);
$correo_esc     = mysqli_real_escape_string($link, $correo);
$salario_val    = floatval($salario);
$hash_contrasena= hash('sha256', $contrasena);

// Verificar duplicado
$checkCor = mysqli_query($link, "SELECT id FROM t_empleados WHERE LOWER(correo) = LOWER('$correo_esc')");
if ($checkCor && mysqli_num_rows($checkCor) > 0) {
    mysqli_close($link);
    echo "<script>alert('El correo electrónico ya está registrado en la base de datos.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$query = "INSERT INTO t_empleados (nombre, salario, numero_telefono, correo, contrasena)
          VALUES ('$nombre_esc', $salario_val, '', '$correo_esc', '$hash_contrasena')";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Colaborador registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar el colaborador. Intente de nuevo.'); window.location.href='Colaboradores1.php';</script>";
}
?>
