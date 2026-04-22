<?php
include("../../../seguridad.php");
include("../../../conex.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: editar_cliente.php");
    exit;
}
$id             = isset($_POST['id'])           ? (int)trim($_POST['id'])           : 0;
$rfc            = isset($_POST['rfc'])           ? trim($_POST['rfc'])               : '';
$razon_social   = isset($_POST['razon_social'])  ? trim($_POST['razon_social'])       : '';
$codigo_postal  = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal'])      : '';
$calle          = isset($_POST['calle'])         ? trim($_POST['calle'])              : '';
$colonia        = isset($_POST['colonia'])       ? trim($_POST['colonia'])            : '';
$estado         = isset($_POST['estado'])        ? trim($_POST['estado'])             : '';

if ($id <= 0) {
    echo "<script>alert('Error: cliente no válido.'); window.location.href='editar_cliente.php';</script>";
    exit;
}
$link = Conectarse();
if (!$link) {
    echo "<script>alert('Error: no se pudo conectar a la base de datos.'); window.location.href='editar_cliente.php';</script>";
    exit;
}
$check = mysqli_query($link, "SELECT id FROM t_clientes WHERE id = $id");
if (!$check || mysqli_num_rows($check) === 0) {
    mysqli_close($link);
    echo "<script>alert('Error: cliente no encontrado.'); window.location.href='editar_cliente.php';</script>";
    exit;
}
if ($rfc !== '') {
    $rfc_esc = mysqli_real_escape_string($link, $rfc);
    $checkRfc = mysqli_query($link, "SELECT id FROM t_clientes WHERE UPPER(rfc) = UPPER('$rfc_esc') AND id != $id");
    if ($checkRfc && mysqli_num_rows($checkRfc) > 0) {
        mysqli_close($link);
        echo "<script>alert('El RFC ya está registrado en otro cliente.'); window.location.href='editar_cliente.php';</script>";
        exit;
    }
} else {
    $rfc_esc = '';
}
$razon_social_esc  = mysqli_real_escape_string($link, $razon_social);
$codigo_postal_esc = mysqli_real_escape_string($link, $codigo_postal);
$calle_esc         = mysqli_real_escape_string($link, $calle);
$colonia_esc       = mysqli_real_escape_string($link, $colonia);
$estado_esc        = mysqli_real_escape_string($link, $estado);

$rfcVal   = $rfc_esc          !== '' ? "'$rfc_esc'"          : 'NULL';
$rsVal    = $razon_social_esc !== '' ? "'$razon_social_esc'" : 'NULL';
$cpVal    = $codigo_postal_esc!== '' ? "'$codigo_postal_esc'": 'NULL';
$calleVal = $calle_esc        !== '' ? "'$calle_esc'"        : 'NULL';
$colVal   = $colonia_esc      !== '' ? "'$colonia_esc'"      : 'NULL';
$estVal   = $estado_esc       !== '' ? "'$estado_esc'"       : 'NULL';

$query = "UPDATE t_clientes SET
    rfc           = $rfcVal,
    razon_social  = $rsVal,
    codigo_postal = $cpVal,
    calle         = $calleVal,
    colonia       = $colVal,
    estado        = $estVal
  WHERE id = $id";

$result = mysqli_query($link, $query);
mysqli_close($link);

if ($result) {
    echo "<script>alert('Cliente actualizado con éxito.'); window.location.href='editar_cliente.php';</script>";
} else {
    echo "<script>alert('Error al actualizar el cliente. Intente de nuevo.'); window.location.href='editar_cliente.php';</script>";
}
?>
