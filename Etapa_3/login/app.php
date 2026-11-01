<?php
/**
 * Aplicación Principal MVP Mobile-First — Tacos Tony Inventario Móvil (Etapa 3)
 * Alumno: Fernando León Del Río (ID: 3478788)
 * Integra los 3 módulos del MVP conectados de punta a punta contra la API REST (/api/index.php)
 * usando token JWT (ADR-01, ADR-03, ADR-04, RF-01 a RF-10, RNF-01 a RNF-06).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/init_mvp.php';

iniciarSesionSegura();
verificarExpiracionSesion();

if (empty($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = obtenerConexion();
    inicializarEsquemaMvp($pdo);
} catch (PDOException $e) {
    error_log('Error inicializando MVP: ' . $e->getMessage());
}

$usuarioActual = [
    'id'      => (int)($_SESSION['id'] ?? 1),
    'usuario' => $_SESSION['usuario'] ?? '',
    'correo'  => $_SESSION['correo'] ?? ($_SESSION['usuario'] . '@tacostony.com'),
    'nombre'  => $_SESSION['nombre'] ?? 'Usuario',
    'rol'     => strtoupper($_SESSION['rol'] ?? 'GERENTE'),
];

$tokenJwt = generarJwt($usuarioActual);
$esGerente = ($usuarioActual['rol'] === 'GERENTE');
// CA-01.1: Si es COLABORADOR, su vista principal operativa es Existencias
$vistaInicial = $_GET['vista'] ?? ($esGerente ? 'alertas' : 'existencias');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/TTlogomini.png">
    <title>Inventario Móvil · Tacos Tony Camino Real</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="app-body">

<!-- Cabecera Institucional Fija con Logo Real y Estado de Sesión -->
<header class="app-header">
    <div class="app-header-inner">
        <div class="app-header-brand">
            <img src="assets/img/Tacos_tony_logo.png" alt="Tacos Tony" class="app-header-logo">
            <div class="app-header-titles">
                <h2>Tacos Tony · Camino Real</h2>
                <p><?= htmlspecialchars($usuarioActual['nombre']) ?> (<?= htmlspecialchars($usuarioActual['rol']) ?>)</p>
            </div>
        </div>
        <div class="app-header-actions">
            <a href="bienvenida.php" class="btn-header-logout" style="background-color: var(--color-primary); color: #000;" title="Perfil y permisos">
                <span>Perfil</span>
            </a>
            <a href="logout.php" class="btn-header-logout" title="Cerrar sesión">
                <img src="assets/img/log-out.png" alt="Salir">
                <span>Salir</span>
            </a>
        </div>
    </div>
</header>

<main class="app-container">
    <!-- Banner global de notificaciones operativas -->
    <div id="globalAlert" style="display: none;"></div>

    <!-- ==================================================================
         VISTA 1: DASHBOARD Y ALERTAS DE STOCK BAJO / DISCREPANCIAS (RF-09)
         ================================================================== -->
    <section id="view-alertas" class="view-section <?= $vistaInicial === 'alertas' ? 'active' : '' ?>">
        <div class="kpi-grid">
            <div class="kpi-box">
                <div class="kpi-num" id="kpiTotalInsumos">0</div>
                <div class="kpi-label">Insumos activos</div>
            </div>
            <div class="kpi-box kpi-bajo">
                <div class="kpi-num" id="kpiTotalBajo">0</div>
                <div class="kpi-label">En stock bajo</div>
            </div>
            <div class="kpi-box kpi-disc">
                <div class="kpi-num" id="kpiTotalDisc">0</div>
                <div class="kpi-label">Discrepancias</div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-warning.png" alt="Alertas">
                    <span>Alertas de Stock Bajo y Discrepancias (RF-09)</span>
                </h3>
            </div>
            <p class="sub" style="margin-bottom: 0.9rem;">
                Insumos con existencias menores o iguales a su mínimo (<code>stock_actual &le; stock_minimo</code>) o con saldo negativo por ventas (<code>discrepancia</code>).
            </p>
            <div id="listaAlertasDashboard" class="insumos-list">
                <p class="sub">Cargando alertas del inventario...</p>
            </div>
        </div>
    </section>

    <!-- ==================================================================
         VISTA 2: EXISTENCIAS MÓVILES, FILTROS Y CATÁLOGO DE INSUMOS (RF-02, RF-08)
         ================================================================== -->
    <section id="view-existencias" class="view-section <?= $vistaInicial === 'existencias' ? 'active' : '' ?>">
        <div class="section-card">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-inv.png" alt="Existencias">
                    <span>Consulta de Existencias (RF-08)</span>
                </h3>
                <?php if ($esGerente): ?>
                    <button type="button" class="pill-btn active" onclick="toggleFormNuevoInsumo()">
                        + Alta Insumo
                    </button>
                <?php endif; ?>
            </div>

            <!-- Formulario colapsable de Alta de Insumo (RF-02, exclusivo Gerente) -->
            <?php if ($esGerente): ?>
                <div id="boxNuevoInsumo" style="display: none; background: #fffaf2; border: 2px solid var(--color-primary); border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                    <h4 style="color: var(--color-secondary); margin-bottom: 0.5rem;">Dar de Alta Nuevo Insumo (RF-02)</h4>
                    <form id="formNuevoInsumo" onsubmit="guardarNuevoInsumo(event)">
                        <label for="insNombre">Nombre del insumo</label>
                        <input type="text" id="insNombre" required placeholder="Ej. Cilantro Fresco">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                            <div>
                                <label for="insUnidad">Unidad de medida</label>
                                <select id="insUnidad" required>
                                    <option value="kg">kg (Kilogramos)</option>
                                    <option value="g">g (Gramos)</option>
                                    <option value="L">L (Litros)</option>
                                    <option value="ml">ml (Mililitros)</option>
                                    <option value="pieza">pieza (Unidades)</option>
                                </select>
                            </div>
                            <div>
                                <label for="insCategoria">Categoría</label>
                                <select id="insCategoria" required>
                                    <option value="Carnes">Carnes</option>
                                    <option value="Tortillas">Tortillas</option>
                                    <option value="Verduras">Verduras</option>
                                    <option value="Salsas">Salsas</option>
                                    <option value="Bebidas">Bebidas</option>
                                    <option value="Desechables">Desechables</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                            <div>
                                <label for="insStockInicial">Stock inicial (DECIMAL 10,3)</label>
                                <input type="number" id="insStockInicial" step="0.001" min="0" required placeholder="Ej. 2.000">
                            </div>
                            <div>
                                <label for="insStockMinimo">Stock mínimo (alerta)</label>
                                <input type="number" id="insStockMinimo" step="0.001" min="0" required placeholder="Ej. 0.500">
                            </div>
                        </div>

                        <button type="submit" class="btn">Guardar Insumo (RF-02)</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Buscador instantáneo (< 300ms CA-08.1) -->
            <label for="busquedaInsumo" style="margin-top: 0;">Buscar materia prima</label>
            <input type="search" id="busquedaInsumo" placeholder="Escribe ej. carne, pan, cebolla, jocoque..." oninput="filtrarInsumosEnTiempoReal()">

            <!-- Píldoras de filtro por categoría -->
            <div class="filter-pills" id="pillsCategoria">
                <button type="button" class="pill-btn active" onclick="seleccionarCategoria('Todas', this)">Todas</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Carnes', this)">Carnes</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Tortillas', this)">Tortillas/Pan</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Verduras', this)">Verduras</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Salsas', this)">Salsas/Jocoque</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Bebidas', this)">Bebidas</button>
                <button type="button" class="pill-btn" onclick="seleccionarCategoria('Desechables', this)">Desechables</button>
            </div>

            <!-- Filtro rápido por estado -->
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.85rem;">
                <select id="filtroEstadoInsumo" onchange="filtrarInsumosEnTiempoReal()" aria-label="Filtrar por estado">
                    <option value="todos">Estado: Todos los insumos activos</option>
                    <option value="bajo">Solo en Stock Bajo (&le; mínimo)</option>
                    <option value="discrepancia">Solo con Discrepancia (&lt; 0)</option>
                    <option value="normal">Solo en Estado Normal</option>
                </select>
            </div>

            <div id="listaExistencias" class="insumos-list">
                <p class="sub">Cargando catálogo de existencias...</p>
            </div>
        </div>
    </section>

    <?php if ($esGerente): ?>
    <!-- ==================================================================
         VISTA 3: CAPTURA DE VENTAS Y CONFIGURACIÓN DE RECETAS (RF-03, RF-04, RF-05)
         ================================================================== -->
    <section id="view-ventas" class="view-section <?= $vistaInicial === 'ventas' ? 'active' : '' ?>">
        <div class="section-card">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-mov.png" alt="Ventas">
                    <span>Captura de Venta y Descuento por Receta (RF-04, RF-05)</span>
                </h3>
                <button type="button" class="pill-btn" onclick="toggleConfiguradorRecetas()">
                    ⚙️ Recetas (RF-03)
                </button>
            </div>
            <p class="sub" style="margin-bottom: 0.85rem;">
                Selecciona las cantidades vendidas. El sistema descontará automáticamente cada insumo en una transacción ACID (<code>DECIMAL(10,3)</code>).
            </p>

            <div id="gridProductosVenta" class="productos-grid">
                <p class="sub">Cargando menú de Tacos Tony...</p>
            </div>

            <!-- Resumen en vivo de la venta por confirmar -->
            <div id="resumenVentaBox" style="margin-top: 1rem; padding: 1rem; background: #fffaf2; border: 2px solid var(--color-primary); border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: var(--color-secondary);">Total de la Venta:</strong>
                    <span id="totalVentaMonto" style="font-size: 1.35rem; font-weight: 900; color: var(--color-text);">$0.00</span>
                </div>
                <div id="previewDescuentoReceta" style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.45rem;">
                    Selecciona al menos un producto del menú usando los botones <strong>+</strong>.
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                    <button type="button" class="btn" style="margin-top: 0;" onclick="confirmarVenta()">
                        Confirmar Venta y Descontar Stock
                    </button>
                    <button type="button" class="btn btn-outline" style="margin-top: 0; width: auto;" onclick="limpiarCarritoVenta()">
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel de Configuración de Recetas y Productos (RF-03) -->
        <div id="panelRecetas" class="section-card section-card-blue" style="display: none;">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-catalogo.png" alt="Recetas">
                    <span>Configuración de Recetas por Producto (RF-03)</span>
                </h3>
            </div>
            <p class="sub" style="margin-bottom: 0.8rem;">
                Asigna las cantidades exactas en <code>DECIMAL(10,3)</code> (ej. <code>0.080 kg</code> o <code>0.003 kg</code>) que consume cada unidad vendida.
            </p>

            <label for="selectProductoReceta">1. Selecciona el producto del menú</label>
            <select id="selectProductoReceta" onchange="cargarRecetaDeProductoSeleccionado()"></select>

            <div id="lineasRecetaEditor" style="margin-top: 0.85rem; display: flex; flex-direction: column; gap: 0.55rem;"></div>

            <button type="button" class="btn btn-outline" onclick="agregarLineaIngredienteReceta()">
                + Agregar Ingrediente a la Receta
            </button>
            <button type="button" class="btn" onclick="guardarRecetaProducto()">
                Guardar Receta del Producto (RF-03)
            </button>

            <hr style="margin: 1.25rem 0; border: none; border-top: 1px solid var(--color-border);">
            <h4 style="color: var(--color-secondary); margin-bottom: 0.4rem;">Dar de alta nuevo producto en el menú</h4>
            <form id="formNuevoProducto" onsubmit="crearNuevoProductoMenu(event)">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.5rem;">
                    <div>
                        <label for="nuevoProdNombre">Nombre del producto</label>
                        <input type="text" id="nuevoProdNombre" required placeholder="Ej. Taco de Bistec">
                    </div>
                    <div>
                        <label for="nuevoProdPrecio">Precio ($)</label>
                        <input type="number" id="nuevoProdPrecio" step="0.50" min="1" required placeholder="25.00">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary">Crear Producto</button>
            </form>
        </div>
    </section>

    <!-- ==================================================================
         VISTA 4: ENTRADAS POR COMPRA Y MERMAS CON MOTIVO (RF-06, RF-07)
         ================================================================== -->
    <section id="view-operaciones" class="view-section <?= $vistaInicial === 'operaciones' ? 'active' : '' ?>">
        <!-- Registro ágil de Merma en <= 4 toques (RF-07 / RNF-04) -->
        <div class="section-card">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-warning.png" alt="Mermas">
                    <span>Registrar Merma de Insumo (RF-07)</span>
                </h3>
            </div>
            <p class="sub" style="margin-bottom: 0.75rem;">
                Descuenta desperdicios o bajas operativas registrando el motivo obligatorio para auditoría.
            </p>
            <form id="formMerma" onsubmit="registrarMerma(event)">
                <label for="mermaInsumoId">1. Insumo afectado</label>
                <select id="mermaInsumoId" required></select>

                <label for="mermaMotivo">2. Motivo de la merma (obligatorio)</label>
                <select id="mermaMotivo" required>
                    <option value="caducidad">caducidad — Producto caducado o vencido</option>
                    <option value="dano">dano — Daño físico o contaminación</option>
                    <option value="preparacion">preparacion — Error en cocina / plancha</option>
                    <option value="otro">otro — Otra causa operativa</option>
                </select>

                <label for="mermaCantidad">3. Cantidad a descontar (DECIMAL 10,3)</label>
                <input type="number" id="mermaCantidad" step="0.001" min="0.001" required placeholder="Ej. 1.250">

                <label for="mermaObs">Observaciones (opcional)</label>
                <input type="text" id="mermaObs" placeholder="Ej. Carne no consumida al cierre de turno">

                <button type="submit" class="btn">Confirmar Merma (RF-07)</button>
            </form>
        </div>

        <!-- Registro de Entrada por Compra (RF-06) -->
        <div class="section-card section-card-blue">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-admin.png" alt="Entradas">
                    <span>Registrar Entrada por Compra (RF-06)</span>
                </h3>
            </div>
            <p class="sub" style="margin-bottom: 0.75rem;">
                Suma mercancía recibida de proveedores al stock actual con precisión de báscula (<code>DECIMAL(10,3)</code>).
            </p>
            <form id="formEntrada" onsubmit="registrarEntradaCompra(event)">
                <label for="entradaInsumoId">Insumo recibido</label>
                <select id="entradaInsumoId" required></select>

                <label for="entradaCantidad">Cantidad que ingresa (ej. 15.375)</label>
                <input type="number" id="entradaCantidad" step="0.001" min="0.001" required placeholder="Ej. 20.000">

                <label for="entradaObs">Proveedor / Nota de entrada</label>
                <input type="text" id="entradaObs" placeholder="Ej. Reabastecimiento Carnes del Centro">

                <button type="submit" class="btn btn-secondary">Registrar Entrada al Inventario (RF-06)</button>
            </form>
        </div>
    </section>

    <!-- ==================================================================
         VISTA 5: HISTORIAL INMUTABLE DE MOVIMIENTOS (RF-10, RNF-06)
         ================================================================== -->
    <section id="view-historial" class="view-section <?= $vistaInicial === 'historial' ? 'active' : '' ?>">
        <div class="section-card">
            <div class="section-title">
                <h3>
                    <img src="assets/img/icon-repo.png" alt="Historial">
                    <span>Historial Inmutable de Movimientos (RF-10)</span>
                </h3>
                <button type="button" class="pill-btn" onclick="probarInmutabilidadHistorial()" title="Envía DELETE a /api/movimientos para comprobar rechazo 405">
                    🔒 Probar Inmutabilidad (CA-10.2)
                </button>
            </div>
            <p class="sub" style="margin-bottom: 0.75rem;">
                Bitácora de solo inserción (<code>RNF-06</code>). Cada movimiento conserva autor, fecha, stock previo y stock resultante.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.55rem; margin-bottom: 0.65rem;">
                <div>
                    <label for="histTipo" style="margin-top: 0;">Tipo de movimiento</label>
                    <select id="histTipo" onchange="cargarHistorialMovimientos()">
                        <option value="">Todos (VENTA, ENTRADA, MERMA)</option>
                        <option value="VENTA">VENTA</option>
                        <option value="ENTRADA">ENTRADA</option>
                        <option value="MERMA">MERMA</option>
                    </select>
                </div>
                <div>
                    <label for="histInsumo" style="margin-top: 0;">Filtrar por insumo</label>
                    <select id="histInsumo" onchange="cargarHistorialMovimientos()">
                        <option value="0">Todos los insumos</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.55rem; margin-bottom: 0.85rem;">
                <div>
                    <label for="histFechaIni" style="margin-top: 0;">Desde fecha</label>
                    <input type="date" id="histFechaIni" onchange="cargarHistorialMovimientos()">
                </div>
                <div>
                    <label for="histFechaFin" style="margin-top: 0;">Hasta fecha</label>
                    <input type="date" id="histFechaFin" onchange="cargarHistorialMovimientos()">
                </div>
            </div>

            <div id="listaMovimientos" class="insumos-list">
                <p class="sub">Cargando bitácora de auditoría...</p>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<!-- Barra de Navegación Inferior Fija (Bottom Navigation Bar — ADR-03 / RNF-04) -->
<nav class="bottom-nav" aria-label="Navegación principal móvil">
    <div class="bottom-nav-inner">
        <button type="button" class="nav-tab <?= $vistaInicial === 'alertas' ? 'active' : '' ?>" data-target="alertas" onclick="cambiarVista('alertas', this)">
            <img src="assets/img/icon-dash.png" alt="Alertas">
            <span>Alertas</span>
        </button>
        <button type="button" class="nav-tab <?= $vistaInicial === 'existencias' ? 'active' : '' ?>" data-target="existencias" onclick="cambiarVista('existencias', this)">
            <img src="assets/img/icon-inv.png" alt="Existencias">
            <span>Existencias</span>
        </button>
        <?php if ($esGerente): ?>
            <button type="button" class="nav-tab <?= $vistaInicial === 'ventas' ? 'active' : '' ?>" data-target="ventas" onclick="cambiarVista('ventas', this)">
                <img src="assets/img/icon-mov.png" alt="Ventas">
                <span>Ventas</span>
            </button>
            <button type="button" class="nav-tab <?= $vistaInicial === 'operaciones' ? 'active' : '' ?>" data-target="operaciones" onclick="cambiarVista('operaciones', this)">
                <img src="assets/img/icon-admin.png" alt="Entradas y Mermas">
                <span>Entradas/Merma</span>
            </button>
            <button type="button" class="nav-tab <?= $vistaInicial === 'historial' ? 'active' : '' ?>" data-target="historial" onclick="cambiarVista('historial', this)">
                <img src="assets/img/icon-repo.png" alt="Historial">
                <span>Auditoría</span>
            </button>
        <?php endif; ?>
    </div>
</nav>

<script>
const JWT_TOKEN = <?= json_encode($tokenJwt) ?>;
const ROL_USUARIO = <?= json_encode($usuarioActual['rol']) ?>;
const ES_GERENTE = (ROL_USUARIO === 'GERENTE');

let catalogoInsumos = [];
let catalogoProductos = [];
let categoriaSeleccionada = 'Todas';
let carritoCantidades = {}; // { producto_id: cantidad }

/**
 * Cliente HTTP unificado hacia la API REST (/api/index.php?ruta=...) con cabecera JWT Bearer
 */
