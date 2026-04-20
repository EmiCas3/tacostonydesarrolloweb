<?php include("../seguridad.php"); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Configuración</title>
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
        </a>
        <a href="Inventario.php" class="menu-item">
            <img src="../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="Movimientos.php" class="menu-item">
            <img src="../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="Reportes.php" class="menu-item">
            <img src="../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="Administracion.php" class="menu-item">
            <img src="../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="Catalogo.php" class="menu-item">
            <img src="../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item activo">
                <img src="../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div style="flex-grow:1">

        <div class="caja-principal">
            <div class="perfil-imagen-container">
                <img src="../Imagenes/imagn-perfil.png" alt="Imagen de Perfil" class="perfil-imagen">
                <input type="file" id="file-upload" style="display: none;" accept="image/*">
            </div>

            <form id="form-config" style="width: 100%;" action="Pant_Ajustes/guardar_contrasena.php" method="POST">
                <div class="form-group">
                    <h2 for="nombre">Nombre Completo</h2>
                    <input type="text" id="nombre" name="nombre" value="<?php echo isset($_SESSION['nombre_empleado']) ? htmlspecialchars($_SESSION['nombre_empleado']) : ''; ?>" readonly>
                </div>

                <div class="form-group">
                    <h2 for="correo">Correo Electrónico</h2>
                    <input type="email" id="correo" name="correo" value="<?php echo isset($_SESSION['correo_empleado']) ? htmlspecialchars($_SESSION['correo_empleado']) : ''; ?>" readonly>
                </div>

                <div class="form-group">
                    <h2 for="password">Nueva Contraseña</h2>
                    <input type="password" id="password" name="password" placeholder="Ingrese nueva contraseña">
                </div>

                <button type="button" class="botones" onclick="guardar_cambios()">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </div>

    <script>
        function guardar_cambios() {
            var password = document.getElementById("password").value;

            if (password === "") {
                alert("Ingrese una nueva contraseña");
                return;
            }
            if (password.length < 8) {
                alert("La contraseña debe tener al menos 8 caracteres");
                return;
            }
            // Enviar el formulario
            document.getElementById("form-config").submit();
        }
    </script>
</body>

</html>