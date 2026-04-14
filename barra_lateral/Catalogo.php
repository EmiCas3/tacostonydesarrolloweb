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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
            box-sizing: border-box;
        }

        .menu-item {
            padding: 15px 20px;
            color: #000000;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-item.activo {
            background-color: #f6821f;
        }

        .menu-item:hover:not(.activo) {
            background-color: #F9D864;
        }

        .menu-dropdown {
            position: relative;
        }

        .submenu {
            display: none;
            flex-direction: column;
            background-color: #f9f9f9;
            border-left: 4px solid #F6821F;
            margin-left: 20px;
            margin-right: 20px;
            margin-top: -5px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .menu-dropdown:hover .submenu {
            display: flex;
        }

        .submenu-item {
            padding: 12px 20px;
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s, color 0.2s;
        }

        .submenu-item:hover {
            color: #F6821F;
            background-color: #E5E5E5;
        }

        /* ===== CATÁLOGO ===== */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .main-content h1 {
            margin: 0 0 15px 0;
            color: #000000;
            font-size: 26px;
        }

        .catalogo-container {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .catalogo-container::-webkit-scrollbar {
            width: 8px;
        }

        .catalogo-container::-webkit-scrollbar-track {
            background: #E5E5E5;
            border-radius: 4px;
        }

        .catalogo-container::-webkit-scrollbar-thumb {
            background: #F6821F;
            border-radius: 4px;
        }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            padding-bottom: 20px;
        }

        .producto-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .producto-card:hover {
            transform: translateY(-4px);
            box-shadow: 2px 4px 16px rgba(0, 0, 0, 0.3);
        }

        .producto-img-container {
            width: 100%;
            height: 160px;
            overflow: hidden;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .producto-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .producto-info {
            padding: 12px 15px;
            border-top: 3px solid #F6821F;
        }

        .producto-nombre {
            font-weight: bold;
            font-size: 14px;
            color: #000000;
            margin-bottom: 6px;
        }

        .producto-precio {
            font-weight: bold;
            font-size: 16px;
            color: #F6821F;
        }

        .producto-id {
            font-size: 11px;
            color: #999999;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <div
        style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%; position: sticky; top: 20px; align-self: flex-start;">
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

    <div class="main-content">
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

    <script>
        // Aplicar cambios del catálogo guardados desde Ajustes del Sitio (opcional si ya usas BD)
        (function () {
            var guardados = localStorage.getItem('catalogoProductos');
            if (!guardados) return;

            var productos = JSON.parse(guardados);
            var cards = document.querySelectorAll('.producto-card');

            for (var i = 0; i < cards.length && i < productos.length; i++) {
                var p = productos[i];
                var card = cards[i];

                var nombreEl = card.querySelector('.producto-nombre');
                if (nombreEl) nombreEl.textContent = p.nombre;

                var precioEl = card.querySelector('.producto-precio');
                if (precioEl) precioEl.textContent = '$' + p.precio;

                var imgEl = card.querySelector('.producto-img-container img');
                if (imgEl) {
                    var nuevaSrc = p.imagen;
                    if (nuevaSrc && nuevaSrc.indexOf('data:') !== 0) {
                        nuevaSrc = p.imagen;
                    }
                    imgEl.src = nuevaSrc;
                    imgEl.alt = p.nombre;
                }
            }
        })();
    </script>
</body>

</html>