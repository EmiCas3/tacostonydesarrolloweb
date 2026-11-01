<?php
require __DIR__ . '/config.php';
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
            // Consulta preparada por PDO (previene inyección SQL)
            $stmt = obtenerConexion()->prepare(
                "SELECT id, usuario, nombre, rol, activo, password_hash 
                 FROM " . TABLA_USUARIOS . " 
                 WHERE usuario = :usuario 
                 LIMIT 1"
            );
            $stmt->execute([':usuario' => $usuarioIngresado]);
            $fila = $stmt->fetch();

            // Verificación con password_verify() y verificación de cuenta activa
            if ($fila && (int)$fila['activo'] === 1 && password_verify($password, $fila['password_hash'])) {
                // Regenerar ID de sesión para prevenir Session Fixation
                session_regenerate_id(true);
                $_SESSION['id']            = $fila['id'];
                $_SESSION['usuario']       = $fila['usuario'];
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
            $error = 'Error de conexión con la base de datos. Intenta más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión · Tacos Tony Inventario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-badge">🌮 Tacos Tony Camino Real</div>
    <h1>Inventario Móvil</h1>
    <p class="sub">Acceso al sistema de control de existencias</p>

    <?php if ($mensajeInfo): ?>
        <div class="alert alert-info" role="status">ℹ️ <?= htmlspecialchars($mensajeInfo) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error" role="alert">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label for="usuario">Usuario del sistema</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username"
               placeholder="Ej. fer.leon_05"
               value="<?= htmlspecialchars($usuarioIngresado) ?>" autofocus>

        <label for="password">Contraseña</label>
        <div class="input-password-wrapper">
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   placeholder="Ingresa tu contraseña">
            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('password', this)" 
                    aria-label="Alternar visibilidad de contraseña" tabindex="-1">👁</button>
        </div>

        <button type="submit" class="btn">Ingresar al sistema</button>
    </form>
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