async function llamarApi(ruta, opciones = {}) {
    const metodo = opciones.method || 'GET';
    const separador = ruta.includes('?') ? '&' : '?';
    const url = 'api/index.php?ruta=' + encodeURIComponent(ruta.split('?')[0]) +
                (ruta.includes('?') ? '&' + ruta.split('?')[1] : '');

    const headers = {
        'Authorization': 'Bearer ' + JWT_TOKEN,
        'Content-Type': 'application/json'
    };

    const config = {
        method: metodo,
        headers: headers
    };

    if (opciones.body) {
        config.body = JSON.stringify(opciones.body);
    }

    const resp = await fetch(url, config);
    let data = null;
    try {
        data = await resp.json();
    } catch (e) {
        data = { error: 'Respuesta inesperada del servidor' };
    }

    if (resp.status === 401) {
        window.location.href = 'index.php?motivo=expirado';
        throw new Error(data.error || 'Sesión expirada');
    }

    return { ok: resp.ok, status: resp.status, data: data };
}

function mostrarAvisoGlobal(mensaje, tipo = 'ok') {
    const box = document.getElementById('globalAlert');
    const clase = tipo === 'error' ? 'alert-error' : (tipo === 'warning' ? 'alert-warning' : 'alert-ok');
    box.className = 'alert ' + clase;
    box.innerHTML = '<span>' + escaparHtml(mensaje) + '</span>';
    box.style.display = 'flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => {
        box.style.display = 'none';
    }, 6500);
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

