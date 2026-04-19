<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();

$idProducto = isset($_POST['idProducto']) ? intval($_POST['idProducto']) : 0;
$nombreProducto = "";
$precioProducto = "";

if ($idProducto > 0) {
    $query = "SELECT nombre, precio FROM t_productos WHERE id = $idProducto";
    $result = mysqli_query($link, $query);
    if ($row = mysqli_fetch_array($result)) {
        $nombreProducto = $row['nombre'];
        $precioProducto = $row['precio'];
    }
}

// Obtener materiales ya asignados a este producto (del registro más reciente en t_necesitar_general)
$materialesAsignados = [];
if ($idProducto > 0) {
    $queryAsignados = "SELECT np.id_material, m.nombre, np.cantidad
                       FROM t_necesitar_general ng
                       JOIN t_necesitar_particular np ON ng.id_ng = np.id_ng
                       JOIN t_materiales m ON np.id_material = m.id
                       WHERE ng.id_producto = $idProducto
                       ORDER BY ng.fecha DESC, m.nombre ASC";
    $resultAsignados = mysqli_query($link, $queryAsignados);

    // Agrupar por el registro más reciente (tomar el id_ng más alto)
    $ultimoNg = null;
    while ($row = mysqli_fetch_array($resultAsignados)) {
        $materialesAsignados[] = $row;
    }

    // Obtener solo los del último id_ng para mostrar como "actuales"
    $queryUltimo = "SELECT ng.id_ng FROM t_necesitar_general ng
                    WHERE ng.id_producto = $idProducto
                    ORDER BY ng.id_ng DESC LIMIT 1";
    $resUltimo = mysqli_query($link, $queryUltimo);
    if ($rowUltimo = mysqli_fetch_array($resUltimo)) {
        $ultimoNg = $rowUltimo['id_ng'];
        $materialesAsignados = [];
        $queryActuales = "SELECT np.id_material, m.nombre, np.cantidad
                          FROM t_necesitar_particular np
                          JOIN t_materiales m ON np.id_material = m.id
                          WHERE np.id_ng = $ultimoNg
                          ORDER BY m.nombre ASC";
        $resActuales = mysqli_query($link, $queryActuales);
        while ($row = mysqli_fetch_array($resActuales)) {
            $materialesAsignados[] = $row;
        }
    }
}

