<?php
include("../../../seguridad.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Clientes.php");
    exit;
}
$nombre         = isset($_POST['nombre'])         ? trim($_POST['nombre'])         : '';
$rfc            = isset($_POST['rfc'])            ? trim($_POST['rfc'])            : '';
$razon_social   = isset($_POST['razon_social'])   ? trim($_POST['razon_social'])   : '';
$codigo_postal  = isset($_POST['codigo_postal'])  ? trim($_POST['codigo_postal'])  : '';
$numero_telefono= isset($_POST['numero_telefono'])? trim($_POST['numero_telefono']): '';
$correo         = isset($_POST['correo'])         ? trim($_POST['correo'])         : '';
$calle          = isset($_POST['calle'])          ? trim($_POST['calle'])          : '';
$colonia        = isset($_POST['colonia'])        ? trim($_POST['colonia'])        : '';
$estado         = isset($_POST['estado'])         ? trim($_POST['estado'])         : '';
if ($nombre === '' || $numero_telefono === '' || $correo === '') {
    echo "<script>alert('Error: campos obligatorios incompletos.'); window.location.href='Clientes.php';</script>";
    exit;
}
if (strlen($numero_telefono) != 10 || !ctype_digit($numero_telefono)) {
    echo "<script>alert('Número de teléfono no válido (debe tener 10 dígitos).'); window.location.href='Clientes.php';</script>";
    exit;
}
$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='Clientes.php';</script>";
    exit;
}
$nombre_esc          = mysqli_real_escape_string($link, $nombre);
$rfc_esc             = mysqli_real_escape_string($link, $rfc);
$razon_social_esc    = mysqli_real_escape_string($link, $razon_social);
$codigo_postal_esc   = mysqli_real_escape_string($link, $codigo_postal);
$numero_telefono_esc = mysqli_real_escape_string($link, $numero_telefono);
$correo_esc          = mysqli_real_escape_string($link, $correo);
$calle_esc           = mysqli_real_escape_string($link, $calle);
$colonia_esc         = mysqli_real_escape_string($link, $colonia);
$estado_esc          = mysqli_real_escape_string($link, $estado);

$checkTel = mysqli_query($link, "SELECT id FROM t_clientes WHERE numero_telefono = '$numero_telefono_esc'");
if ($checkTel && mysqli_num_rows($checkTel) > 0) {
    mysqli_close($link);
    echo "<script>alert('El número de teléfono ya está registrado en la base de datos.'); window.location.href='Clientes.php';</script>";
    exit;
}

$checkCor = mysqli_query($link, "SELECT id FROM t_clientes WHERE LOWER(correo) = LOWER('$correo_esc')");
if ($checkCor && mysqli_num_rows($checkCor) > 0) {
    mysqli_close($link);
    echo "<script>alert('El correo electrónico ya está registrado en la base de datos.'); window.location.href='Clientes.php';</script>";
    exit;
}

if ($rfc_esc !== '') {
    $checkRfc = mysqli_query($link, "SELECT id FROM t_clientes WHERE UPPER(rfc) = UPPER('$rfc_esc')");
    if ($checkRfc && mysqli_num_rows($checkRfc) > 0) {
        mysqli_close($link);
        echo "<script>alert('El RFC ya está registrado en la base de datos.'); window.location.href='Clientes.php';</script>";
        exit;
    }
}
$rfcVal          = $rfc_esc          !== '' ? "'$rfc_esc'"          : 'NULL';
$razonSocialVal  = $razon_social_esc !== '' ? "'$razon_social_esc'" : 'NULL';
$cpVal           = $codigo_postal_esc!== '' ? "'$codigo_postal_esc'": 'NULL';
$calleVal        = $calle_esc        !== '' ? "'$calle_esc'"        : 'NULL';
$coloniaVal      = $colonia_esc      !== '' ? "'$colonia_esc'"      : 'NULL';
$estadoVal       = $estado_esc       !== '' ? "'$estado_esc'"       : 'NULL';

$query = "INSERT INTO t_clientes (nombre, rfc, razon_social, codigo_postal, numero_telefono, correo, calle, colonia, estado)
          VALUES ('$nombre_esc', $rfcVal, $razonSocialVal, $cpVal, '$numero_telefono_esc', '$correo_esc', $calleVal, $coloniaVal, $estadoVal)";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Cliente registrado con éxito.'); window.location.href='../../Administracion.php';</script>";
} else {
    echo "<script>alert('Error al guardar el cliente. Intente de nuevo.'); window.location.href='Clientes.php';</script>";
}
?>
