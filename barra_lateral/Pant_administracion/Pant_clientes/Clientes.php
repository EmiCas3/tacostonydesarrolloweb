<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
$telefonos = [];
$correos = [];
$res = mysqli_query($link, "SELECT numero_telefono, correo FROM t_clientes");
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
    <title>Tacos Tony - Administración - Clientes</title>
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
                INFORMACIÓN DEL CLIENTE
            </div>

            <form id="clienteForm" method="post" action="guardar_cliente.php">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre Cliente <span style="color: #073A79;">*</span></label>
                        <input type="text" id="nombreCliente" name="nombre" placeholder="Ingrese el nombre">
                    </div>
                    <div class="input-grupo">
                        <label>RFC</label>
                        <input type="text" id="rfc" name="rfc" placeholder="Ingrese el RFC">
                    </div>
                    <div class="input-grupo">
                        <label>Razón Social</label>
                        <input type="text" id="razonSocial" name="razon_social" placeholder="Ingrese la razón social">
                    </div>
                    <div class="input-grupo">
                        <label>Código Postal</label>
                        <input type="number" id="codigoPostal" name="codigo_postal" placeholder="Ingrese el CP">
                    </div>
                    <div class="input-grupo">
                        <label>Número de Teléfono <span style="color: #073A79;">*</span></label>
                        <input type="text" id="numeroTelefono" name="numero_telefono" placeholder="Ingrese el número">
                    </div>
                    <div class="input-grupo">
                        <label>Correo Electrónico <span style="color: #073A79;">*</span></label>
                        <input type="text" id="correo" name="correo" placeholder="Ingrese el correo">
                    </div>
                    <div class="input-grupo">
                        <label>Calle</label>
                        <input type="text" id="calle" name="calle" placeholder="Ingrese la calle">
                    </div>
                    <div class="input-grupo">
                        <label>Colonia</label>
                        <input type="text" id="colonia" name="colonia" placeholder="Ingrese la colonia">
                    </div>
                    <div class="input-grupo">
                        <label>Estado</label>
                        <input type="text" id="estado" name="estado" placeholder="Ingrese el estado">
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">* Campos obligatorios</label>
                    </div>
                </div>
            </form>

            <div class="botones-bottom">
                <a class="btn-secundario" href="../../Administracion.php">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
                <button type="submit" id="btnSubmitCliente" form="clienteForm" style="display:none;"></button>
            </div>

        </div>
    </div>

    <script>
        function valida_enviar() {
            var telefonosDB = <?php echo $jsonTelefonos; ?>;
            var correosDB = <?php echo $jsonCorreos; ?>;

            if (document.getElementById("nombreCliente").value == "") {
                alert("Nombre del cliente no ingresado"); return;
            }
            if (document.getElementById("numeroTelefono").value == "") {
                alert("Número de Teléfono no ingresado"); return;
            }
            if (document.getElementById("numeroTelefono").value.length != 10) {
                alert("Número de Teléfono no es válido"); return;
            }
            if (telefonosDB.includes(document.getElementById("numeroTelefono").value)) {
                alert("El número de teléfono ya está registrado en la base de datos."); return;
            }
            if (document.getElementById("correo").value == "") {
                alert("Correo Electrónico no ingresado"); return;
            }
            if (correosDB.includes(document.getElementById("correo").value)) {
                alert("El correo electrónico ya está registrado en la base de datos."); return;
            }
            document.getElementById("btnSubmitCliente").click();

        }
    </script>
</body>

</html>