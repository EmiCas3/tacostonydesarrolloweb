<?php
/**
 * recuperar_contrasena.php
 * Genera una contraseña temporal, la actualiza en la BD y la envía por correo.
 * Usa PHPMailer con SMTP de Gmail.
 */

// PHPMailer (sin Composer)
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("conex.php");

header('Content-Type: application/json; charset=utf-8');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';

if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Correo no válido.']);
    exit();
}

// Conectar a la BD
$link = Conectarse();
if (!$link) {
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor.']);
    exit();
}

// Buscar empleado por correo
$correo_esc = mysqli_real_escape_string($link, $correo);
$query = "SELECT id, nombre, correo FROM t_empleados WHERE correo = '$correo_esc' LIMIT 1";
$resultado = mysqli_query($link, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    mysqli_close($link);
    echo json_encode(['ok' => false, 'error' => 'No se encontró una cuenta con ese correo.']);
    exit();
}

$empleado = mysqli_fetch_assoc($resultado);

// Generar contraseña temporal (8 caracteres alfanuméricos)
$caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$nueva_contra = '';
for ($i = 0; $i < 8; $i++) {
    $nueva_contra .= $caracteres[random_int(0, strlen($caracteres) - 1)];
}

// Actualizar la contraseña en la BD (hasheada con SHA2-256, igual que el login)
$query_update = "UPDATE t_empleados SET contrasena = SHA2('$nueva_contra', 256) WHERE id = " . intval($empleado['id']);
$res_update = mysqli_query($link, $query_update);

if (!$res_update) {
    mysqli_close($link);
    echo json_encode(['ok' => false, 'error' => 'Error al generar la nueva contraseña.']);
    exit();
}

mysqli_close($link);

// Enviar correo con PHPMailer
try {
    $mail = new PHPMailer(true);

    // Configuración SMTP de Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'fer.leondrg@gmail.com';
    $mail->Password   = 'inpgumxeygikinie'; // Sin espacios
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 15; // Timeout en segundos

    // Compatibilidad con servidores locales (EasyPHP)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );



    // Remitente y destinatario
    $mail->setFrom('fer.leondrg@gmail.com', 'Tacos Tony - Sistema');
    $mail->addAddress($empleado['correo'], $empleado['nombre']);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de contraseña - Tacos Tony';
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
        <div style="background-color: #F6821F; padding: 20px; text-align: center;">
            <h2 style="color: #FFFFFF; margin: 0;">Tacos Tony</h2>
            <p style="color: #FFFFFF; margin: 5px 0 0 0; font-size: 14px;">Sistema de Control de Inventario</p>
        </div>
        <div style="padding: 30px; background-color: #FFFFFF;">
            <p style="color: #333; font-size: 15px;">Hola <strong>' . htmlspecialchars($empleado['nombre']) . '</strong>,</p>
            <p style="color: #555; font-size: 14px;">Recibimos una solicitud para restablecer tu contraseña. Tu nueva contraseña temporal es:</p>
            <div style="background-color: #073A79; color: #FFFFFF; font-size: 22px; font-weight: bold; text-align: center; padding: 15px; border-radius: 8px; margin: 20px 0; letter-spacing: 3px;">
                ' . $nueva_contra . '
            </div>
            <p style="color: #555; font-size: 14px;">Ingresa al sistema con esta contraseña. Te recomendamos cambiarla desde tu perfil lo antes posible.</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">
            <p style="color: #999; font-size: 12px; text-align: center;">Si no solicitaste este cambio, contacta al administrador inmediatamente.</p>
        </div>
        <div style="background-color: #073A79; padding: 12px; text-align: center;">
            <p style="color: #F9D864; font-size: 12px; margin: 0;">© ' . date('Y') . ' Tacos Tony — Todos los derechos reservados</p>
        </div>
    </div>';

    $mail->AltBody = "Hola {$empleado['nombre']}, tu nueva contraseña temporal es: {$nueva_contra}. Ingresa al sistema y cámbiala lo antes posible.";

    $mail->send();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el correo. (Detalle: ' . $mail->ErrorInfo . ')']);
}
?>
