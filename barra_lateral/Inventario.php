<?php include("../conex.php");
$link = Conectarse();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Inventario</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
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

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inventario-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 800px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .controles-top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .control-grupo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
        }

        .input-caja {
            background-color: #F0F0F0;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
        }

        .input-caja select,
        .input-caja input {
            background: transparent;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .tabla-contenedor {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            margin-bottom: 40px;
        }

        .tabla-header {
            background: #f6821f;
            color: #000;
            font-weight: bold;
            display: flex;
            padding: 15px 30px;
        }

        .tabla-body {
            background-color: #E6E6E6;
            height: 300px;
            overflow-y: auto;
            padding: 10px 0;
        }

        .tabla-body::-webkit-scrollbar {
            width: 6px;
        }

        .tabla-body::-webkit-scrollbar-track {
            background: transparent;
            margin: 10px 0;
        }

        .tabla-body::-webkit-scrollbar-thumb {
            background-color: #A0A0A0;
            border-radius: 10px;
        }

        .fila {
            display: flex;
            padding: 10px 30px;
            font-weight: bold;
            color: #333;
        }

        .col-id {
            width: 15%;
        }

        .col-mat {
            width: 55%;
        }

        .col-stock {
            width: 30%;
            text-align: right;
            padding-right: 20px;
        }

        .botones-bottom {
            display: flex;
            justify-content: space-between;
        }

        .btn-accion {
            background: #f6821f;
            color: #000000;
            border: none;
            padding: 15px 50px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            text-decoration: none;
            text-align: center;
        }

        .btn-accion:hover {
            background-color: #DC7B3C;
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
    </style>

</head>

<body>
    <div
        style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%">
        <div align="center">
            <a href="Dashboard.html">
                <img src="../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="Dashboard.html" class="menu-item">
            <img src="../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="Inventario.php" class="menu-item activo">
            <img src="../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="Movimientos.html" class="menu-item">
            <img src="../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="Reportes.html" class="menu-item">
            <img src="../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="Administracion.html" class="menu-item">
            <img src="../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="Catalogo.html" class="menu-item">
            <img src="../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="Configuracion.html" class="submenu-item">Editar Perfil</a>
                <a href="Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
                <a href="../Login.php" class="submenu-item">Cerrar Sesión</a>
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
                <a href="Pant_inventario/Pant_altas/Altas1.html" class="btn-accion">ALTAS</a>
                <a href="Pant_inventario/Pant_ajustes/ajustes1.php" class="btn-accion">AJUSTES</a>
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
