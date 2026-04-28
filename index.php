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
    </div>
    
    <script>
        function valida_enviar() {
            var form = document.getElementById("loginForm");
            if (form.user.value == "") {
                alert("Usuario no ingresado")
                return;
            }
            if (form.contra.value == "") {
                alert("Contraseña no ingresada")
                return;
            }
            // Enviamos al servidor PHP para que valide contra la BDD
            form.submit();
        }
    </script>


</body>

</html>