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
        <div align="center" ">
            <a href=" ../../Dashboard.php">
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
                INFORMACIÓN GENERAL
            </div>
            <form id="venta1form" method="post" action="#">
                <div class="input-grupo">
                    <label>Seleccionar Venta</label>
                    <select id="idVenta" style="margin-bottom: 20px;">
                        <option value="">-- Seleccione una Venta --</option>
                        <?php
                        $query_ventas = "SELECT v.id, v.fecha, v.id_cliente, c.nombre as cliente_nombre, v.servicio_a_domicilio, v.id_empleado, e.nombre as empleado_nombre 
                                         FROM t_vender_general v 
                                         INNER JOIN t_clientes c ON v.id_cliente = c.id
                                         INNER JOIN t_empleados e ON v.id_empleado = e.id
                                         ORDER BY v.id ASC";
                        $result_ventas = mysqli_query($link, $query_ventas) or die(mysqli_error($link));
                        while($row_v = mysqli_fetch_array($result_ventas)){
                            echo '<option value="'.$row_v['id'].'" 
                                  data-idcliente="'.$row_v['id_cliente'].'"
                                  data-fecha="'.date('Y-m-d', strtotime($row_v['fecha'])).'"
                                  data-domicilio="'.strtolower($row_v['servicio_a_domicilio']).'"
                                  data-idempleado="'.$row_v['id_empleado'].'"
                                  data-nombreempleado="'.htmlspecialchars($row_v['empleado_nombre']).'">'.
                                  str_pad($row_v['id'], 4, '0', STR_PAD_LEFT).' - '.$row_v['fecha'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-grid">

                    <div class="input-grupo">
                        <label>Nombre Cliente</label>
                        <select id="nombreCliente" disabled>
                            <option value="">-- Seleccione --</option>
                            <?php
                            $result = mysqli_query($link, "SELECT id, nombre FROM t_clientes ORDER BY nombre") or die(mysqli_error($link));
                            while($row = mysqli_fetch_array($result)){
                                echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-grupo">
                        <label>ID Cliente</label>
                        <input type="number" id="idCliente" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Fecha</label>
                        <input type="date" id="fechaDia" readonly>
                        <script>
                            let fecha = new Date();
                            let dia = fecha.getDate();
                            let mes = fecha.getMonth() + 1;
                            let anio = fecha.getFullYear();
                            if (dia < 10) {
                                dia = "0" + dia;
                            }
                            if (mes < 10) {
                                mes = "0" + mes;
                            }
                            document.getElementById('fechaDia').value = anio + "-" + mes + "-" + dia;
                        </script>
                    </div>

                    <div class="input-grupo">
                        <label>Servicio a Domicilio</label>
                        <select id="servicioDomicilio" disabled>
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                    <div class="input-grupo">
                        <label>Nombre Empleado</label>
                        <input type="text" id="nombreEmpleado" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>ID Empleado</label>
                        <input type="number" id="idEmpleado" placeholder="Se llena automáticamente" readonly>
                    </div>
                </div>
            </form>
            <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                <a class="btn-accion" href="../../Movimientos.php">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONTINUAR</a>
            </div>

        </div>
    </div>

    <script>
        // Llenar datos de la venta al seleccionar
        document.getElementById('idVenta').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            if (this.value !== "") {
                document.getElementById('nombreCliente').value = selectedOption.getAttribute('data-idcliente');
                document.getElementById('idCliente').value = selectedOption.getAttribute('data-idcliente');
                document.getElementById('fechaDia').value = selectedOption.getAttribute('data-fecha');
                document.getElementById('servicioDomicilio').value = selectedOption.getAttribute('data-domicilio');
                document.getElementById('nombreEmpleado').value = selectedOption.getAttribute('data-nombreempleado');
                document.getElementById('idEmpleado').value = selectedOption.getAttribute('data-idempleado');
            } else {
                document.getElementById('nombreCliente').value = "";
                document.getElementById('idCliente').value = "";
                document.getElementById('servicioDomicilio').value = "no";
                document.getElementById('nombreEmpleado').value = "";
                document.getElementById('idEmpleado').value = "";
            }
        });

        function valida_enviar() {
            var idVenta = document.getElementById('idVenta').value;
            if (idVenta == "") {
                alert("Por favor, seleccione una venta a modificar");
                return 0;
            } else {
                // Redirigir a ModificarV2.php pasando el id_venta como parámetro
                window.location.href = "ModificarV2.php?id_venta=" + idVenta;
            }
        }
    </script>
</body>

</html>
