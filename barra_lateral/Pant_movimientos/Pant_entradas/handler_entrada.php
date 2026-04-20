<?php
/**
 * handler_entrada.php
 * Recibe datos JSON por POST e inserta la entrada en la base de datos.
 * - INSERT en t_proporcionar_general (cabecera)
 * - INSERT en t_proporcionar_particular (detalle por material)
 * - UPDATE t_materiales.existencias += cantidad (aumenta stock)
 * Todo dentro de una transacción MySQL.
 */

include("../../../conex.php");
include("../../../seguridad.php");
header('Content-Type: application/json; charset=utf-8');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Leer cuerpo JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$id_proveedor = isset($input['id_proveedor']) ? intval($input['id_proveedor']) : 0;
$fecha = isset($input['fecha']) ? trim($input['fecha']) : '';
$materiales = isset($input['materiales']) ? $input['materiales'] : [];

// Validaciones básicas
if ($id_proveedor <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de proveedor inválido.']);
    exit;
}
if (empty($fecha)) {
    echo json_encode(['success' => false, 'message' => 'Fecha no proporcionada.']);
    exit;
}
if (empty($materiales) || !is_array($materiales)) {
    echo json_encode(['success' => false, 'message' => 'No hay materiales para registrar.']);
    exit;
}

$link = Conectarse();
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($link);

try {
    // 1. Insertar cabecera en t_proporcionar_general
    $fecha_esc = mysqli_real_escape_string($link, $fecha);
    $query_general = "INSERT INTO t_proporcionar_general (id_provedoor, fecha) VALUES ($id_proveedor, '$fecha_esc')";
    
    if (!mysqli_query($link, $query_general)) {
        throw new Exception('Error al insertar la cabecera de entrada: ' . mysqli_error($link));
    }
    
    $id_pg = mysqli_insert_id($link);
    
    // 2. Por cada material: insertar detalle y actualizar existencias
    foreach ($materiales as $mat) {
        $id_material = intval($mat['id']);
        $cantidad = floatval($mat['cantidad']);
        $costo = floatval($mat['precio']); // precio unitario = costo en proporcionar_particular
        
        if ($id_material <= 0 || $cantidad <= 0) {
            throw new Exception('Datos de material inválidos (id: ' . $id_material . ', cantidad: ' . $cantidad . ').');
        }
        
        // 2a. Insertar en t_proporcionar_particular
        $query_particular = "INSERT INTO t_proporcionar_particular (id_pg, id_material, cantidad, costo) 
                             VALUES ($id_pg, $id_material, $cantidad, $costo)";
        
        if (!mysqli_query($link, $query_particular)) {
            throw new Exception('Error al insertar detalle de material: ' . mysqli_error($link));
        }
        
        // 2b. Aumentar existencias en t_materiales
        $query_stock = "UPDATE t_materiales SET existencias = existencias + $cantidad WHERE id = $id_material";
        
        if (!mysqli_query($link, $query_stock)) {
            throw new Exception('Error al actualizar el stock del material: ' . mysqli_error($link));
        }
    }
    
    // Confirmar transacción
    mysqli_commit($link);
    echo json_encode(['success' => true, 'message' => 'Entrada registrada correctamente.', 'id_entrada' => $id_pg]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($link);
?>
