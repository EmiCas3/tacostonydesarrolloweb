<?php
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

        .menu-item.activo { background-color: #f6821f; }
        .menu-item:hover:not(.activo) { background-color: #F9D864; }

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .formulario-card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 50px;
            width: 100%;
            max-width: 700px;
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
            margin-bottom: 40px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
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
            background-color: #F0F0F0;
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
            padding: 15px 50px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }

        .btn-accion:hover { background-color: #DC7B3C; }

        .tabla-contenedor {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            margin-top: 30px;
        }

        .tabla-header {
            background-color: #f6821f;
            color: #000;
            font-weight: bold;
            display: grid;
            padding: 12px 20px;
            grid-template-columns: 2fr 1fr;
        }

        .tabla-body {
            background-color: #E6E6E6;
            max-height: 300px;
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
            grid-template-columns: 2fr 1fr;
        }

        .fila:last-child { border-bottom: none; }

        .sin-datos {
            padding: 20px;
            text-align: center;
            color: #888;
            font-style: italic;
        }

        .total-box {
            background: #073A79;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 1px 1px 5px rgba(0,0,0,0.2);
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
    </style>
</head>
<body>
    <div style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%">
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
            <div style="display: flex; justify-content: flex-end;">
                <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
            </div>
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
                Total productos vendidos: <?php echo number_format($totalGeneral, 0); ?>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <span>Producto</span><span>Cantidad Vendida</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rRotacion && mysqli_num_rows($rRotacion) > 0) {
                        while ($row = mysqli_fetch_array($rRotacion)) {
                            echo '<div class="fila">';
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
        </div>
    </div>
    <script>
        function filtrar() {
            var mes  = document.getElementById('mes').value;
            var anio = document.getElementById('anio').value;
            if (!anio) { alert('Ingrese un año válido'); return; }
            window.location.href = 'Reportes_Rotacion.php?mes=' + mes + '&anio=' + anio;
        }
    </script>
</body>
</html>