function formatearDecimal(num, decimales = 3) {
    return Number(num || 0).toFixed(decimales);
}

function cambiarVista(nombreVista, btnElement) {
    document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
    const target = document.getElementById('view-' + nombreVista);
    if (target) {
        target.classList.add('active');
    }
    document.querySelectorAll('.nav-tab').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-target') === nombreVista);
    });
    if (nombreVista === 'historial' && ES_GERENTE) {
        cargarHistorialMovimientos();
    }
}

/**
 * Carga el catálogo completo de insumos y actualiza Dashboard, Existencias y Selectores
 */
async function cargarInsumos() {
    const res = await llamarApi('/insumos');
    if (!res.ok || !Array.isArray(res.data)) {
        return;
    }
    catalogoInsumos = res.data;
    renderizarDashboardAlertas();
    filtrarInsumosEnTiempoReal();
    poblarSelectoresInsumos();
}

function renderizarDashboardAlertas() {
    const total = catalogoInsumos.length;
    const bajos = catalogoInsumos.filter(i => i.estado === 'bajo');
    const discrepancias = catalogoInsumos.filter(i => i.estado === 'discrepancia');

    document.getElementById('kpiTotalInsumos').textContent = total;
    document.getElementById('kpiTotalBajo').textContent = bajos.length;
    document.getElementById('kpiTotalDisc').textContent = discrepancias.length;

    const contenedor = document.getElementById('listaAlertasDashboard');
    const alertas = [...discrepancias, ...bajos];

    if (alertas.length === 0) {
        contenedor.innerHTML = `
            <div class="alert alert-ok" style="margin-bottom:0;">
                <span>✔</span>
                <span>Todos los insumos activos se encuentran por encima de su stock mínimo.</span>
            </div>`;
        return;
    }

    contenedor.innerHTML = alertas.map(ins => {
        const esDisc = ins.estado === 'discrepancia';
        const tituloAlerta = esDisc
            ? `⚠️ Discrepancia negativa: ${escaparHtml(ins.nombre)} (${formatearDecimal(ins.stock_actual)} ${escaparHtml(ins.unidad_medida)})`
            : `Stock bajo: ${escaparHtml(ins.nombre)} (${formatearDecimal(ins.stock_actual)} ${escaparHtml(ins.unidad_medida)} restantes)`;

        const botonesAccion = ES_GERENTE ? `
            <div class="insumo-actions">
                <button type="button" class="btn btn-sm-touch" onclick="irAReabastecerInsumo(${ins.id})">
                    + Registrar Entrada (RF-06)
                </button>
                <button type="button" class="btn btn-outline btn-sm-touch" onclick="irAMermaRapida(${ins.id})">
                    Registrar Merma
                </button>
            </div>` : '';

        return `
            <article class="insumo-card estado-${ins.estado}">
                <div class="insumo-top">
                    <div>
                        <div class="insumo-nombre">${tituloAlerta}</div>
                        <div class="insumo-meta">
                            Categoría: ${escaparHtml(ins.categoria)} · Mínimo requerido: ${formatearDecimal(ins.stock_minimo)} ${escaparHtml(ins.unidad_medida)}
                        </div>
                    </div>
                    <span class="badge-estado ${ins.estado}">${ins.estado}</span>
                </div>
                ${botonesAccion}
            </article>`;
    }).join('');
}

