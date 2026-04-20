<?php include("../seguridad.php"); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Administracion - Colaboradores</title>
    <link rel="stylesheet" href="../estilos/estilogenerico.css">
</head>

<body>

    <div class="sidebar">
        <div align="center">
            <a href="Dashboard.php">
                <img src="../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="Dashboard.php" class="menu-item">
            <img src="../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard

            <a href="Inventario.php" class="menu-item">
                <img src="../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
            </a>
            <a href="Movimientos.php" class="menu-item">
                <img src="../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
            </a>
            <a href="Reportes.php" class="menu-item">
                <img src="../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
            </a>
            <a href="Administracion.php" class="menu-item activo">
                <img src="../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
            </a>
            <a href="Catalogo.php" class="menu-item">
                <img src="../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
            </a>
            <div class="menu-dropdown">
                <a class="menu-item">
                    <img src="../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
                </a>
                <div class="submenu">
                    <a href="Configuracion.php" class="submenu-item">Editar Perfil</a>
                    <a href="Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                    <a href="../salir.php" class="submenu-item">Cerrar Sesión</a>
                </div>
            </div>
    </div>

    <div class="main-content">
        <div class="movimientos-card">

            <div class="titulo-caja">
                ¿QUÉ DESEA REGISTRAR?
            </div>

            <div class="botones-contenedor">
                <a href="Pant_administracion/Pant_clientes/Clientes.php" class="btn-opcion">CLIENTES</a>
                <a href="Pant_administracion/Pant_colaboradores/Colaboradores1.php"
                    class="btn-opcion">COLABORADORES</a>
            </div>

            <div style="height: 40px;"></div>

            <div class="botones-contenedor">
                <a href="Pant_administracion/Pant_proveedores/Proveedor.php" class="btn-opcion">PROVEEDORES</a>
                <a href="Pant_administracion/Pant_productos/Productos1.php" class="btn-opcion">PRODUCTOS</a>
            </div>

        </div>
    </div>

</body>

</html>