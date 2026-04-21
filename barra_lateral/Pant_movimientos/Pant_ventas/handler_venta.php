<?php
/**
 * handler_venta.php
 * Recibe datos JSON por POST e inserta la venta en la base de datos.
 * - INSERT en t_vender_general (cabecera)
 * - INSERT en t_vender_particular (detalle por producto; el trigger calcula subtotal)
 * - Consulta la receta de materiales del producto
 * - UPDATE t_materiales.existencias -= cantidad (disminuye stock)
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

$id_cliente = isset($input['id_cliente']) ? intval($input['id_cliente']) : 0;
$fecha = isset($input['fecha']) ? trim($input['fecha']) : '';
$servicio_domicilio = isset($input['servicio_domicilio']) ? strtoupper(trim($input['servicio_domicilio'])) : 'NO';
$id_empleado = isset($input['id_empleado']) ? intval($input['id_empleado']) : 0;
$productos = isset($input['productos']) ? $input['productos'] : [];

// Validaciones básicas
if ($id_cliente <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente inválido.']);
    exit;
}
if (empty($fecha)) {
    echo json_encode(['success' => false, 'message' => 'Fecha no proporcionada.']);
    exit;
}
if ($id_empleado <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de empleado inválido.']);
    exit;
}
if (empty($productos) || !is_array($productos)) {
    echo json_encode(['success' => false, 'message' => 'No hay productos para registrar.']);
    exit;
}

$link = Conectarse();
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// === VALIDACIÓN DE MATERIALES DISPONIBLES ===
// Acumular todos los materiales necesarios para todos los productos de la venta
$materiales_requeridos = []; // id_material => cantidad_total_necesaria

foreach ($productos as $prod) {
    $id_producto = intval($prod['id']);
    $cantidad_vendida = floatval($prod['cantidad']);
    
    if ($id_producto <= 0 || $cantidad_vendida <= 0) {
        continue; // Se validará después en la transacción
    }
    
    // Buscar la receta más reciente para este producto
    $query_receta_check = "SELECT np.id_material, np.cantidad 
                           FROM t_necesitar_particular np 
                           INNER JOIN t_necesitar_general ng ON np.id_ng = ng.id_ng 
                           WHERE ng.id_producto = $id_producto 
                           AND ng.id_ng = (
                               SELECT MAX(ng2.id_ng) 
                               FROM t_necesitar_general ng2 
                               WHERE ng2.id_producto = $id_producto
                           )";
    
    $result_receta_check = mysqli_query($link, $query_receta_check);
    
    if ($result_receta_check) {
        while ($receta = mysqli_fetch_assoc($result_receta_check)) {
            $id_mat = intval($receta['id_material']);
            $cant_mat = floatval($receta['cantidad']) * $cantidad_vendida;
            
            if (isset($materiales_requeridos[$id_mat])) {
                $materiales_requeridos[$id_mat] += $cant_mat;
            } else {
                $materiales_requeridos[$id_mat] = $cant_mat;
            }
        }
    }
}

// Verificar disponibilidad de cada material
$faltantes = [];
foreach ($materiales_requeridos as $id_mat => $cant_necesaria) {
    $query_stock_check = "SELECT nombre, existencias FROM t_materiales WHERE id = $id_mat";
    $result_stock_check = mysqli_query($link, $query_stock_check);
    
    if ($result_stock_check && $row_stock = mysqli_fetch_assoc($result_stock_check)) {
        $existencias = floatval($row_stock['existencias']);
        if ($existencias < $cant_necesaria) {
            $faltante = $cant_necesaria - $existencias;
            $faltantes[] = $row_stock['nombre'] . ' (necesario: ' . number_format($cant_necesaria, 2) . ', disponible: ' . number_format($existencias, 2) . ', faltante: ' . number_format($faltante, 2) . ')';
        }
    } else {
        $faltantes[] = 'Material ID ' . $id_mat . ' no encontrado en inventario';
    }
}

if (!empty($faltantes)) {
    echo json_encode([
        'success' => false, 
        'message' => 'No hay suficientes materiales para realizar la venta. Faltantes: ' . implode('; ', $faltantes)
    ]);
    mysqli_close($link);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($link);

try {
    // 1. Insertar cabecera en t_vender_general
    $fecha_esc = mysqli_real_escape_string($link, $fecha);
    $servicio_esc = mysqli_real_escape_string($link, $servicio_domicilio);
    
    $query_vg = "INSERT INTO t_vender_general (id_cliente, fecha, servicio_a_domicilio, id_empleado) 
                 VALUES ($id_cliente, '$fecha_esc', '$servicio_esc', $id_empleado)";
    
    if (!mysqli_query($link, $query_vg)) {
        throw new Exception('Error al insertar la cabecera de venta: ' . mysqli_error($link));
    }
    
    $id_vg = mysqli_insert_id($link);
    
    // 2. Por cada producto vendido
    foreach ($productos as $prod) {
        $id_producto = intval($prod['id']);
        $cantidad_vendida = floatval($prod['cantidad']);
        
        if ($id_producto <= 0 || $cantidad_vendida <= 0) {
            throw new Exception('Datos de producto inválidos (id: ' . $id_producto . ', cantidad: ' . $cantidad_vendida . ').');
        }
        
        // 2a. Insertar en t_vender_particular (el trigger calcula el subtotal automáticamente)
        $query_vp = "INSERT INTO t_vender_particular (id_vg, id_producto, cantidad) 
                     VALUES ($id_vg, $id_producto, $cantidad_vendida)";
        
        if (!mysqli_query($link, $query_vp)) {
            throw new Exception('Error al insertar detalle de producto: ' . mysqli_error($link));
        }
        
        // 2b. Buscar la última receta de materiales para este producto
        //     (el registro más reciente en t_necesitar_particular via t_necesitar_general)
        $query_receta = "SELECT np.id_material, np.cantidad 
                         FROM t_necesitar_particular np 
                         INNER JOIN t_necesitar_general ng ON np.id_ng = ng.id_ng 
                         WHERE ng.id_producto = $id_producto 
                         AND ng.id_ng = (
                             SELECT MAX(ng2.id_ng) 
                             FROM t_necesitar_general ng2 
                             WHERE ng2.id_producto = $id_producto
                         )";
        
        $result_receta = mysqli_query($link, $query_receta);
        
        if (!$result_receta) {
            throw new Exception('Error al buscar receta de materiales: ' . mysqli_error($link));
        }
        
        // 2c. Descontar stock basado en la receta
        while ($receta = mysqli_fetch_assoc($result_receta)) {
            $id_material = intval($receta['id_material']);
            // La cantidad de materiales se multiplica por la cantidad vendida del producto
            $cantidad_material = floatval($receta['cantidad']) * $cantidad_vendida;
            
            // Disminuir existencias en t_materiales
            $query_stock = "UPDATE t_materiales SET existencias = existencias - $cantidad_material WHERE id = $id_material";
            
            if (!mysqli_query($link, $query_stock)) {
                throw new Exception('Error al actualizar el stock del material: ' . mysqli_error($link));
            }
        }
    }
    
    // Confirmar transacción
    mysqli_commit($link);
    echo json_encode(['success' => true, 'message' => 'Venta registrada correctamente.', 'id_venta' => $id_vg]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($link);
?>