function seleccionarCategoria(cat, btn) {
    categoriaSeleccionada = cat;
    document.querySelectorAll('#pillsCategoria .pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filtrarInsumosEnTiempoReal();
}

function limpiarFiltrosExistencias() {
    document.getElementById('busquedaInsumo').value = '';
    document.getElementById('filtroEstadoInsumo').value = 'todos';
    const primerPill = document.querySelector('#pillsCategoria .pill-btn');
    seleccionarCategoria('Todas', primerPill);
}

/**
 * Filtra en tiempo real (< 300ms CA-08.1) por texto, categoría y estado
 */
function filtrarInsumosEnTiempoReal() {
    const texto = (document.getElementById('busquedaInsumo')?.value || '').trim().toLowerCase();
    const estadoFiltro = document.getElementById('filtroEstadoInsumo')?.value || 'todos';
    const contenedor = document.getElementById('listaExistencias');

    const filtrados = catalogoInsumos.filter(ins => {
        const coincideTexto = texto === '' || ins.nombre.toLowerCase().includes(texto);
        const coincideCat = categoriaSeleccionada === 'Todas' || ins.categoria === categoriaSeleccionada;
        const coincideEstado = estadoFiltro === 'todos' || ins.estado === estadoFiltro;
        return coincideTexto && coincideCat && coincideEstado;
    });

    // CA-08.2: Estado vacío accesible cuando ningún insumo coincide
    if (filtrados.length === 0) {
        contenedor.innerHTML = `
            <div style="text-align: center; padding: 1.5rem 1rem; background: #f8fafc; border-radius: 10px; border: 1.5px dashed var(--color-border);">
                <p style="font-weight: 800; color: var(--color-secondary); margin-bottom: 0.4rem;">
                    No se encontraron insumos que coincidan con la búsqueda
                </p>
                <p class="sub" style="margin-bottom: 0.85rem;">
                    Intenta con otro término o restablece los filtros activos.
                </p>
                <button type="button" class="btn btn-secondary" style="margin-top: 0;" onclick="limpiarFiltrosExistencias()">
                    Limpiar filtros
                </button>
            </div>`;
        return;
    }

    contenedor.innerHTML = filtrados.map(ins => {
        const accionesGerente = ES_GERENTE ? `
            <div class="insumo-actions">
                <button type="button" class="btn btn-sm-touch" onclick="irAReabastecerInsumo(${ins.id})">
                    + Entrada
                </button>
                <button type="button" class="btn btn-outline btn-sm-touch" onclick="irAMermaRapida(${ins.id})">
                    - Merma
                </button>
                <button type="button" class="btn btn-danger btn-sm-touch" onclick="desactivarInsumo(${ins.id}, '${escaparHtml(ins.nombre)}')">
                    Desactivar
                </button>
            </div>` : '';

        return `
            <article class="insumo-card estado-${ins.estado}">
                <div class="insumo-top">
                    <div>
                        <div class="insumo-nombre">${escaparHtml(ins.nombre)}</div>
                        <div class="insumo-meta">
                            ${escaparHtml(ins.categoria)} · Mínimo: ${formatearDecimal(ins.stock_minimo)} ${escaparHtml(ins.unidad_medida)}
                        </div>
                    </div>
                    <div class="insumo-stock-wrap">
                        <div class="insumo-stock-val">${formatearDecimal(ins.stock_actual)} ${escaparHtml(ins.unidad_medida)}</div>
                        <span class="badge-estado ${ins.estado}">${ins.estado}</span>
                    </div>
                </div>
                ${accionesGerente}
            </article>`;
    }).join('');
}

function toggleFormNuevoInsumo() {
    const box = document.getElementById('boxNuevoInsumo');
    if (box) {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
}

async function guardarNuevoInsumo(e) {
    e.preventDefault();
    const payload = {
        nombre: document.getElementById('insNombre').value.trim(),
        unidad_medida: document.getElementById('insUnidad').value,
        categoria: document.getElementById('insCategoria').value,
        stock_inicial: parseFloat(document.getElementById('insStockInicial').value),
        stock_minimo: parseFloat(document.getElementById('insStockMinimo').value)
    };

    const res = await llamarApi('/insumos', { method: 'POST', body: payload });
    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'Error al guardar el insumo', 'error');
        return;
    }

    document.getElementById('formNuevoInsumo').reset();
    document.getElementById('boxNuevoInsumo').style.display = 'none';
    mostrarAvisoGlobal(`Insumo "${res.data.nombre}" creado exitosamente en estado ${res.data.estado} (CA-02.1).`, 'ok');
    await cargarInsumos();
}

