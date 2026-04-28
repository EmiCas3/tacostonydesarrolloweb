<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="./Imagenes/TTlogomini.png">
    <title>Login - Tacos Tony</title>
    <link rel="stylesheet" href="estilos/estilogenerico.css">
    <style>
        .recuperar-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #555;
            text-decoration: underline;
            cursor: pointer;
        }
        .recuperar-link:hover {
            color: #F6821F;
        }

        /* Modal overlay */
        .modal-recuperar {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }
        .modal-recuperar.activo {
            display: flex;
        }
        .modal-recuperar-box {
            background: #fff;
            border-radius: 12px;
            padding: 35px 30px;
            width: 320px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            text-align: center;
        }
        .modal-recuperar-box h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #000;
        }
        .modal-recuperar-box p {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }
        .modal-recuperar-box input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #d4d4d4;
            border-radius: 8px;
            background-color: #e5e5e5;
            box-sizing: border-box;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .modal-recuperar-box input[type="email"]:focus {
            border-color: #F6821F;
            outline: none;
        }
        .modal-recuperar-box .btn-enviar {
            width: 100%;
            padding: 12px;
            background: #F6821F;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .modal-recuperar-box .btn-enviar:hover {
            background: #d4701a;
        }
        .modal-recuperar-box .btn-cancelar-rec {
            width: 100%;
            padding: 10px;
            background: #e5e5e5;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            color: #333;
        }
        .modal-recuperar-box .btn-cancelar-rec:hover {
            background: #ccc;
        }
        #msg-recuperar {
            font-size: 13px;
            margin-bottom: 10px;
            min-height: 18px;
        }
    </style>
</head>

<body style="height: 100vh; justify-content: center; align-items: center">

    <div class="login-card">
        <img src="./Imagenes/Tacos_tony_logo.png" name="Logo" width="220" margin-bottom="25px">
        <h2 style="font-size: large;">Sistema de control de inventario</h2>

        <?php if (!empty($_GET['error'])) { ?>
            <p style="color: red; font-weight: bold; margin-bottom: 15px;">
            <?php
                if ($_GET['error'] == 'auth_required') {
                    echo 'Debe iniciar sesión para acceder al sistema';
                } else {
                    echo 'Error de autenticación';
                }
            ?>
            </p>
        <?php } ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success') { ?>
            <script>alert("Sesión cerrada correctamente")</script>
        <?php } ?>

        <form id="loginForm" method="post" action="validar_login.php">
            <div class="input-group">
                <input type="text" name="user" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <input type="password" name="contra" placeholder="Contraseña" required>
            </div>

            <input class="button" type="button" name="Ingresar" value="INGRESAR" onclick="valida_enviar()">
        </form>

        <!-- Enlace recuperar contraseña -->
        <span class="recuperar-link" onclick="abrirModalRecuperar()">Recuperar contraseña</span>
    </div>

    <!-- Modal recuperar contraseña -->
    <div class="modal-recuperar" id="modalRecuperar">
        <div class="modal-recuperar-box">
            <h3>Recuperar Contraseña</h3>
            <p>Ingresa tu correo y te enviaremos una contraseña temporal.</p>
            <div id="msg-recuperar" style="color: green;"></div>
            <input type="email" id="correo-recuperar" placeholder="Tu correo electrónico">
            <button class="btn-enviar" onclick="enviarRecuperacion()">ENVIAR CORREO</button>
            <button class="btn-cancelar-rec" onclick="cerrarModalRecuperar()">Cancelar</button>
        </div>
    </div>

    <script>
        function valida_enviar() {
            var form = document.getElementById("loginForm");
            if (form.user.value == "") {
                alert("Usuario no ingresado");
                return;
            }
            if (form.contra.value == "") {
                alert("Contraseña no ingresada");
                return;
            }
            form.submit();
        }

        function abrirModalRecuperar() {
            document.getElementById("modalRecuperar").classList.add("activo");
            document.getElementById("msg-recuperar").textContent = "";
            document.getElementById("correo-recuperar").value = "";
        }

        function cerrarModalRecuperar() {
            document.getElementById("modalRecuperar").classList.remove("activo");
        }

        function enviarRecuperacion() {
            var correo = document.getElementById("correo-recuperar").value.trim();
            var msg = document.getElementById("msg-recuperar");

            if (correo === "") {
                msg.style.color = "red";
                msg.textContent = "Ingresa tu correo.";
                return;
            }

            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(correo)) {
                msg.style.color = "red";
                msg.textContent = "Correo no válido.";
                return;
            }

            msg.style.color = "#555";
            msg.textContent = "Enviando...";

            fetch("recuperar_contrasena.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "correo=" + encodeURIComponent(correo)
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    msg.style.color = "green";
                    msg.textContent = "¡Correo enviado! Revisa tu bandeja.";
                } else {
                    msg.style.color = "red";
                    msg.textContent = data.error || "No se pudo enviar el correo.";
                }
            })
            .catch(() => {
                msg.style.color = "red";
                msg.textContent = "Error de conexión.";
            });
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById("modalRecuperar").addEventListener("click", function(e) {
            if (e.target === this) cerrarModalRecuperar();
        });
    </script>

</body>

</html>
