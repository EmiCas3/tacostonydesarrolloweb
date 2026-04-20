<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();

// Obtener el ID de la venta desde GET
$id_venta = isset($_GET['id_venta']) ? intval($_GET['id_venta']) : 0;

// Obtener los productos de esa venta
$productos = [];
if ($id_venta > 0) {
    $query = "SELECT vp.id_producto, p.nombre, vp.cantidad, vp.subtotal
              FROM t_vender_particular vp
              INNER JOIN t_productos p ON vp.id_producto = p.id
              WHERE vp.id_vg = $id_venta
              ORDER BY p.nombre";
    $result = mysqli_query($link, $query) or die(mysqli_error($link));
    while($row = mysqli_fetch_assoc($result)){
        $productos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Movimientos - Modificar Venta</title>
    <link rel="stylesheet" href="../../../estilos/estilogenerico.css">
</head>

<body>
    <div class="sidebar">
        <div align="center">
            <a href="../../Dashboard.php">
                <img src="../../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="../../Dashboard.php" class="menu-item">
            <img src="../../../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="../../Inventario.php" class="menu-item">
            <img src="../../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../../Movimientos.php" class="menu-item activo">
            <img src="../../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../../Reportes.php" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../../Administracion.php" class="menu-item">
            <img src="../../../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="../../Catalogo.php" class="menu-item">
            <img src="../../../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../../../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="../../Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="../../Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="inventario-card">

            <div class="titulo-caja">
                PRODUCTOS DE LA VENTA
            </div>

            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <div class="col-id">ID</div>
                    <div class="col-mat">Nombre</div>
                    <div class="col-stock">Cantidad</div>
                    <div class="col-subtotal">Subtotal</div>
                    <div class="col-accion">Acción</div>
                </div>
                <div class="tabla-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php foreach ($productos as $producto): ?>
                        <tr class="fila">
                            <td class="col-id"><?php echo $producto['id_producto']; ?></td>
                            <td class="col-mat"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td class="col-stock"><?php echo $producto['cantidad']; ?></td>
                            <td class="col-subtotal">$<?php echo number_format($producto['subtotal'], 2); ?></td>
                            <td class="col-accion">
                                <a href="ModificarV3.php?id_venta=<?php echo $id_venta; ?>&id_producto=<?php echo $producto['id_producto']; ?>" style="text-decoration: none;">
                                    <img src="../../../Imagenes/icon-edit.png" width="22" alt="Editar">
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($productos)): ?>
                        <tr class="fila">
                            <td colspan="5" style="text-align:center; width: 100%; padding: 20px;">No hay productos registrados en esta venta.</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <div style="text-align: center; margin-top: -10px; margin-bottom: 30px;">
                <a href="ModificarV3.php?id_venta=<?php echo $id_venta; ?>" style="background: none; border: none; cursor: pointer; ">
                    <img src="../../../Imagenes/masP.png" alt="Agregar" style="width: 45px; height: auto;">
                </a>
                <div style="color: #888; font-size: 16px;">¿Agregar otro producto?</div>
            </div>
            <div class="botones-bottom">
                <input type="button" value="ATRÁS" onClick="history.go(-1)" class="btn-secundario">
                <a href="../../Movimientos.php" class="btn-accion" onclick="alert('Cambios guardados con éxito');">CONFIRMAR</a>
            </div>

        </div>
    </div>

</body>

</html>