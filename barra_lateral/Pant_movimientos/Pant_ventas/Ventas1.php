<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
?>
<!--Si la venta es a domicilio, disminuir desechables en la tabla de materiales-->
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
                <div class="form-grid">

                    <div class="input-grupo">
                        <label>Nombre Cliente</label>
                        <select id="nombreCliente">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $clientesData = [];
                            $result = mysqli_query($link, "SELECT id, nombre, codigo_postal, calle, colonia, estado FROM t_clientes ORDER BY nombre") or die(mysqli_error($link));
                            while($row = mysqli_fetch_array($result)){
                                echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
                                $clientesData[$row['id']] = [
                                    'cp' => $row['codigo_postal'],
                                    'calle' => $row['calle'],
                                    'colonia' => $row['colonia'],
                                    'estado' => $row['estado']
                                ];
                            }
                            $jsonClientes = json_encode($clientesData);
                            ?>
                        </select>
                    </div>

                    <div class="input-grupo">
                        <label>ID Cliente</label>
                        <input type="number" id="idCliente" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Fecha</label>
                        <input type="date" id="fechaDia">
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
                        <select id="servicioDomicilio">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                    <div class="input-grupo">
                        <label>Nombre Empleado</label>
                        <input type="text" id="nombreEmpleado" value="<?php echo isset($_SESSION['nombre_empleado']) ? $_SESSION['nombre_empleado'] : ''; ?>" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>ID Empleado</label>
                        <input type="number" id="idEmpleado" value="<?php echo isset($_SESSION['id_empleado']) ? $_SESSION['id_empleado'] : ''; ?>" readonly>
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">Todos los campos son obligatorios</label>
                    </div>
                </div>
            </form>
            <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                <a class="btn-accion" href="../../Movimientos.php" onclick="sessionStorage.removeItem('productosVenta'); sessionStorage.removeItem('ventaIdCliente'); sessionStorage.removeItem('ventaFecha'); sessionStorage.removeItem('ventaServicioDomicilio'); sessionStorage.removeItem('ventaIdEmpleado');">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONTINUAR</a>
            </div>

        </div>
    </div>

    <script>
        var clientesData = <?php echo isset($jsonClientes) ? $jsonClientes : '{}'; ?>;

        // Auto-llenar ID al seleccionar un cliente
        document.getElementById('nombreCliente').addEventListener('change', function() {
            document.getElementById('idCliente').value = this.value;
        });

        function valida_enviar() {
            var idCli = document.getElementById('idCliente').value;
            var servDom = document.getElementById('servicioDomicilio').value;

            if (document.getElementById('nombreCliente').value == "") {
                alert("Nombre del cliente no ingresado");
                return 0;
            } if (idCli == "") {
                alert("ID del cliente no ingresado");
                return 0;
            } if (document.getElementById('nombreEmpleado').value == "") {
                alert("Nombre del empleado no ingresado");
                return 0;
            } if (document.getElementById('idEmpleado').value == "") {
                alert("ID del empleado no ingresado");
                return 0;
            }

            if (servDom === "si") {
                var c = clientesData[idCli];
                if (!c || !c.cp || !c.calle || !c.colonia || !c.estado || c.cp.trim() === "" || c.calle.trim() === "" || c.colonia.trim() === "" || c.estado.trim() === "") {
                    alert("El cliente no cuenta con una dirección completa (código postal, calle, colonia y estado) para el servicio a domicilio.");
                    return 0;
                }
            }

            // Guardar datos generales de la venta en sessionStorage para Ventas3
            sessionStorage.setItem('ventaIdCliente', document.getElementById('idCliente').value);
            sessionStorage.setItem('ventaFecha', document.getElementById('fechaDia').value);
            sessionStorage.setItem('ventaServicioDomicilio', document.getElementById('servicioDomicilio').value);
            sessionStorage.setItem('ventaIdEmpleado', document.getElementById('idEmpleado').value);
            window.location.href = "Ventas2.php";
        }
    </script>
</body>

</html>
