<?php include("../seguridad.php");
include("../conex.php");
$link = Conectarse();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Dashboard</title>
    <link rel="stylesheet" href="../estilos/estilogenerico.css">
    <!-- Chart.js para la gráfica dinámica -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <div align="center">
            <a href="Dashboard.php">
                <img src="../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="Dashboard.php" class="menu-item activo">
            <img src="../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="Inventario.php" class="menu-item">
            <img src="../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="Movimientos.php" class="menu-item">
            <img src="../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="Reportes.php" class="menu-item">
            <img src="../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="Administracion.php" class="menu-item">
            <img src="../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="Catalogo.php" class="menu-item">
            <img src="../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div style="flex-grow:1">
        <h1>Dashboard</h1>

        <div class="cajas-superiores">

            <!-- INVENTARIO BAJO -->
            <div class="caja">
                <div class="encabezado-caja">
                    <img src="../Imagenes/icon-warning.png" width="20" name="Warning"> INVENTARIO BAJO
                </div>
                <div class="inventory-scroll">
                    <table class="inventory">
                        <?php
                        $queryBajo = "SELECT nombre, existencias FROM t_materiales WHERE existencias <= 5 ORDER BY existencias ASC";
                        $resBajo = mysqli_query($link, $queryBajo);
                        if ($resBajo && mysqli_num_rows($resBajo) > 0) {
                            while ($rowBajo = mysqli_fetch_array($resBajo)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($rowBajo['nombre']) . '</td>';
                                echo '<td align="right">' . $rowBajo['existencias'] . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td class="sin-datos" colspan="2">Sin materiales con inventario bajo</td></tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>

            <!-- TIEMPO GUARDADO -->
            <div class="caja">
                <div class="encabezado-caja">
                    <img src="../Imagenes/icon-warning.png" width="20" name="Warning"> TIEMPO GUARDADO
                </div>
                <div class="inventory-scroll">
                    <table class="inventory">
                        <?php
                        $queryTiempo = "SELECT m.nombre, 
                                            DATEDIFF(NOW(), MAX(ng.fecha)) AS dias_guardado
                                        FROM t_materiales m
                                        JOIN t_necesitar_particular np ON np.id_material = m.id
                                        JOIN t_necesitar_general ng ON ng.id_ng = np.id_ng
                                        GROUP BY m.id, m.nombre
                                        ORDER BY dias_guardado DESC
                                        LIMIT 5";
                        $resTiempo = mysqli_query($link, $queryTiempo);
                        if ($resTiempo && mysqli_num_rows($resTiempo) > 0) {
                            while ($rowT = mysqli_fetch_array($resTiempo)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($rowT['nombre']) . '</td>';
                                echo '<td align="right">' . $rowT['dias_guardado'] . ' Días</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td class="sin-datos" colspan="2">Sin datos de entradas registradas</td></tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>

        </div>

        <div class="caja-inferior">
            <h2>RESUMEN DE INVENTARIO</h2>

            <div class="contenido-inferior">
                <div class="columna-botones">
                    <a href="Pant_movimientos/Pant_entradas/Entrada1.php">
                        <button class="botones">REGISTRAR ENTRADA</button>
                    </a>
                    <a href="Pant_inventario/Pant_asignar/asignar1.php">
                        <button class="botones">ASIGNAR MATERIALES</button>
                    </a>
                </div>

                <!-- Gráfica dinámica: Top 8 materiales por existencias -->
                <div style="background-color: #F6821F; padding: 15px; border-radius: 10px; width: 350px;">
                    <canvas id="graficaInventario" width="320" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php
    $queryGrafica = "SELECT nombre, existencias FROM t_materiales ORDER BY existencias DESC LIMIT 8";
    $resGrafica = mysqli_query($link, $queryGrafica);
    $labelsGrafica = [];
    $datosGrafica  = [];
    if ($resGrafica) {
        while ($rowG = mysqli_fetch_array($resGrafica)) {
            $labelsGrafica[] = htmlspecialchars($rowG['nombre']);
            $datosGrafica[]  = (float)$rowG['existencias'];
        }
    }
    $labelsJson = json_encode($labelsGrafica);
    $datosJson  = json_encode($datosGrafica);
    ?>

    <script>
        var ctx = document.getElementById('graficaInventario').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labelsJson; ?>,
                datasets: [{
                    label: 'Existencias',
                    data: <?php echo $datosJson; ?>,
                    backgroundColor: '#073A79',
                    borderColor: '#000000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#FFFFFF',
                            font: { size: 9 },
                            maxRotation: 45
                        },
                        grid: { color: 'rgba(255,255,255,0.2)' }
                    },
                    y: {
                        ticks: { color: '#FFFFFF', font: { size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.2)' }
                    }
                }
            }
        });
    </script>
</body>

</html>