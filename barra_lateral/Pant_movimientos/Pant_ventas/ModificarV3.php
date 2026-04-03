<?php include("../../../conex.php");
$link = Conectarse();

$id_venta = isset($_GET['id_venta']) ? intval($_GET['id_venta']) : 0;
$id_producto_modificar = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;

$cantidad_inicial = '';
$subtotal_inicial = '';
if ($id_venta > 0 && $id_producto_modificar > 0) {
    $q_det = "SELECT cantidad, subtotal FROM t_vender_particular WHERE id_vg = $id_venta AND id_producto = $id_producto_modificar";
    $r_det = mysqli_query($link, $q_det);
    if ($row_det = mysqli_fetch_assoc($r_det)) {
        $cantidad_inicial = $row_det['cantidad'];
        $subtotal_inicial = $row_det['subtotal'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Movimientos - Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
            box-sizing: border-box;
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

        .formulario-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 50px;
            width: 100%;
            max-width: 700px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
        }

        .input-grupo {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .input-grupo label {
            font-weight: bold;
            color: #333333;
            font-size: 14px;
        }

        .input-grupo input,
        .input-grupo select {
            background-color: #F0F0F0;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            outline: none;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .input-grupo input:focus,
        .input-grupo select:focus {
            border-color: #f6821f;
        }

        .botones-bottom {
            display: flex;
            justify-content: flex-end;
        }

        .btn-accion {
            background: #f6821f;
            color: #000000;
            border: none;
            padding: 15px 50px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18px;
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
            padding: 15px 50px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18px;
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
            <a href="../../Dashboard.html">
                <img src="../../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="../../Dashboard.html" class="menu-item">
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
        <div class="formulario-card">

            <div class="titulo-caja">
                INFORMACIÓN PARTICULAR
            </div>
            <form id="venta2form" onsubmit="event.preventDefault(); valida_enviar();">
                <div class="form-grid">

                    <div class="input-grupo">
                        <label>Nombre Producto</label>
                        <select id="nombreProducto">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $result = mysqli_query($link, "SELECT id, nombre, precio FROM t_productos ORDER BY nombre") or die(mysqli_error($link));
                            while($row = mysqli_fetch_array($result)){
                                $selected = ($id_producto_modificar == $row['id']) ? 'selected' : '';
                                echo '<option value="'.$row['id'].'" data-precio="'.$row['precio'].'" '.$selected.'>'.$row['nombre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-grupo">
                        <label>ID Producto</label>
                        <input type="number" id="idProducto" value="<?php echo $id_producto_modificar > 0 ? $id_producto_modificar : ''; ?>" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Cantidad</label>
                        <input type="number" id="cantidad" value="<?php echo htmlspecialchars($cantidad_inicial); ?>" placeholder="Ingrese la cantidad">
                    </div>

                    <div class="input-grupo">
                        <label>Subtotal</label>
                        <input type="number" id="subtotal" value="<?php echo htmlspecialchars($subtotal_inicial); ?>" placeholder="Se calcula automáticamente" step="0.01" readonly>
                    </div>
                </div>
            </form>
            <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                <input type="button" value="ATRÁS" onClick="history.go(-1)" class="btn-secundario">

                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        // Auto-llenar ID al seleccionar un producto y recalcular
        document.getElementById('nombreProducto').addEventListener('change', function() {
            document.getElementById('idProducto').value = this.value;
            calcularSubtotal();
        });

        document.getElementById('cantidad').addEventListener('input', function() {
            calcularSubtotal();
        });

        function calcularSubtotal() {
            var select = document.getElementById('nombreProducto');
            var selectedOption = select.options[select.selectedIndex];
            var precio = selectedOption && selectedOption.value !== "" ? parseFloat(selectedOption.getAttribute('data-precio')) : 0;
            var cantidadString = document.getElementById('cantidad').value;
            var cantidad = cantidadString !== "" ? parseFloat(cantidadString) : NaN;
            
            if (precio > 0 && !isNaN(cantidad)) {
                // Cálculo simple de la BDD.
                document.getElementById('subtotal').value = (precio * cantidad).toFixed(2);
            } else {
                document.getElementById('subtotal').value = '';
            }
        }

        function valida_enviar() {
            var form = document.getElementById("venta2form");
            if (form.nombreProducto.value == "") {
                alert("Nombre de producto no ingresado");
                return 0;
            } if (form.idProducto.value == "") {
                alert("ID de producto no ingresado");
                return 0;
            } if (form.cantidad.value == "") {
                alert("Cantidad no ingresada");
                return 0;
            } if (form.subtotal.value == "") {
                alert("Subtotal no ingresado");
                return 0;
            } else {
                alert("Guardado correctamente.");
                window.location.href = "ModificarV2.php?id_venta=<?php echo $id_venta; ?>";
            }
        }
    </script>
</body>

</html>
