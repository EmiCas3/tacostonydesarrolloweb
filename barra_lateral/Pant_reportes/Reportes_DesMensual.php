<?php
include("../../conex.php");
$link = Conectarse();

$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Construir rango de fechas del mes seleccionado
$fecha_inicio = sprintf('%04d-%02d-01', $anio_sel, $mes_sel);
$ultimo_dia   = date('t', mktime(0, 0, 0, $mes_sel, 1, $anio_sel));
$fecha_fin    = sprintf('%04d-%02d-%02d', $anio_sel, $mes_sel, $ultimo_dia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Descripción Mensual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5E5E5;
            margin: 0;
            padding: 20px;
            display: flex;
            box-sizing: border-box;
            height: 100vh;
            overflow: hidden;
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

        .menu-item.activo { background-color: #f6821f; }
        .menu-item:hover:not(.activo) { background-color: #F9D864; }

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow-y: auto;
            padding-bottom: 20px;
            max-height: calc(100vh - 40px);
        }

        .reporte-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 900px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .titulo-caja {
            background: #f6821f;
            color: #000000;
            font-weight: bold;
            font-size: 22px;
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .filtro-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            background: #F0F0F0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 0;
            flex: 1;
        }

        .input-grupo {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .input-grupo label {
            font-weight: bold;
            color: #333333;
            font-size: 14px;
        }

        .input-grupo input,
        .input-grupo select {
            background-color: #FFFFFF;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            outline: none;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .input-grupo input:focus,
        .input-grupo select:focus { border-color: #f6821f; }

        .btn-accion {
            background: #f6821f;
            color: #000000;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 1px 1px 5px rgba(0, 0, 0, .3);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-accion:hover { background-color: #DC7B3C; }

        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .carta-stat {
            background-color: #F9F9F9;
            border-left: 5px solid #f6821f;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
        }

        .carta-stat.azul { border-left-color: #073A79; }
        .carta-stat.amarillo { border-left-color: #F9D864; }

        .stat-titulo {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-valor {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .tabla-contenedor {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .tabla-header {
            background-color: #f6821f;
            color: #000;
            font-weight: bold;
            display: grid;
            padding: 12px 20px;
            grid-template-columns: 1fr 2fr 1fr 1fr;
        }

        .tabla-body {
            background-color: #E6E6E6;
            max-height: 250px;
            overflow-y: auto;
        }

        .tabla-body::-webkit-scrollbar { width: 6px; }
        .tabla-body::-webkit-scrollbar-thumb { background-color: #A0A0A0; border-radius: 10px; }

        .fila {
            display: grid;
            padding: 10px 20px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #D0D0D0;
            grid-template-columns: 1fr 2fr 1fr 1fr;
        }

        .fila:last-child { border-bottom: none; }

        .sin-datos {
            padding: 20px;
            text-align: center;
            color: #888;
            font-style: italic;
        }

        .menu-dropdown { position: relative; }

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

        .menu-dropdown:hover .submenu { display: flex; }

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

        .subtitulo {
            font-size: 18px;
            color: #333;
            border-bottom: 2px solid #f6821f;
            padding-bottom: 5px;
            margin-bottom: 20px;
            margin-top: 30px;
            font-weight: bold;
        }

        .grid-empleados {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .grid-empleados .carta-stat {
            text-align: center;
            border-left: none;
            border-top: 4px solid #073A79;
        }

        @media (max-width: 900px) {
            .resumen-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid-empleados {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 600px) {
            .resumen-grid, .grid-empleados {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div style="background-color: #FFFFFF; width: 250px; min-width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); position: sticky; top: 20px; align-self: flex-start; max-height: calc(100vh - 40px); overflow-y: auto;">
        <div align="center">
            <a href="../Dashboard.php"><img src="../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo"></a>
        </div>
        <a href="../Dashboard.php" class="menu-item"><img src="../../Imagenes/icon-dash.png" width="25"> Dashboard</a>
        <a href="../Inventario.php" class="menu-item"><img src="../../Imagenes/icon-inv.png" width="25"> Inventario</a>
        <a href="../Movimientos.html" class="menu-item"><img src="../../Imagenes/icon-mov.png" width="25"> Movimientos</a>
        <a href="../Reportes.html" class="menu-item activo"><img src="../../Imagenes/icon-repo.png" width="25"> Reportes</a>
        <a href="../Administracion.html" class="menu-item"><img src="../../Imagenes/icon-admin.png" width="25"> Administración</a>
        <a href="../Catalogo.html" class="menu-item"><img src="../../Imagenes/icon-catalogo.png" width="25"> Catálogo</a>
        <div class="menu-dropdown">
            <a class="menu-item"><img src="../../Imagenes/icon-config.png" width="25"> Configuración</a>
            <div class="submenu">
                <a href="../Configuracion.html" class="submenu-item">Editar Perfil</a>
                <a href="../Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../Login.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="reporte-card">
            <div class="titulo-caja">DESCRIPCIÓN DE VENTAS MENSUAL</div>

            <div class="filtro-container">
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
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>

            <?php
            // Consulta basada en el procedimiento total_mes
            $q_reporte = "SELECT 
                CONCAT('\$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)), 2), 2)) AS total_del_mes,
                COUNT(DISTINCT vg.id) AS numero_de_folios,
                CONCAT('\$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) / 1.16, 2), 2)) AS gravado_al_16,
                CONCAT('\$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) - (SUM((tp.precio * vp.cantidad)) / 1.16), 2), 2)) AS impuesto,
                CONCAT('\$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)), 2), 2)) AS total_con_impuesto,
                COUNT(DISTINCT CASE WHEN vg.servicio_a_domicilio = 'Si' THEN vg.id END) AS numero_servicios_domicilio,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN vg.servicio_a_domicilio = 'Si' THEN vg.id END) > 0 
                    THEN CONCAT('\$', FORMAT(ROUND(SUM(CASE WHEN vg.servicio_a_domicilio = 'Si' THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '\$0.00'
                END AS ingreso_servicios_domicilio,
                CONCAT('\$', FORMAT(ROUND(SUM((tp.precio * vp.cantidad)) / NULLIF(COUNT(DISTINCT vg.id_cliente), 0), 2), 2)) AS venta_promedio_por_cliente,
                COUNT(CASE WHEN tp.id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,33,34,38) THEN 1 END) AS count_alimentos,
                CASE 
                    WHEN COUNT(CASE WHEN tp.id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,33,34,38) THEN 1 END) > 0 
                    THEN CONCAT('\$', FORMAT(ROUND(SUM(CASE WHEN tp.id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,33,34,38) THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '\$0.00'
                END AS ingreso_alimentos,
                COUNT(CASE WHEN tp.id IN (32,35,36,37) THEN 1 END) AS count_bebidas,
                CASE 
                    WHEN COUNT(CASE WHEN tp.id IN (32,35,36,37) THEN 1 END) > 0 
                    THEN CONCAT('\$', FORMAT(ROUND(SUM(CASE WHEN tp.id IN (32,35,36,37) THEN (tp.precio * vp.cantidad) END), 2), 2))
                    ELSE '\$0.00'
                END AS ingreso_bebidas,
                CONCAT('\$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 1 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_1,
                CONCAT('\$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 2 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_2,
                CONCAT('\$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 3 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_3,
                CONCAT('\$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 4 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_4,
                CONCAT('\$', FORMAT(ROUND(COALESCE(SUM(CASE WHEN vg.id_empleado = 5 THEN (tp.precio * vp.cantidad) END), 0), 0), 2)) AS ventas_empleado_5
            FROM ((t_vender_general vg JOIN t_vender_particular vp ON (vg.id = vp.id_vg)) JOIN t_productos tp ON (vp.id_producto = tp.id))
            WHERE CAST(vg.fecha AS DATE) BETWEEN '$fecha_inicio' AND '$fecha_fin'";

            $r_reporte = mysqli_query($link, $q_reporte);
            $resumen = $r_reporte ? mysqli_fetch_assoc($r_reporte) : false;

            if(!$resumen || $resumen['numero_de_folios'] == 0) {
                $resumen = array(
                    'total_del_mes' => '$0.00',
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
                    <div class="stat-titulo">Venta Total del Mes</div>
                    <div class="stat-valor" style="color:#d35400;"><?php echo $resumen['total_del_mes']; ?></div>
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

            <div class="subtitulo">Detalle de Ventas por Día</div>
            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <span>Día</span>
                    <span>Folios</span>
                    <span>Productos</span>
                    <span>Total</span>
                </div>
                <div class="tabla-body">
                    <?php
                    $q = "SELECT DAY(vg.fecha) AS dia, 
                                 COUNT(DISTINCT vg.id) AS folios,
                                 SUM(vp.cantidad) AS productos,
                                 SUM(tp.precio * vp.cantidad) AS total
                          FROM t_vender_general vg
                          JOIN t_vender_particular vp ON vg.id = vp.id_vg
                          JOIN t_productos tp ON vp.id_producto = tp.id
                          WHERE CAST(vg.fecha AS DATE) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                          GROUP BY DAY(vg.fecha)
                          ORDER BY dia ASC";

                    $r = mysqli_query($link, $q);

                    if($r && mysqli_num_rows($r) > 0){
                        while($row = mysqli_fetch_array($r)){
                            echo '<div class="fila">';
                            echo '<span>' . $row['dia'] . '</span>';
                            echo '<span>' . $row['folios'] . '</span>';
                            echo '<span>' . number_format($row['productos'], 0) . '</span>';
                            echo '<span>$' . number_format($row['total'], 2) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin ventas registradas en este mes</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        function filtrar(){
            var mes  = document.getElementById('mes').value;
            var anio = document.getElementById('anio').value;

            if(!anio || anio < 2020){
                alert('Ingrese un año válido');
                return;
            }

            window.location.href = 'Reportes_DesMensual.php?mes=' + mes + '&anio=' + anio;
        }
    </script>
</body>
</html>
