<?php
include("../../seguridad.php");
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

            <div id="reporte-contenido">
            <div class="barra-info-reporte">
                <?php $meses_es = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']; echo 'Ganancia por Producto — ' . date('d') . ' de ' . $meses_es[intval(date('m'))] . ' de ' . date('Y'); ?>
            </div>
            <div class="tabla-contenedor">
                <div class="tabla-header grid-ganancia">
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

                            echo '<div class="fila grid-ganancia">';
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

            <div class="contenedor-btn-pdf">
                <button class="btn-descargar-pdf" onclick="descargarPDF()" title="Descargar PDF"><img src="../../Imagenes/Descarga.png" alt="Descargar PDF"></button>
            </div>

        </div>
    </div>
    <script>
        function descargarPDF() {
            var elemento = document.getElementById('reporte-contenido');
            var opt = {
                margin:       [10, 10, 10, 10],
                filename:     'Ganancia_por_Producto.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(elemento).save();
        }
    </script>
</body>
</html>