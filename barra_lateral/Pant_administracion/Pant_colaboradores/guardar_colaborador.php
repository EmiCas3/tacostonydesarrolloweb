<?php
include("../../../seguridad_admin.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Colaboradores1.php");
    exit;
}

$nombre     = isset($_POST['nombre'])     ? trim($_POST['nombre'])     : '';
$correo     = isset($_POST['correo'])     ? trim($_POST['correo'])     : '';
$salario    = isset($_POST['salario'])    ? trim($_POST['salario'])    : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// 1. We removed $telefono from the mandatory check
if ($nombre === '' || $correo === '' || $salario === '' || $contrasena === '') {
    echo "<script>alert('Error: todos los campos son obligatorios.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error de conexión.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

$nombre_esc  = mysqli_real_escape_string($link, $nombre);
$correo_esc  = mysqli_real_escape_string($link, $correo);
$salario_val = floatval($salario);
$hash_pw     = hash('sha256', $contrasena);

/**
 * 2. THE FIX FOR THE DUPLICATE ERROR:
 * Since your DB doesn't allow NULL and requires UNIQUE, we generate 
 * a "fake" unique number using the current timestamp.
 * This satisfies the database without asking the user for a phone.
 */
$telefono_placeholder = substr(time(), -10); 

// 3. Check for Duplicate Email
$checkCor = mysqli_query($link, "SELECT id FROM t_empleados WHERE LOWER(correo) = LOWER('$correo_esc')");
if ($checkCor && mysqli_num_rows($checkCor) > 0) {
    mysqli_close($link);
    echo "<script>alert('El correo ya está registrado.'); window.location.href='Colaboradores1.php';</script>";
    exit;
}

// 4. Insert using the placeholder phone number
$query = "INSERT INTO t_empleados (nombre, salario, numero_telefono, correo, contrasena)
          VALUES ('$nombre_esc', $salario_val, '$telefono_placeholder', '$correo_esc', '$hash_pw')";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Colaborador registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar. Intente de nuevo.'); window.location.href='Colaboradores1.php';</script>";
}
?>