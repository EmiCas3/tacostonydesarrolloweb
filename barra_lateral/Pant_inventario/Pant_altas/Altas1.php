<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
$nombres = [];
$res = mysqli_query($link, "SELECT nombre FROM t_materiales");
if ($res) {
    while($row = mysqli_fetch_array($res)) {
        $nombres[] = $row['nombre'];
    }
}
$jsonNombres = json_encode($nombres);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Inventario - Altas</title>
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
        <a href="../../Inventario.php" class="menu-item activo">
            <img src="../../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../../Movimientos.php" class="menu-item">
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
                REGISTRAR NUEVO MATERIAL
            </div>

            <form id="altasForm1" method="post" action="Altas2.php">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre del Material</label>
                        <input type="text" id="nombreMaterial" name="nombre" placeholder="Ingrese el nombre">
                    </div>
                    <div class="input-grupo">
                        <label>Cantidad Inicial</label>
                        <input type="number" id="cantidadInicial" name="cantidad" placeholder="Ingrese la cantidad" min="0" step="0.1">
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">Todos los campos son obligatorios</label>
                    </div>
                </div>

                <div class="botones-bottom">
                    <input type="button" value="CANCELAR" onclick="history.go(-1)" class="btn-secundario">
                    <a class="btn-accion" onclick="validarAlta()">CONTINUAR</a>
                </div>
            </form>

        </div>
    </div>

    <script>
        function validarAlta() {
            var nombresDB = <?php echo $jsonNombres; ?>;
            var form = document.getElementById("altasForm1");
            var nombreInput = document.getElementById("nombreMaterial");
            var cantidadInput = document.getElementById("cantidadInicial");
            var nombreStr = nombreInput.value.trim().toLowerCase();

            if (nombreInput.value.trim() == "") {
                alert("Nombre del material no ingresado");
                nombreInput.focus();
                return false;
            }
            var nombreExiste = nombresDB.some(n => n.toLowerCase() === nombreStr);
            if (nombreExiste) {
                alert("Este material ya existe en la base de datos.");
                nombreInput.focus();
                return false;
            }
            if (cantidadInput.value.trim() == "") {
                alert("Cantidad inicial no ingresada");
                cantidadInput.focus();
                return false;
            }
            if (isNaN(cantidadInput.value) || parseFloat(cantidadInput.value) < 0) {
                alert("Ingrese una cantidad válida");
                cantidadInput.focus();
                return false;
            }

form.submit();
        }
    </script>
</body>

</html>