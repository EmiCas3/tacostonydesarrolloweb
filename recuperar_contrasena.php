<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';

if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Correo no válido.']);
    exit;
}

include(__DIR__ . "/conex.php");
$link = Conectarse();

if (!$link) {
    echo json_encode(['ok' => false, 'error' => 'MySQL error: ' . mysqli_connect_error() . ' | errno: ' . mysqli_connect_errno()]);
    exit;
}

$correo_esc = mysqli_real_escape_string($link, $correo);

$query     = "SELECT id, nombre FROM t_empleados WHERE correo = '$correo_esc' LIMIT 1";
$resultado = mysqli_query($link, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo json_encode(['ok' => false, 'error' => 'No existe una cuenta con ese correo.']);
    exit;
}

$empleado = mysqli_fetch_assoc($resultado);
$id       = $empleado['id'];
$nombre   = $empleado['nombre'];

// Generar contraseña temporal de 10 caracteres
$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$nueva = '';
for ($i = 0; $i < 10; $i++) {
    $nueva .= $chars[random_int(0, strlen($chars) - 1)];
}

// Guardar contraseña temporal hasheada
$nueva_esc = mysqli_real_escape_string($link, $nueva);
$upd = "UPDATE t_empleados SET contrasena = SHA2('$nueva_esc', 256) WHERE id = $id";
if (!mysqli_query($link, $upd)) {
    echo json_encode(['ok' => false, 'error' => 'Error al generar contraseña temporal.']);
    exit;
}

// Enviar correo
$asunto  = "Recuperar_TacosTony";
$cuerpo  = "Hola $nombre,\n\n";
$cuerpo .= "Tu contraseña temporal es:\n\n";
$cuerpo .= "    $nueva\n\n";
$cuerpo .= "Por favor inicia sesión con esta contraseña y cámbiala desde Configuración > Editar Perfil.\n\n";
$cuerpo .= "— Sistema Tacos Tony";

$headers  = "From: no-reply@tacostony.com\r\n";
$headers .= "Reply-To: no-reply@tacostony.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8";

mail($correo, $asunto, $cuerpo, $headers);

echo json_encode(['ok' => true]);

mysqli_close($link);
?>