async function desactivarInsumo(id, nombre) {
    if (!confirm(`¿Desactivar lógicamente el insumo "${nombre}"?\nSe conservará intacto su historial de movimientos (CA-02.3).`)) {
        return;
    }
    const res = await llamarApi(`/insumos/${id}/desactivar`, {
        method: 'POST',
        body: { _method: 'PATCH', activo: false }
    });
    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'No se pudo desactivar el insumo', 'error');
        return;
    }
    mostrarAvisoGlobal(`Insumo "${nombre}" desactivado lógicamente (activo = false). Historial preservado.`, 'ok');
    await cargarInsumos();
    if (ES_GERENTE) await cargarProductosYRecetas();
}

function poblarSelectoresInsumos() {
    if (!ES_GERENTE) return;
    const opcionesHtml = catalogoInsumos.map(i =>
        `<option value="${i.id}">${escaparHtml(i.nombre)} (Actual: ${formatearDecimal(i.stock_actual)} ${escaparHtml(i.unidad_medida)})</option>`
    ).join('');

    const selMerma = document.getElementById('mermaInsumoId');
    const selEntrada = document.getElementById('entradaInsumoId');
    const selHist = document.getElementById('histInsumo');

    if (selMerma) selMerma.innerHTML = opcionesHtml;
    if (selEntrada) selEntrada.innerHTML = opcionesHtml;
    if (selHist) {
        selHist.innerHTML = '<option value="0">Todos los insumos</option>' +
            catalogoInsumos.map(i => `<option value="${i.id}">${escaparHtml(i.nombre)}</option>`).join('');
    }
}

