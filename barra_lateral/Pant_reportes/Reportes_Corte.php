<?php
include("../../seguridad.php");
include("../../conex.php");
$link = Conectarse();

$dia_sel  = isset($_GET['dia'])  ? intval($_GET['dia'])  : intval(date('d'));
$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$fecha = sprintf('%04d-%02d-%02d', $anio_sel, $mes_sel, $dia_sel);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Corte de Caja</title>
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

    <div class="main-content">
        <div class="reporte-card">
            <div class="titulo-caja">CORTE DIARIO</div>

            <div class="filtro-container">
                <div class="form-grid" style="gap:15px; margin-bottom:0;">
                    <div class="input-grupo">
                        <label>Día</label>
                        <select id="dia">
                            <?php
                            for ($d = 1; $d <= 31; $d++) {
                                $sel = ($d == $dia_sel) ? 'selected' : '';
                                echo "<option value='$d' $sel>$d</option>";
                            }
                            ?>
                        </select>
                    </div>
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
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>

            <?php
            // Consulta para el reporte general del día
            $q_reporte = "SELECT 
                CONCAT('$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)), 2), 2)) AS total_del_dia,
                COUNT(DISTINCT vg.id) AS numero_de_folios,
                CONCAT('$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) / 1.16, 2), 2)) AS gravado_al_16,
                CONCAT('$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) - (SUM((tp.precio * vp.cantidad)) / 1.16), 2), 2)) AS impuesto,
                CONCAT('$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)), 2), 2)) AS total_con_impuesto,
                COUNT(DISTINCT CASE WHEN vg.servicio_a_domicilio = 'Si' THEN vg.id END) AS numero_servicios_domicilio,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN vg.servicio_a_domicilio = 'Si' THEN vg.id END) > 0 
                    THEN CONCAT('$', FORMAT(ROUND(SUM(CASE WHEN vg.servicio_a_domicilio = 'Si' THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '$0.00'
                END AS ingreso_servicios_domicilio,
                CONCAT('$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) / NULLIF(COUNT(DISTINCT vg.id_cliente), 0), 2), 2)) AS venta_promedio_por_cliente,
                COUNT(CASE WHEN tp.id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 34, 38) THEN 1 END) AS count_alimentos,
                CASE 
                    WHEN COUNT(CASE WHEN tp.id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 34, 38) THEN 1 END) > 0 
                    THEN CONCAT('$', FORMAT(ROUND(SUM(CASE WHEN tp.id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 34, 38) THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '$0.00'
                END AS ingreso_alimentos,
                COUNT(CASE WHEN tp.id IN (32, 35, 36, 37) THEN 1 END) AS count_bebidas,
                CASE 
                    WHEN COUNT(CASE WHEN tp.id IN (32, 35, 36, 37) THEN 1 END) > 0 
                    THEN CONCAT('$', FORMAT(ROUND(SUM(CASE WHEN tp.id IN (32, 35, 36, 37) THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '$0.00'
                END AS ingreso_bebidas,
                CONCAT('$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 1 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_1,
                CONCAT('$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 2 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_2,
                CONCAT('$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 3 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_3,
                CONCAT('$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 4 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_4,
                CONCAT('$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 5 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_5
            FROM ((t_vender_general vg JOIN t_vender_particular vp ON (vg.id = vp.id_vg)) JOIN t_productos tp ON (vp.id_producto = tp.id))
            WHERE DATE(vg.fecha) = '$fecha'";

            $r_reporte = mysqli_query($link, $q_reporte);
            $resumen = $r_reporte ? mysqli_fetch_assoc($r_reporte) : false;

            if(!$resumen || $resumen['numero_de_folios'] == 0) {
                // Valores por defecto cuando no hay ventas
                $resumen = array(
                    'total_del_dia' => '$0.00',
                    'numero_de_folios' => '0',
                    'gravado_al_16' => '$0.00',
                    'impuesto' => '$0.00',
                    'total_con_impuesto' => '$0.00',
                    'numero_servicios_domicilio' => '0',
                    'ingreso_servicios_domicilio' => '$0.00',
                    'venta_promedio_por_cliente' => '$0.00',
                    'count_alimentos' => '0',
                    'ingreso_alimentos' => '$0.00',
                    'count_bebidas' => '0',
                    'ingreso_bebidas' => '$0.00',
                    'ventas_empleado_1' => '$0.00',
                    'ventas_empleado_2' => '$0.00',
                    'ventas_empleado_3' => '$0.00',
                    'ventas_empleado_4' => '$0.00',
                    'ventas_empleado_5' => '$0.00'
                );
            } else {
                if ($resumen['venta_promedio_por_cliente'] === '$' || is_null($resumen['venta_promedio_por_cliente'])) {
                    $resumen['venta_promedio_por_cliente'] = '$0.00';
                }
            }
            ?>

            <div class="subtitulo">Resumen Financiero</div>
            <div class="resumen-grid">
                <div class="carta-stat">
                    <div class="stat-titulo">Venta Total del Día</div>
                    <div class="stat-valor" style="color:#d35400;"><?php echo $resumen['total_del_dia']; ?></div>
                </div>
                <div class="carta-stat azul">
                    <div class="stat-titulo">Gravado al 16%</div>
                    <div class="stat-valor"><?php echo $resumen['gravado_al_16']; ?></div>
                </div>
                <div class="carta-stat amarillo">
                    <div class="stat-titulo">Impuesto (IVA)</div>
                    <div class="stat-valor"><?php echo $resumen['impuesto']; ?></div>
                </div>

                <div class="carta-stat amarillo">
                    <div class="stat-titulo">Folios Generados</div>
                    <div class="stat-valor"><?php echo $resumen['numero_de_folios']; ?></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Servicios a Domicilio</div>
                    <div class="stat-valor"><?php echo $resumen['numero_servicios_domicilio']; ?> <span style="font-size:14px; color:#555;">(<?php echo $resumen['ingreso_servicios_domicilio']; ?>)</span></div>
                </div>
                <div class="carta-stat azul">
                    <div class="stat-titulo">Ticket Promedio</div>
                    <div class="stat-valor"><?php echo $resumen['venta_promedio_por_cliente']; ?></div>
                </div>

                <div class="carta-stat azul">
                    <div class="stat-titulo">Alimentos Vendidos</div>
                    <div class="stat-valor"><?php echo $resumen['count_alimentos']; ?> <span style="font-size:14px; color:#555;">(<?php echo $resumen['ingreso_alimentos']; ?>)</span></div>
                </div>
                <div class="carta-stat amarillo">
                    <div class="stat-titulo">Bebidas Vendidas</div>
                    <div class="stat-valor"><?php echo $resumen['count_bebidas']; ?> <span style="font-size:14px; color:#555;">(<?php echo $resumen['ingreso_bebidas']; ?>)</span></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Total con Impuesto</div>
                    <div class="stat-valor"><?php echo $resumen['total_con_impuesto']; ?></div>
                </div>
            </div>

            <div class="subtitulo">Ventas por Empleado</div>
            <div class="grid-empleados">
                <div class="carta-stat">
                    <div class="stat-titulo">Empleado 1</div>
                    <div class="stat-valor" style="font-size: 16px;"><?php echo $resumen['ventas_empleado_1']; ?></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Empleado 2</div>
                    <div class="stat-valor" style="font-size: 16px;"><?php echo $resumen['ventas_empleado_2']; ?></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Empleado 3</div>
                    <div class="stat-valor" style="font-size: 16px;"><?php echo $resumen['ventas_empleado_3']; ?></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Empleado 4</div>
                    <div class="stat-valor" style="font-size: 16px;"><?php echo $resumen['ventas_empleado_4']; ?></div>
                </div>
                <div class="carta-stat">
                    <div class="stat-titulo">Empleado 5</div>
                    <div class="stat-valor" style="font-size: 16px;"><?php echo $resumen['ventas_empleado_5']; ?></div>
                </div>
            </div>

            <div class="subtitulo">Detalle de Ventas</div>
            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <span>Folio</span>
                    <span>Empleado</span>
                    <span>Hora</span>
                    <span>Total</span>
                </div>
                <div class="tabla-body">
                    <?php
                    $q = "SELECT vg.id, e.nombre, TIME(vg.fecha) AS hora, SUM(vp.subtotal) AS total
                          FROM t_vender_general vg
                          JOIN t_empleados e ON vg.id_empleado = e.id
                          JOIN t_vender_particular vp ON vg.id = vp.id_vg
                          WHERE DATE(vg.fecha) = '$fecha'
                          GROUP BY vg.id, e.nombre, vg.fecha
                          ORDER BY vg.fecha ASC";

                    $r = mysqli_query($link, $q);

                    if($r && mysqli_num_rows($r)>0){
                        while($row=mysqli_fetch_array($r)){
                            echo '<div class="fila">';
                            echo '<span>#'.$row['id'].'</span>';
                            echo '<span>'.htmlspecialchars($row['nombre']).'</span>';
                            echo '<span>'.$row['hora'].'</span>';
                            echo '<span>$'.number_format($row['total'],2).'</span>';
                            echo '</div>';
                        }
                    }else{
                        echo '<div class="sin-datos">Sin ventas registradas en esta fecha</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        function filtrar(){
            var dia  = parseInt(document.getElementById('dia').value);
            var mes  = parseInt(document.getElementById('mes').value);
            var anio = parseInt(document.getElementById('anio').value);

            if(!anio || anio < 2020){
                alert('Ingrese un año válido');
                return;
            }

            // Días máximos por mes
            var diasPorMes = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            var nombresMes = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

            // Año bisiesto
            if ((anio % 4 === 0 && anio % 100 !== 0) || (anio % 400 === 0)) {
                diasPorMes[2] = 29;
            }

            if (dia < 1 || dia > diasPorMes[mes]) {
                alert(nombresMes[mes] + ' de ' + anio + ' solo tiene ' + diasPorMes[mes] + ' días. Ingrese un día válido.');
                return;
            }

            window.location.href = 'Reportes_Corte.php?dia=' + dia + '&mes=' + mes + '&anio=' + anio;
        }
    </script>
</body>
</html>