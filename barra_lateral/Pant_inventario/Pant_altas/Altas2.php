<?php include("../../../seguridad.php"); ?>
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
                CONFIRMAR REGISTRO
            </div>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th class="col-campo">Campo</th>
                            <th class="col-valor">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-campo">Nombre del Material</td>
                            <td class="col-valor" id="show-nombre">—</td>
                        </tr>
                        <tr>
                            <td class="col-campo">Cantidad Inicial</td>
                            <td class="col-valor" id="show-cantidad">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="botones-bottom">
                <input type="button" value="ATRÁS" onclick="history.go(-1)" class="btn-secundario">
                <a class="btn-accion" onclick="confirmarAlta()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        window.onload = function () {
            var nombre = sessionStorage.getItem("alta_nombre");
            var cantidad = sessionStorage.getItem("alta_cantidad");

            if (!nombre || !cantidad) {
                alert("Error: No se encontraron los datos del material");
                window.location.href = "Altas1.php";
                return;
            }

            document.getElementById("show-nombre").textContent = nombre;
            document.getElementById("show-cantidad").textContent = cantidad;
        };

        function confirmarAlta() {
            sessionStorage.removeItem("alta_nombre");
            sessionStorage.removeItem("alta_cantidad");
            alert("Material registrado con éxito.");
            window.location.href = "../../Inventario.php";
        }
    </script>
</body>

</html>