<?php
include("../../../seguridad_admin.php");
include("../../../conex.php");
$link = Conectarse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto = intval($_POST['idProducto']);
    $materiales = isset($_POST['materiales']) ? $_POST['materiales'] : [];
    $cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];

    if ($idProducto <= 0 || empty($materiales)) {
        echo "<script>alert('Datos incompletos.'); window.location.href='asignar1.php';</script>";
        exit;
    }

    // Insertar un nuevo registro en t_necesitar_general
    $queryGeneral = "INSERT INTO t_necesitar_general (id_producto, fecha) VALUES ($idProducto, NOW())";
    $resultGeneral = mysqli_query($link, $queryGeneral);

    if ($resultGeneral) {
        $idNg = mysqli_insert_id($link);

        $todoBien = true;
        for ($i = 0; $i < count($materiales); $i++) {
            $idMaterial = intval($materiales[$i]);
            $cantidad = floatval($cantidades[$i]);

            if ($idMaterial > 0 && $cantidad > 0) {
                $queryParticular = "INSERT INTO t_necesitar_particular (id_ng, id_material, cantidad) VALUES ($idNg, $idMaterial, $cantidad)";
                if (!mysqli_query($link, $queryParticular)) {
                    $todoBien = false;
                }
            }
        }

        if ($todoBien) {
            echo "<script>alert('Asignación guardada con éxito.'); window.location.href='asignar1.php';</script>";
        } else {
            echo "<script>alert('Algunos materiales no pudieron guardarse.'); window.location.href='asignar1.php';</script>";
        }
    } else {
        echo "<script>alert('Error al guardar la asignación: " . mysqli_error($link) . "'); window.location.href='asignar1.php';</script>";
    }
} else {
    header("Location: asignar1.php");
    exit;
}
?>
