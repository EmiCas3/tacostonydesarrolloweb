<?php
/**
 * Script de inicialización - Tacos Tony Inventario Móvil
 * (Usar UNA sola vez para crear tabla y usuario de prueba, luego BORRAR del servidor).
 */
require __DIR__ . '/config.php';

$mensaje = '';
$tipo = '';
$bloqueado = false;

try {
    $pdo = obtenerConexion();

    // 1. Crear tabla con soporte para roles RBAC (RF-01) y cuenta activa
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_USUARIOS . " (
        `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `usuario`       VARCHAR(50)   NOT NULL,
        `password_hash` VARCHAR(255)  NOT NULL,
        `nombre`        VARCHAR(100)  NOT NULL,
        `rol`           ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE',
        `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
        `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_usuario` (`usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Asegurar retrocompatibilidad si la tabla fue creada en una versión previa
    $columnasExistentes = $pdo->query("SHOW COLUMNS FROM " . TABLA_USUARIOS)->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('rol', $columnasExistentes, true)) {
        $pdo->exec("ALTER TABLE " . TABLA_USUARIOS . " ADD COLUMN `rol` ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE' AFTER `nombre`");
    }
    if (!in_array('activo', $columnasExistentes, true)) {
        $pdo->exec("ALTER TABLE " . TABLA_USUARIOS . " ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `rol`");
    }

    $total = (int) $pdo->query("SELECT COUNT(*) FROM " . TABLA_USUARIOS)->fetchColumn();
    
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
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

            // Insertar o actualizar si ya existía para permitir regenerar la prueba
            $stmt = $pdo->prepare("INSERT INTO " . TABLA_USUARIOS . " 
                (usuario, password_hash, nombre, rol, activo) 
                VALUES (:u, :h, :n, :r, 1)
                ON DUPLICATE KEY UPDATE 
                    password_hash = VALUES(password_hash),
                    nombre = VALUES(nombre),
                    rol = VALUES(rol),
                    activo = 1");
            
            $stmt->execute([
                ':u' => $usuario,
                ':h' => $hash,
                ':n' => $nombre,
                ':r' => $rol,
            ]);

            $bloqueado = true;
            $mensaje = "¡Usuario '$usuario' ($rol) configurado exitosamente con bcrypt! Por seguridad, ahora ELIMINA este archivo setup.php del servidor.";
            $tipo = 'ok';
        }
    } elseif ($total > 0) {
        $mensaje = "La tabla ya contiene $total usuario(s). Puedes crear otro usuario o sobrescribir ingresando los datos abajo:";
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
    <title>Setup · Tacos Tony Inventario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-badge">🌮 Tacos Tony Camino Real</div>
    <h1>Inicializar usuario</h1>
    <p class="sub">Tabla: <code>3478788_usuarios</code> (BD: <code>uzfjkpte_equipo4</code>)</p>

    <?php if ($mensaje): ?>
        <div class="alert <?= $tipo === 'ok' ? 'alert-ok' : ($tipo === 'info' ? 'alert-info' : 'alert-error') ?>">
            <?= htmlspecialchars($mensaje) ?>
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
            <option value="COLABORADOR">COLABORADOR (Solo consulta de existencias y pedidos)</option>
        </select>

        <label for="password">Contraseña (mínimo 8 caracteres)</label>
        <div class="input-password-wrapper">
            <input type="password" id="password" name="password" required minlength="8" value="1234567890">
            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('password', this)" aria-label="Mostrar contraseña">👁</button>
        </div>

        <button type="submit" class="btn">Guardar usuario de prueba</button>
    </form>

    <div style="margin-top: 1.5rem; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
        <a class="btn btn-secondary" href="index.php">Ir a la página de Login</a>
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
