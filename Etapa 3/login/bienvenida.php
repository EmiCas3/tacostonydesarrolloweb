<?php
require __DIR__ . '/config.php';
iniciarSesionSegura();
verificarExpiracionSesion();

// Control de acceso: requiere sesión autenticada activa
if (empty($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$nombre  = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario');
$usuario = htmlspecialchars($_SESSION['usuario'] ?? '');
$rol     = strtoupper($_SESSION['rol'] ?? 'GERENTE');
$inicial = strtoupper(mb_substr($nombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida · Tacos Tony Inventario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-badge">🌮 Tacos Tony Camino Real</div>

    <div class="avatar"><?= $inicial ?></div>
    <h1>¡Bienvenido, <?= $nombre ?>!</h1>
    <p class="sub">Sesión iniciada como <strong><?= $usuario ?></strong></p>

    <span class="badge-rol <?= $rol === 'GERENTE' ? 'badge-gerente' : 'badge-colaborador' ?>">
        <?= $rol === 'GERENTE' ? '⭐ ROL: GERENTE' : '👤 ROL: COLABORADOR' ?>
    </span>

    <div class="alert alert-ok">
        <span>✔</span>
        <span>Login correcto. Credenciales validadas exitosamente.</span>
    </div>

    <!-- Módulo de Permisos según Rol (RF-01) -->
    <div class="permisos-card">
        <h3>Atribuciones operativas (RF-01):</h3>
        <ul>
            <?php if ($rol === 'GERENTE'): ?>
                <li>✅ Catálogo de insumos y recetas (RF-02, RF-03)</li>
                <li>✅ Descuento automático por ventas (RF-04, RF-05)</li>
                <li>✅ Registro de compras y mermas (RF-06, RF-07)</li>
                <li>✅ Auditoría de movimientos y alertas (RF-09, RF-10)</li>
            <?php else: ?>
                <li>✅ Consulta de existencias móvil (RF-08)</li>
                <li>✅ Confirmación de recepción de pedidos</li>
                <li>🔒 Altas, mermas y ajustes restringidos a Gerente</li>
            <?php endif; ?>
        </ul>
    </div>

    <p class="timeout-note">⏱ Sesión protegida: expirará tras 30 min de inactividad (RNF-05).</p>

    <a class="btn" href="logout.php">Cerrar sesión</a>
</main>
</body>
</html>
