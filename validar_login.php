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

// Paso 1: Conectamos con el usuario administrador para poder hacer el SELECT de verificación
$link_temp = Conectarse(DB_ADMIN_USER, DB_ADMIN_PASS);

if (!$link_temp) {
    echo "<script>
            alert('Error interno: no se pudo conectar a la base de datos.');
            window.location.href='index.php';
          </script>";
    exit();
}

// Paso 2: Buscamos al empleado por correo y contraseña en t_empleados
$correo_esc = mysqli_real_escape_string($link_temp, $correo);
$contra_esc = mysqli_real_escape_string($link_temp, $contra);

$query = "SELECT id, nombre, salario, correo FROM t_empleados WHERE correo = '$correo_esc' AND contrasena = '$contra_esc'";
$resultado = mysqli_query($link_temp, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    mysqli_close($link_temp);
    echo "<script>
            alert('Usuario o contraseña incorrectos');
            window.location.href='index.php';
          </script>";
    exit();
}

// Paso 3: Credenciales correctas. Determinamos el rol según el salario
$empleado = mysqli_fetch_assoc($resultado);
mysqli_close($link_temp);

$salario = floatval($empleado['salario']);

if ($salario > 7500) {
    // Es administrador → usa el usuario MySQL con ALL PRIVILEGES
    $_SESSION['db_user'] = DB_ADMIN_USER;
    $_SESSION['db_pass'] = DB_ADMIN_PASS;
    $_SESSION['rol'] = 'administrador';
} else {
    // Es empleado → usa el usuario MySQL con permisos limitados
    $_SESSION['db_user'] = DB_EMPLEADO_USER;
    $_SESSION['db_pass'] = DB_EMPLEADO_PASS;
    $_SESSION['rol'] = 'empleado';
}

// Guardamos los datos del empleado en sesión para autollenar formularios
$_SESSION['id_empleado'] = $empleado['id'];
$_SESSION['nombre_empleado'] = $empleado['nombre'];
$_SESSION['correo_empleado'] = $empleado['correo'];
$_SESSION['salario_empleado'] = $empleado['salario'];

// Redirigimos al Dashboard
header("Location: barra_lateral/Dashboard.php");
exit();
?>
