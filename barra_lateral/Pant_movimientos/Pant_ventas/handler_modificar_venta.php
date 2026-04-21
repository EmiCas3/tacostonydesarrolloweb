<?php
/**
 * handler_modificar_venta.php
 * Recibe datos JSON por POST para modificar un detalle de venta existente
 * o agregar un nuevo producto a una venta.
 * - Si id_producto_anterior > 0: UPDATE t_vender_particular + ajuste de materiales
 * - Si id_producto_anterior == 0: INSERT nuevo producto a la venta
 * - Ajusta t_necesitar_general/particular y t_materiales.existencias
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

$id_venta = isset($input['id_venta']) ? intval($input['id_venta']) : 0;
$id_producto = isset($input['id_producto']) ? intval($input['id_producto']) : 0;
$cantidad = isset($input['cantidad']) ? floatval($input['cantidad']) : 0;
$id_producto_anterior = isset($input['id_producto_anterior']) ? intval($input['id_producto_anterior']) : 0;

// Validaciones básicas
if ($id_venta <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de venta inválido.']);
    exit;
}
if ($id_producto <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido.']);
    exit;
}
if ($cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cantidad inválida.']);
    exit;
}

$link = Conectarse();
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// === VALIDACIÓN DE MATERIALES DISPONIBLES ===
// Calcular materiales que se devolverían si hay producto anterior
$materiales_devueltos = []; // id_material => cantidad a devolver

if ($id_producto_anterior > 0) {
    // Obtener cantidad anterior vendida
    $q_cant_ant = "SELECT cantidad FROM t_vender_particular WHERE id_vg = $id_venta AND id_producto = $id_producto_anterior";
    $r_cant_ant = mysqli_query($link, $q_cant_ant);
    $cantidad_anterior_check = 0;
    if ($r_cant_ant && $row_ca = mysqli_fetch_assoc($r_cant_ant)) {
        $cantidad_anterior_check = floatval($row_ca['cantidad']);
    }
    
    // Obtener receta del producto anterior
    $q_receta_ant = "SELECT np.id_material, np.cantidad 
                     FROM t_necesitar_particular np 
                     INNER JOIN t_necesitar_general ng ON np.id_ng = ng.id_ng 
                     WHERE ng.id_producto = $id_producto_anterior 
                     AND ng.id_ng = (
                         SELECT MAX(ng2.id_ng) 
                         FROM t_necesitar_general ng2 
                         WHERE ng2.id_producto = $id_producto_anterior
                     )";
    $r_receta_ant = mysqli_query($link, $q_receta_ant);
    if ($r_receta_ant) {
        while ($rec = mysqli_fetch_assoc($r_receta_ant)) {
            $id_mat = intval($rec['id_material']);
            // Nota: las cantidades en receta son por unidad, multiplicar por cantidad vendida anterior
            $materiales_devueltos[$id_mat] = floatval($rec['cantidad']) * $cantidad_anterior_check;
        }
    }
}

// Calcular materiales necesarios para el nuevo producto
$materiales_necesarios = []; // id_material => cantidad necesaria
$q_receta_nueva = "SELECT np.id_material, np.cantidad 
                   FROM t_necesitar_particular np 
                   INNER JOIN t_necesitar_general ng ON np.id_ng = ng.id_ng 
                   WHERE ng.id_producto = $id_producto 
                   AND ng.id_ng = (
                       SELECT MAX(ng2.id_ng) 
                       FROM t_necesitar_general ng2 
                       WHERE ng2.id_producto = $id_producto
                   )";
$r_receta_nueva = mysqli_query($link, $q_receta_nueva);
if ($r_receta_nueva) {
    while ($rec = mysqli_fetch_assoc($r_receta_nueva)) {
        $id_mat = intval($rec['id_material']);
        $materiales_necesarios[$id_mat] = floatval($rec['cantidad']) * $cantidad;
    }
}

// Verificar disponibilidad considerando la devolución
$faltantes = [];
foreach ($materiales_necesarios as $id_mat => $cant_necesaria) {
    $devuelto = isset($materiales_devueltos[$id_mat]) ? $materiales_devueltos[$id_mat] : 0;
    $neto_necesario = $cant_necesaria - $devuelto; // Lo que realmente se necesita extra
    
    if ($neto_necesario > 0) {
        $q_stock = "SELECT nombre, existencias FROM t_materiales WHERE id = $id_mat";
        $r_stock = mysqli_query($link, $q_stock);
        
        if ($r_stock && $row_s = mysqli_fetch_assoc($r_stock)) {
            $existencias = floatval($row_s['existencias']);
            if ($existencias < $neto_necesario) {
                $faltante_cant = $neto_necesario - $existencias;
                $faltantes[] = $row_s['nombre'] . ' (necesario: ' . number_format($neto_necesario, 2) . ', disponible: ' . number_format($existencias, 2) . ', faltante: ' . number_format($faltante_cant, 2) . ')';
            }
        } else {
            $faltantes[] = 'Material ID ' . $id_mat . ' no encontrado en inventario';
        }
    }
}

if (!empty($faltantes)) {
    echo json_encode([
        'success' => false, 
        'message' => 'No hay suficientes materiales para realizar la modificación. Faltantes: ' . implode('; ', $faltantes)
    ]);
    mysqli_close($link);
    exit;
}

mysqli_begin_transaction($link);

try {
    // Obtener la fecha de la venta
    $q_fecha = "SELECT fecha FROM t_vender_general WHERE id = $id_venta";
    $r_fecha = mysqli_query($link, $q_fecha);
    if (!$r_fecha || mysqli_num_rows($r_fecha) == 0) {
        throw new Exception('No se encontró la venta con id ' . $id_venta);
    }
    $fecha_venta = mysqli_fetch_assoc($r_fecha)['fecha'];

    if ($id_producto_anterior > 0) {
        // === CASO 1: Modificar producto existente ===
        
        // 1a. Obtener la cantidad anterior
        $q_ant = "SELECT cantidad FROM t_vender_particular WHERE id_vg = $id_venta AND id_producto = $id_producto_anterior";
        $r_ant = mysqli_query($link, $q_ant);
        if (!$r_ant || mysqli_num_rows($r_ant) == 0) {
            throw new Exception('No se encontró el producto anterior en la venta.');
        }
        $cantidad_anterior = floatval(mysqli_fetch_assoc($r_ant)['cantidad']);
        
        // 1b. Revertir el consumo de materiales del producto anterior
        //     Buscar el registro de necesitar_general más reciente para este producto en esta venta
        $q_ng_ant = "SELECT ng.id_ng FROM t_necesitar_general ng 
                     WHERE ng.id_producto = $id_producto_anterior 
                     ORDER BY ng.id_ng DESC LIMIT 1";
        $r_ng_ant = mysqli_query($link, $q_ng_ant);
        
        if ($r_ng_ant && $row_ng_ant = mysqli_fetch_assoc($r_ng_ant)) {
            $id_ng_ant = $row_ng_ant['id_ng'];
            
            // Revertir stock: devolver materiales consumidos
            $q_mat_ant = "SELECT id_material, cantidad FROM t_necesitar_particular WHERE id_ng = $id_ng_ant";
            $r_mat_ant = mysqli_query($link, $q_mat_ant);
            
            while ($mat = mysqli_fetch_assoc($r_mat_ant)) {
                $q_revertir = "UPDATE t_materiales SET existencias = existencias + " . floatval($mat['cantidad']) . " WHERE id = " . intval($mat['id_material']);
                if (!mysqli_query($link, $q_revertir)) {
                    throw new Exception('Error al revertir stock del material: ' . mysqli_error($link));
                }
            }
            
            // Eliminar registros de necesitar_particular para ese id_ng
            $q_del_np = "DELETE FROM t_necesitar_particular WHERE id_ng = $id_ng_ant";
            if (!mysqli_query($link, $q_del_np)) {
                throw new Exception('Error al eliminar necesitar_particular: ' . mysqli_error($link));
            }
            
            // Eliminar el registro de necesitar_general
            $q_del_ng = "DELETE FROM t_necesitar_general WHERE id_ng = $id_ng_ant";
            if (!mysqli_query($link, $q_del_ng)) {
                throw new Exception('Error al eliminar necesitar_general: ' . mysqli_error($link));
            }
        }
        
        // 1c. Si el producto cambió, eliminar el viejo e insertar el nuevo en vender_particular
        if ($id_producto_anterior != $id_producto) {
            $q_del_vp = "DELETE FROM t_vender_particular WHERE id_vg = $id_venta AND id_producto = $id_producto_anterior";
            if (!mysqli_query($link, $q_del_vp)) {
                throw new Exception('Error al eliminar producto anterior: ' . mysqli_error($link));
            }
            
            $q_ins_vp = "INSERT INTO t_vender_particular (id_vg, id_producto, cantidad) VALUES ($id_venta, $id_producto, $cantidad)";
            if (!mysqli_query($link, $q_ins_vp)) {
                throw new Exception('Error al insertar nuevo producto: ' . mysqli_error($link));
            }
        } else {
            // Mismo producto, solo actualizar cantidad (trigger recalcula subtotal en INSERT, para UPDATE hacemos manual)
            // Obtener el precio del producto
            $q_precio = "SELECT precio FROM t_productos WHERE id = $id_producto";
            $r_precio = mysqli_query($link, $q_precio);
            $precio = floatval(mysqli_fetch_assoc($r_precio)['precio']);
            $nuevo_subtotal = $cantidad * $precio;
            
            $q_upd = "UPDATE t_vender_particular SET cantidad = $cantidad, subtotal = $nuevo_subtotal WHERE id_vg = $id_venta AND id_producto = $id_producto";
            if (!mysqli_query($link, $q_upd)) {
                throw new Exception('Error al actualizar producto: ' . mysqli_error($link));
            }
        }
        
    } else {
        // === CASO 2: Agregar nuevo producto a la venta ===
        $q_ins = "INSERT INTO t_vender_particular (id_vg, id_producto, cantidad) VALUES ($id_venta, $id_producto, $cantidad)";
        if (!mysqli_query($link, $q_ins)) {
            throw new Exception('Error al insertar nuevo producto en la venta: ' . mysqli_error($link));
        }
    }
    
    // === Para ambos casos: crear nuevo registro de necesitar y descontar materiales ===
    
    // Insertar cabecera en t_necesitar_general
    $fecha_esc = mysqli_real_escape_string($link, $fecha_venta);
    $q_ng = "INSERT INTO t_necesitar_general (id_producto, fecha) VALUES ($id_producto, '$fecha_esc')";
    if (!mysqli_query($link, $q_ng)) {
        throw new Exception('Error al insertar necesitar general: ' . mysqli_error($link));
    }
    $id_ng_nuevo = mysqli_insert_id($link);
    
    // Buscar la receta de materiales más reciente para este producto (excluyendo el que acabamos de crear)
    $q_receta = "SELECT np.id_material, np.cantidad 
                 FROM t_necesitar_particular np 
                 INNER JOIN t_necesitar_general ng ON np.id_ng = ng.id_ng 
                 WHERE ng.id_producto = $id_producto 
                 AND ng.id_ng = (
                     SELECT MAX(ng2.id_ng) 
                     FROM t_necesitar_general ng2 
                     WHERE ng2.id_producto = $id_producto 
                     AND ng2.id_ng != $id_ng_nuevo
                 )";
    
    $r_receta = mysqli_query($link, $q_receta);
    if (!$r_receta) {
        throw new Exception('Error al buscar receta de materiales: ' . mysqli_error($link));
    }
    
    while ($receta = mysqli_fetch_assoc($r_receta)) {
        $id_material = intval($receta['id_material']);
        $cantidad_material = floatval($receta['cantidad']) * $cantidad;
        
        // Insertar en t_necesitar_particular
        $q_np = "INSERT INTO t_necesitar_particular (id_ng, id_material, cantidad) VALUES ($id_ng_nuevo, $id_material, $cantidad_material)";
        if (!mysqli_query($link, $q_np)) {
            throw new Exception('Error al insertar necesitar particular: ' . mysqli_error($link));
        }
        
        // Disminuir existencias
        $q_stock = "UPDATE t_materiales SET existencias = existencias - $cantidad_material WHERE id = $id_material";
        if (!mysqli_query($link, $q_stock)) {
            throw new Exception('Error al actualizar stock del material: ' . mysqli_error($link));
        }
    }
    
    // Confirmar transacción
    mysqli_commit($link);
    echo json_encode(['success' => true, 'message' => 'Venta modificada correctamente.']);
    
} catch (Exception $e) {
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($link);
?>
