<?php
require __DIR__ . '/config.php';
require __DIR__ . '/init_mvp.php';
iniciarSesionSegura();
verificarExpiracionSesion();

// Si ya hay sesión activa válida, redirigir a bienvenida
if (!empty($_SESSION['usuario'])) {
    header('Location: bienvenida.php');
    exit;
}

// Token de protección contra CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$mensajeInfo = '';
$usuarioIngresado = '';

// Mensajes según redirección de sesión
$motivo = $_GET['motivo'] ?? '';
if ($motivo === 'expirado') {
    $mensajeInfo = 'Tu sesión ha expirado tras 30 minutos de inactividad (RNF-05). Inicia sesión nuevamente.';
} elseif ($motivo === 'logout') {
    $mensajeInfo = 'Has cerrado sesión exitosamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioIngresado = trim($_POST['usuario'] ?? '');
    $password         = $_POST['password'] ?? '';
    $csrf             = $_POST['csrf'] ?? '';

    // Validación CSRF y campos no vacíos
    if (!hash_equals($_SESSION['csrf'], $csrf) || $usuarioIngresado === '' || $password === '') {
        $error = 'login incorrecto';
    } else {
        try {
            $pdo = obtenerConexion();
            inicializarEsquemaMvp($pdo);

            // Consulta preparada por PDO (soporta usuario o correo electrónico, RF-01)
            $stmt = $pdo->prepare(
                "SELECT id, usuario, correo, nombre, rol, activo, password_hash 
                 FROM " . TABLA_USUARIOS . " 
                 WHERE usuario = :u OR correo = :c 
                 LIMIT 1"
            );
            $stmt->execute([
                ':u' => $usuarioIngresado,
                ':c' => $usuarioIngresado,
            ]);
            $fila = $stmt->fetch();

            // Verificación con password_verify() y verificación de cuenta activa
            if ($fila && (int)$fila['activo'] === 1 && password_verify($password, $fila['password_hash'])) {
                // Regenerar ID de sesión para prevenir Session Fixation
                session_regenerate_id(true);
                $_SESSION['id']            = $fila['id'];
                $_SESSION['usuario']       = $fila['usuario'];
                $_SESSION['correo']        = $fila['correo'] ?? ($fila['usuario'] . '@tacostony.com');
                $_SESSION['nombre']        = $fila['nombre'];
                $_SESSION['rol']           = $fila['rol'] ?? 'GERENTE';
                $_SESSION['ultimo_acceso'] = time(); // Marca de tiempo para RNF-05 (30 min)

                unset($_SESSION['csrf']);
                header('Location: bienvenida.php');
                exit;
            }

            // Mensaje estándar exigido por las especificaciones de entrega
            $error = 'login incorrecto';
        } catch (PDOException $e) {
            error_log('Error Login 3478788: ' . $e->getMessage());
            $error = 'Error de conexión con la base de datos. Verifica tu configuración en el archivo .env.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/TTlogomini.png">
    <title>Login - Tacos Tony Inventario Móvil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-logo-wrap">
        <img src="assets/img/Tacos_tony_logo.png" alt="Logo Tacos Tony" class="brand-logo">
    </div>
    <span class="brand-badge">Sucursal Camino Real</span>
    <h1>Sistema de control de inventario</h1>
    <p class="sub">Acceso móvil al control de existencias y recetas</p>

    <?php if ($mensajeInfo): ?>
        <div class="alert alert-info" role="status">
            <span>ℹ️</span>
            <span><?= htmlspecialchars($mensajeInfo) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error" role="alert">
            <img src="assets/img/icon-warning.png" alt="Alerta" class="alert-icon">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label for="usuario">Usuario o correo electrónico</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username"
               placeholder="Ej. fer.leon_05 o colab.tony"
               value="<?= htmlspecialchars($usuarioIngresado) ?>" autofocus>

        <label for="password">Contraseña</label>
        <div class="input-password-wrapper">
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   placeholder="Ingresa tu contraseña">
            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('password', this)"
                    aria-label="Alternar visibilidad de contraseña" tabindex="-1">👁</button>
        </div>

        <button type="submit" class="btn">INGRESAR</button>
    </form>

    <div class="brand-footer">
        © <?= date('Y') ?> Tacos Tony — Inventario Móvil
    </div>
</main>

<script>
function togglePassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}
</script>
</body>
</html>
