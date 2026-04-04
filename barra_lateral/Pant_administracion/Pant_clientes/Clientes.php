<?php include("../../../conex.php");
$link = Conectarse();
$telefonos = [];
$correos = [];
$res = mysqli_query($link, "SELECT numero_telefono, correo FROM t_clientes");
if ($res) {
    while($row = mysqli_fetch_array($res)) {
        $telefonos[] = $row['numero_telefono'];
        $correos[] = $row['correo'];
    }
}
$jsonTelefonos = json_encode($telefonos);
$jsonCorreos = json_encode($correos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Administración - Clientes</title>
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
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
        .input-grupo select:focus {
            border-color: #f6821f;
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
        <a href="../../Inventario.php" class="menu-item">
            <img src="../../../Imagenes/icon-inv.png" width="25" name="Inventario"> Inventario
        </a>
        <a href="../../Movimientos.html" class="menu-item">
            <img src="../../../Imagenes/icon-mov.png" width="25" name="Movimientos"> Movimientos
        </a>
        <a href="../../Reportes.html" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25" name="Reportes"> Reportes
        </a>
        <a href="../../Administracion.html" class="menu-item activo">
            <img src="../../../Imagenes/icon-admin.png" width="25" name="Administración"> Administración
        </a>
        <a href="../../Catalogo.html" class="menu-item">
            <img src="../../../Imagenes/icon-catalogo.png" width="25" name="Catálogo"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../../../Imagenes/icon-config.png" width="25" name="Configuración"> Configuración
            </a>
            <div class="submenu">
                <a href="../../Configuracion.html" class="submenu-item">Editar Perfil</a>
                <a href="../../Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../../Login.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="formulario-card">

            <div class="titulo-caja">
                INFORMACIÓN DEL CLIENTE
            </div>

            <form id="clienteForm" method="post" action="#">
                <div class="form-grid">
                    <div class="input-grupo">
                        <label>Nombre Cliente</label>
                        <input type="text" id="nombreCliente" placeholder="Ingrese el nombre">
                    </div>
                    <div class="input-grupo">
                        <label>RFC</label>
                        <input type="text" id="rfc" placeholder="Ingrese el RFC">
                    </div>
                    <div class="input-grupo">
                        <label>Razón Social</label>
                        <input type="text" id="razonSocial" placeholder="Ingrese la razón social">
                    </div>
                    <div class="input-grupo">
                        <label>Código Postal</label>
                        <input type="number" id="codigoPostal" placeholder="Ingrese el CP">
                    </div>
                    <div class="input-grupo">
                        <label>Número de Teléfono</label>
                        <input type="number" id="numeroTelefono" placeholder="Ingrese el número">
                    </div>
                    <div class="input-grupo">
                        <label>Correo Electrónico</label>
                        <input type="text" id="correo" placeholder="Ingrese el correo">
                    </div>
                    <div class="input-grupo">
                        <label>Calle</label>
                        <input type="text" id="calle" placeholder="Ingrese la calle">
                    </div>
                    <div class="input-grupo">
                        <label>Colonia</label>
                        <input type="text" id="colonia" placeholder="Ingrese la colonia">
                    </div>
                    <div class="input-grupo">
                        <label>Estado</label>
                        <input type="text" id="estado" placeholder="Ingrese el estado">
                    </div>
                </div>
            </form>

            <div class="botones-bottom">
                <a class="btn-secundario" href="../../Administracion.html">CANCELAR</a>
                <a class="btn-accion" onclick="valida_enviar()">CONFIRMAR</a>
            </div>

        </div>
    </div>

    <script>
        function valida_enviar() {
            var telefonosDB = <?php echo $jsonTelefonos; ?>;
            var correosDB = <?php echo $jsonCorreos; ?>;

            if (document.getElementById("nombreCliente").value == "") {
                alert("Nombre del cliente no ingresado"); return;
            }
            if (document.getElementById("rfc").value == "") {
                alert("RFC no ingresado"); return;
            }
            if (document.getElementById("razonSocial").value == "") {
                alert("Razón Social no ingresada"); return;
            }
            if (document.getElementById("codigoPostal").value == "") {
                alert("Código Postal no ingresado"); return;
            }
            if (document.getElementById("numeroTelefono").value == "") {
                alert("Número de Teléfono no ingresado"); return;
            }
            if (document.getElementById("numeroTelefono").value.length != 10) {
                alert("Número de Teléfono no es válido"); return;
            }
            if (telefonosDB.includes(document.getElementById("numeroTelefono").value)) {
                alert("El número de teléfono ya está registrado en la base de datos."); return;
            }
            if (document.getElementById("correo").value == "") {
                alert("Correo Electrónico no ingresado"); return;
            }
            if (correosDB.includes(document.getElementById("correo").value)) {
                alert("El correo electrónico ya está registrado en la base de datos."); return;
            }
            if (document.getElementById("calle").value == "") {
                alert("Calle no ingresada"); return;
            }
            if (document.getElementById("colonia").value == "") {
                alert("Colonia no ingresada"); return;
            }
            if (document.getElementById("estado").value == "") {
                alert("Estado no ingresado"); return;
            }

            alert("Cliente registrado con éxito");
            window.location.href = "../../Administracion.html";
        }
    </script>
</body>

</html>