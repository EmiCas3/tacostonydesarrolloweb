<?php
include("../../seguridad_admin.php");
include("../../conex.php");

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit();
}

$id     = isset($_POST['id'])     ? intval($_POST['id'])            : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre'])          : '';
$precio = isset($_POST['precio']) ? floatval($_POST['precio'])      : 0;

if ($id <= 0 || $nombre === '' || $precio < 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit();
}

$conexion = Conectarse();
if (!$conexion) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión a la BD']);
    exit();
}

// Verificar si se subió una imagen nueva
$rutaImagenBD = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $archivo   = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    // Validar extensión
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $permitidas)) {
        mysqli_close($conexion);
        echo json_encode(['ok' => false, 'msg' => 'Formato de imagen no permitido']);
        exit();
    }

    // Validar tamaño (máx 2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        mysqli_close($conexion);
        echo json_encode(['ok' => false, 'msg' => 'La imagen no debe superar 2MB']);
        exit();
    }

    // Generar nombre único para evitar colisiones
    $nombreArchivo = 'prod_' . $id . '_' . time() . '.' . $extension;
    $dirDestino    = realpath(__DIR__ . '/../../Imagenes/Productos') . DIRECTORY_SEPARATOR;

    if (!is_dir($dirDestino)) {
        mkdir($dirDestino, 0755, true);
    }

    $rutaCompleta = $dirDestino . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        mysqli_close($conexion);
        echo json_encode(['ok' => false, 'msg' => 'Error al guardar la imagen en el servidor']);
        exit();
    }

    // Ruta que se guardará en la BD (relativa al proyecto web)
    $rutaImagenBD = '/tacostonydesarrolloweb/Imagenes/Productos/' . $nombreArchivo;
}

// Construir el UPDATE
$nombre_esc = mysqli_real_escape_string($conexion, $nombre);

if ($rutaImagenBD !== null) {
    $imagen_esc = mysqli_real_escape_string($conexion, $rutaImagenBD);
    $query = "UPDATE t_productos SET nombre = '$nombre_esc', precio = $precio, imagen = '$imagen_esc' WHERE id = $id";
} else {
    $query = "UPDATE t_productos SET nombre = '$nombre_esc', precio = $precio WHERE id = $id";
}

$resultado = mysqli_query($conexion, $query);
mysqli_close($conexion);

if ($resultado) {
    echo json_encode(['ok' => true, 'msg' => 'Producto actualizado correctamente']);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error al actualizar el producto en la BD']);
}
?>
