<?php include("../../../seguridad.php");
include("../../../conex.php");
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
            }

            // Preparar datos para el handler
            var datos = {
                id_venta: <?php echo $id_venta; ?>,
                id_producto: parseInt(form.idProducto.value),
                cantidad: parseFloat(form.cantidad.value),
                id_producto_anterior: <?php echo $id_producto_modificar; ?>
            };

            // Enviar datos al handler via AJAX
            fetch("handler_modificar_venta.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    alert("Guardado correctamente.");
                    window.location.href = "ModificarV2.php?id_venta=<?php echo $id_venta; ?>";
                } else {
                    alert("Error al guardar: " + result.message);
                }
            })
            .catch(function(error) {
                alert("Error de conexión: " + error.message);
            });
        }
    </script>
</body>

</html>
