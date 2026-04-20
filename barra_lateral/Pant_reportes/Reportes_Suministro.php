<?php
include("../../seguridad.php");
include("../../conex.php");
$link = Conectarse();

$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Construir rango de fechas del mes seleccionado
$fecha_inicio = sprintf('%04d-%02d-01 00:00:00', $anio_sel, $mes_sel);
$ultimo_dia   = date('t', mktime(0, 0, 0, $mes_sel, 1, $anio_sel));
$fecha_fin    = sprintf('%04d-%02d-%02d 23:59:59', $anio_sel, $mes_sel, $ultimo_dia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Suministros por Fecha</title>
    <link rel=\"stylesheet\" href=\"../../estilos/estilogenerico.css\">
    
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
            <div class="titulo-caja">SUMINISTROS POR FECHA</div>
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
            // Query basado en el procedimiento reporte_suministros
            $qSuministros = "SELECT
                                tp.nombre AS Proveedor,
                                tpg.fecha AS FechaDeSuministro,
                                CONCAT('\$', FORMAT(ROUND(SUM(tpp.cantidad), 2), 2)) AS CantidadTotal,
                                SUM(tpp.cantidad) AS cantidad_num
                             FROM t_proovedores tp
                             JOIN t_proporcionar_general tpg ON tp.id = tpg.id_provedoor
                             JOIN t_proporcionar_particular tpp ON tpg.id = tpp.id_pg
                             WHERE tpg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                             GROUP BY tp.nombre, tpg.fecha
                             ORDER BY tpg.fecha";
            $rSuministros = mysqli_query($link, $qSuministros);

            // Total de suministros en el periodo
            $qTotal = "SELECT SUM(tpp.cantidad) AS total
                       FROM t_proporcionar_general tpg
                       JOIN t_proporcionar_particular tpp ON tpg.id = tpp.id_pg
                       WHERE tpg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            $rTotal = mysqli_query($link, $qTotal);
            $rowTotal = $rTotal ? mysqli_fetch_assoc($rTotal) : null;
            $totalGeneral = ($rowTotal && $rowTotal['total']) ? $rowTotal['total'] : 0;
            ?>
            <div class="total-box">
                Costo total: $<?php echo number_format($totalGeneral, 2); ?>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header grid-suministro">
                    <span>Proveedor</span><span>Fecha</span><span>Cantidad Total</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rSuministros && mysqli_num_rows($rSuministros) > 0) {
                        while ($row = mysqli_fetch_array($rSuministros)) {
                            echo '<div class="fila grid-suministro">';
                            echo '<span>' . htmlspecialchars($row['Proveedor']) . '</span>';
                            echo '<span>' . date('d/m/Y', strtotime($row['FechaDeSuministro'])) . '</span>';
                            echo '<span>' . $row['CantidadTotal'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin suministros para este mes</div>';
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
            window.location.href = 'Reportes_Suministro.php?mes=' + mes + '&anio=' + anio;
        }
    </script>
</body>
</html>