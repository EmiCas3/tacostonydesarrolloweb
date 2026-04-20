<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Movimientos - Entradas</title>
    <link rel="stylesheet" href="../../../estilos/estilogenerico.css">
</head>

<body>
    <div class="sidebar">
        <div align="center">
            <a href="../../Dashboard.php">
                <img src="../../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="../../Dashboard.php" class="menu-item">
            <img src="../../../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="../../Inventario.php" class="menu-item">
            <img src="../../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../../Movimientos.php" class="menu-item activo">
            <img src="../../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../../Reportes.php" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../../Administracion.php" class="menu-item">
            <img src="../../../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="../../Catalogo.php" class="menu-item">
            <img src="../../../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../../../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="../../Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="../../Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="formulario-card">

            <div class="titulo-caja">
                REGISTRAR ENTRADA
            </div>

            <form id="entrada2form" method="post" action="#">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre Material</label>
                        <select id="nombreMaterial">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $result = mysqli_query($link, "SELECT id, nombre FROM t_materiales ORDER BY nombre") or die(mysqli_error($link));
                            while($row = mysqli_fetch_array($result)){
                                echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-grupo">
                        <label>ID Material</label>
                        <input type="number" id="idMaterial" placeholder="Se llena automáticamente" readonly>
                    </div>

                    <div class="input-grupo">
                        <label>Cantidad</label>
                        <input type="number" id="cantidad" placeholder="Ingrese la cantidad">
                    </div>

                    <div class="input-grupo">
                        <label>Precio Unitario</label>
                        <input type="number" id="precioUnitario" placeholder="Ingrese el precio unitario" step="0.01">
                    </div>
                </div>

                <div style="text-align: center; margin-top: -10px; margin-bottom: 30px;">
                    <button type="button" onclick="agregarEntrada()"
                        style="background: none; border: none; cursor: pointer; padding: 0; margin: 0 auto 10px auto; display: block;">
                        <img src="../../../Imagenes/masP.png" alt="Agregar" style="width: 45px; height: auto;">
                    </button>
                    <div style="color: #888; font-size: 16px;">¿Agregar otro material?</div>
                </div>

                <div class="botones-bottom" style="justify-content: space-between; width: 100%;">
                    <input type="button" value="ATRÁS" onClick="history.go(-1)" class="btn-secundario">
                    <a class="btn-accion" onclick="validarEntrada()">CONFIRMAR</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Arreglo para almacenar en sesión los productos de la entrada actual
        var productosEntrada = JSON.parse(sessionStorage.getItem('productosEntrada') || "[]");

        // Auto-llenar ID al seleccionar un material
        document.getElementById('nombreMaterial').addEventListener('change', function() {
            document.getElementById('idMaterial').value = this.value;
        });

        function agregarEntrada() {
            var select = document.getElementById('nombreMaterial');
            if (select.value == "") {
                alert("Seleccione un material para agregarlo.");
                return;
            }
            var cantidad = document.getElementById('cantidad').value;
            var precio = document.getElementById('precioUnitario').value;

            if (cantidad == "" || precio == "") {
                alert("Ingrese cantidad y precio unitario válidos.");
                return;
            }

            var selectedOption = select.options[select.selectedIndex];
            productosEntrada.push({
                nombre: selectedOption.text,
                id: document.getElementById('idMaterial').value,
                cantidad: cantidad,
                precio: precio,
                subtotal: (parseFloat(cantidad) * parseFloat(precio)).toFixed(2)
            });

            sessionStorage.setItem('productosEntrada', JSON.stringify(productosEntrada));
            alert("Material agregado.");
            
            document.getElementById('entrada2form').reset();
            document.getElementById('idMaterial').value = '';
        }

        function validarEntrada() {
            var select = document.getElementById('nombreMaterial');
            var cantidad = document.getElementById('cantidad').value;
            var precio = document.getElementById('precioUnitario').value;
            
            // Si hay algo escrito en el form, lo intentamos agregar o avisamos
            if (select.value !== "" && cantidad !== "" && precio !== "") {
                var selectedOption = select.options[select.selectedIndex];
                productosEntrada.push({
                    nombre: selectedOption.text,
                    id: document.getElementById('idMaterial').value,
                    cantidad: cantidad,
                    precio: precio,
                    subtotal: (parseFloat(cantidad) * parseFloat(precio)).toFixed(2)
                });
                sessionStorage.setItem('productosEntrada', JSON.stringify(productosEntrada));
            } else if (select.value !== "" || cantidad !== "" || precio !== "") {
                alert("Complete los campos del material actual, o límpielos para continuar.");
                return 0;
            }

            if (productosEntrada.length === 0) {
                alert("No hay materiales agregados a la entrada.");
                return 0;
            }

            window.location.href = "Entrada3.php";
        }
    </script>
</body>

</html>