// Obtener todos los materiales disponibles para el select
$todosLosMateriales = [];
$resMateriales = mysqli_query($link, "SELECT id, nombre FROM t_materiales ORDER BY nombre");
while ($row = mysqli_fetch_array($resMateriales)) {
    $todosLosMateriales[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Asignar Materiales</title>
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

        /* Estilos del submenú faltante */
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

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ajustes-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 50px;
            width: 100%;
            max-width: 700px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            text-align: center;
        }

        .titulo-caja {
            background: #f6821f;
            color: #000000;
            font-weight: bold;
            font-size: 22px;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .input-grupo {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 25px;
            text-align: left;
        }

        .input-grupo label {
            font-weight: bold;
            color: #333333;
            font-size: 14px;
        }

        .input-grupo input,
        .input-grupo select {
            background-color: #F0F0F0;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            outline: none;
            color: #333;
            width: 100%;
            box-sizing: border-box;
        }

        .input-grupo input:focus,
        .input-grupo select:focus {
            border-color: #f6821f;
        }

        .input-grupo input.read-only {
            background-color: #E0E0E0;
            color: #888;
            cursor: not-allowed;
            font-weight: bold;
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
            display: inline-block;
            transition: background-color 0.2s;
            margin-top: 20px;
        }

        .btn-accion:hover {
            background-color: #DC7B3C;
        }

        .btn-agregar {
            background: #073A79;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .btn-agregar:hover {
            background-color: #074c9fff;
        }

        .btn-eliminar {
            background: #f6821f;
            color: #FFFFFF;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-eliminar:hover {
            background-color: #DC7B3C;
        }

        .material-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #E0E0E0;
        }

        .material-row select,
        .material-row input {
            background-color: #F0F0F0;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
            color: #333;
            box-sizing: border-box;
        }

        .material-row select {
            flex: 3;
        }

        .material-row input {
            flex: 1;
            min-width: 80px;
        }

        .material-row select:focus,
        .material-row input:focus {
            border-color: #f6821f;
        }

        .materiales-actuales {
            text-align: left;
            margin-bottom: 25px;
        }

        .materiales-actuales h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .tabla-materiales {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .tabla-materiales th {
            background-color: #f6821f;
            color: #000;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }

        .tabla-materiales td {
            padding: 10px;
            border-bottom: 1px solid #E0E0E0;
            font-size: 14px;
        }

        .tabla-materiales tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .seccion-titulo {
            font-weight: bold;
            color: #333;
            font-size: 16px;
            text-align: left;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #f6821f;
        }
    </style>
</head>

<body>
    <div
        style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%; position: sticky; top: 20px; align-self: flex-start;">
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
        <div class="ajustes-card">
            <div class="titulo-caja">
                ASIGNAR MATERIALES A PRODUCTO
            </div>

            <!-- Información del producto seleccionado -->
            <div class="input-grupo">
                <label>ID del Producto</label>
                <input type="text" class="read-only" readonly value="<?php echo htmlspecialchars($idProducto); ?>">
            </div>
            <div class="input-grupo">
                <label>Producto</label>
                <input type="text" class="read-only" readonly value="<?php echo htmlspecialchars($nombreProducto); ?>">
            </div>
            <div class="input-grupo">
                <label>Precio</label>
                <input type="text" class="read-only" readonly value="$<?php echo htmlspecialchars($precioProducto); ?>">
            </div>

            <!-- Materiales actualmente asignados -->
            <?php if (!empty($materialesAsignados)): ?>
            <div class="materiales-actuales">
                <div class="seccion-titulo">Materiales Actualmente Asignados</div>
                <table class="tabla-materiales">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materialesAsignados as $mat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mat['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($mat['cantidad']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="materiales-actuales">
                <div class="seccion-titulo">Sin materiales asignados</div>
                <p style="text-align: left; color: #888; font-size: 14px;">Este producto aún no tiene materiales asignados.</p>
            </div>
            <?php endif; ?>

            <!-- Formulario para asignar nuevos materiales -->
            <div class="seccion-titulo">Asignar Nuevos Materiales</div>
            <form id="formAsignar" method="POST" action="guardar_asignacion.php">
                <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">

                <div id="contenedorMateriales">
                    <div class="material-row">
                        <select name="materiales[]" required>
                            <option value="">--Material--</option>
                            <?php foreach ($todosLosMateriales as $mat): ?>
                            <option value="<?php echo $mat['id']; ?>"><?php echo htmlspecialchars($mat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="cantidades[]" placeholder="Cantidad" step="0.01" min="0.01" required>
                        <button type="button" class="btn-eliminar" onclick="eliminarFila(this)">✕</button>
                    </div>
                </div>

                <button type="button" class="btn-agregar" onclick="agregarMaterial()">+ Agregar Material</button>

                <br>
                <div style="display: flex; justify-content: space-between;">
                    <a class="btn-accion" href="../../Inventario.php">CANCELAR</a>
                    <a class="btn-accion" onclick="validarForm()">CONFIRMAR</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        var materialesJSON = <?php echo json_encode($todosLosMateriales); ?>;

        function agregarMaterial() {
            var contenedor = document.getElementById('contenedorMateriales');
            var fila = document.createElement('div');
            fila.className = 'material-row';

            var select = document.createElement('select');
            select.name = 'materiales[]';
            select.required = true;
            var optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.textContent = '--Material--';
            select.appendChild(optDefault);

            for (var i = 0; i < materialesJSON.length; i++) {
                var opt = document.createElement('option');
                opt.value = materialesJSON[i]['id'];
                opt.textContent = materialesJSON[i]['nombre'];
                select.appendChild(opt);
            }

            var input = document.createElement('input');
            input.type = 'number';
            input.name = 'cantidades[]';
            input.placeholder = 'Cantidad';
            input.step = '0.01';
            input.min = '0.01';
            input.required = true;

            var btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className = 'btn-eliminar';
            btnEliminar.textContent = '✕';
            btnEliminar.onclick = function() { eliminarFila(this); };

            fila.appendChild(select);
            fila.appendChild(input);
            fila.appendChild(btnEliminar);

            contenedor.appendChild(fila);
        }

        function eliminarFila(btn) {
            var contenedor = document.getElementById('contenedorMateriales');
            if (contenedor.children.length > 1) {
                btn.parentElement.remove();
            } else {
                alert("Debe haber al menos un material.");
            }
        }

        function validarForm() {
            var form = document.getElementById('formAsignar');
            var selects = form.querySelectorAll('select[name="materiales[]"]');
            var inputs = form.querySelectorAll('input[name="cantidades[]"]');
            var valido = true;

            // Verificar que no haya campos vacíos
            for (var i = 0; i < selects.length; i++) {
                if (selects[i].value === '') {
                    alert('Seleccione un material en la fila ' + (i + 1));
                    valido = false;
                    break;
                }
                if (inputs[i].value === '' || parseFloat(inputs[i].value) <= 0) {
                    alert('Ingrese una cantidad válida en la fila ' + (i + 1));
                    valido = false;
                    break;
                }
            }

            // Verificar materiales duplicados
            if (valido) {
                var valores = [];
                for (var i = 0; i < selects.length; i++) {
                    if (valores.indexOf(selects[i].value) !== -1) {
                        alert('El material en la fila ' + (i + 1) + ' está duplicado. Seleccione uno diferente.');
                        valido = false;
                        break;
                    }
                    valores.push(selects[i].value);
                }
            }

            if (valido) {
                form.submit();
            }
        }
    </script>
</body>

</html>