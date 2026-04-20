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
                    <p style="text-align: left; color: #888; font-size: 14px;">Este producto aún no tiene materiales
                        asignados.</p>
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
                                <option value="<?php echo $mat['id']; ?>"><?php echo htmlspecialchars($mat['nombre']); ?>
                                </option>
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
            btnEliminar.onclick = function () { eliminarFila(this); };

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