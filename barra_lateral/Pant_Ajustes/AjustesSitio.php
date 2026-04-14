<?php 
include("../../seguridad.php"); 
include("../../conex.php");

$conexion = Conectarse();
$query = "SELECT id, nombre, precio, imagen FROM t_productos";
$resultado = mysqli_query($conexion, $query);

$productosDB = [];
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Extraemos solo el nombre de la foto y construimos la ruta relativa
        $nombre_foto = basename($row['imagen']);
        $ruta_segura = "../../Imagenes/Productos/" . $nombre_foto;

        $productosDB[] = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'precio' => (float)$row['precio'],
            'imagen' => $ruta_segura
        ];
    }
}
if(isset($conexion)) { mysqli_close($conexion); }
?>
<!DOCTYPE html>
<html lang="es">
<!-- Solo el administrador puede acceder a esta pagina -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Ajustes del Sitio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
        }

        h1 {
            color: #000000;
            margin-top: 30px;
            margin-bottom: 20px;
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
            background-color: #F6821F;
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

        /* ===== CONTENIDO PRINCIPAL ===== */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ===== TABLA DE PRODUCTOS ===== */
        .tabla-container {
            background-color: #FFFFFF;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .tabla-header {
            background-color: #F6821F;
            color: #000000;
            display: grid;
            grid-template-columns: 60px 100px 1fr 100px 120px;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 14px;
            align-items: center;
        }

        .tabla-body {
            max-height: 400px;
            overflow-y: auto;
        }

        .tabla-body::-webkit-scrollbar {
            width: 8px;
        }

        .tabla-body::-webkit-scrollbar-track {
            background: #E5E5E5;
        }

        .tabla-body::-webkit-scrollbar-thumb {
            background: #F6821F;
            border-radius: 4px;
        }

        .tabla-row {
            display: grid;
            grid-template-columns: 60px 100px 1fr 100px 120px;
            padding: 10px 15px;
            align-items: center;
            border-bottom: 1px solid #E5E5E5;
            transition: background-color 0.2s;
        }

        .tabla-row:hover {
            background-color: #FFF8F0;
        }

        .tabla-row img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #E5E5E5;
        }

        .tabla-row .producto-nombre {
            font-weight: bold;
            font-size: 14px;
        }

        .tabla-row .producto-precio {
            font-weight: bold;
            color: #F6821F;
            font-size: 15px;
        }

        .tabla-row .producto-id-cell {
            font-size: 13px;
            color: #999;
            font-weight: bold;
        }

        .btn-editar {
            background-color: #F6821F;
            color: #000000;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-editar:hover {
            background-color: #d96f14;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.activo {
            display: flex;
        }

        .modal {
            background-color: #FFFFFF;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-header {
            background-color: #073A79;
            color: #FFFFFF;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-cerrar {
            background: none;
            border: none;
            color: #FFFFFF;
            font-size: 22px;
            cursor: pointer;
            padding: 0 5px;
            line-height: 1;
        }

        .modal-cerrar:hover {
            color: #F6821F;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-body .form-group {
            margin-bottom: 18px;
        }

        .modal-body label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 6px;
            color: #333;
        }

        .modal-body input[type="text"],
        .modal-body input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #E5E5E5;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .modal-body input:focus {
            border-color: #F6821F;
            outline: none;
        }

        .imagen-preview-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .imagen-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid #E5E5E5;
        }

        .btn-cambiar-img {
            background-color: #073A79;
            color: #FFFFFF;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-cambiar-img:hover {
            background-color: #0a4a94;
        }

        .modal-footer {
            padding: 15px 25px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancelar {
            background-color: #E5E5E5;
            color: #333;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-cancelar:hover {
            background-color: #ccc;
        }

        .btn-guardar {
            background-color: #F6821F;
            color: #000000;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-guardar:hover {
            background-color: #d96f14;
        }

        /* ===== BARRA DE ACCIONES ===== */
        .barra-acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .buscar-input {
            padding: 10px 14px;
            border: 2px solid #E5E5E5;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            width: 280px;
            transition: border-color 0.2s;
        }

        .buscar-input:focus {
            border-color: #F6821F;
            outline: none;
        }

        .buscar-select {
            padding: 10px 14px;
            border: 2px solid #E5E5E5;
            border-right: none;
            border-radius: 8px 0 0 8px;
            font-size: 14px;
            font-weight: bold;
            background-color: #073A79;
            color: #FFFFFF;
            cursor: pointer;
            outline: none;
        }
    </style>
</head>

<body>
    <div
        style="background-color: #FFFFFF; width: 250px; min-width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: fit-content">
        <div align="center">
            <a href="../Dashboard.php">
                <img src="../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="../Dashboard.php" class="menu-item">
            <img src="../../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="../Inventario.php" class="menu-item">
            <img src="../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../Movimientos.php" class="menu-item">
            <img src="../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../Reportes.php" class="menu-item">
            <img src="../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../Administracion.php" class="menu-item">
            <img src="../../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="../Catalogo.php" class="menu-item">
            <img src="../../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item activo">
                <img src="../../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="../Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <h1>Ajustes del Sitio</h1>
        <div class="barra-acciones">
            <div style="display: flex; align-items: center;">
                <select class="buscar-select" id="tipoBusqueda" onchange="actualizarPlaceholder()">
                    <option value="nombre">Nombre</option>
                    <option value="id">ID</option>
                </select>
                <input type="text" class="buscar-input" id="buscarProducto" placeholder="Buscar producto por nombre..."
                    oninput="filtrarProductos()">
            </div>
        </div>

        <div class="tabla-container">
            <div class="tabla-header">
                <span>ID</span>
                <span>Imagen</span>
                <span>Nombre</span>
                <span>Precio</span>
                <span>Acción</span>
            </div>
            <div class="tabla-body" id="tablaProductos">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <span id="modalTitulo">Editar Producto</span>
                <button class="modal-cerrar" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndex">

                <div class="form-group">
                    <label for="editNombre">Nombre del Producto</label>
                    <input type="text" id="editNombre" placeholder="Nombre del producto">
                </div>

                <div class="form-group">
                    <label for="editPrecio">Precio ($)</label>
                    <input type="number" id="editPrecio" placeholder="0" min="0">
                </div>

                <div class="form-group">
                    <label>Imagen del Producto</label>
                    <div class="imagen-preview-container">
                        <img id="editImgPreview" class="imagen-preview" src="" alt="Vista previa">
                        <div>
                            <button class="btn-cambiar-img" onclick="document.getElementById('editImgFile').click()">
                                Cambiar Imagen
                            </button>
                            <input type="file" id="editImgFile" accept="image/*" style="display:none"
                                onchange="previsualizarImagen(event)">
                            <p style="font-size:12px; color:#999; margin-top:6px;">JPG, PNG (Máx. 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-guardar" onclick="guardarCambios()">Guardar Cambios</button>
            </div>
        </div>
    </div>



    <script>
        // Datos del catálogo obtenidos desde la base de datos
        var productos = <?php echo json_encode($productosDB); ?>;
        var archivoImagen = null;

        function renderTabla(listaFiltrada) {
            var lista = listaFiltrada || productos;
            var tabla = document.getElementById('tablaProductos');
            tabla.innerHTML = '';

            for (var i = 0; i < lista.length; i++) {
                var p = lista[i];
                var idx = productos.indexOf(p);
                var imgSrc = p.imagen || '../../Imagenes/TTlogomini.png';

                var row = document.createElement('div');
                row.className = 'tabla-row';
                row.setAttribute('data-index', idx);

                row.innerHTML =
                    '<span class="producto-id-cell">#' + p.id + '</span>' +
                    '<img src="' + imgSrc + '" alt="' + p.nombre + '" onerror="this.src=\'../../Imagenes/TTlogomini.png\'">' +
                    '<span class="producto-nombre">' + p.nombre + '</span>' +
                    '<span class="producto-precio">$' + p.precio + '</span>' +
                    '<button class="btn-editar" onclick="abrirModal(' + idx + ')">Editar</button>';

                tabla.appendChild(row);
            }
        }

        function filtrarProductos() {
            var busqueda = document.getElementById('buscarProducto').value.trim().toLowerCase();
            var tipo = document.getElementById('tipoBusqueda').value;
            if (!busqueda) {
                renderTabla();
                return;
            }
            var filtrados = productos.filter(function (p) {
                if (tipo === 'id') {
                    return p.id.toString() === busqueda;
                } else {
                    return p.nombre.toLowerCase().indexOf(busqueda) !== -1;
                }
            });
            renderTabla(filtrados);
        }

        function actualizarPlaceholder() {
            var tipo = document.getElementById('tipoBusqueda').value;
            var input = document.getElementById('buscarProducto');
            input.value = '';
            if (tipo === 'id') {
                input.placeholder = 'Buscar producto por ID...';
            } else {
                input.placeholder = 'Buscar producto por nombre...';
            }
            renderTabla();
        }

        // Modal
        function abrirModal(index) {
            var p = productos[index];
            document.getElementById('editIndex').value = index;
            document.getElementById('editNombre').value = p.nombre;
            document.getElementById('editPrecio').value = p.precio;
            document.getElementById('editImgPreview').src = p.imagen || '../../Imagenes/TTlogomini.png';
            document.getElementById('editImgPreview').onerror = function () {
                this.src = '../../Imagenes/TTlogomini.png';
            };
            document.getElementById('modalTitulo').textContent = 'Editar: ' + p.nombre;
            archivoImagen = null;
            document.getElementById('editImgFile').value = '';
            document.getElementById('modalOverlay').classList.add('activo');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.remove('activo');
            archivoImagen = null;
        }

        function previsualizarImagen(event) {
            var file = event.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar 2MB');
                event.target.value = '';
                return;
            }

            // Guardamos el archivo real para enviarlo al servidor
            archivoImagen = file;

            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('editImgPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function guardarCambios() {
            var index = parseInt(document.getElementById('editIndex').value);
            var p = productos[index];
            var nombre = document.getElementById('editNombre').value.trim();
            var precio = parseFloat(document.getElementById('editPrecio').value);

            if (!nombre) {
                alert('El nombre no puede estar vacío');
                return;
            }
            if (isNaN(precio) || precio < 0) {
                alert('Ingresa un precio válido');
                return;
            }

            // Construir FormData para enviar al servidor (incluye archivo si hay)
            var formData = new FormData();
            formData.append('id', p.id);
            formData.append('nombre', nombre);
            formData.append('precio', precio);

            if (archivoImagen) {
                formData.append('imagen', archivoImagen);
            }

            // Enviar por AJAX a guardar_producto.php
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'guardar_producto.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var resp = JSON.parse(xhr.responseText);
                            if (resp.ok) {
                                alert('Producto actualizado correctamente');
                                // Recargar la página para traer los datos frescos de la BD
                                window.location.reload();
                            } else {
                                alert('Error: ' + resp.msg);
                            }
                        } catch (e) {
                            alert('Error inesperado al procesar la respuesta');
                        }
                    } else {
                        alert('Error de conexión con el servidor');
                    }
                }
            };
            xhr.send(formData);
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalOverlay').addEventListener('click', function (e) {
            if (e.target === this) cerrarModal();
        });

        // Cerrar modal con Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarModal();
        });

        // Inicializar
        renderTabla();
    </script>
</body>

</html>