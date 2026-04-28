<?php include("../../../seguridad_admin.php");
include("../../../conex.php");
$link = Conectarse();
$telefonos = [];
$correos = [];
$res = mysqli_query($link, "SELECT numero_telefono, correo FROM t_proovedores");
if ($res) {
    while($row = mysqli_fetch_array($res)) {
        $telefonos[] = $row['numero_telefono'];
        $correos[] = $row['correo'];
    }
}
$jsonTelefonos = json_encode($telefonos);
$jsonCorreos = json_encode($correos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Administración - Proveedores</title>
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
        <a href="../../Movimientos.php" class="menu-item">
            <img src="../../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../../Reportes.php" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../../Administracion.php" class="menu-item activo">
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
                DATOS DEL PROVEEDOR
            </div>

            <form id="proveedorForm" method="post" action="guardar_proveedor.php">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre</label>
                        <input type="text" id="nombreProveedor" name="nombre" placeholder="Ingrese el nombre">
                    </div>
                    <div class="input-grupo">
                        <label>Número de Teléfono</label>
                        <input type="text" id="telefono" name="numero_telefono" placeholder="Ingrese el número">
                    </div>
                    <div class="input-grupo">
                        <label>Correo Electrónico</label>
                        <input type="text" id="correo" name="correo" placeholder="Ingrese el correo">
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">Todos los campos son obligatorios</label>
                    </div>
                </div>
                <button type="submit" id="btnSubmitProv" style="display:none;"></button>
            </form>

            <div class="botones-bottom">
                <a class="btn-secundario" href="../../Administracion.php">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        function valida_enviar() {
            var telefonosDB = <?php echo $jsonTelefonos; ?>;
            var correosDB = <?php echo $jsonCorreos; ?>;

            if (document.getElementById("nombreProveedor").value == "") {
                alert("Nombre del proveedor no ingresado"); return;
            }
            if (document.getElementById("telefono").value == "") {
                alert("Número de Teléfono no ingresado"); return;
            }
            if (document.getElementById("telefono").value.length != 10) {
                alert("Número de Teléfono no es válido"); return;
            }
            if (telefonosDB.includes(document.getElementById("telefono").value)) {
                alert("El número de teléfono ya está registrado en la base de datos."); return;
            }
            if (document.getElementById("correo").value == "") {
                alert("Correo Electrónico no ingresado"); return;
            }
            if (correosDB.includes(document.getElementById("correo").value)) {
                alert("El correo electrónico ya está registrado en la base de datos."); return;
            }

            alert("Proveedor registrado con éxito");
            document.getElementById("btnSubmitProv").click();
        }
    </script>
</body>

</html>