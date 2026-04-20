<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
$nombres = [];
$res = mysqli_query($link, "SELECT nombre FROM t_productos");
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
    <title>Tacos Tony - Administracion - Productos</title>
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
        <div class="productos-card">

            <div class="titulo-caja">
                INFORMACIÓN DEL PRODUCTO
            </div>

<form id="productos1form" method="post" action="guardar_producto.php">
                    <div class="form-grid">

                    <div class="input-grupo">
                        <label>Nombre Producto</label>
                        <input type="text" id="nombreProducto" name="nombre" placeholder="Ingrese el nombre">
                    </div>

                    <div class="input-grupo">
                        <label>Precio</label>
                        <input type="number" id="precioProducto" name="precio" placeholder="Ingrese el precio" step="0.01">
                    </div>
                    <div class="input-grupo">
                        <label style="color: #073A79;">Todos los campos son obligatorios</label>
                    </div>
                </div>
                <button type="submit" id="btnSubmitProd" style="display:none;"></button>
            </form>

            <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                <a class="btn-accion" href="../../Administracion.php">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
            </div>
        </div>
    </div>

    <script>
        function valida_enviar() {
            var nombresDB = <?php echo $jsonNombres; ?>;
            var form = document.getElementById("productos1form");
            var nombreStr = document.getElementById("nombreProducto").value.trim().toLowerCase();

            if (document.getElementById("nombreProducto").value == "") {
                alert("Nombre del producto no ingresado");
                return 0;
            }
            var nombreExiste = nombresDB.some(n => n.toLowerCase() === nombreStr);
            if (nombreExiste) {
                alert("El nombre de este producto ya está registrado en la base de datos.");
                return 0;
            }
            if (document.getElementById("precioProducto").value == "") {
                alert("Precio del producto no ingresado");
                return 0;
            } else {
                document.getElementById("btnSubmitProd").click();
            }
        }
    </script>
</body>

</html>