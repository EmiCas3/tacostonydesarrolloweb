<?php
/**
 * Script de inicialización del MVP - Tacos Tony Inventario Móvil (Etapa 3)
 * Inicializa las 7 tablas con prefijo 3478788_, siembra el catálogo oficial de Tacos Tony
 * y permite crear/actualizar usuarios con bcrypt costo 10 (RNF-05).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/init_mvp.php';

$mensaje = '';
$tipo = '';

try {
    $pdo = obtenerConexion();
    inicializarEsquemaMvp($pdo);

    $total = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_USUARIOS)->fetchColumn();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario  = trim($_POST['usuario'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $rol      = trim($_POST['rol'] ?? 'GERENTE');
        $password = $_POST['password'] ?? '';

        if (!in_array($rol, ['GERENTE', 'COLABORADOR'], true)) {
            $rol = 'GERENTE';
        }

        if ($usuario === '' || $nombre === '' || strlen($password) < 8) {
            $mensaje = 'Por favor completa todos los campos (la contraseña debe tener al menos 8 caracteres).';
            $tipo = 'error';
        } else {
            // RNF-05: Hashing con bcrypt factor de costo >= 10
            $hash   = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $correo = str_contains($usuario, '@') ? $usuario : ($usuario . '@tacostony.com');

            $stmt = $pdo->prepare("INSERT INTO " . TABLA_USUARIOS . " 
                (usuario, correo, password_hash, nombre, rol, activo) 
                VALUES (:u, :c, :h, :n, :r, 1)
                ON DUPLICATE KEY UPDATE 
                    correo = VALUES(correo),
                    password_hash = VALUES(password_hash),
                    nombre = VALUES(nombre),
                    rol = VALUES(rol),
                    activo = 1");

            $stmt->execute([
                ':u' => $usuario,
                ':c' => $correo,
                ':h' => $hash,
                ':n' => $nombre,
                ':r' => $rol,
            ]);

            $mensaje = "¡Usuario '$usuario' ($rol) y las 7 tablas del MVP configurados exitosamente con bcrypt!";
            $tipo = 'ok';
        }
    } elseif ($total > 0) {
        $mensaje = "Esquema MVP listo (7 tablas 3478788_* y catálogo Tacos Tony). Usuarios activos: $total (incluye fer.leon_05 [GERENTE] y colab.tony [COLABORADOR]).";
        $tipo = 'info';
    }
} catch (PDOException $e) {
    $mensaje = 'Error de conexión con la base de datos: ' . $e->getMessage();
    $tipo = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/TTlogomini.png">
    <title>Setup MVP · Tacos Tony Inventario Móvil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-logo-wrap">
        <img src="assets/img/Tacos_tony_logo.png" alt="Logo Tacos Tony" class="brand-logo-sm">
    </div>
    <span class="brand-badge">Sucursal Camino Real</span>
    <h1>Inicializar MVP (Etapa 3)</h1>
    <p class="sub">Prefijo: <code><?= htmlspecialchars(DB_PREFIX) ?>*</code> (BD: <code><?= htmlspecialchars(DB_NAME) ?></code>)</p>

    <?php if ($mensaje): ?>
        <div class="alert <?= $tipo === 'ok' ? 'alert-ok' : ($tipo === 'info' ? 'alert-info' : 'alert-error') ?>">
            <?php if ($tipo === 'error'): ?>
                <img src="assets/img/icon-warning.png" alt="Alerta" class="alert-icon">
            <?php endif; ?>
            <span><?= htmlspecialchars($mensaje) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <label for="usuario">Usuario del sistema</label>
        <input type="text" id="usuario" name="usuario" required maxlength="50" value="fer.leon_05">

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" value="Fernando León">

        <label for="rol">Rol en el sistema (RF-01)</label>
        <select id="rol" name="rol" required>
            <option value="GERENTE" selected>GERENTE (Acceso total: inventario, mermas, ventas)</option>
            <option value="COLABORADOR">COLABORADOR (Solo consulta de existencias y alertas)</option>
        </select>

        <label for="password">Contraseña (mínimo 8 caracteres)</label>
        <div class="input-password-wrapper">
            <input type="password" id="password" name="password" required minlength="8" value="1234567890">
            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('password', this)" aria-label="Mostrar contraseña">👁</button>
        </div>

        <button type="submit" class="btn">Guardar usuario y verificar tablas</button>
    </form>

    <div style="margin-top: 0.75rem;">
        <a class="btn btn-secondary" href="index.php">Ir a la página de Login</a>
    </div>

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
