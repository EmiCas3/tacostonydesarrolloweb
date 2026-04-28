<?php
include("../../../seguridad_admin.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Productos1.php");
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$precio = isset($_POST['precio']) ? trim($_POST['precio']) : '';

if ($nombre === '' || $precio === '') {
    echo "<script>alert('Error: todos los campos son obligatorios.'); window.location.href='Productos1.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error de conexión.'); window.location.href='Productos1.php';</script>";
    exit;
}

$nombre_esc = mysqli_real_escape_string($link, $nombre);
$precio_val = floatval($precio);

if ($precio_val <= 0 || $precio_val > 9999.99) {
    echo "<script>alert('Precio fuera de rango permitido.'); window.location.href='Productos1.php';</script>";
    exit;
}

$check = mysqli_query($link, "SELECT nombre FROM t_productos WHERE LOWER(nombre) = LOWER('$nombre_esc')");
if ($check && mysqli_num_rows($check) > 0) {
    mysqli_close($link);
    echo "<script>alert('El producto ya existe.'); window.location.href='Productos1.php';</script>";
    exit;
}

$query = "INSERT INTO t_productos (nombre, precio) VALUES ('$nombre_esc', $precio_val)";
$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Producto guardado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar.'); window.location.href='Productos1.php';</script>";
}
?>