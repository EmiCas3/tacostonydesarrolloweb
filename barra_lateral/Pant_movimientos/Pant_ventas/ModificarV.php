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
            gap: 20px;
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