function irAReabastecerInsumo(insumoId) {
    cambiarVista('operaciones');
    const sel = document.getElementById('entradaInsumoId');
    if (sel) {
        sel.value = String(insumoId);
        document.getElementById('entradaCantidad')?.focus();
    }
}

function irAMermaRapida(insumoId) {
    cambiarVista('operaciones');
    const sel = document.getElementById('mermaInsumoId');
    if (sel) {
        sel.value = String(insumoId);
        document.getElementById('mermaCantidad')?.focus();
    }
}

/**
 * MÓDULO DE VENTAS Y RECETAS (RF-03, RF-04, RF-05)
 */
async function cargarProductosYRecetas() {
    if (!ES_GERENTE) return;
    const res = await llamarApi('/productos');
    if (!res.ok || !Array.isArray(res.data)) return;

    catalogoProductos = res.data;
    renderizarGridProductosVenta();
    poblarSelectorProductosReceta();
}

function renderizarGridProductosVenta() {
    const grid = document.getElementById('gridProductosVenta');
    if (!grid) return;

    grid.innerHTML = catalogoProductos.map(prod => {
        const cant = carritoCantidades[prod.id] || 0;
        const resumenIng = (prod.ingredientes || [])
            .map(ing => `${formatearDecimal(ing.cantidad)} ${ing.unidad_medida} ${ing.insumo_nombre}`)
            .join(' · ') || '⚠️ Sin receta configurada';

        return `
            <div class="producto-card ${cant > 0 ? 'seleccionado' : ''}">
                <img src="${escaparHtml(prod.imagen)}" alt="${escaparHtml(prod.nombre)}" class="producto-img" onerror="this.src='assets/img/Tacos_tony_logo.png'">
                <div class="producto-info">
                    <div class="producto-nombre">${escaparHtml(prod.nombre)}</div>
                    <div class="producto-precio">$${Number(prod.precio).toFixed(2)}</div>
                    <div class="producto-receta-resumen">${escaparHtml(resumenIng)}</div>
                </div>
                <div class="stepper">
                    <button type="button" class="btn btn-secondary btn-step" onclick="ajustarCantidadVenta(${prod.id}, -1)" aria-label="Restar">-</button>
                    <span class="step-val">${cant}</span>
                    <button type="button" class="btn btn-step" onclick="ajustarCantidadVenta(${prod.id}, 1)" aria-label="Sumar">+</button>
                </div>
            </div>`;
    }).join('');

    actualizarResumenVenta();
}

function ajustarCantidadVenta(prodId, delta) {
    const actual = carritoCantidades[prodId] || 0;
    const nuevo = Math.max(0, actual + delta);
    if (nuevo === 0) {
        delete carritoCantidades[prodId];
    } else {
        carritoCantidades[prodId] = nuevo;
    }
    renderizarGridProductosVenta();
}

function limpiarCarritoVenta() {
    carritoCantidades = {};
    renderizarGridProductosVenta();
}

function actualizarResumenVenta() {
    let total = 0;
    const acumuladoInsumos = {};

    Object.entries(carritoCantidades).forEach(([pidStr, cant]) => {
        const prod = catalogoProductos.find(p => p.id === Number(pidStr));
        if (!prod || cant <= 0) return;
        total += Number(prod.precio) * cant;

        (prod.ingredientes || []).forEach(ing => {
            const clave = ing.insumo_id;
            if (!acumuladoInsumos[clave]) {
                acumuladoInsumos[clave] = {
                    nombre: ing.insumo_nombre,
                    unidad: ing.unidad_medida,
                    cantidad: 0
                };
            }
            acumuladoInsumos[clave].cantidad += Number(ing.cantidad) * cant;
        });
    });

    document.getElementById('totalVentaMonto').textContent = '$' + total.toFixed(2);
    const previewEl = document.getElementById('previewDescuentoReceta');
    const listaInsumos = Object.values(acumuladoInsumos);

    if (listaInsumos.length === 0) {
        previewEl.innerHTML = 'Selecciona al menos un producto del menú usando los botones <strong>+</strong>.';
    } else {
        previewEl.innerHTML = '<strong>Insumos a descontar por receta:</strong> ' +
            listaInsumos.map(i => `${escaparHtml(i.nombre)}: <code>-${formatearDecimal(i.cantidad)} ${escaparHtml(i.unidad)}</code>`).join(', ');
    }
}

