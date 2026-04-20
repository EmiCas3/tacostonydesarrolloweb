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
            'id' => (int) $row['id'],
            'nombre' => $row['nombre'],
            'precio' => (float) $row['precio'],
            'imagen' => $ruta_segura
        ];
    }
}
if (isset($conexion)) {
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<!-- Solo el administrador puede acceder a esta pagina -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Ajustes del Sitio</title>
    <link rel="stylesheet" href="../../estilos/estilogenerico.css">
</head>

<body>
    <div class="sidebar">
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

    <div class="main-content-column">
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
            <div class="tabla-header-grid">
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