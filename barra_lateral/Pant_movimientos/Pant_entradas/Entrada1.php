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
    <title>Tacos Tony - Movimientos - Entradas</title>
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
                REGISTRAR ENTRADA
            </div>

            <form id="entrada1form" method="post" action="#">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre Proveedor</label>
                        <select id="nombreProveedor">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $result = mysqli_query($link, "SELECT id, nombre FROM t_proovedores ORDER BY nombre") or die(mysqli_error($link));
                            while($row = mysqli_fetch_array($result)){
                                echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-grupo">
                        <label>ID Proveedor</label>
                        <input type="number" id="idProveedor" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Fecha</label>
                        <input type="date" id="fechaEntrada">
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
                            document.getElementById('fechaEntrada').value = anio + "-" + mes + "-" + dia;
                        </script>
                    </div>
                </div>

                <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                    <input type="button" value="CANCELAR" onClick="history.go(-1)" class="btn-secundario">
                    <a class="btn-accion" onclick="validarEntrada()">CONTINUAR</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-llenar ID al seleccionar un proveedor
        document.getElementById('nombreProveedor').addEventListener('change', function() {
            document.getElementById('idProveedor').value = this.value;
        });

        function validarEntrada() {
            if (document.getElementById('nombreProveedor').value == "") {
                alert("Nombre del proveedor no ingresado");
                return;
            }
            if (document.getElementById('idProveedor').value == "") {
                alert("ID del proveedor no ingresado");
                return;
            } else {
                window.location.href = "Entrada2.php";
            }
        }
    </script>
</body>

</html>
