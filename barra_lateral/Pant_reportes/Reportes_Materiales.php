<?php
include("../../seguridad.php");
include("../../conex.php");
$link = Conectarse();

$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Materiales Más Usados</title>
    <link rel="stylesheet" href="../../estilos/estilogenerico.css">
</head>
<body>
    <div class="sidebar">
        <div align="center">
            <a href="../Dashboard.php"><img src="../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo"></a>
        </div>
        <a href="../Dashboard.php" class="menu-item"><img src="../../Imagenes/icon-dash.png" width="25"> Dashboard</a>
        <a href="../Inventario.php" class="menu-item"><img src="../../Imagenes/icon-inv.png" width="25"> Inventario</a>
        <a href="../Movimientos.php" class="menu-item"><img src="../../Imagenes/icon-mov.png" width="25"> Movimientos</a>
        <a href="../Reportes.php" class="menu-item activo"><img src="../../Imagenes/icon-repo.png" width="25"> Reportes</a>
        <a href="../Administracion.php" class="menu-item"><img src="../../Imagenes/icon-admin.png" width="25"> Administración</a>
        <a href="../Catalogo.php" class="menu-item"><img src="../../Imagenes/icon-catalogo.png" width="25"> Catálogo</a>
        <div class="menu-dropdown">
            <a class="menu-item"><img src="../../Imagenes/icon-config.png" width="25"> Configuración</a>
            <div class="submenu">
                <a href="../Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="../Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content-top">
        <div class="formulario-card">
            <div class="titulo-caja">MATERIALES MÁS USADOS</div>

            <div class="form-grid">
                <div class="input-grupo">
                    <label>Mes</label>
                    <select id="mes">
                        <?php
                        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                        for ($i = 1; $i <= 12; $i++) {
                            $sel = ($i == $mes_sel) ? 'selected' : '';
                            echo "<option value='$i' $sel>{$meses[$i]}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="input-grupo">
                    <label>Año</label>
                    <input type="number" id="anio" value="<?php echo $anio_sel; ?>" min="2020" max="2099">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>

            <?php
            // Materiales más usados en productos vendidos durante el mes seleccionado
            $qMateriales = "SELECT 
                                m.nombre AS material,
                                COUNT(DISTINCT ng.id_producto) AS productos_asociados
                            FROM t_necesitar_general ng
                            JOIN t_necesitar_particular np ON (np.id_ng = ng.id_ng)
                            JOIN t_materiales m ON (np.id_material = m.id)
                            JOIN t_vender_particular vp ON (vp.id_producto = ng.id_producto)
                            JOIN t_vender_general vg ON (vg.id = vp.id_vg)
                            WHERE MONTH(vg.fecha) = $mes_sel AND YEAR(vg.fecha) = $anio_sel
                            GROUP BY m.id, m.nombre
                            ORDER BY productos_asociados DESC
                            LIMIT 4";
            $rMateriales = mysqli_query($link, $qMateriales);
            ?>

            <div class="tabla-contenedor">
                <div class="tabla-header grid-materiales">
                    <span>Material</span><span>Productos Asociados</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rMateriales && mysqli_num_rows($rMateriales) > 0) {
                        while ($row = mysqli_fetch_array($rMateriales)) {
                            echo '<div class="fila grid-materiales">';
                            echo '<span>' . htmlspecialchars($row['material']) . '</span>';
                            echo '<span>' . $row['productos_asociados'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin datos para este mes</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filtrar() {
            var mes  = document.getElementById('mes').value;
            var anio = document.getElementById('anio').value;
            if (!anio) { alert('Ingrese un año válido'); return; }
            window.location.href = 'Reportes_Materiales.php?mes=' + mes + '&anio=' + anio;
        }
    </script>
</body>
</html>