<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
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
                                echo '<option value="'.$row['id'].'" data-precio="'.$row['precio'].'">'.$row['nombre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-grupo">
                        <label>ID Producto</label>
                        <input type="number" id="idProducto" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Cantidad</label>
                        <input type="number" id="cantidad" placeholder="Ingrese la cantidad">
                    </div>

                    <div class="input-grupo">
                        <label>Subtotal</label>
                        <input type="number" id="subtotal" placeholder="Se calcula automáticamente" step="0.01" readonly>
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">Todos los campos son obligatorios</label>
                    </div>
                </div>
            </form>

            <div style="text-align: center; margin-top: -10px; margin-bottom: 30px;">
                <button type="button" onclick="agregarProducto()"
                    style="background: none; border: none; cursor: pointer; ">
                    <img src="../../../Imagenes/masP.png" alt="Agregar" style="width: 45px; height: auto;">
                </button>
                <div style="color: #888; font-size: 16px;">¿Agregar otro producto?</div>
            </div>

            <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                <input type="button" value="ATRÁS" onClick="history.go(-1)" class="btn-secundario">

                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        // Arreglo para almacenar en sesion los productos de la venta actual
        var productosVenta = JSON.parse(sessionStorage.getItem('productosVenta') || "[]");

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
                // Cálculo automático
                document.getElementById('subtotal').value = (precio * cantidad).toFixed(2);
            } else {
                document.getElementById('subtotal').value = '';
            }
        }

        function agregarProducto() {
            var select = document.getElementById('nombreProducto');
            if (select.value == "") {
                alert("Seleccione un producto para agregarlo.");
                return;
            }
            if (document.getElementById('cantidad').value == "" || document.getElementById('subtotal').value == "") {
                alert("Ingrese una cantidad válida.");
                return;
            }

            var selectedOption = select.options[select.selectedIndex];
            productosVenta.push({
                nombre: selectedOption.text,
                id: document.getElementById('idProducto').value,
                cantidad: document.getElementById('cantidad').value,
                subtotal: document.getElementById('subtotal').value
            });

            sessionStorage.setItem('productosVenta', JSON.stringify(productosVenta));
            alert("Producto agregado.");
            
            document.getElementById('venta2form').reset();
            document.getElementById('idProducto').value = '';
        }

        function valida_enviar() {
            var select = document.getElementById('nombreProducto');
            
            // Si hay algo escrito en el form, lo intentamos agregar o avisamos
            if (select.value !== "" && document.getElementById('cantidad').value !== "") {
                var selectedOption = select.options[select.selectedIndex];
                productosVenta.push({
                    nombre: selectedOption.text,
                    id: document.getElementById('idProducto').value,
                    cantidad: document.getElementById('cantidad').value,
                    subtotal: document.getElementById('subtotal').value
                });
                sessionStorage.setItem('productosVenta', JSON.stringify(productosVenta));
            } else if (select.value !== "" || document.getElementById('cantidad').value !== "") {
                alert("Complete los campos del producto actual, o límpielos para continuar.");
                return 0;
            }

            if (productosVenta.length === 0) {
                alert("No hay productos agregados a la venta.");
                return 0;
            }

            window.location.href = "Ventas3.php";
        }
    </script>
</body>

</html>
