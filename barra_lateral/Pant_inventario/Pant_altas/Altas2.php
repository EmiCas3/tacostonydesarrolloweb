<?php include("../../../seguridad.php"); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Inventario - Altas</title>
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

        .menu-item.activo {
            background-color: #f6821f;
        }

        .menu-item:hover:not(.activo) {
            background-color: #F9D864;
        }

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

        .tabla-contenedor {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .5);
            margin-bottom: 40px;
        }

        .tabla-contenedor table {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-contenedor thead th {
            background: #f6821f;
            color: #000;
            font-weight: bold;
            padding: 15px 30px;
            text-align: left;
        }

        .tabla-contenedor tbody td {
            padding: 12px 30px;
            background-color: #E6E6E6;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #D0D0D0;
        }

        .tabla-contenedor tbody tr:last-child td {
            border-bottom: none;
        }

        .col-campo {
            width: 50%;
            color: #555;
        }

        .col-valor {
            width: 50%;
            font-weight: bold;
        }

        .botones-bottom {
            display: flex;
            justify-content: space-between;
        }

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

        .btn-accion:hover {
            background-color: #DC7B3C;
        }

        .btn-secundario {
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

        .btn-secundario:hover {
            background-color: #DC7B3C;
        }

        .menu-dropdown {
            position: relative;
        }

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

        .menu-dropdown:hover .submenu {
            display: flex;
        }

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
    <div
        style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%; position: sticky; top: 20px;">
        <div align="center">
            <a href="../../Dashboard.php">
                <img src="../../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>

        <a href="../../Dashboard.php" class="menu-item">
            <img src="../../../Imagenes/icon-dash.png" width="25" name="Dashboard"> Dashboard
        </a>
        <a href="../../Inventario.php" class="menu-item activo">
            <img src="../../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../../Movimientos.php" class="menu-item">
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
                CONFIRMAR REGISTRO
            </div>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th class="col-campo">Campo</th>
                            <th class="col-valor">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-campo">Nombre del Material</td>
                            <td class="col-valor" id="show-nombre">—</td>
                        </tr>
                        <tr>
                            <td class="col-campo">Cantidad Inicial</td>
                            <td class="col-valor" id="show-cantidad">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="botones-bottom">
                <input type="button" value="ATRÁS" onclick="history.go(-1)" class="btn-secundario">
                <a class="btn-accion" onclick="confirmarAlta()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        window.onload = function () {
            var nombre = sessionStorage.getItem("alta_nombre");
            var cantidad = sessionStorage.getItem("alta_cantidad");

            if (!nombre || !cantidad) {
                alert("Error: No se encontraron los datos del material");
                window.location.href = "Altas1.php";
                return;
            }

            document.getElementById("show-nombre").textContent = nombre;
            document.getElementById("show-cantidad").textContent = cantidad;
        };

        function confirmarAlta() {
            sessionStorage.removeItem("alta_nombre");
            sessionStorage.removeItem("alta_cantidad");
            alert("Material registrado con éxito.");
            window.location.href = "../../Inventario.php";
        }
    </script>
</body>

</html>