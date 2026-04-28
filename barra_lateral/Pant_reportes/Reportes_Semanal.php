<?php
include("../../seguridad.php");
include("../../conex.php");
$link = Conectarse();

// Recibir fechas o usar la semana actual por defecto
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin    = $_GET['fecha_fin'];
} else {
    // Lunes de esta semana
    $fecha_inicio = date('Y-m-d', strtotime('monday this week'));
    // Domingo de esta semana
    $fecha_fin    = date('Y-m-d', strtotime('sunday this week'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Ventas por Semana</title>
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
            <div class="titulo-caja">VENTAS POR SEMANA</div>
            <div class="form-grid">
                <div class="input-grupo">
                    <label>Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="input-grupo">
                    <label>Fecha Fin</label>
                    <input type="date" id="fecha_fin" value="<?php echo $fecha_fin; ?>">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>
            <?php
            // Query basado en el procedimiento ventas_semana
            $fi = $fecha_inicio . ' 00:00:00';
            $ff = $fecha_fin . ' 23:59:59';

            $qSemana = "SELECT 
                            DAYNAME(vg.fecha) AS dia_semana,
                            CONCAT('\$', FORMAT(ROUND(SUM(vp.cantidad * pr.precio), 2), 2)) AS total_ventas,
                            SUM(vp.cantidad) AS total_productos_vendidos,
                            SUM(vp.cantidad * pr.precio) AS total_num
                        FROM t_vender_general vg
                        JOIN t_vender_particular vp ON vg.id = vp.id_vg
                        JOIN t_productos pr ON vp.id_producto = pr.id
                        WHERE vg.fecha BETWEEN '$fi' AND '$ff'
                        GROUP BY dia_semana
                        ORDER BY FIELD(dia_semana, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')";
            $rSemana = mysqli_query($link, $qSemana);

            // Total general del rango
            $qTotal = "SELECT SUM(vp.cantidad * pr.precio) AS total
                       FROM t_vender_general vg
                       JOIN t_vender_particular vp ON vg.id = vp.id_vg
                       JOIN t_productos pr ON vp.id_producto = pr.id
                       WHERE vg.fecha BETWEEN '$fi' AND '$ff'";
            $rTotal = mysqli_query($link, $qTotal);
            $rowTotal = $rTotal ? mysqli_fetch_assoc($rTotal) : null;
            $totalGeneral = ($rowTotal && $rowTotal['total']) ? $rowTotal['total'] : 0;

            // Traducción de días
            $dias_es = [
                'Monday'    => 'Lunes',
                'Tuesday'   => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday'  => 'Jueves',
                'Friday'    => 'Viernes',
                'Saturday'  => 'Sábado',
                'Sunday'    => 'Domingo'
            ];
            ?>
            <div id="reporte-contenido">
            <div class="barra-info-reporte">
                <?php echo 'Ventas por Semana del ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)); ?>
            </div>
            <div class="total-box">
                Total del periodo: $<?php echo number_format($totalGeneral, 2); ?>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header grid-semanal">
                    <span>Día</span><span>Productos Vendidos</span><span>Total Ventas</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rSemana && mysqli_num_rows($rSemana) > 0) {
                        while ($row = mysqli_fetch_array($rSemana)) {
                            $dia_nombre = isset($dias_es[$row['dia_semana']]) ? $dias_es[$row['dia_semana']] : $row['dia_semana'];
                            echo '<div class="fila grid-semanal">';
                            echo '<span>' . $dia_nombre . '</span>';
                            echo '<span>' . $row['total_productos_vendidos'] . '</span>';
                            echo '<span>' . $row['total_ventas'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin datos para este periodo</div>';
                    }
                    ?>
                </div>
            </div>
            </div>

            <div class="contenedor-btn-pdf">
                <button class="btn-descargar-pdf" onclick="descargarPDF()" title="Descargar PDF"><img src="../../Imagenes/Descarga.png" alt="Descargar PDF"></button>
            </div>

        </div>
    </div>
    <script>
        function filtrar() {
            var fi = document.getElementById('fecha_inicio').value;
            var ff = document.getElementById('fecha_fin').value;
            if (!fi || !ff) { alert('Seleccione ambas fechas'); return; }
            if (fi > ff) { alert('La fecha de inicio debe ser anterior a la fecha fin'); return; }
            window.location.href = 'Reportes_Semanal.php?fecha_inicio=' + fi + '&fecha_fin=' + ff;
        }

        function descargarPDF() {
            var elemento = document.getElementById('reporte-contenido');
            var titulo = 'Ventas_Semanal_<?php echo $fecha_inicio; ?>_a_<?php echo $fecha_fin; ?>';
            var opt = {
                margin:       [10, 10, 10, 10],
                filename:     titulo + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(elemento).save();
        }
    </script>
</body>
</html>