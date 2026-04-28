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
    <title>Tacos Tony - Rotación de Productos</title>
    <link rel="stylesheet" href="../../estilos/estilogenerico.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            <div class="titulo-caja">ROTACIÓN DE PRODUCTOS</div>
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
            // Query basado en el procedimiento productos_sin_vender
            $qRotacion = "SELECT 
                            p.nombre AS producto,
                            SUM(vp.cantidad) AS total_vendido
                          FROM t_productos p
                          JOIN t_vender_particular vp ON p.id = vp.id_producto
                          JOIN t_vender_general vg ON vp.id_vg = vg.id
                          WHERE vg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                          GROUP BY p.nombre
                          ORDER BY total_vendido DESC";
            $rRotacion = mysqli_query($link, $qRotacion);

            // Total de productos vendidos en el periodo
            $qTotal = "SELECT SUM(vp.cantidad) AS total
                       FROM t_vender_particular vp
                       JOIN t_vender_general vg ON vp.id_vg = vg.id
                       WHERE vg.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            $rTotal = mysqli_query($link, $qTotal);
            $rowTotal = $rTotal ? mysqli_fetch_assoc($rTotal) : null;
            $totalGeneral = ($rowTotal && $rowTotal['total']) ? $rowTotal['total'] : 0;
            ?>
            <div class="total-box">
                <span>Total productos vendidos en <?php echo $meses[$mes_sel]; ?> de <?php echo $anio_sel; ?>: <?php echo number_format($totalGeneral, 0); ?></span>
                <button class="btn-descargar-pdf" onclick="descargarPDF()" title="Descargar PDF">
                    <img src="../../Imagenes/Descarga.png" width="24" height="24" alt="Descargar PDF">
                </button>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header grid-rotacion">
                    <span>Producto</span><span>Cantidad Vendida</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rRotacion && mysqli_num_rows($rRotacion) > 0) {
                        while ($row = mysqli_fetch_array($rRotacion)) {
                            echo '<div class="fila grid-rotacion">';
                            echo '<span>' . htmlspecialchars($row['producto']) . '</span>';
                            echo '<span>' . number_format($row['total_vendido'], 0) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin datos para este mes</div>';
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
            window.location.href = 'Reportes_Rotacion.php?mes=' + mes + '&anio=' + anio;
        }
        function descargarPDF() {
            var elemento = document.getElementById('contenido-reporte');
            var opt = {
                margin: 10,
                filename: 'Reporte_Rotacion_<?php echo $meses[$mes_sel] . "_" . $anio_sel; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(elemento).save();
        }
    </script>
</body>
</html>