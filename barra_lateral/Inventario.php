<?php include("../seguridad.php");
include("../conex.php");
$link = Conectarse();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Inventario</title>
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
        <a href="Inventario.php" class="menu-item activo">
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
        <div class="inventario-card">

            <div class="controles-top" style="justify-content: space-between;">
                <div class="control-grupo">
                    <label>Ordenar por:</label>
                    <div class="input-caja">
                        <select id="criterioOrdenar">
                            <option value="id">ID</option>
                            <option value="nombre">Nombre</option>
                            <option value="stock">Stock</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div class="control-grupo">
                        <label>Buscar por:</label>
                        <div class="input-caja">
                            <select id="criterioBusqueda">
                                <option value="nombre">Nombre</option>
                                <option value="id">ID</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-grupo">
                        <div class="input-caja">
                            <input type="text" id="textoBusqueda" placeholder="Buscar">
                            <span style="cursor: pointer; display: flex; align-items: center;">
                                <img src="../Imagenes/Search.png" alt="search" width="20" style="vertical-align: middle;">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <div class="col-id">ID</div>
                    <div class="col-mat">Materiales</div>
                    <div class="col-stock">Stock actual</div>
                </div>
                <div class="tabla-body">
                    <table id="tablaInventario" style="width: 100%; border-collapse: collapse;">
                        <?php
                        $query = "SELECT id, nombre, existencias FROM t_materiales ORDER BY id";
                        $result = mysqli_query($link, $query) or die(mysqli_error($link));
                        while($row = mysqli_fetch_array($result)){
                            echo '<tr class="fila">';
                            echo '<td class="col-id">'.$row['id'].'</td>';
                            echo '<td class="col-mat">'.htmlspecialchars($row['nombre']).'</td>';
                            echo '<td class="col-stock">'.$row['existencias'].'</td>';
                            echo '</tr>';
                        }
                        if (mysqli_num_rows($result) == 0) {
                            echo '<tr class="fila"><td colspan="3" style="text-align:center; padding:20px;">No hay materiales registrados.</td></tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>

            <div class="botones-bottom">
                <a href="Pant_inventario/Pant_altas/Altas1.php" class="btn-accion">ALTAS</a>
                <a href="Pant_inventario/Pant_asignar/asignar1.php" class="btn-accion">ASIGNAR</a>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('textoBusqueda').addEventListener('input', filtrarTabla);
        document.getElementById('criterioBusqueda').addEventListener('change', filtrarTabla);
        document.getElementById('criterioOrdenar').addEventListener('change', ordenarTabla);

        function ordenarTabla() {
            var criterio = document.getElementById('criterioOrdenar').value;
            var tabla = document.getElementById('tablaInventario');
            var tbody = tabla.tBodies[0] || tabla;
            var filas = Array.from(tbody.querySelectorAll('tr.fila'));

            filas.sort(function(a, b) {
                var valA = "";
                var valB = "";

                if (criterio === "id") {
                    valA = parseInt(a.querySelector('.col-id').textContent.trim()) || 0;
                    valB = parseInt(b.querySelector('.col-id').textContent.trim()) || 0;
                    return valA - valB;
                } else if (criterio === "nombre") {
                    valA = a.querySelector('.col-mat').textContent.trim().toLowerCase();
                    valB = b.querySelector('.col-mat').textContent.trim().toLowerCase();
                    if (valA < valB) return -1;
                    if (valA > valB) return 1;
                    return 0;
                } else if (criterio === "stock") {
                    valA = parseFloat(a.querySelector('.col-stock').textContent.trim()) || 0;
                    valB = parseFloat(b.querySelector('.col-stock').textContent.trim()) || 0;
                    return valA - valB;
                }
            });

            filas.forEach(function(fila) {
                tbody.appendChild(fila);
            });
        }

        function filtrarTabla() {
            var filtro = document.getElementById('textoBusqueda').value.toLowerCase();
            var criterio = document.getElementById('criterioBusqueda').value;
            var filas = document.querySelectorAll('#tablaInventario tr.fila');

            filas.forEach(function(fila) {
                var tdTexto = "";
                if (criterio === "id") {
                    tdTexto = fila.querySelector('.col-id').textContent.toLowerCase();
                } else if (criterio === "nombre") {
                    tdTexto = fila.querySelector('.col-mat').textContent.toLowerCase();
                } else if (criterio === "stock") {
                    tdTexto = fila.querySelector('.col-stock').textContent.toLowerCase();
                }

                if (tdTexto.includes(filtro)) {
                    fila.style.display = ""; // Mostrar
                } else {
                    fila.style.display = "none"; // Ocultar
                }
            });
        }
    </script>
</body>

</html>
