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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/TTlogomini.png">
    <title>Bienvenida · Tacos Tony Inventario Móvil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="card">
    <div class="brand-logo-wrap">
        <img src="assets/img/Tacos_tony_logo.png" alt="Logo Tacos Tony" class="brand-logo-sm">
    </div>
    <span class="brand-badge">Sucursal Camino Real</span>

    <div class="avatar">
        <img src="assets/img/imagn-perfil.png" alt="Perfil de usuario">
    </div>
    <h1>¡Bienvenido, <?= $nombre ?>!</h1>
    <p class="sub">Sesión iniciada como <strong><?= $usuario ?></strong></p>

    <span class="badge-rol <?= $rol === 'GERENTE' ? 'badge-gerente' : 'badge-colaborador' ?>">
        <?= $rol === 'GERENTE' ? 'ROL: GERENTE' : 'ROL: COLABORADOR' ?>
    </span>

    <div class="alert alert-ok">
        <span>✔</span>
        <span>Login correcto. Credenciales validadas exitosamente.</span>
    </div>

    <!-- Acceso directo principal al MVP Mobile-First integrado -->
    <a class="btn" href="app.php" style="margin-top: 0.5rem;">
        <img src="assets/img/icon-dash.png" alt="Abrir MVP" class="btn-icon">
        <span>ABRIR INVENTARIO MÓVIL (MVP)</span>
    </a>

    <!-- Módulo de Permisos según Rol (RF-01) con iconos oficiales de Tacos Tony -->
    <div class="permisos-card">
        <h3>Atribuciones operativas (RF-01):</h3>
        <ul>
            <?php if ($rol === 'GERENTE'): ?>
                <li>
                    <img src="assets/img/icon-inv.png" alt="Inventario">
                    <span>Catálogo de insumos y recetas (RF-02, RF-03)</span>
                </li>
                <li>
                    <img src="assets/img/icon-mov.png" alt="Movimientos">
                    <span>Descuento automático por ventas (RF-04, RF-05)</span>
                </li>
                <li>
                    <img src="assets/img/icon-admin.png" alt="Compras y mermas">
                    <span>Registro de compras y mermas (RF-06, RF-07)</span>
                </li>
                <li>
                    <img src="assets/img/icon-dash.png" alt="Alertas y auditoría">
                    <span>Auditoría de movimientos y alertas (RF-09, RF-10)</span>
                </li>
            <?php else: ?>
                <li>
                    <img src="assets/img/icon-inv.png" alt="Existencias">
                    <span>Consulta de existencias móvil (RF-08)</span>
                </li>
                <li>
                    <img src="assets/img/icon-catalogo.png" alt="Catálogo">
                    <span>Consulta de alertas operativas</span>
                </li>
                <li>
                    <img src="assets/img/icon-warning.png" alt="Restringido">
                    <span>Altas, mermas y ajustes restringidos a Gerente</span>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <p class="timeout-note">⏱ Sesión protegida: expirará tras 30 min de inactividad (RNF-05).</p>

    <a class="btn btn-secondary" href="logout.php">
        <img src="assets/img/log-out.png" alt="Salir" class="btn-icon" style="filter: brightness(0) invert(1);">
        <span>CERRAR SESIÓN</span>
    </a>

    <div class="brand-footer">
        © <?= date('Y') ?> Tacos Tony — Inventario Móvil
    </div>
</main>
</body>
</html>
