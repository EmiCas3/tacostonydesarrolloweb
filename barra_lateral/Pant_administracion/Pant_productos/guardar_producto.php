<?php
include("../../../seguridad.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: productos.php");
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$precio = isset($_POST['precio']) ? trim($_POST['precio']) : '';

if ($nombre === '' || $precio === '') {
    echo "<script>alert('Error: todos los campos son obligatorios.'); window.location.href='productos.php';</script>";
    exit;
}

if (!is_numeric($precio) || $precio <= 0) {
    echo "<script>alert('Error: el precio debe ser un número válido mayor a 0.'); window.location.href='productos.php';</script>";
    exit;
}

$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='productos.php';</script>";
    exit;
}

$nombre_esc = mysqli_real_escape_string($link, $nombre);
$precio_esc = mysqli_real_escape_string($link, $precio);

$checkProd = mysqli_query($link, "SELECT nombre FROM t_productos WHERE LOWER(nombre) = LOWER('$nombre_esc')");
if ($checkProd && mysqli_num_rows($checkProd) > 0) {
    mysqli_close($link);
    echo "<script>alert('El nombre de este producto ya está registrado en la base de datos.'); window.location.href='productos.php';</script>";
    exit;
}

$query = "INSERT INTO t_productos (nombre, precio) VALUES ('$nombre_esc', '$precio_esc')";
$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Producto registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar el producto. Intente de nuevo.'); window.location.href='productos.php';</script>";
}
?>