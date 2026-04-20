<?php include("../../../seguridad.php"); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Movimientos - Ventas - Confirmar</title>
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
                RESUMEN DE VENTA
            </div>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Nombre del Producto</th>
                            <th style="width: 25%; text-align: center;">Cantidad</th>
                            <th style="width: 25%; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <!-- Las filas se agregarán dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div class="total-container">
                TOTAL GENERAL: $<span id="total-general">0.00</span>
            </div>

            <div class="botones-bottom">
                <input type="button" value="ATRÁS" onclick="history.go(-1)" class="btn-secundario">
                <a class="btn-accion" onclick="confirmarVenta()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        window.onload = function () {
            var storedData = sessionStorage.getItem("productosVenta");
            
            if (!storedData) {
                alert("Error: No se encontraron los datos de la venta");
                window.location.href = "Ventas2.php";
                return;
            }

            var productos = JSON.parse(storedData);
            
            if (productos.length === 0) {
                alert("No hay productos agregados a la venta.");
                window.location.href = "Ventas2.php";
                return;
            }

            var tablaBody = document.getElementById("tabla-body");
            var totalGeneral = 0;

            productos.forEach(function(producto) {
                var tr = document.createElement("tr");

                var tdNombre = document.createElement("td");
                tdNombre.textContent = producto.nombre;

                var tdCantidad = document.createElement("td");
                tdCantidad.textContent = producto.cantidad;
                tdCantidad.style.textAlign = "center";

                var tdSubtotal = document.createElement("td");
                tdSubtotal.textContent = "$" + parseFloat(producto.subtotal).toFixed(2);
                tdSubtotal.style.textAlign = "right";

                tr.appendChild(tdNombre);
                tr.appendChild(tdCantidad);
                tr.appendChild(tdSubtotal);
                tablaBody.appendChild(tr);

                totalGeneral += parseFloat(producto.subtotal);
            });

            document.getElementById("total-general").textContent = totalGeneral.toFixed(2);
        };

        function confirmarVenta() {
            var storedData = sessionStorage.getItem("productosVenta");
            var idCliente = sessionStorage.getItem("ventaIdCliente");
            var fecha = sessionStorage.getItem("ventaFecha");
            var servicioDomicilio = sessionStorage.getItem("ventaServicioDomicilio");
            var idEmpleado = sessionStorage.getItem("ventaIdEmpleado");

            if (!storedData || !idCliente || !fecha || !idEmpleado) {
                alert("Error: Faltan datos para registrar la venta.");
                return;
            }

            var productos = JSON.parse(storedData);

            // Preparar datos para el handler
            var datos = {
                id_cliente: parseInt(idCliente),
                fecha: fecha,
                servicio_domicilio: servicioDomicilio === "si" ? "SI" : "NO",
                id_empleado: parseInt(idEmpleado),
                productos: productos.map(function(p) {
                    return {
                        id: parseInt(p.id),
                        cantidad: parseFloat(p.cantidad)
                    };
                })
            };

            // Enviar datos al handler via AJAX
            fetch("handler_venta.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    // Limpiar sessionStorage
                    sessionStorage.removeItem("productosVenta");
                    sessionStorage.removeItem("ventaIdCliente");
                    sessionStorage.removeItem("ventaFecha");
                    sessionStorage.removeItem("ventaServicioDomicilio");
                    sessionStorage.removeItem("ventaIdEmpleado");
                    alert("Venta registrada con éxito.");
                    window.location.href = "../../Movimientos.php";
                } else {
                    alert("Error al registrar la venta: " + result.message);
                }
            })
            .catch(function(error) {
                alert("Error de conexión: " + error.message);
            });
        }
    </script>
</body>

</html>