async function confirmarVenta() {
    const items = Object.entries(carritoCantidades)
        .filter(([, cant]) => cant > 0)
        .map(([pid, cant]) => ({ producto_id: Number(pid), cantidad: Number(cant) }));

    const res = await llamarApi('/ventas', {
        method: 'POST',
        body: { items: items }
    });

    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'Error al registrar la venta', 'error');
        return;
    }

    limpiarCarritoVenta();
    await cargarInsumos();
    await cargarProductosYRecetas();

    const disc = res.data.discrepancias_detectadas || [];
    if (disc.length > 0) {
        const detalleDisc = disc.map(d => `${d.insumo_nombre} (${formatearDecimal(d.stock_resultante)})`).join(', ');
        mostrarAvisoGlobal(
            `Venta #${res.data.venta_id} registrada ($${Number(res.data.total).toFixed(2)}). ⚠️ Discrepancia detectada (CA-05.2): stock negativo en ${detalleDisc}.`,
            'warning'
        );
    } else {
        mostrarAvisoGlobal(
            `Venta #${res.data.venta_id} registrada ($${Number(res.data.total).toFixed(2)}) con descuento automático de insumos (CA-04.1, CA-05.1).`,
            'ok'
        );
    }
}

function toggleConfiguradorRecetas() {
    const panel = document.getElementById('panelRecetas');
    if (panel) {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        if (panel.style.display === 'block') {
            cargarRecetaDeProductoSeleccionado();
            panel.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

function poblarSelectorProductosReceta() {
    const sel = document.getElementById('selectProductoReceta');
    if (!sel) return;
    const valorPrevio = sel.value;
    sel.innerHTML = catalogoProductos.map(p =>
        `<option value="${p.id}">${escaparHtml(p.nombre)} ($${Number(p.precio).toFixed(2)})</option>`
    ).join('');
    if (valorPrevio && catalogoProductos.some(p => String(p.id) === String(valorPrevio))) {
        sel.value = valorPrevio;
    }
    cargarRecetaDeProductoSeleccionado();
}

function cargarRecetaDeProductoSeleccionado() {
    const sel = document.getElementById('selectProductoReceta');
    const contenedor = document.getElementById('lineasRecetaEditor');
    if (!sel || !contenedor) return;

    const prod = catalogoProductos.find(p => String(p.id) === String(sel.value));
    contenedor.innerHTML = '';

    const ingredientes = prod?.ingredientes || [];
    if (ingredientes.length === 0) {
        agregarLineaIngredienteReceta();
    } else {
        ingredientes.forEach(ing => agregarLineaIngredienteReceta(ing.insumo_id, ing.cantidad));
    }
}

function agregarLineaIngredienteReceta(insumoIdSeleccionado = null, cantidadInicial = '') {
    const contenedor = document.getElementById('lineasRecetaEditor');
    if (!contenedor) return;

    const fila = document.createElement('div');
    fila.className = 'fila-ingrediente-receta';
    fila.style.cssText = 'display: grid; grid-template-columns: 1.6fr 1fr auto; gap: 0.45rem; align-items: center;';

    const opcionesInsumo = catalogoInsumos.map(i =>
        `<option value="${i.id}" ${Number(insumoIdSeleccionado) === i.id ? 'selected' : ''}>${escaparHtml(i.nombre)} (${escaparHtml(i.unidad_medida)})</option>`
    ).join('');

    fila.innerHTML = `
        <select class="receta-insumo-select" aria-label="Insumo de receta">${opcionesInsumo}</select>
        <input type="number" class="receta-insumo-cantidad" step="0.001" placeholder="Ej. 0.080" value="${cantidadInicial !== '' ? Number(cantidadInicial).toFixed(3) : ''}" aria-label="Cantidad por unidad">
        <button type="button" class="btn btn-danger btn-step" onclick="this.parentElement.remove()" title="Quitar ingrediente">✕</button>
    `;
    contenedor.appendChild(fila);
}

async function guardarRecetaProducto() {
    const prodId = document.getElementById('selectProductoReceta').value;
    const filas = document.querySelectorAll('#lineasRecetaEditor .fila-ingrediente-receta');
    const ingredientes = [];

    filas.forEach(f => {
        const insumoId = parseInt(f.querySelector('.receta-insumo-select').value, 10);
        const cantVal = parseFloat(f.querySelector('.receta-insumo-cantidad').value);
        if (!isNaN(insumoId)) {
            ingredientes.push({ insumo_id: insumoId, cantidad: cantVal });
        }
    });

    const res = await llamarApi(`/productos/${prodId}/receta`, {
        method: 'POST',
        body: { _method: 'PUT', ingredientes: ingredientes }
    });

    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'Error al guardar la receta', 'error');
        return;
    }

    mostrarAvisoGlobal(res.data.mensaje || 'Receta configurada exitosamente (CA-03.1).', 'ok');
    await cargarProductosYRecetas();
}

async function crearNuevoProductoMenu(e) {
    e.preventDefault();
    const nombre = document.getElementById('nuevoProdNombre').value.trim();
    const precio = parseFloat(document.getElementById('nuevoProdPrecio').value);

    const res = await llamarApi('/productos', {
        method: 'POST',
        body: { nombre: nombre, precio: precio }
    });

    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'No se pudo crear el producto', 'error');
        return;
    }

    document.getElementById('formNuevoProducto').reset();
    await cargarProductosYRecetas();
    document.getElementById('selectProductoReceta').value = String(res.data.id);
    cargarRecetaDeProductoSeleccionado();
    mostrarAvisoGlobal(`Producto "${res.data.nombre}" creado. Ahora asígnale su receta arriba.`, 'ok');
}

