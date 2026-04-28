<?php
include("../../seguridad_admin.php");
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
    <link rel="stylesheet" href="../../estilos/estilogenerico.css?v=<?php echo time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .btn-detalle {
            background-color: #073A79; /* Azul Corporativo */
            color: #FFFFFF;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-detalle:hover {
            background-color: #0a4a94;
        }
        .detalle-panel {
            display: none;
            background-color: #F9F9F9;
            padding: 20px;
            border-bottom: 2px solid #D0D0D0;
        }
        .detalle-tabla {
            width: 100%;
            border-collapse: collapse;
            background-color: #FFFFFF;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .detalle-header {
            background-color: #F6821F; /* Naranja Corporativo */
            color: #000000;
        }
        .detalle-header th {
            padding: 10px 15px;
            text-align: left;
            font-size: 14px;
        }
        .detalle-fila td {
            padding: 10px 15px;
            border-bottom: 1px solid #EEE;
            font-size: 14px;
            color: #333;
        }
        .detalle-fila:last-child td {
            border-bottom: none;
        }
        .fila-contenedor {
            border-bottom: 1px solid #D0D0D0;
        }
        .fila-contenedor:last-child {
            border-bottom: none;
        }
        /* Ajuste de alineación para 4 columnas */
        .tabla-header span, .fila span {
            flex: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
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
            <div class="titulo-caja">SUMINISTROS MENSUALES</div>
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
            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>
            <div id="contenido-reporte">
            <?php
            // Query basado en el procedimiento reporte_suministros
            $qSuministros = "SELECT
                                tp.nombre AS Proveedor,
                                tpg.fecha AS FechaDeSuministro,
                                CONCAT('$', FORMAT(ROUND(SUM(tpp.cantidad * tpp.costo), 2), 2)) AS CostoTotal,
                                SUM(tpp.cantidad) AS cantidad_num,
                                tpg.id AS id_suministro
                             FROM t_proovedores tp
                             JOIN t_proporcionar_general tpg ON tp.id = tpg.id_provedoor
                             JOIN t_proporcionar_particular tpp ON tpg.id = tpp.id_pg
                             WHERE tpg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                             GROUP BY tp.nombre, tpg.fecha, tpg.id
                             ORDER BY tpg.fecha";
            $rSuministros = mysqli_query($link, $qSuministros);

            // Total de suministros en el periodo
            $qTotal = "SELECT SUM(tpp.cantidad * tpp.costo) AS total
                       FROM t_proporcionar_general tpg
                       JOIN t_proporcionar_particular tpp ON tpg.id = tpp.id_pg
                       WHERE tpg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            $rTotal = mysqli_query($link, $qTotal);
            $rowTotal = $rTotal ? mysqli_fetch_assoc($rTotal) : null;
            $totalGeneral = ($rowTotal && $rowTotal['total']) ? $rowTotal['total'] : 0;
            ?>
            <div class="total-box">
                <span>Costo total de <?php echo $meses[$mes_sel]; ?> de <?php echo $anio_sel; ?>: $<?php echo number_format($totalGeneral, 2); ?></span>
                <button class="btn-descargar-pdf" onclick="descargarPDF()" title="Descargar PDF">
                    <img src="../../Imagenes/Descarga.png" width="24" height="24" alt="Descargar PDF">
                </button>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <span>Proveedor</span><span>Fecha</span><span>Costo Total</span><span>Acción</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rSuministros && mysqli_num_rows($rSuministros) > 0) {
                        while ($row = mysqli_fetch_array($rSuministros)) {
                            $id_sum = $row['id_suministro'];
                            echo '<div class="fila-contenedor">';
                            echo '<div class="fila">';
                            echo '<span>' . htmlspecialchars($row['Proveedor']) . '</span>';
                            echo '<span>' . date('d/m/Y', strtotime($row['FechaDeSuministro'])) . '</span>';
                            echo '<span>' . $row['CostoTotal'] . '</span>';
                            echo '<span><button class="btn-detalle" onclick="toggleDetalle(' . $id_sum . ', this)">▼ Ver detalle</button></span>';
                            echo '</div>';

                            // Tarea 4: Sub-consulta de detalle (incluye cálculo de subtotal)
                            $qDetalle = "SELECT tm.nombre AS Material, tpp.cantidad AS Cantidad, tpp.costo AS CostoU, (tpp.cantidad * tpp.costo) AS Subtotal
                                         FROM t_proporcionar_particular tpp
                                         JOIN t_materiales tm ON tpp.id_material = tm.id
                                         WHERE tpp.id_pg = $id_sum
                                         ORDER BY tm.nombre";
                            $rDetalle = mysqli_query($link, $qDetalle);

                            echo '<div id="detalle-' . $id_sum . '" class="detalle-panel">';
                            echo '<table class="detalle-tabla">';
                            echo '<tr class="detalle-header"><th>Material</th><th>Cantidad</th><th>Costo U.</th><th>Subtotal</th></tr>';
                            if ($rDetalle && mysqli_num_rows($rDetalle) > 0) {
                                while ($det = mysqli_fetch_array($rDetalle)) {
                                    echo '<tr class="detalle-fila">';
                                    echo '<td>' . htmlspecialchars($det['Material']) . '</td>';
                                    echo '<td>' . number_format($det['Cantidad'], 2) . '</td>';
                                    echo '<td>$' . number_format($det['CostoU'], 2) . '</td>';
                                    echo '<td>$' . number_format($det['Subtotal'], 2) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" style="text-align:center;">No hay detalles disponibles</td></tr>';
                            }
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin suministros para este mes</div>';
                    }
                    ?>
                </div>
            </div>
            </div><!-- fin contenido-reporte -->
        </div>
    </div>
    <script>
        function filtrar() {
            var mes  = document.getElementById('mes').value;
            var anio = document.getElementById('anio').value;
            if (!anio) { alert('Ingrese un año válido'); return; }
            window.location.href = 'Reportes_Suministro.php?mes=' + mes + '&anio=' + anio;
        }

        function toggleDetalle(id, btn) {
            var panel = document.getElementById('detalle-' + id);
            if (panel.style.display === 'block') {
                panel.style.display = 'none';
                btn.innerHTML = '▼ Ver detalle';
            } else {
                panel.style.display = 'block';
                btn.innerHTML = '▲ Ocultar';
            }
        }

        function descargarPDF() {
            var elemento = document.getElementById('contenido-reporte');
            var opt = {
                margin: 10,
                filename: 'Reporte_Suministro_<?php echo $meses[$mes_sel] . "_" . $anio_sel; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(elemento).save();
        }
    </script>
</body>
</html>