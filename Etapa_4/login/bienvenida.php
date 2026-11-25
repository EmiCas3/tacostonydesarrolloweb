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

    <!-- Acceso directo principal a la versión final Mobile-First -->
    <a class="btn" href="app.php" style="margin-top: 0.5rem;">
        <img src="assets/img/icon-dash.png" alt="Abrir Inventario" class="btn-icon">
        <span>ABRIR INVENTARIO MÓVIL (v2.0)</span>
    </a>

    <!-- Módulo de Permisos según Rol (RF-01) con iconos oficiales de Tacos Tony -->
    <div class="permisos-card">
        <h3>Atribuciones operativas (RF-01):</h3>
        <ul>
            <?php if ($rol === 'GERENTE'): ?>
                <li>
                    <img src="assets/img/icon-inv.png" alt="Inventario">
                    <span>Catálogo de insumos, recetas y conteo físico (RF-02, RF-03, RF-17)</span>
                </li>
                <li>
                    <img src="assets/img/icon-mov.png" alt="Movimientos">
                    <span>Ventas manuales / CSV y descuento automático (RF-04, RF-05, RF-14)</span>
                </li>
                <li>
                    <img src="assets/img/icon-admin.png" alt="Compras y pedidos">
                    <span>Entradas, mermas, reabastecimiento y pedidos (RF-06, RF-07, RF-11..13)</span>
                </li>
                <li>
                    <img src="assets/img/icon-dash.png" alt="Alertas y auditoría">
                    <span>Alertas, auditoría inmutable, reportes y CSV (RF-09, RF-10, RF-15, RF-16)</span>
                </li>
            <?php else: ?>
                <li>
                    <img src="assets/img/icon-inv.png" alt="Existencias">
                    <span>Consulta de existencias móvil (RF-08)</span>
                </li>
                <li>
                    <img src="assets/img/icon-catalogo.png" alt="Conteo físico">
                    <span>Captura de conteo físico en borrador (RF-17)</span>
                </li>
                <li>
                    <img src="assets/img/icon-admin.png" alt="Recepción de pedidos">
                    <span>Confirmación de recepción de pedidos (RF-13)</span>
                </li>
                <li>
                    <img src="assets/img/icon-warning.png" alt="Restringido">
                    <span>Aprobación de ajustes, altas, ventas, mermas y reportes restringidos a Gerente</span>
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
