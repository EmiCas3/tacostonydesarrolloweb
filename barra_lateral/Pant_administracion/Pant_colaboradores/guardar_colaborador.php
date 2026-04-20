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
$telefono  = isset($_POST['numero_telefono']) ? trim($_POST['numero_telefono']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if ($nombre === '' || $correo === '' || $salario === '' || $contrasena === '' || $telefono === '') {
    echo "<script>alert('Error: Todos los campos, incluyendo el teléfono, son obligatorios.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

if (strlen($telefono) != 10 || !ctype_digit($telefono)) {
    echo "<script>alert('Número de teléfono no válido (debe tener 10 dígitos).'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error de conexión.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$nombre_esc   = mysqli_real_escape_string($link, $nombre);
$correo_esc   = mysqli_real_escape_string($link, $correo);
$telefono_esc = mysqli_real_escape_string($link, $telefono);
$salario_val  = floatval($salario);
$hash_pw      = hash('sha256', $contrasena);

// 5. Check for Duplicate Email
$checkCor = mysqli_query($link, "SELECT id FROM t_empleados WHERE LOWER(correo) = LOWER('$correo_esc')");
if ($checkCor && mysqli_num_rows($checkCor) > 0) {
    mysqli_close($link);
    echo "<script>alert('El correo ya está registrado.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

// 6. Check for Duplicate Phone (This prevents the Fatal Error)
$checkTel = mysqli_query($link, "SELECT id FROM t_empleados WHERE numero_telefono = '$telefono_esc'");
if ($checkTel && mysqli_num_rows($checkTel) > 0) {
    mysqli_close($link);
    echo "<script>alert('El número de teléfono ya está asignado a otro colaborador.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

// 7. Insert
$query = "INSERT INTO t_empleados (nombre, salario, numero_telefono, correo, contrasena)
          VALUES ('$nombre_esc', $salario_val, '$telefono_esc', '$correo_esc', '$hash_pw')";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Colaborador registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar. Intente de nuevo.'); window.location.href='Colaboradores1.php';</script>";
}
?>