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
    // Domingo de esta semana (+6 días desde el lunes)
    $fecha_fin    = date('Y-m-d', strtotime('monday this week +6 days'));
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
                    <input type="date" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>"
                           onchange="calcularFechaFin()">
                </div>
                <div class="input-grupo">
                    <label>Fecha Fin <small style="color:#888; font-weight:normal;">(calculada automáticamente)</small></label>
                    <input type="date" id="fecha_fin" value="<?php echo $fecha_fin; ?>" readonly
                           style="background-color:#f0f0f0; cursor:not-allowed; color:#555;">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>
            <div id="contenido-reporte">
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
            <?php
            $fi_fmt = date('d/m/Y', strtotime($fecha_inicio));
            $ff_fmt = date('d/m/Y', strtotime($fecha_fin));
            ?>
            <div class="total-box">
                <span>Total del <?php echo $fi_fmt; ?> al <?php echo $ff_fmt; ?>: $<?php echo number_format($totalGeneral, 2); ?></span>
                <button class="btn-descargar-pdf" onclick="descargarPDF()" title="Descargar PDF">
                    <img src="../../Imagenes/Descarga.png" width="24" height="24" alt="Descargar PDF">
                </button>
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
            </div><!-- fin contenido-reporte -->
        </div>
    </div>
    <script>
        function calcularFechaFin() {
            var fi = document.getElementById('fecha_inicio').value;
            if (!fi) return;
            var inicio = new Date(fi + 'T00:00:00'); // forzar hora local, evitar offset UTC
            var fin = new Date(inicio);
            fin.setDate(fin.getDate() + 6); // +6 días = rango de 7 días inclusive
            var anio = fin.getFullYear();
            var mes  = String(fin.getMonth() + 1).padStart(2, '0');
            var dia  = String(fin.getDate()).padStart(2, '0');
            document.getElementById('fecha_fin').value = anio + '-' + mes + '-' + dia;
        }

        function filtrar() {
            var fi = document.getElementById('fecha_inicio').value;
            var ff = document.getElementById('fecha_fin').value;
            if (!fi) { alert('Seleccione una fecha de inicio'); return; }
            window.location.href = 'Reportes_Semanal.php?fecha_inicio=' + fi + '&fecha_fin=' + ff;
        }

        // Al cargar la página, si hay fecha_inicio pero no se ha calculado aún, recalcular
        window.addEventListener('DOMContentLoaded', function() {
            var fi = document.getElementById('fecha_inicio').value;
            var ff = document.getElementById('fecha_fin').value;
            // Solo recalcular si la diferencia no es exactamente 6 días
            if (fi && ff) {
                var inicio = new Date(fi + 'T00:00:00');
                var finActual = new Date(ff + 'T00:00:00');
                var diff = Math.round((finActual - inicio) / (1000 * 60 * 60 * 24));
                if (diff !== 6) {
                    calcularFechaFin();
                }
            }
        });

        function descargarPDF() {
            var elemento = document.getElementById('contenido-reporte');
            var opt = {
                margin: 10,
                filename: 'Reporte_Semanal_<?php echo $fecha_inicio . "_" . $fecha_fin; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(elemento).save();
        }
    </script>
</body>
</html>