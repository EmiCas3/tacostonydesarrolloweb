<?php
session_start();
include("conex.php");

$correo = isset($_POST['user']) ? trim($_POST['user']) : '';
$contra = isset($_POST['contra']) ? $_POST['contra'] : '';

if ($correo === '' || $contra === '') {
    echo "<script>
            alert('Debe ingresar usuario y contraseña');
            window.location.href='index.php';
          </script>";
    exit();
}

// Paso 1: Conectamos con el único usuario MySQL
$link = Conectarse();

if (!$link) {
    echo "<script>
            alert('Error interno: no se pudo conectar a la base de datos.');
            window.location.href='index.php';
          </script>";
    exit();
}

// Paso 2: Buscamos al empleado por correo y contraseña (texto plano) en t_empleados
$correo_esc = mysqli_real_escape_string($link, $correo);
$contra_esc = mysqli_real_escape_string($link, $contra);

$query = "SELECT id, nombre, salario, correo FROM t_empleados WHERE correo = '$correo_esc' AND contrasena = '$contra_esc'";
$resultado = mysqli_query($link, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    mysqli_close($link);
    echo "<script>
            alert('Usuario o contraseña incorrectos');
            window.location.href='index.php';
          </script>";
    exit();
}

// Paso 3: Credenciales correctas. Determinamos el rol según el salario
$empleado = mysqli_fetch_assoc($resultado);
mysqli_close($link);

$salario = floatval($empleado['salario']);

if ($salario > 7500) {
    $_SESSION['rol'] = 'administrador';
} else {
    $_SESSION['rol'] = 'empleado';
}

// Guardamos los datos del empleado en sesión
$_SESSION['id_empleado'] = $empleado['id'];
$_SESSION['nombre_empleado'] = $empleado['nombre'];
$_SESSION['correo_empleado'] = $empleado['correo'];
$_SESSION['salario_empleado'] = $empleado['salario'];

// Marcamos la sesión como autentificada
$_SESSION["autentificado"] = "SI";

// Redirigimos al Dashboard
header("Location: barra_lateral/Dashboard.php");
exit();
?>