/**
 * MÓDULO DE ENTRADAS Y MERMAS (RF-06, RF-07)
 */
async function registrarMerma(e) {
    e.preventDefault();
    const payload = {
        insumo_id: parseInt(document.getElementById('mermaInsumoId').value, 10),
        motivo: document.getElementById('mermaMotivo').value,
        cantidad: parseFloat(document.getElementById('mermaCantidad').value),
        observaciones: document.getElementById('mermaObs').value.trim()
    };

    const res = await llamarApi('/mermas', { method: 'POST', body: payload });
    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'Error al registrar la merma', 'error');
        return;
    }

    document.getElementById('mermaCantidad').value = '';
    document.getElementById('mermaObs').value = '';
    await cargarInsumos();
    mostrarAvisoGlobal(
        `Merma registrada: -${formatearDecimal(res.data.cantidad_descontada)} de ${res.data.insumo_nombre}. Stock actual: ${formatearDecimal(res.data.stock_nuevo)} (${res.data.nuevo_estado}).`,
        res.data.nuevo_estado === 'normal' ? 'ok' : 'warning'
    );
}

async function registrarEntradaCompra(e) {
    e.preventDefault();
    const payload = {
        insumo_id: parseInt(document.getElementById('entradaInsumoId').value, 10),
        cantidad: parseFloat(document.getElementById('entradaCantidad').value),
        observaciones: document.getElementById('entradaObs').value.trim()
    };

    const res = await llamarApi('/entradas', { method: 'POST', body: payload });
    if (!res.ok) {
        mostrarAvisoGlobal(res.data.error || 'Error al registrar la entrada', 'error');
        return;
    }

    document.getElementById('entradaCantidad').value = '';
    document.getElementById('entradaObs').value = '';
    await cargarInsumos();
    mostrarAvisoGlobal(
        `Entrada registrada exitosamente. ${res.data.insumo_nombre}: ${formatearDecimal(res.data.stock_anterior)} → ${formatearDecimal(res.data.stock_nuevo)} (Estado: ${res.data.nuevo_estado}).`,
        'ok'
    );
}

/**
 * MÓDULO DE AUDITORÍA E HISTORIAL INMUTABLE (RF-10, RNF-06)
 */
async function cargarHistorialMovimientos() {
    if (!ES_GERENTE) return;
    const tipo = document.getElementById('histTipo')?.value || '';
    const insumoId = document.getElementById('histInsumo')?.value || '0';
    const fIni = document.getElementById('histFechaIni')?.value || '';
    const fFin = document.getElementById('histFechaFin')?.value || '';

    const params = new URLSearchParams();
    if (tipo) params.set('tipo', tipo);
    if (insumoId && insumoId !== '0') params.set('insumo_id', insumoId);
    if (fIni) params.set('fecha_inicio', fIni);
    if (fFin) params.set('fecha_fin', fFin);

    const query = params.toString() ? ('?' + params.toString()) : '';
    const res = await llamarApi('/movimientos' + query);
    const contenedor = document.getElementById('listaMovimientos');

    if (!res.ok || !Array.isArray(res.data)) {
        contenedor.innerHTML = '<p class="sub">No se pudo cargar el historial.</p>';
        return;
    }

    if (res.data.length === 0) {
        contenedor.innerHTML = '<p class="sub">No hay movimientos registrados para los filtros seleccionados.</p>';
        return;
    }

    contenedor.innerHTML = res.data.map(mov => {
        const esPositivo = Number(mov.cantidad) > 0;
        const signo = esPositivo ? '+' : '';
        const colorBadge = mov.tipo_movimiento === 'ENTRADA'
            ? 'normal'
            : (mov.tipo_movimiento === 'MERMA' ? 'discrepancia' : 'bajo');

        const detalleMotivo = mov.motivo ? ` · Motivo: <strong>${escaparHtml(mov.motivo)}</strong>` : '';
        const detalleObs = mov.observaciones ? `<div class="insumo-meta">${escaparHtml(mov.observaciones)}</div>` : '';

        return `
            <article class="insumo-card">
                <div class="insumo-top">
                    <div>
                        <div class="insumo-nombre">
                            #${mov.id} · ${escaparHtml(mov.insumo_nombre)}
                        </div>
                        <div class="insumo-meta">
                            ${escaparHtml(mov.fecha_hora)} · Por: ${escaparHtml(mov.usuario_nombre)}${detalleMotivo}
                        </div>
                        ${detalleObs}
                    </div>
                    <div class="insumo-stock-wrap">
                        <span class="badge-estado ${colorBadge}">${escaparHtml(mov.tipo_movimiento)}</span>
                        <div class="insumo-stock-val" style="color: ${esPositivo ? '#166534' : '#b91c1c'};">
                            ${signo}${formatearDecimal(mov.cantidad)} ${escaparHtml(mov.unidad_medida)}
                        </div>
                        <div class="insumo-meta">
                            ${formatearDecimal(mov.stock_previo)} &rarr; ${formatearDecimal(mov.stock_posterior)}
                        </div>
                    </div>
                </div>
            </article>`;
    }).join('');
}

async function probarInmutabilidadHistorial() {
    const res = await llamarApi('/movimientos/1', {
        method: 'POST',
        body: { _method: 'DELETE' }
    });
    if (res.status === 405) {
        mostrarAvisoGlobal(
            `Verificación exitosa (CA-10.2 / RNF-06): El servidor rechazó el borrado con HTTP 405 (${res.data.error}).`,
            'ok'
        );
    } else {
        mostrarAvisoGlobal(`Respuesta HTTP ${res.status}: ${res.data.error || ''}`, 'warning');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await cargarInsumos();
    if (ES_GERENTE) {
        await cargarProductosYRecetas();
    }
});
</script>
</body>
</html>
