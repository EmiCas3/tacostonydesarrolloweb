<?php
include("../../conex.php");
$link = Conectarse();
?>
<!--Hay que checar la sentencia SQL, probablemente no este funcionando del todo bien-->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Ganancia por Producto</title>
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
            max-width: 800px;
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
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        }

        .tabla-body {
            background-color: #E6E6E6;
            max-height: 350px;
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
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        }

        .fila:last-child { border-bottom: none; }

        .sin-datos {
            padding: 20px;
            text-align: center;
            color: #888;
            font-style: italic;
        }

        .margen-positivo { color: #27ae60; }
        .margen-bajo { color: #e67e22; }
        .margen-negativo { color: #e74c3c; }

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
                <a href="../../index.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="formulario-card">
            <div class="titulo-caja">GANANCIA POR PRODUCTO</div>

            <?php
            // Query basado en la vista ganancia_por_producto
            $qGanancia = "SELECT 
                p.nombre AS producto,
                p.precio AS precio_venta,
                CONCAT('\$', FORMAT(ROUND(SUM(IFNULL((np.cantidad * uc.costo_unitario), 0)), 2), 2)) AS costo_total_produccion,
                CONCAT('\$', FORMAT(ROUND((p.precio - SUM(IFNULL((np.cantidad * uc.costo_unitario), 0))), 2), 2)) AS ganancia_real,
                ROUND((((p.precio - ROUND(SUM(IFNULL((np.cantidad * uc.costo_unitario), 0)), 2)) / p.precio) * 100), 2) AS margen_num,
                CONCAT(ROUND((((p.precio - ROUND(SUM(IFNULL((np.cantidad * uc.costo_unitario), 0)), 2)) / p.precio) * 100), 2), '%') AS margen_ganancia
            FROM (((t_productos p
                LEFT JOIN (SELECT ng.id_ng, ng.id_producto
                    FROM t_necesitar_general ng
                    JOIN (SELECT id_producto, MAX(fecha) AS fecha_reciente
                          FROM t_necesitar_general
                          GROUP BY id_producto) ult_ng 
                    ON (ng.id_producto = ult_ng.id_producto AND ng.fecha = ult_ng.fecha_reciente)) ng 
                ON (p.id = ng.id_producto))
                LEFT JOIN t_necesitar_particular np ON (ng.id_ng = np.id_ng))
                LEFT JOIN (SELECT pp.id_material, pp.costo AS costo_unitario
                    FROM t_proporcionar_particular pp
                    JOIN (SELECT pp_sub.id_material, MAX(pg.fecha) AS fecha_ultima
                          FROM t_proporcionar_particular pp_sub
                          JOIN t_proporcionar_general pg ON (pp_sub.id_pg = pg.id)
                          GROUP BY pp_sub.id_material) ult_pp 
                    ON (pp.id_material = ult_pp.id_material)
                    JOIN t_proporcionar_general pg ON (pp.id_pg = pg.id AND pg.fecha = ult_pp.fecha_ultima)) uc 
                ON (np.id_material = uc.id_material))
            WHERE np.id_material NOT IN (7, 8, 9) OR np.id_material IS NULL
            GROUP BY p.id, p.nombre, p.precio
            HAVING SUM(IFNULL((np.cantidad * uc.costo_unitario), 0)) > 0
            ORDER BY margen_num DESC, ganancia_real DESC";

            $rGanancia = mysqli_query($link, $qGanancia);
            ?>

            <div class="tabla-contenedor">
                <div class="tabla-header">
                    <span>Producto</span>
                    <span>Precio Venta</span>
                    <span>Costo Prod.</span>
                    <span>Ganancia</span>
                    <span>Margen</span>
                </div>
                <div class="tabla-body">
                    <?php
                    if ($rGanancia && mysqli_num_rows($rGanancia) > 0) {
                        while ($row = mysqli_fetch_array($rGanancia)) {
                            $margen = floatval($row['margen_num']);
                            if ($margen >= 50) {
                                $clase = 'margen-positivo';
                            } elseif ($margen >= 20) {
                                $clase = 'margen-bajo';
                            } else {
                                $clase = 'margen-negativo';
                            }

                            echo '<div class="fila">';
                            echo '<span>' . htmlspecialchars($row['producto']) . '</span>';
                            echo '<span>$' . number_format($row['precio_venta'], 2) . '</span>';
                            echo '<span>' . $row['costo_total_produccion'] . '</span>';
                            echo '<span>' . $row['ganancia_real'] . '</span>';
                            echo '<span class="' . $clase . '">' . $row['margen_ganancia'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="sin-datos">Sin datos de ganancia disponibles</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>