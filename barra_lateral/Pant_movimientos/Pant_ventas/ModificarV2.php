<?php include("../../../conex.php");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
            box-sizing: border-box;
            min-height: 100vh;
        }

        .menu-item {
            padding: 15px 20px;
            color: #000000;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-item.activo {
            background-color: #f6821f;
        }

        .menu-item:hover:not(.activo) {
            background-color: #F9D864;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inventario-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 50px;
            width: 100%;
            max-width: 800px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .titulo-caja {
            background: #f6821f;
            color: #000000;
            font-weight: bold;
            font-size: 22px;
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .tabla-contenedor {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            margin-bottom: 40px;
        }

        .tabla-header {
            background: #f6821f;
            color: #000;
            font-weight: bold;
            display: flex;
            padding: 15px 30px;
        }

        .tabla-body {
            background-color: #E6E6E6;
            height: auto;
            max-height: 250px;
            overflow-y: auto;
            padding: 10px 0;
        }

        .tabla-body::-webkit-scrollbar {
            width: 6px;
        }

        .tabla-body::-webkit-scrollbar-track {
            background: transparent;
            margin: 10px 0;
        }

        .tabla-body::-webkit-scrollbar-thumb {
            background-color: #A0A0A0;
            border-radius: 10px;
        }

        .fila {
            display: flex;
            padding: 10px 30px;
            font-weight: bold;
            color: #333;
            align-items: center;
        }

        .col-id {
            width: 10%;
        }

        .col-mat {
            width: 40%;
        }

        .col-stock {
            width: 20%;
            text-align: center;
        }

        .col-subtotal {
            width: 20%;
            text-align: center;
        }

        .col-accion {
            width: 10%;
            text-align: center;
            display: flex;
            justify-content: center;
        }

        .botones-bottom {
            display: flex;
            justify-content: space-between;
        }

        .btn-accion {
            background: #f6821f;
            color: #000000;
            border: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }

        .btn-accion:hover {
            background-color: #DC7B3C;
        }

        .btn-secundario {
            background: #f6821f;
            color: #000000;
            border: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }

        .btn-secundario:hover {
            background-color: #DC7B3C;
        }

        .menu-dropdown {
            position: relative;
        }

        .submenu {
            display: none;
            flex-direction: column;
            background-color: #f9f9f9;
            border-left: 4px solid #F6821F;
            margin-left: 20px;
            margin-right: 20px;
            margin-top: -5px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .menu-dropdown:hover .submenu {
            display: flex;
        }

        .submenu-item {
            padding: 12px 20px;
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s, color 0.2s;
        }

        .submenu-item:hover {
            color: #F6821F;
            background-color: #E5E5E5;
        }
    </style>
</head>

<body>
    <div
        style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%; position: sticky; top: 20px;">
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
        <a href="../../Movimientos.html" class="menu-item activo">
            <img src="../../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../../Reportes.html" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../../Administracion.html" class="menu-item">
            <img src="../../../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="../../Catalogo.html" class="menu-item">
            <img src="../../../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../../../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="../../Configuracion.html" class="submenu-item">Editar Perfil</a>
                <a href="../../Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../../Login.php" class="submenu-item">Cerrar Sesión</a>
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
                <a href="../../Movimientos.html" class="btn-accion">CONFIRMAR</a>
            </div>

        </div>
    </div>

</body>

</html>