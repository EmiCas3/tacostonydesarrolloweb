<?php 
include("../seguridad.php"); 
include("../conex.php");

// 1. Conexión a la base de datos
$conexion = Conectarse();

// 2. Consulta para traer todos los productos
$query = "SELECT id, nombre, precio, imagen FROM t_productos";
$resultado = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Catálogo</title>
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
        <a href="Catalogo.php" class="menu-item activo">
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

    <div class="main-content-column">
        <h1>Catálogo de Productos</h1>
        <div class="catalogo-container">
            <div class="productos-grid">

                <?php 
                // 3. AQUÍ SUCEDE LA MAGIA
                if ($resultado && mysqli_num_rows($resultado) > 0) {
                    while ($producto = mysqli_fetch_assoc($resultado)) { 
                        
                        // EL TRUCO: Extraemos solo el nombre de la foto (ej: "TacoArabe.png")
                        $nombre_foto = basename($producto['imagen']);
                        
                        // Construimos la ruta EXACTA que usabas en tu HTML manual
                        $ruta_segura = "../Imagenes/Productos/" . $nombre_foto;
                ?>
                        <div class="producto-card">
                            <div class="producto-img-container">
                                <img src="<?php echo $ruta_segura; ?>" alt="<?php echo $producto['nombre']; ?>">
                            </div>
                            <div class="producto-info">
                                <div class="producto-id">#<?php echo $producto['id']; ?></div>
                                <div class="producto-nombre"><?php echo $producto['nombre']; ?></div>
                                <div class="producto-precio">$<?php echo floatval($producto['precio']); ?></div>
                            </div>
                        </div>
                <?php 
                    } 
                } else {
                    echo "<p>No hay productos registrados en el catálogo aún.</p>";
                }
                
                // Cerramos la conexión
                if(isset($conexion)) { mysqli_close($conexion); }
                ?>

            </div>
        </div>
    </div>



</body>

</html>