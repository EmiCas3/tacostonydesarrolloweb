<?php
/**
 * Front Controller y Enrutador API RESTful — Tacos Tony Inventario Móvil (Etapa 3)
 * Alumno: Fernando León Del Río (ID: 3478788)
 * Cumple ADR-01 (API-First para Web y Etapa 6 Móvil), ADR-02 (Transacciones ACID + DECIMAL(10,3)),
 * ADR-04 (JWT + RBAC), ADR-05 (PHP 8 nativo sin framework) y RF-01 a RF-10.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../init_mvp.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * Envía respuesta JSON estandarizada y finaliza la ejecución
 */
function responderJson(int $status, mixed $datos): never
{
    http_response_code($status);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    exit;
}

/**
 * Obtiene el cuerpo JSON de la petición
 */
function leerCuerpoJson(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST ?: [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : ($_POST ?: []);
}

/**
 * Extrae el token Bearer de las cabeceras HTTP
 */
function extraerBearerToken(): ?string
{
    $auth = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($auth === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (preg_match('/Bearer\s+(\S+)/i', $auth, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Middleware authenticateToken() (ADR-04 / RNF-05):
 * Valida token JWT Bearer o sesión PHP activa (30 min de vigencia).
 */
function authenticateToken(): array
{
    $token = extraerBearerToken();
    if ($token !== null) {
        $payload = verificarJwt($token);
        if ($payload === null) {
            responderJson(401, ['error' => 'Token no proporcionado o expirado']);
        }
        return [
            'id'      => (int)$payload['sub'],
            'usuario' => $payload['usuario'] ?? '',
            'correo'  => $payload['correo'] ?? '',
            'nombre'  => $payload['nombre'] ?? '',
            'rol'     => strtoupper($payload['rol'] ?? 'COLABORADOR'),
        ];
    }

    // Fallback a sesión PHP segura cuando se invoca desde el mismo navegador
    iniciarSesionSegura();
    if (!empty($_SESSION['usuario'])) {
        if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT)) {
            session_destroy();
            responderJson(401, ['error' => 'Tu sesión ha expirado por inactividad']);
        }
        $_SESSION['ultimo_acceso'] = time();
        return [
            'id'      => (int)($_SESSION['id'] ?? 1),
            'usuario' => $_SESSION['usuario'],
            'correo'  => $_SESSION['correo'] ?? $_SESSION['usuario'],
            'nombre'  => $_SESSION['nombre'] ?? 'Usuario',
            'rol'     => strtoupper($_SESSION['rol'] ?? 'COLABORADOR'),
        ];
    }

    responderJson(401, ['error' => 'Token no proporcionado o expirado']);
}

/**
 * Middleware authorizeRole() (ADR-04 / RF-01 / CA-02.2):
 * Verifica que el rol autenticado esté dentro de los roles permitidos.
 */
function authorizeRole(array $usuario, array $rolesPermitidos, string $mensajeError = 'Permisos insuficientes'): void
{
    $rolUsuario = strtoupper($usuario['rol'] ?? '');
    if (!in_array($rolUsuario, $rolesPermitidos, true)) {
        responderJson(403, ['error' => $mensajeError]);
    }
}

try {
    $pdo = obtenerConexion();
    inicializarEsquemaMvp($pdo);
} catch (PDOException $e) {
    error_log('Error BD API 3478788: ' . $e->getMessage());
    responderJson(500, ['error' => 'Error de conexión con la base de datos']);
}

// Determinar método HTTP y ruta solicitada
$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$body   = leerCuerpoJson();
if ($metodo === 'POST' && !empty($body['_method'])) {
    $metodo = strtoupper((string)$body['_method']);
}

$ruta = $_GET['ruta'] ?? '';
if ($ruta === '') {
    $uri       = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
        $ruta = substr($uri, strlen($scriptDir));
    } else {
        $ruta = $uri;
    }
    if (str_starts_with($ruta, '/index.php')) {
        $ruta = substr($ruta, strlen('/index.php'));
    }
}
$ruta = '/' . trim($ruta, '/');

// =========================================================================
// 1. POST /api/auth/login — Autenticación JWT y Sesión (RF-01, CA-01.1..01.3)
// =========================================================================
if ($ruta === '/auth/login' && $metodo === 'POST') {
    $identificador = trim((string)($body['correo'] ?? $body['usuario'] ?? ''));
    $password      = (string)($body['password'] ?? '');

    if ($identificador === '' || $password === '') {
        responderJson(400, ['error' => 'Correo y contraseña son requeridos']);
    }

    $stmt = $pdo->prepare(
        "SELECT id, usuario, correo, nombre, rol, activo, password_hash
         FROM " . TABLA_USUARIOS . "
         WHERE usuario = :id1 OR correo = :id2
         LIMIT 1"
    );
    $stmt->execute([':id1' => $identificador, ':id2' => $identificador]);
    $fila = $stmt->fetch();

    if (!$fila || !password_verify($password, $fila['password_hash'])) {
        responderJson(401, ['error' => 'Credenciales inválidas']);
    }

    if ((int)$fila['activo'] !== 1) {
        responderJson(403, ['error' => 'Usuario inactivo en el sistema']);
    }

    $datosUsuario = [
        'id'      => (int)$fila['id'],
        'usuario' => $fila['usuario'],
        'nombre'  => $fila['nombre'],
        'correo'  => $fila['correo'] ?: ($fila['usuario'] . '@tacostony.com'),
        'rol'     => strtoupper($fila['rol']),
    ];

    // Sincronizar también con sesión PHP para navegación híbrida
    iniciarSesionSegura();
    session_regenerate_id(true);
    $_SESSION['id']            = $datosUsuario['id'];
    $_SESSION['usuario']       = $datosUsuario['usuario'];
    $_SESSION['correo']        = $datosUsuario['correo'];
    $_SESSION['nombre']        = $datosUsuario['nombre'];
    $_SESSION['rol']           = $datosUsuario['rol'];
    $_SESSION['ultimo_acceso'] = time();

    $token = generarJwt($datosUsuario);

    responderJson(200, [
        'token'   => $token,
        'usuario' => [
            'id'     => $datosUsuario['id'],
            'nombre' => $datosUsuario['nombre'],
            'correo' => $datosUsuario['correo'],
            'rol'    => $datosUsuario['rol'],
        ],
    ]);
}

// =========================================================================
// 2. GET /api/insumos — Consulta de Existencias y Filtros (RF-08, RF-09)
// =========================================================================
if ($ruta === '/insumos' && $metodo === 'GET') {
    $usuario   = authenticateToken();
    $busqueda  = trim((string)($_GET['busqueda'] ?? ''));
    $categoria = trim((string)($_GET['categoria'] ?? ''));
    $estado    = trim((string)($_GET['estado'] ?? ''));
    $incluirInactivos = isset($_GET['incluir_inactivos']) && $_GET['incluir_inactivos'] === '1';

    $condiciones = [];
    $params      = [];

    if (!$incluirInactivos) {
        $condiciones[] = 'activo = 1';
    }
    if ($busqueda !== '') {
        $condiciones[]       = 'nombre LIKE :busqueda';
        $params[':busqueda'] = '%' . $busqueda . '%';
    }
    if ($categoria !== '' && strtolower($categoria) !== 'todas') {
        $condiciones[]        = 'categoria = :categoria';
        $params[':categoria'] = $categoria;
    }
    if ($estado !== '' && strtolower($estado) !== 'todos') {
        $condiciones[]     = 'estado = :estado';
        $params[':estado'] = $estado;
    }

    $whereSql = count($condiciones) > 0 ? ('WHERE ' . implode(' AND ', $condiciones)) : '';
    $stmt     = $pdo->prepare(
        "SELECT id, nombre, unidad_medida, categoria, stock_actual, stock_minimo, estado, activo
         FROM " . TABLA_INSUMOS . "
         $whereSql
         ORDER BY FIELD(estado, 'discrepancia', 'bajo', 'normal'), nombre ASC"
    );
    $stmt->execute($params);
    $filas = $stmt->fetchAll();

    $resultado = array_map(static function (array $r): array {
        return [
            'id'            => (int)$r['id'],
            'nombre'        => $r['nombre'],
            'unidad_medida' => $r['unidad_medida'],
            'categoria'     => $r['categoria'],
            'stock_actual'  => round((float)$r['stock_actual'], 3),
            'stock_minimo'  => round((float)$r['stock_minimo'], 3),
            'estado'        => $r['estado'],
            'activo'        => (int)$r['activo'] === 1,
        ];
    }, $filas);

    responderJson(200, $resultado);
}

// =========================================================================
// 2B. GET /api/proveedores — Catálogo de Proveedores del Sistema Original
// =========================================================================
if ($ruta === '/proveedores' && $metodo === 'GET') {
    $usuario = authenticateToken();
    $rows    = $pdo->query(
        "SELECT id, nombre, numero_telefono, correo
         FROM " . TABLA_PROVEEDORES . "
         WHERE activo = 1
         ORDER BY id ASC"
    )->fetchAll();

    $salida = array_map(static function (array $r): array {
        return [
            'id'              => (int)$r['id'],
            'nombre'          => $r['nombre'],
            'numero_telefono' => $r['numero_telefono'],
            'correo'          => $r['correo'],
        ];
    }, $rows);

    responderJson(200, $salida);
}

// =========================================================================
// 3. POST /api/insumos — Alta de Nuevo Insumo (RF-02, CA-02.1, CA-02.2)
// =========================================================================
if ($ruta === '/insumos' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso restringido: solo el Gerente puede dar de alta insumos'
    );

    $nombre       = trim((string)($body['nombre'] ?? ''));
    $unidadMedida = trim((string)($body['unidad_medida'] ?? ''));
    $categoria    = trim((string)($body['categoria'] ?? ''));
    $stockInicial = $body['stock_inicial'] ?? null;
    $stockMinimo  = $body['stock_minimo'] ?? null;

    $unidadesValidas   = ['kg', 'g', 'L', 'ml', 'pieza'];
    $categoriasValidas = ['Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables', 'Otros'];

    if (
        $nombre === '' ||
        !in_array($unidadMedida, $unidadesValidas, true) ||
        !in_array($categoria, $categoriasValidas, true) ||
        !is_numeric($stockInicial) ||
        !is_numeric($stockMinimo) ||
        (float)$stockInicial < 0 ||
        (float)$stockMinimo < 0
    ) {
        responderJson(400, ['error' => 'Unidad de medida inválida o campos incompletos']);
    }

    // Verificar duplicado activo (409 Conflict)
    $stmtDup = $pdo->prepare(
        "SELECT id FROM " . TABLA_INSUMOS . " WHERE LOWER(nombre) = LOWER(:nombre) AND activo = 1 LIMIT 1"
    );
    $stmtDup->execute([':nombre' => $nombre]);
    if ($stmtDup->fetch()) {
        responderJson(409, ['error' => 'Ya existe un insumo activo con ese nombre']);
    }

    $actual = round((float)$stockInicial, 3);
    $minimo = round((float)$stockMinimo, 3);
    $estado = calcularEstadoInsumo($actual, $minimo);

    $stmtIns = $pdo->prepare(
        "INSERT INTO " . TABLA_INSUMOS . "
         (nombre, unidad_medida, categoria, stock_actual, stock_minimo, estado, activo)
         VALUES (:nombre, :unidad, :cat, :actual, :minimo, :estado, 1)"
    );
    $stmtIns->execute([
        ':nombre' => $nombre,
        ':unidad' => $unidadMedida,
        ':cat'    => $categoria,
        ':actual' => number_format($actual, 3, '.', ''),
        ':minimo' => number_format($minimo, 3, '.', ''),
        ':estado' => $estado,
    ]);

    $nuevoId = (int)$pdo->lastInsertId();

    responderJson(201, [
        'id'            => $nuevoId,
        'nombre'        => $nombre,
        'unidad_medida' => $unidadMedida,
        'categoria'     => $categoria,
        'stock_actual'  => $actual,
        'stock_minimo'  => $minimo,
        'estado'        => $estado,
        'activo'        => true,
    ]);
}

// =========================================================================
// 4. PATCH /api/insumos/:id/desactivar — Desactivación Lógica (RF-02, CA-02.3)
// =========================================================================
if (preg_match('#^/insumos/(\d+)/desactivar$#', $ruta, $m) && in_array($metodo, ['PATCH', 'POST', 'PUT'], true)) {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso restringido: solo el Gerente puede desactivar insumos'
    );

    $insumoId = (int)$m[1];
    $stmtEx   = $pdo->prepare("SELECT id, activo FROM " . TABLA_INSUMOS . " WHERE id = :id LIMIT 1");
    $stmtEx->execute([':id' => $insumoId]);
    $insumo = $stmtEx->fetch();

    if (!$insumo) {
        responderJson(404, ['error' => 'Insumo no encontrado']);
    }

    $nuevoEstadoActivo = isset($body['activo']) ? ((bool)$body['activo'] ? 1 : 0) : 0;
    $stmtUpd = $pdo->prepare("UPDATE " . TABLA_INSUMOS . " SET activo = :act WHERE id = :id");
    $stmtUpd->execute([':act' => $nuevoEstadoActivo, ':id' => $insumoId]);

    responderJson(200, [
        'id'     => $insumoId,
        'activo' => $nuevoEstadoActivo === 1,
    ]);
}

// =========================================================================
// 5. GET /api/productos y POST /api/productos — Menú y Recetas (RF-03)
// =========================================================================
if ($ruta === '/productos' && $metodo === 'GET') {
    $usuario = authenticateToken();

    $stmtProd = $pdo->query(
        "SELECT id, nombre, categoria, precio, imagen, activo
         FROM " . TABLA_PRODUCTOS . "
         WHERE activo = 1
         ORDER BY id ASC"
    );
    $productos = $stmtProd->fetchAll();

    $stmtRec = $pdo->query(
        "SELECT r.producto_id, r.insumo_id, r.cantidad, i.nombre AS insumo_nombre, i.unidad_medida, i.stock_actual
         FROM " . TABLA_RECETAS . " r
         INNER JOIN " . TABLA_INSUMOS . " i ON i.id = r.insumo_id
         WHERE i.activo = 1
         ORDER BY r.id ASC"
    );
    $recetasRaw = $stmtRec->fetchAll();

    $recetasPorProd = [];
    foreach ($recetasRaw as $rec) {
        $pid = (int)$rec['producto_id'];
        $recetasPorProd[$pid][] = [
            'insumo_id'     => (int)$rec['insumo_id'],
            'insumo_nombre' => $rec['insumo_nombre'],
            'unidad_medida' => $rec['unidad_medida'],
            'cantidad'      => round((float)$rec['cantidad'], 3),
            'stock_actual'  => round((float)$rec['stock_actual'], 3),
        ];
    }

    $salida = array_map(static function (array $p) use ($recetasPorProd): array {
        $pid = (int)$p['id'];
        return [
            'id'           => $pid,
            'nombre'       => $p['nombre'],
            'categoria'    => $p['categoria'],
            'precio'       => round((float)$p['precio'], 2),
            'imagen'       => $p['imagen'] ?: 'assets/img/productos/TacoArabe.png',
            'activo'       => (int)$p['activo'] === 1,
            'ingredientes' => $recetasPorProd[$pid] ?? [],
        ];
    }, $productos);

    responderJson(200, $salida);
}

if ($ruta === '/productos' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso restringido: solo el Gerente puede configurar recetas'
    );

    $nombre    = trim((string)($body['nombre'] ?? ''));
    $categoria = trim((string)($body['categoria'] ?? 'Especialidades Árabes'));
    $precio    = $body['precio'] ?? null;
    $imagen    = trim((string)($body['imagen'] ?? 'assets/img/productos/TacoArabe.png'));

    if ($nombre === '' || !is_numeric($precio) || (float)$precio <= 0) {
        responderJson(400, ['error' => 'Nombre y precio mayor a cero son obligatorios']);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO " . TABLA_PRODUCTOS . " (nombre, categoria, precio, imagen, activo)
         VALUES (:nombre, :cat, :precio, :img, 1)"
    );
    $stmt->execute([
        ':nombre' => $nombre,
        ':cat'    => $categoria,
        ':precio' => number_format((float)$precio, 2, '.', ''),
        ':img'    => $imagen,
    ]);

    responderJson(201, [
        'id'        => (int)$pdo->lastInsertId(),
        'nombre'    => $nombre,
        'categoria' => $categoria,
        'precio'    => round((float)$precio, 2),
        'imagen'    => $imagen,
        'activo'    => true,
    ]);
}

// =========================================================================
// 6. PUT /api/productos/:id/receta — Configurar Receta en DECIMAL(10,3) (RF-03)
// =========================================================================
if (preg_match('#^/productos/(\d+)/receta$#', $ruta, $m) && in_array($metodo, ['PUT', 'POST'], true)) {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso restringido: solo el Gerente puede configurar recetas'
    );

    $productoId   = (int)$m[1];
    $ingredientes = $body['ingredientes'] ?? [];

    $stmtProd = $pdo->prepare("SELECT id, nombre FROM " . TABLA_PRODUCTOS . " WHERE id = :id LIMIT 1");
    $stmtProd->execute([':id' => $productoId]);
    $producto = $stmtProd->fetch();
    if (!$producto) {
        responderJson(404, ['error' => 'Producto o insumo no encontrado']);
    }

    if (!is_array($ingredientes) || count($ingredientes) === 0) {
        responderJson(400, ['error' => 'La cantidad del insumo en la receta debe ser un número positivo mayor a cero']);
    }

    foreach ($ingredientes as $ing) {
        $insId = (int)($ing['insumo_id'] ?? 0);
        $cant  = $ing['cantidad'] ?? null;
        if ($insId <= 0 || !is_numeric($cant) || (float)$cant <= 0) {
            responderJson(400, ['error' => 'La cantidad del insumo en la receta debe ser un número positivo mayor a cero']);
        }

        $stmtCheckIns = $pdo->prepare("SELECT id FROM " . TABLA_INSUMOS . " WHERE id = :id AND activo = 1 LIMIT 1");
        $stmtCheckIns->execute([':id' => $insId]);
        if (!$stmtCheckIns->fetch()) {
            responderJson(404, ['error' => 'Producto o insumo no encontrado']);
        }
    }

    $pdo->beginTransaction();
    try {
        $stmtDel = $pdo->prepare("DELETE FROM " . TABLA_RECETAS . " WHERE producto_id = :pid");
        $stmtDel->execute([':pid' => $productoId]);

        $stmtInsRec = $pdo->prepare(
            "INSERT INTO " . TABLA_RECETAS . " (producto_id, insumo_id, cantidad)
             VALUES (:pid, :iid, :cant)"
        );

        $guardados = [];
        foreach ($ingredientes as $ing) {
            $insId   = (int)$ing['insumo_id'];
            $cantDec = round((float)$ing['cantidad'], 3);
            $stmtInsRec->execute([
                ':pid'  => $productoId,
                ':iid'  => $insId,
                ':cant' => number_format($cantDec, 3, '.', ''),
            ]);
            $guardados[] = [
                'insumo_id' => $insId,
                'cantidad'  => $cantDec,
            ];
        }

        $pdo->commit();
        responderJson(200, [
            'producto_id'  => $productoId,
            'mensaje'      => 'Receta de ' . $producto['nombre'] . ' configurada exitosamente',
            'ingredientes' => $guardados,
        ]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        responderJson(500, ['error' => 'Error al guardar la receta']);
    }
}

/**
 * Helper transaccional compartido para procesar ventas (RF-04, RF-05, RF-14).
 * Debe ejecutarse dentro de una transacción PDO activa (caller controla begin/commit/rollBack).
 */
function ejecutarVentaTransaccional(
    PDO $pdo,
    array $usuario,
    array $lineasVenta,
    array $consumoPorInsumo,
    float $totalVenta,
    ?string $fechaHora = null
): array {
    if ($fechaHora !== null && $fechaHora !== '') {
        $stmtInsertVenta = $pdo->prepare(
            "INSERT INTO " . TABLA_VENTAS . " (usuario_id, fecha_hora, total) VALUES (:uid, :fh, :total)"
        );
        $stmtInsertVenta->execute([
            ':uid'   => $usuario['id'],
            ':fh'    => $fechaHora,
            ':total' => number_format($totalVenta, 2, '.', ''),
        ]);
    } else {
        $stmtInsertVenta = $pdo->prepare(
            "INSERT INTO " . TABLA_VENTAS . " (usuario_id, total) VALUES (:uid, :total)"
        );
        $stmtInsertVenta->execute([
            ':uid'   => $usuario['id'],
            ':total' => number_format($totalVenta, 2, '.', ''),
        ]);
    }
    $ventaId = (int)$pdo->lastInsertId();

    $stmtInsertDet = $pdo->prepare(
        "INSERT INTO " . TABLA_VENTA_DETALLES . " (venta_id, producto_id, cantidad, precio_unitario, subtotal)
         VALUES (:vid, :pid, :cant, :precio, :sub)"
    );
    foreach ($lineasVenta as $linea) {
        $stmtInsertDet->execute([
            ':vid'    => $ventaId,
            ':pid'    => $linea['producto_id'],
            ':cant'   => $linea['cantidad'],
            ':precio' => number_format($linea['precio_unitario'], 2, '.', ''),
            ':sub'    => number_format($linea['subtotal'], 2, '.', ''),
        ]);
    }

    $discrepancias = [];
    $stmtLockIns   = $pdo->prepare(
        "SELECT id, nombre, stock_actual, stock_minimo FROM " . TABLA_INSUMOS . " WHERE id = :id FOR UPDATE"
    );
    $stmtUpdIns = $pdo->prepare(
        "UPDATE " . TABLA_INSUMOS . " SET stock_actual = :nuevo, estado = :estado WHERE id = :id"
    );

    if ($fechaHora !== null && $fechaHora !== '') {
        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, referencia_id, observaciones, fecha_hora)
             VALUES (:iid, :uid, 'VENTA', :cant, :prev, :post, :ref, :obs, :fh)"
        );
    } else {
        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, referencia_id, observaciones)
             VALUES (:iid, :uid, 'VENTA', :cant, :prev, :post, :ref, :obs)"
        );
    }

    foreach ($consumoPorInsumo as $insumoId => $consumoTotal) {
        $stmtLockIns->execute([':id' => $insumoId]);
        $insumo = $stmtLockIns->fetch();
        if (!$insumo) {
            throw new RuntimeException("Insumo $insumoId no encontrado");
        }

        $stockPrevio    = round((float)$insumo['stock_actual'], 3);
        $stockMinimo    = round((float)$insumo['stock_minimo'], 3);
        $stockPosterior = round($stockPrevio - $consumoTotal, 3);
        $nuevoEstado    = calcularEstadoInsumo($stockPosterior, $stockMinimo);

        $stmtUpdIns->execute([
            ':nuevo'  => number_format($stockPosterior, 3, '.', ''),
            ':estado' => $nuevoEstado,
            ':id'     => $insumoId,
        ]);

        $paramsMov = [
            ':iid'  => $insumoId,
            ':uid'  => $usuario['id'],
            ':cant' => number_format(-$consumoTotal, 3, '.', ''),
            ':prev' => number_format($stockPrevio, 3, '.', ''),
            ':post' => number_format($stockPosterior, 3, '.', ''),
            ':ref'  => $ventaId,
            ':obs'  => "Descuento automático por Venta #$ventaId",
        ];
        if ($fechaHora !== null && $fechaHora !== '') {
            $paramsMov[':fh'] = $fechaHora;
        }
        $stmtMov->execute($paramsMov);

        if ($nuevoEstado === 'discrepancia') {
            $discrepancias[] = [
                'insumo_id'        => $insumoId,
                'insumo_nombre'    => $insumo['nombre'],
                'stock_resultante' => $stockPosterior,
                'mensaje'          => 'Stock resultante negativo. Marcado con discrepancia.',
            ];
        }
    }

    return [
        'venta_id'                 => $ventaId,
        'discrepancias_detectadas' => $discrepancias,
    ];
}

// =========================================================================
// 7. POST /api/ventas — Venta + Descuento por Receta + Discrepancias (RF-04, RF-05)
// =========================================================================
if ($ruta === '/ventas' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole($usuario, ['GERENTE'], 'Permisos insuficientes');

    $items = $body['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        responderJson(400, ['error' => 'La venta debe incluir al menos un producto válido']);
    }

    // Validar artículos y acumular consumo total de insumos por receta
    $consumoPorInsumo = [];
    $lineasVenta      = [];
    $totalVenta       = 0.0;

    foreach ($items as $item) {
        $prodId   = (int)($item['producto_id'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($prodId <= 0 || $cantidad <= 0) {
            responderJson(400, ['error' => 'La venta debe incluir al menos un producto con cantidad positiva']);
        }

        $stmtProd = $pdo->prepare("SELECT id, nombre, precio FROM " . TABLA_PRODUCTOS . " WHERE id = :id AND activo = 1 LIMIT 1");
        $stmtProd->execute([':id' => $prodId]);
        $prod = $stmtProd->fetch();
        if (!$prod) {
            responderJson(404, ['error' => "El producto con ID $prodId no existe"]);
        }

        $stmtRec = $pdo->prepare(
            "SELECT insumo_id, cantidad FROM " . TABLA_RECETAS . " WHERE producto_id = :pid"
        );
        $stmtRec->execute([':pid' => $prodId]);
        $receta = $stmtRec->fetchAll();

        if (count($receta) === 0) {
            responderJson(422, ['error' => "El producto con ID $prodId no tiene una receta configurada"]);
        }

        foreach ($receta as $ing) {
            $iid          = (int)$ing['insumo_id'];
            $consumoLinea = round((float)$ing['cantidad'] * $cantidad, 3);
            $consumoPorInsumo[$iid] = round(($consumoPorInsumo[$iid] ?? 0.0) + $consumoLinea, 3);
        }

        $precioUnit = round((float)$prod['precio'], 2);
        $subtotal   = round($precioUnit * $cantidad, 2);
        $totalVenta = round($totalVenta + $subtotal, 2);

        $lineasVenta[] = [
            'producto_id'     => $prodId,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precioUnit,
            'subtotal'        => $subtotal,
        ];
    }

    // Transacción ACID con bloqueo SELECT ... FOR UPDATE (ADR-02 / CA-04.3 / CA-05.1..05.3)
    $pdo->beginTransaction();
    try {
        $resVenta = ejecutarVentaTransaccional($pdo, $usuario, $lineasVenta, $consumoPorInsumo, $totalVenta);
        $pdo->commit();

        responderJson(201, [
            'venta_id'                 => $resVenta['venta_id'],
            'fecha_hora'               => gmdate('Y-m-d\TH:i:s\Z'),
            'total'                    => $totalVenta,
            'discrepancias_detectadas' => $resVenta['discrepancias_detectadas'],
            'mensaje'                  => 'Venta registrada exitosamente con descuento automático de insumos',
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error en venta transaccional: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al procesar la transacción de venta']);
    }
}

// =========================================================================
// 8. POST /api/entradas — Registro de Entrada por Compra (RF-06, CA-06.1..06.3)
// =========================================================================
if ($ruta === '/entradas' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso denegado: solo el Gerente puede registrar entradas'
    );

    $insumoId      = (int)($body['insumo_id'] ?? 0);
    $proveedorId   = (int)($body['proveedor_id'] ?? 0);
    $cantidadRaw   = $body['cantidad'] ?? null;
    $notaEntrada   = trim((string)($body['observaciones'] ?? ''));

    if ($insumoId <= 0 || !is_numeric($cantidadRaw) || (float)$cantidadRaw <= 0) {
        responderJson(400, ['error' => 'La cantidad de entrada debe ser superior a cero']);
    }

    $observaciones = $notaEntrada !== '' ? $notaEntrada : 'Entrada por compra a proveedor';
    $refProveedor  = null;
    if ($proveedorId > 0) {
        $stmtProv = $pdo->prepare("SELECT id, nombre FROM " . TABLA_PROVEEDORES . " WHERE id = :pid AND activo = 1 LIMIT 1");
        $stmtProv->execute([':pid' => $proveedorId]);
        $prov = $stmtProv->fetch();
        if (!$prov) {
            responderJson(404, ['error' => 'Proveedor no encontrado o inactivo']);
        }
        $refProveedor  = (int)$prov['id'];
        $observaciones = 'Proveedor: ' . $prov['nombre'] . ($notaEntrada !== '' ? (' · ' . $notaEntrada) : '');
    }

    $cantidad = round((float)$cantidadRaw, 3);

    $pdo->beginTransaction();
    try {
        $stmtIns = $pdo->prepare(
            "SELECT id, nombre, stock_actual, stock_minimo FROM " . TABLA_INSUMOS . " WHERE id = :id AND activo = 1 FOR UPDATE"
        );
        $stmtIns->execute([':id' => $insumoId]);
        $insumo = $stmtIns->fetch();

        if (!$insumo) {
            $pdo->rollBack();
            responderJson(404, ['error' => 'Insumo no encontrado o inactivo']);
        }

        $stockAnterior = round((float)$insumo['stock_actual'], 3);
        $stockMinimo   = round((float)$insumo['stock_minimo'], 3);
        $stockNuevo    = round($stockAnterior + $cantidad, 3);
        $nuevoEstado   = calcularEstadoInsumo($stockNuevo, $stockMinimo);

        $stmtUpd = $pdo->prepare(
            "UPDATE " . TABLA_INSUMOS . " SET stock_actual = :nuevo, estado = :estado WHERE id = :id"
        );
        $stmtUpd->execute([
            ':nuevo'  => number_format($stockNuevo, 3, '.', ''),
            ':estado' => $nuevoEstado,
            ':id'     => $insumoId,
        ]);

        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, observaciones, referencia_id)
             VALUES (:iid, :uid, 'ENTRADA', :cant, :prev, :post, :obs, :ref)"
        );
        $stmtMov->execute([
            ':iid'  => $insumoId,
            ':uid'  => $usuario['id'],
            ':cant' => number_format($cantidad, 3, '.', ''),
            ':prev' => number_format($stockAnterior, 3, '.', ''),
            ':post' => number_format($stockNuevo, 3, '.', ''),
            ':obs'  => $observaciones,
            ':ref'  => $refProveedor,
        ]);
        $movId = (int)$pdo->lastInsertId();

        $pdo->commit();

        responderJson(201, [
            'movimiento_id'  => $movId,
            'insumo_id'      => $insumoId,
            'insumo_nombre'  => $insumo['nombre'],
            'stock_anterior' => $stockAnterior,
            'stock_nuevo'    => $stockNuevo,
            'nuevo_estado'   => $nuevoEstado,
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        responderJson(500, ['error' => 'Error al registrar la entrada de insumo']);
    }
}

// =========================================================================
// 9. POST /api/mermas — Registro de Merma con Motivo (RF-07, CA-07.1..07.3)
// =========================================================================
if ($ruta === '/mermas' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso denegado: solo el Gerente puede registrar mermas'
    );

    $insumoId       = (int)($body['insumo_id'] ?? 0);
    $cantidadRaw    = $body['cantidad'] ?? null;
    $motivo         = trim((string)($body['motivo'] ?? ''));
    $observaciones  = trim((string)($body['observaciones'] ?? ''));
    $motivosValidos = ['caducidad', 'dano', 'preparacion', 'otro'];

    if (!in_array($motivo, $motivosValidos, true)) {
        responderJson(400, [
            'error' => 'El motivo de la merma es obligatorio y debe ser: caducidad, dano, preparacion u otro',
        ]);
    }

    if ($insumoId <= 0 || !is_numeric($cantidadRaw) || (float)$cantidadRaw <= 0) {
        responderJson(400, [
            'error' => 'Cantidad debe ser mayor a 0 y motivo válido (caducidad, dano, preparacion, otro)',
        ]);
    }

    $cantidad = round((float)$cantidadRaw, 3);

    $pdo->beginTransaction();
    try {
        $stmtIns = $pdo->prepare(
            "SELECT id, nombre, stock_actual, stock_minimo FROM " . TABLA_INSUMOS . " WHERE id = :id AND activo = 1 FOR UPDATE"
        );
        $stmtIns->execute([':id' => $insumoId]);
        $insumo = $stmtIns->fetch();

        if (!$insumo) {
            $pdo->rollBack();
            responderJson(404, ['error' => 'Insumo no encontrado o inactivo']);
        }

        $stockAnterior = round((float)$insumo['stock_actual'], 3);
        $stockMinimo   = round((float)$insumo['stock_minimo'], 3);
        $stockNuevo    = round($stockAnterior - $cantidad, 3);
        $nuevoEstado   = calcularEstadoInsumo($stockNuevo, $stockMinimo);

        $stmtUpd = $pdo->prepare(
            "UPDATE " . TABLA_INSUMOS . " SET stock_actual = :nuevo, estado = :estado WHERE id = :id"
        );
        $stmtUpd->execute([
            ':nuevo'  => number_format($stockNuevo, 3, '.', ''),
            ':estado' => $nuevoEstado,
            ':id'     => $insumoId,
        ]);

        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, motivo, observaciones)
             VALUES (:iid, :uid, 'MERMA', :cant, :prev, :post, :mot, :obs)"
        );
        $stmtMov->execute([
            ':iid'  => $insumoId,
            ':uid'  => $usuario['id'],
            ':cant' => number_format(-$cantidad, 3, '.', ''),
            ':prev' => number_format($stockAnterior, 3, '.', ''),
            ':post' => number_format($stockNuevo, 3, '.', ''),
            ':mot'  => $motivo,
            ':obs'  => $observaciones ?: "Merma por $motivo",
        ]);
        $movId = (int)$pdo->lastInsertId();

        $pdo->commit();

        responderJson(201, [
            'movimiento_id'       => $movId,
            'insumo_id'           => $insumoId,
            'insumo_nombre'       => $insumo['nombre'],
            'cantidad_descontada' => $cantidad,
            'stock_anterior'      => $stockAnterior,
            'stock_nuevo'         => $stockNuevo,
            'nuevo_estado'        => $nuevoEstado,
            'fecha_hora'          => gmdate('Y-m-d\TH:i:s\Z'),
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        responderJson(500, ['error' => 'Error al registrar la merma']);
    }
}

/**
 * Construye cláusula WHERE y parámetros para filtrar movimientos (compartido entre RF-10 y RF-16)
 */
function construirFiltroMovimientos(array $query): array
{
    $fechaInicio = trim((string)($query['fecha_inicio'] ?? ''));
    $fechaFin    = trim((string)($query['fecha_fin'] ?? ''));
    $tipo        = strtoupper(trim((string)($query['tipo'] ?? '')));
    $insumoId    = (int)($query['insumo_id'] ?? 0);

    $conds  = [];
    $params = [];

    if ($fechaInicio !== '') {
        $conds[]         = 'm.fecha_hora >= :fini';
        $params[':fini'] = $fechaInicio . ' 00:00:00';
    }
    if ($fechaFin !== '') {
        $conds[]         = 'm.fecha_hora <= :ffin';
        $params[':ffin'] = $fechaFin . ' 23:59:59';
    }
    if (in_array($tipo, ['VENTA', 'ENTRADA', 'MERMA', 'AJUSTE', 'RECEPCION'], true)) {
        $conds[]         = 'm.tipo_movimiento = :tipo';
        $params[':tipo'] = $tipo;
    }
    if ($insumoId > 0) {
        $conds[]        = 'm.insumo_id = :iid';
        $params[':iid'] = $insumoId;
    }

    $whereSql = count($conds) > 0 ? ('WHERE ' . implode(' AND ', $conds)) : '';
    return [$whereSql, $params];
}

/**
 * Helper compartido para construir el reporte analítico de consumo y merma (RF-15 y RF-16)
 */
function construirReporteConsumoMerma(PDO $pdo, string $fechaInicio, string $fechaFin): array
{
    $conds  = [];
    $params = [];
    if ($fechaInicio !== '') {
        $conds[]         = 'm.fecha_hora >= :fini';
        $params[':fini'] = $fechaInicio . ' 00:00:00';
    }
    if ($fechaFin !== '') {
        $conds[]         = 'm.fecha_hora <= :ffin';
        $params[':ffin'] = $fechaFin . ' 23:59:59';
    }
    $whereSql = count($conds) > 0 ? ('WHERE ' . implode(' AND ', $conds)) : '';

    $sqlInsumos = "SELECT i.id AS insumo_id, i.nombre AS insumo_nombre, i.unidad_medida, i.categoria,
                          COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'VENTA' THEN ABS(m.cantidad) ELSE 0 END), 0) AS consumo_ventas,
                          COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'MERMA' THEN ABS(m.cantidad) ELSE 0 END), 0) AS total_merma,
                          COALESCE(SUM(CASE WHEN m.tipo_movimiento IN ('ENTRADA', 'RECEPCION') THEN m.cantidad ELSE 0 END), 0) AS total_entradas,
                          COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'AJUSTE' THEN m.cantidad ELSE 0 END), 0) AS neto_ajustes
                   FROM " . TABLA_MOVIMIENTOS . " m
                   INNER JOIN " . TABLA_INSUMOS . " i ON i.id = m.insumo_id
                   $whereSql
                   GROUP BY i.id, i.nombre, i.unidad_medida, i.categoria
                   ORDER BY total_merma DESC, consumo_ventas DESC, i.nombre ASC";

    $stmtIns = $pdo->prepare($sqlInsumos);
    $stmtIns->execute($params);
    $rowsIns = $stmtIns->fetchAll();

    $resumenInsumos = array_map(static function (array $r): array {
        $consumo = round((float)$r['consumo_ventas'], 3);
        $merma   = round((float)$r['total_merma'], 3);
        $pct     = $consumo > 0
            ? round(($merma / $consumo) * 100, 2)
            : ($merma > 0 ? 100.0 : 0.0);

        return [
            'insumo_id'        => (int)$r['insumo_id'],
            'insumo_nombre'    => $r['insumo_nombre'],
            'unidad_medida'    => $r['unidad_medida'],
            'categoria'        => $r['categoria'],
            'consumo_ventas'   => $consumo,
            'total_merma'      => $merma,
            'porcentaje_merma' => $pct,
            'total_entradas'   => round((float)$r['total_entradas'], 3),
            'neto_ajustes'     => round((float)$r['neto_ajustes'], 3),
        ];
    }, $rowsIns);

    $condsMotivo   = $conds;
    $condsMotivo[] = "m.tipo_movimiento = 'MERMA'";
    $whereMotivo   = 'WHERE ' . implode(' AND ', $condsMotivo);

    $sqlMotivos = "SELECT COALESCE(m.motivo, 'otro') AS motivo,
                          COUNT(*) AS eventos,
                          COALESCE(SUM(ABS(m.cantidad)), 0) AS cantidad_total
                   FROM " . TABLA_MOVIMIENTOS . " m
                   $whereMotivo
                   GROUP BY COALESCE(m.motivo, 'otro')
                   ORDER BY cantidad_total DESC";
    $stmtMot = $pdo->prepare($sqlMotivos);
    $stmtMot->execute($params);
    $mermasPorMotivo = array_map(static function (array $r): array {
        return [
            'motivo'         => $r['motivo'],
            'eventos'        => (int)$r['eventos'],
            'cantidad_total' => round((float)$r['cantidad_total'], 3),
        ];
    }, $stmtMot->fetchAll());

    return [
        'resumen_insumos'   => $resumenInsumos,
        'mermas_por_motivo' => $mermasPorMotivo,
    ];
}

/**
 * Helper transaccional para confirmar/conciliar un conteo físico dentro de una transacción activa (RF-17).
 * Recalcula las diferencias contra el stock_actual bloqueado (FOR UPDATE) para resistir ventas concurrentes.
 */
function conciliarConteoEnBd(
    PDO $pdo,
    int $conteoId,
    array $usuarioAprobador,
    array $itemsConteo,
    bool $actualizarDetallesExistentes
): array {
    $stmtLockIns = $pdo->prepare(
        "SELECT id, nombre, unidad_medida, stock_actual, stock_minimo
         FROM " . TABLA_INSUMOS . "
         WHERE id = :id AND activo = 1
         FOR UPDATE"
    );
    $stmtUpdIns = $pdo->prepare(
        "UPDATE " . TABLA_INSUMOS . " SET stock_actual = :nuevo, estado = :estado WHERE id = :id"
    );
    $stmtInsDet = $pdo->prepare(
        "INSERT INTO " . TABLA_CONTEO_DETALLES . "
         (conteo_id, insumo_id, stock_sistema, stock_contado, diferencia, estado_resultante)
         VALUES (:cid, :iid, :sys, :cont, :dif, :est)"
    );
    $stmtUpdDet = $pdo->prepare(
        "UPDATE " . TABLA_CONTEO_DETALLES . "
         SET stock_sistema = :sys, diferencia = :dif, estado_resultante = :est
         WHERE conteo_id = :cid AND insumo_id = :iid"
    );
    $stmtMov = $pdo->prepare(
        "INSERT INTO " . TABLA_MOVIMIENTOS . "
         (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, motivo, observaciones, referencia_id)
         VALUES (:iid, :uid, 'AJUSTE', :cant, :prev, :post, 'conteo_fisico', :obs, :ref)"
    );

    $ajustesGenerados = 0;
    $detallesReporte  = [];

    foreach ($itemsConteo as $item) {
        $insumoId     = (int)$item['insumo_id'];
        $stockContado = round((float)$item['stock_contado'], 3);

        $stmtLockIns->execute([':id' => $insumoId]);
        $insumo = $stmtLockIns->fetch();
        if (!$insumo) {
            throw new RuntimeException("Insumo $insumoId no encontrado o inactivo");
        }

        // Cálculo atómico dentro de la transacción de confirmación (preserva ventas/movimientos ocurridos entre captura y aprobación)
        $stockSistemaVivo = round((float)$insumo['stock_actual'], 3);
        $stockMinimo      = round((float)$insumo['stock_minimo'], 3);
        $stockBaseCaptura = (isset($item['stock_sistema']) && is_numeric($item['stock_sistema']))
            ? round((float)$item['stock_sistema'], 3)
            : $stockSistemaVivo;

        $diferencia          = round($stockContado - $stockBaseCaptura, 3);
        $movsPosteriores     = round($stockSistemaVivo - $stockBaseCaptura, 3);
        $stockFinalAplicado  = round($stockSistemaVivo + $diferencia, 3);
        $estadoResultante    = calcularEstadoInsumo($stockFinalAplicado, $stockMinimo);

        if ($actualizarDetallesExistentes) {
            $stmtUpdDet->execute([
                ':sys' => number_format($stockBaseCaptura, 3, '.', ''),
                ':dif' => number_format($diferencia, 3, '.', ''),
                ':est' => $estadoResultante,
                ':cid' => $conteoId,
                ':iid' => $insumoId,
            ]);
        } else {
            $stmtInsDet->execute([
                ':cid'  => $conteoId,
                ':iid'  => $insumoId,
                ':sys'  => number_format($stockBaseCaptura, 3, '.', ''),
                ':cont' => number_format($stockContado, 3, '.', ''),
                ':dif'  => number_format($diferencia, 3, '.', ''),
                ':est'  => $estadoResultante,
            ]);
        }

        $stmtUpdIns->execute([
            ':nuevo'  => number_format($stockFinalAplicado, 3, '.', ''),
            ':estado' => $estadoResultante,
            ':id'     => $insumoId,
        ]);

        if (abs($diferencia) >= 0.001) {
            $etiqueta = $diferencia < 0 ? 'Faltante' : 'Sobrante';
            $notaMovs = abs($movsPosteriores) >= 0.001
                ? ' · Movs. entre captura y aprobación: ' . ($movsPosteriores > 0 ? '+' : '') . number_format($movsPosteriores, 3, '.', '')
                : '';
            $stmtMov->execute([
                ':iid'  => $insumoId,
                ':uid'  => $usuarioAprobador['id'],
                ':cant' => number_format($diferencia, 3, '.', ''),
                ':prev' => number_format($stockSistemaVivo, 3, '.', ''),
                ':post' => number_format($stockFinalAplicado, 3, '.', ''),
                ':obs'  => "Conciliación Conteo Físico #$conteoId ($etiqueta)$notaMovs",
                ':ref'  => $conteoId,
            ]);
            $ajustesGenerados++;
        }

        $detallesReporte[] = [
            'insumo_id'               => $insumoId,
            'insumo_nombre'           => $insumo['nombre'],
            'unidad_medida'           => $insumo['unidad_medida'],
            'stock_sistema'           => $stockBaseCaptura,
            'stock_contado'           => $stockContado,
            'diferencia'              => $diferencia,
            'movimientos_posteriores' => $movsPosteriores,
            'stock_actual_aplicado'   => $stockFinalAplicado,
            'estado_resultante'       => $estadoResultante,
        ];
    }

    $stmtUpdCab = $pdo->prepare(
        "UPDATE " . TABLA_CONTEOS . "
         SET estado = 'CONFIRMADO', usuario_aprueba_id = :uid, fecha_confirmacion = NOW()
         WHERE id = :cid"
    );
    $stmtUpdCab->execute([
        ':uid' => $usuarioAprobador['id'],
        ':cid' => $conteoId,
    ]);

    // Ordenar reporte con mayores faltantes arriba (diferencia más negativa primero)
    usort($detallesReporte, static function (array $a, array $b): int {
        if ($a['diferencia'] === $b['diferencia']) {
            return strcmp($a['insumo_nombre'], $b['insumo_nombre']);
        }
        return $a['diferencia'] <=> $b['diferencia'];
    });

    return [
        'ajustes_generados' => $ajustesGenerados,
        'detalles'          => $detallesReporte,
    ];
}

// =========================================================================
// 10. /api/movimientos — Libro Mayor Inmutable de Auditoría (RF-10, RNF-06)
// =========================================================================
if (str_starts_with($ruta, '/movimientos')) {
    // CA-10.2 y RNF-06: Prohibido modificar o borrar movimientos históricos
    if ($metodo !== 'GET') {
        responderJson(405, [
            'error' => 'Método no permitido: el historial de movimientos es inmutable y no admite modificaciones ni eliminaciones (RNF-06)',
        ]);
    }

    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede auditar el historial de movimientos'
    );

    [$whereSql, $params] = construirFiltroMovimientos($_GET);
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limite = 25; // CA-10.3: Paginación LIMIT 25 OFFSET 0
    $offset = ($pagina - 1) * $limite;

    $sql = "SELECT m.id, m.fecha_hora, m.tipo_movimiento, m.insumo_id,
                   i.nombre AS insumo_nombre, i.unidad_medida,
                   m.cantidad, m.stock_previo, m.stock_posterior,
                   m.motivo, m.observaciones, m.referencia_id,
                   u.nombre AS usuario_nombre
            FROM " . TABLA_MOVIMIENTOS . " m
            INNER JOIN " . TABLA_INSUMOS . " i ON i.id = m.insumo_id
            LEFT JOIN " . TABLA_USUARIOS . " u ON u.id = m.usuario_id
            $whereSql
            ORDER BY m.fecha_hora DESC, m.id DESC
            LIMIT $limite OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filas = $stmt->fetchAll();

    $salida = array_map(static function (array $r): array {
        return [
            'id'              => (int)$r['id'],
            'fecha_hora'      => $r['fecha_hora'],
            'tipo_movimiento' => $r['tipo_movimiento'],
            'insumo_id'       => (int)$r['insumo_id'],
            'insumo_nombre'   => $r['insumo_nombre'],
            'unidad_medida'   => $r['unidad_medida'],
            'cantidad'        => round((float)$r['cantidad'], 3),
            'stock_previo'    => round((float)$r['stock_previo'], 3),
            'stock_posterior' => round((float)$r['stock_posterior'], 3),
            'motivo'          => $r['motivo'],
            'observaciones'   => $r['observaciones'],
            'referencia_id'   => $r['referencia_id'] !== null ? (int)$r['referencia_id'] : null,
            'usuario_nombre'  => $r['usuario_nombre'] ?: 'Gerente Demo',
        ];
    }, $filas);

    responderJson(200, $salida);
}

// =========================================================================
// 11. /api/conteos — Conteo Físico con Conciliación (RF-17 · Equipo 4)
// =========================================================================
if (preg_match('#^/conteos/(\d+)/confirmar$#', $ruta, $m) && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Acceso denegado: solo el Gerente puede confirmar y conciliar un conteo físico'
    );

    $conteoId = (int)$m[1];
    $pdo->beginTransaction();
    try {
        $stmtCab = $pdo->prepare(
            "SELECT id, estado FROM " . TABLA_CONTEOS . " WHERE id = :id FOR UPDATE"
        );
        $stmtCab->execute([':id' => $conteoId]);
        $cab = $stmtCab->fetch();

        if (!$cab) {
            $pdo->rollBack();
            responderJson(404, ['error' => 'Conteo físico no encontrado']);
        }
        if ($cab['estado'] !== 'PENDIENTE') {
            $pdo->rollBack();
            responderJson(409, ['error' => 'Este conteo físico ya fue procesado (' . $cab['estado'] . ')']);
        }

        $stmtDets = $pdo->prepare(
            "SELECT insumo_id, stock_sistema, stock_contado FROM " . TABLA_CONTEO_DETALLES . " WHERE conteo_id = :cid ORDER BY id ASC"
        );
        $stmtDets->execute([':cid' => $conteoId]);
        $itemsConteo = $stmtDets->fetchAll();

        if (count($itemsConteo) === 0) {
            $pdo->rollBack();
            responderJson(422, ['error' => 'El conteo no contiene insumos registrados']);
        }

        $resultado = conciliarConteoEnBd($pdo, $conteoId, $usuario, $itemsConteo, true);
        $pdo->commit();

        responderJson(200, [
            'conteo_id'         => $conteoId,
            'estado'            => 'CONFIRMADO',
            'ajustes_generados' => $resultado['ajustes_generados'],
            'detalles'          => $resultado['detalles'],
            'mensaje'           => 'Conteo físico conciliado y movimientos AJUSTE registrados',
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al confirmar conteo físico: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al conciliar el conteo físico']);
    }
}

if (preg_match('#^/conteos/(\d+)/rechazar$#', $ruta, $m) && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede rechazar un conteo pendiente'
    );

    $conteoId = (int)$m[1];
    $stmtUpd  = $pdo->prepare(
        "UPDATE " . TABLA_CONTEOS . "
         SET estado = 'RECHAZADO', usuario_aprueba_id = :uid, fecha_confirmacion = NOW()
         WHERE id = :id AND estado = 'PENDIENTE'"
    );
    $stmtUpd->execute([':uid' => $usuario['id'], ':id' => $conteoId]);

    if ($stmtUpd->rowCount() === 0) {
        responderJson(404, ['error' => 'Conteo pendiente no encontrado o ya procesado']);
    }

    responderJson(200, [
        'conteo_id' => $conteoId,
        'estado'    => 'RECHAZADO',
        'mensaje'   => 'Borrador de conteo rechazado; no se realizaron cambios al inventario',
    ]);
}

if (preg_match('#^/conteos/(\d+)$#', $ruta, $m) && $metodo === 'GET') {
    $usuario  = authenticateToken();
    $conteoId = (int)$m[1];

    $stmtCab = $pdo->prepare(
        "SELECT c.*, u1.nombre AS captura_nombre, u2.nombre AS aprueba_nombre
         FROM " . TABLA_CONTEOS . " c
         LEFT JOIN " . TABLA_USUARIOS . " u1 ON u1.id = c.usuario_captura_id
         LEFT JOIN " . TABLA_USUARIOS . " u2 ON u2.id = c.usuario_aprueba_id
         WHERE c.id = :id
         LIMIT 1"
    );
    $stmtCab->execute([':id' => $conteoId]);
    $cab = $stmtCab->fetch();

    if (!$cab) {
        responderJson(404, ['error' => 'Conteo físico no encontrado']);
    }

    $stmtDet = $pdo->prepare(
        "SELECT d.insumo_id, i.nombre AS insumo_nombre, i.unidad_medida,
                d.stock_sistema, d.stock_contado, d.diferencia, d.estado_resultante
         FROM " . TABLA_CONTEO_DETALLES . " d
         INNER JOIN " . TABLA_INSUMOS . " i ON i.id = d.insumo_id
         WHERE d.conteo_id = :cid
         ORDER BY d.diferencia ASC, i.nombre ASC"
    );
    $stmtDet->execute([':cid' => $conteoId]);
    $detalles = array_map(static function (array $r): array {
        return [
            'insumo_id'         => (int)$r['insumo_id'],
            'insumo_nombre'     => $r['insumo_nombre'],
            'unidad_medida'     => $r['unidad_medida'],
            'stock_sistema'     => round((float)$r['stock_sistema'], 3),
            'stock_contado'     => round((float)$r['stock_contado'], 3),
            'diferencia'        => round((float)$r['diferencia'], 3),
            'estado_resultante' => $r['estado_resultante'],
        ];
    }, $stmtDet->fetchAll());

    responderJson(200, [
        'id'                 => (int)$cab['id'],
        'estado'             => $cab['estado'],
        'observaciones'      => $cab['observaciones'],
        'fecha_captura'      => $cab['fecha_captura'],
        'fecha_confirmacion' => $cab['fecha_confirmacion'],
        'captura_nombre'     => $cab['captura_nombre'] ?: 'Usuario',
        'aprueba_nombre'     => $cab['aprueba_nombre'],
        'detalles'           => $detalles,
    ]);
}

if ($ruta === '/conteos' && $metodo === 'GET') {
    $usuario = authenticateToken();

    $sql = "SELECT c.id, c.estado, c.observaciones, c.fecha_captura, c.fecha_confirmacion,
                   u1.nombre AS captura_nombre, u2.nombre AS aprueba_nombre,
                   COUNT(d.id) AS total_insumos,
                   SUM(CASE WHEN ABS(d.diferencia) >= 0.001 THEN 1 ELSE 0 END) AS con_diferencia,
                   SUM(CASE WHEN d.diferencia <= -0.001 THEN 1 ELSE 0 END) AS faltantes_count,
                   SUM(CASE WHEN d.diferencia >= 0.001 THEN 1 ELSE 0 END) AS sobrantes_count
            FROM " . TABLA_CONTEOS . " c
            LEFT JOIN " . TABLA_USUARIOS . " u1 ON u1.id = c.usuario_captura_id
            LEFT JOIN " . TABLA_USUARIOS . " u2 ON u2.id = c.usuario_aprueba_id
            LEFT JOIN " . TABLA_CONTEO_DETALLES . " d ON d.conteo_id = c.id
            GROUP BY c.id, c.estado, c.observaciones, c.fecha_captura, c.fecha_confirmacion, u1.nombre, u2.nombre
            ORDER BY FIELD(c.estado, 'PENDIENTE', 'CONFIRMADO', 'RECHAZADO'), c.fecha_captura DESC, c.id DESC
            LIMIT 25";

    $rows = $pdo->query($sql)->fetchAll();
    $salida = array_map(static function (array $r): array {
        return [
            'id'                 => (int)$r['id'],
            'estado'             => $r['estado'],
            'observaciones'      => $r['observaciones'],
            'fecha_captura'      => $r['fecha_captura'],
            'fecha_confirmacion' => $r['fecha_confirmacion'],
            'captura_nombre'     => $r['captura_nombre'] ?: 'Usuario',
            'aprueba_nombre'     => $r['aprueba_nombre'],
            'total_insumos'      => (int)$r['total_insumos'],
            'con_diferencia'     => (int)$r['con_diferencia'],
            'faltantes_count'    => (int)$r['faltantes_count'],
            'sobrantes_count'    => (int)$r['sobrantes_count'],
        ];
    }, $rows);

    responderJson(200, $salida);
}

if ($ruta === '/conteos' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole($usuario, ['GERENTE', 'COLABORADOR'], 'Permisos insuficientes');

    $itemsRaw      = $body['items'] ?? [];
    $observaciones = trim((string)($body['observaciones'] ?? 'Conteo físico de bodega'));
    $esGerente     = (strtoupper($usuario['rol']) === 'GERENTE');
    $confirmarDir  = $esGerente ? ($body['confirmar_directo'] ?? true) === true : false;

    if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
        responderJson(400, ['error' => 'Debes capturar al menos un insumo en el conteo físico']);
    }

    $itemsUnicos = [];
    foreach ($itemsRaw as $item) {
        $iid  = (int)($item['insumo_id'] ?? 0);
        $cant = $item['stock_contado'] ?? null;

        if ($iid <= 0 || !is_numeric($cant) || (float)$cant < 0) {
            responderJson(400, ['error' => 'Todos los conteos deben corresponder a insumos válidos con cantidad mayor o igual a 0']);
        }
        if (isset($itemsUnicos[$iid])) {
            responderJson(400, ['error' => 'Insumo duplicado en el mismo conteo físico']);
        }
        $itemsUnicos[$iid] = [
            'insumo_id'     => $iid,
            'stock_contado' => round((float)$cant, 3),
            'stock_sistema' => (isset($item['stock_sistema']) && is_numeric($item['stock_sistema']))
                ? round((float)$item['stock_sistema'], 3)
                : null,
        ];
    }

    // Validar que los insumos existan y estén activos
    $stmtCheck = $pdo->prepare(
        "SELECT id, nombre, unidad_medida, stock_actual, stock_minimo
         FROM " . TABLA_INSUMOS . " WHERE id = :id AND activo = 1 LIMIT 1"
    );
    foreach ($itemsUnicos as $iid => $datos) {
        $stmtCheck->execute([':id' => $iid]);
        if (!$stmtCheck->fetch()) {
            responderJson(404, ['error' => "Insumo ID $iid no encontrado o inactivo"]);
        }
    }

    $pdo->beginTransaction();
    try {
        $stmtCab = $pdo->prepare(
            "INSERT INTO " . TABLA_CONTEOS . " (usuario_captura_id, estado, observaciones)
             VALUES (:uid, 'PENDIENTE', :obs)"
        );
        $stmtCab->execute([
            ':uid' => $usuario['id'],
            ':obs' => $observaciones ?: 'Conteo físico de bodega',
        ]);
        $conteoId = (int)$pdo->lastInsertId();

        if ($confirmarDir) {
            $resultado = conciliarConteoEnBd($pdo, $conteoId, $usuario, array_values($itemsUnicos), false);
            $pdo->commit();

            responderJson(201, [
                'conteo_id'         => $conteoId,
                'estado'            => 'CONFIRMADO',
                'ajustes_generados' => $resultado['ajustes_generados'],
                'detalles'          => $resultado['detalles'],
                'mensaje'           => 'Conteo físico confirmado y conciliado en inventario',
            ]);
        } else {
            $stmtDet = $pdo->prepare(
                "INSERT INTO " . TABLA_CONTEO_DETALLES . "
                 (conteo_id, insumo_id, stock_sistema, stock_contado, diferencia, estado_resultante)
                 VALUES (:cid, :iid, :sys, :cont, :dif, :est)"
            );
            $detallesPrelim = [];
            foreach ($itemsUnicos as $item) {
                $stmtCheck->execute([':id' => $item['insumo_id']]);
                $ins = $stmtCheck->fetch();

                $stockSys = round((float)$ins['stock_actual'], 3);
                $stockCnt = $item['stock_contado'];
                $dif      = round($stockCnt - $stockSys, 3);
                $estRes   = calcularEstadoInsumo($stockCnt, (float)$ins['stock_minimo']);

                $stmtDet->execute([
                    ':cid'  => $conteoId,
                    ':iid'  => $item['insumo_id'],
                    ':sys'  => number_format($stockSys, 3, '.', ''),
                    ':cont' => number_format($stockCnt, 3, '.', ''),
                    ':dif'  => number_format($dif, 3, '.', ''),
                    ':est'  => $estRes,
                ]);
                $detallesPrelim[] = [
                    'insumo_id'         => $item['insumo_id'],
                    'insumo_nombre'     => $ins['nombre'],
                    'unidad_medida'     => $ins['unidad_medida'],
                    'stock_sistema'     => $stockSys,
                    'stock_contado'     => $stockCnt,
                    'diferencia'        => $dif,
                    'estado_resultante' => $estRes,
                ];
            }
            $pdo->commit();

            usort($detallesPrelim, static fn(array $a, array $b): int => $a['diferencia'] <=> $b['diferencia']);

            responderJson(201, [
                'conteo_id'         => $conteoId,
                'estado'            => 'PENDIENTE',
                'ajustes_generados' => 0,
                'detalles'          => $detallesPrelim,
                'mensaje'           => 'Borrador de conteo guardado para aprobación del Gerente',
            ]);
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al registrar conteo físico: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al registrar el conteo físico']);
    }
}

// =========================================================================
// 12. /api/reabastecimiento/sugerido y /api/pedidos (RF-11, RF-12, RF-13)
// =========================================================================
if ($ruta === '/reabastecimiento/sugerido' && $metodo === 'GET') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede consultar sugerencias de reabastecimiento'
    );

    $dias = max(1, min(90, (int)($_GET['dias_cobertura'] ?? 7)));

    $stmtIns = $pdo->query(
        "SELECT id, nombre, unidad_medida, categoria, stock_actual, stock_minimo, estado
         FROM " . TABLA_INSUMOS . "
         WHERE activo = 1 AND (estado IN ('bajo', 'discrepancia') OR stock_actual <= stock_minimo)
         ORDER BY FIELD(estado, 'discrepancia', 'bajo', 'normal'), nombre ASC"
    );
    $insumosBajos = $stmtIns->fetchAll();

    $stmtConsumo = $pdo->query(
        "SELECT insumo_id, COALESCE(SUM(ABS(cantidad)), 0) AS consumo_7d
         FROM " . TABLA_MOVIMIENTOS . "
         WHERE tipo_movimiento IN ('VENTA', 'MERMA')
           AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY insumo_id"
    );
    $mapaConsumo = [];
    foreach ($stmtConsumo->fetchAll() as $row) {
        $mapaConsumo[(int)$row['insumo_id']] = round((float)$row['consumo_7d'], 3);
    }

    $sugerencias = array_map(static function (array $ins) use ($mapaConsumo, $dias): array {
        $iid        = (int)$ins['id'];
        $stockAct   = round((float)$ins['stock_actual'], 3);
        $stockMin   = round((float)$ins['stock_minimo'], 3);
        $consumo7d  = $mapaConsumo[$iid] ?? 0.0;
        $promDiario = round($consumo7d / 7, 3);

        // Fórmula RF-11: (consumo promedio diario 7d * días cobertura) - stock actual, ceil hacia unidad
        $bruto = ($promDiario * $dias) - $stockAct;
        // Si no hubo movimientos en los últimos 7 días o la fórmula queda debajo de la reposición al mínimo, asegurar piso práctico
        $reposicionMinimo = ($stockMin * 2) - $stockAct;
        $cantidadCalc     = max($bruto, $reposicionMinimo, 1.0);
        $cantidadSugerida = (int)ceil($cantidadCalc);

        return [
            'insumo_id'         => $iid,
            'nombre'            => $ins['nombre'],
            'unidad_medida'     => $ins['unidad_medida'],
            'categoria'         => $ins['categoria'],
            'estado'            => $ins['estado'],
            'stock_actual'      => $stockAct,
            'stock_minimo'      => $stockMin,
            'consumo_7d'        => $consumo7d,
            'consumo_diario'    => $promDiario,
            'dias_cobertura'    => $dias,
            'cantidad_sugerida' => (float)$cantidadSugerida,
        ];
    }, $insumosBajos);

    responderJson(200, $sugerencias);
}

if (preg_match('#^/pedidos/(\d+)/recibir$#', $ruta, $m) && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['COLABORADOR', 'GERENTE'],
        'Permisos insuficientes para recibir pedidos'
    );

    $pedidoId      = (int)$m[1];
    $itemsRaw      = $body['items'] ?? [];
    $observaciones = trim((string)($body['observaciones'] ?? 'Recepción de pedido en sucursal'));

    if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
        responderJson(400, ['error' => 'Debes indicar las cantidades recibidas para al menos un insumo']);
    }

    $recibidos = [];
    foreach ($itemsRaw as $item) {
        $iid  = (int)($item['insumo_id'] ?? 0);
        $cant = $item['cantidad_recibida'] ?? null;
        if ($iid <= 0 || !is_numeric($cant) || (float)$cant < 0) {
            responderJson(400, ['error' => 'Cada cantidad recibida debe ser numérica mayor o igual a 0']);
        }
        $recibidos[$iid] = round((float)$cant, 3);
    }

    $pdo->beginTransaction();
    try {
        $stmtPed = $pdo->prepare(
            "SELECT id, estado FROM " . TABLA_PEDIDOS . " WHERE id = :id FOR UPDATE"
        );
        $stmtPed->execute([':id' => $pedidoId]);
        $ped = $stmtPed->fetch();

        if (!$ped) {
            $pdo->rollBack();
            responderJson(404, ['error' => 'Pedido no encontrado']);
        }
        if ($ped['estado'] !== 'PENDIENTE') {
            $pdo->rollBack();
            responderJson(409, ['error' => 'Este pedido ya fue marcado como ' . $ped['estado']]);
        }

        $stmtDetPed = $pdo->prepare(
            "SELECT insumo_id, cantidad_pedida FROM " . TABLA_PEDIDO_DETALLES . " WHERE pedido_id = :pid"
        );
        $stmtDetPed->execute([':pid' => $pedidoId]);
        $detallesPed = $stmtDetPed->fetchAll();

        $stmtUpdDet = $pdo->prepare(
            "UPDATE " . TABLA_PEDIDO_DETALLES . " SET cantidad_recibida = :rec WHERE pedido_id = :pid AND insumo_id = :iid"
        );
        $stmtLockIns = $pdo->prepare(
            "SELECT id, nombre, stock_actual, stock_minimo FROM " . TABLA_INSUMOS . " WHERE id = :id FOR UPDATE"
        );
        $stmtUpdIns = $pdo->prepare(
            "UPDATE " . TABLA_INSUMOS . " SET stock_actual = :nuevo, estado = :estado WHERE id = :id"
        );
        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, observaciones, referencia_id)
             VALUES (:iid, :uid, 'RECEPCION', :cant, :prev, :post, :obs, :ref)"
        );

        $entradasGeneradas = 0;
        foreach ($detallesPed as $det) {
            $iid          = (int)$det['insumo_id'];
            $cantRecibida = $recibidos[$iid] ?? 0.0;

            $stmtUpdDet->execute([
                ':rec' => number_format($cantRecibida, 3, '.', ''),
                ':pid' => $pedidoId,
                ':iid' => $iid,
            ]);

            if ($cantRecibida > 0) {
                $stmtLockIns->execute([':id' => $iid]);
                $ins = $stmtLockIns->fetch();
                if ($ins) {
                    $stockPrev  = round((float)$ins['stock_actual'], 3);
                    $stockMin   = round((float)$ins['stock_minimo'], 3);
                    $stockNuevo = round($stockPrev + $cantRecibida, 3);
                    $estadoNvo  = calcularEstadoInsumo($stockNuevo, $stockMin);

                    $stmtUpdIns->execute([
                        ':nuevo'  => number_format($stockNuevo, 3, '.', ''),
                        ':estado' => $estadoNvo,
                        ':id'     => $iid,
                    ]);

                    $stmtMov->execute([
                        ':iid'  => $iid,
                        ':uid'  => $usuario['id'],
                        ':cant' => number_format($cantRecibida, 3, '.', ''),
                        ':prev' => number_format($stockPrev, 3, '.', ''),
                        ':post' => number_format($stockNuevo, 3, '.', ''),
                        ':obs'  => "Recepción de Pedido #$pedidoId" . ($observaciones ? ": $observaciones" : ''),
                        ':ref'  => $pedidoId,
                    ]);
                    $entradasGeneradas++;
                }
            }
        }

        $stmtUpdPed = $pdo->prepare(
            "UPDATE " . TABLA_PEDIDOS . "
             SET estado = 'RECIBIDO', usuario_recibe_id = :uid, fecha_recepcion = NOW()
             WHERE id = :id"
        );
        $stmtUpdPed->execute([':uid' => $usuario['id'], ':id' => $pedidoId]);

        $pdo->commit();

        responderJson(200, [
            'pedido_id'          => $pedidoId,
            'estado'             => 'RECIBIDO',
            'entradas_generadas' => $entradasGeneradas,
            'mensaje'            => 'Recepción confirmada e inventario actualizado con movimientos RECEPCION',
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al recibir pedido: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al confirmar la recepción del pedido']);
    }
}

if ($ruta === '/pedidos' && $metodo === 'GET') {
    $usuario = authenticateToken();

    $pedidos = $pdo->query(
        "SELECT p.id, p.dias_cobertura, p.estado, p.observaciones, p.fecha_creacion, p.fecha_recepcion,
                u1.nombre AS crea_nombre, u2.nombre AS recibe_nombre
         FROM " . TABLA_PEDIDOS . " p
         LEFT JOIN " . TABLA_USUARIOS . " u1 ON u1.id = p.usuario_crea_id
         LEFT JOIN " . TABLA_USUARIOS . " u2 ON u2.id = p.usuario_recibe_id
         ORDER BY FIELD(p.estado, 'PENDIENTE', 'RECIBIDO', 'CANCELADO'), p.fecha_creacion DESC, p.id DESC
         LIMIT 25"
    )->fetchAll();

    $detallesRaw = $pdo->query(
        "SELECT pd.pedido_id, pd.insumo_id, pd.cantidad_pedida, pd.cantidad_recibida,
                i.nombre AS insumo_nombre, i.unidad_medida, i.stock_actual
         FROM " . TABLA_PEDIDO_DETALLES . " pd
         INNER JOIN " . TABLA_INSUMOS . " i ON i.id = pd.insumo_id
         ORDER BY pd.id ASC"
    )->fetchAll();

    $mapaDetalles = [];
    foreach ($detallesRaw as $d) {
        $pid = (int)$d['pedido_id'];
        $mapaDetalles[$pid][] = [
            'insumo_id'         => (int)$d['insumo_id'],
            'insumo_nombre'     => $d['insumo_nombre'],
            'unidad_medida'     => $d['unidad_medida'],
            'stock_actual'      => round((float)$d['stock_actual'], 3),
            'cantidad_pedida'   => round((float)$d['cantidad_pedida'], 3),
            'cantidad_recibida' => $d['cantidad_recibida'] !== null ? round((float)$d['cantidad_recibida'], 3) : null,
        ];
    }

    $salida = array_map(static function (array $p) use ($mapaDetalles): array {
        $pid = (int)$p['id'];
        return [
            'id'              => $pid,
            'dias_cobertura'  => (int)$p['dias_cobertura'],
            'estado'          => $p['estado'],
            'observaciones'   => $p['observaciones'],
            'fecha_creacion'  => $p['fecha_creacion'],
            'fecha_recepcion' => $p['fecha_recepcion'],
            'crea_nombre'     => $p['crea_nombre'] ?: 'Gerente',
            'recibe_nombre'   => $p['recibe_nombre'],
            'items'           => $mapaDetalles[$pid] ?? [],
        ];
    }, $pedidos);

    responderJson(200, $salida);
}

if ($ruta === '/pedidos' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede generar pedidos de reabastecimiento'
    );

    $itemsRaw      = $body['items'] ?? [];
    $diasCobertura = max(1, min(90, (int)($body['dias_cobertura'] ?? 7)));
    $observaciones = trim((string)($body['observaciones'] ?? 'Pedido de reabastecimiento'));

    if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
        responderJson(400, ['error' => 'El pedido debe incluir al menos un insumo con cantidad mayor a cero']);
    }

    $itemsUnicos = [];
    $stmtChk     = $pdo->prepare("SELECT id FROM " . TABLA_INSUMOS . " WHERE id = :id AND activo = 1 LIMIT 1");
    foreach ($itemsRaw as $item) {
        $iid  = (int)($item['insumo_id'] ?? 0);
        $cant = $item['cantidad_pedida'] ?? null;
        if ($iid <= 0 || !is_numeric($cant) || (float)$cant <= 0) {
            responderJson(400, ['error' => 'Cada insumo del pedido debe tener una cantidad positiva mayor a cero']);
        }
        if (isset($itemsUnicos[$iid])) {
            responderJson(400, ['error' => 'Insumo duplicado en el pedido']);
        }
        $stmtChk->execute([':id' => $iid]);
        if (!$stmtChk->fetch()) {
            responderJson(404, ['error' => "Insumo ID $iid no encontrado o inactivo"]);
        }
        $itemsUnicos[$iid] = round((float)$cant, 3);
    }

    $pdo->beginTransaction();
    try {
        $stmtCab = $pdo->prepare(
            "INSERT INTO " . TABLA_PEDIDOS . " (usuario_crea_id, dias_cobertura, estado, observaciones)
             VALUES (:uid, :dias, 'PENDIENTE', :obs)"
        );
        $stmtCab->execute([
            ':uid'  => $usuario['id'],
            ':dias' => $diasCobertura,
            ':obs'  => $observaciones ?: 'Pedido de reabastecimiento',
        ]);
        $pedidoId = (int)$pdo->lastInsertId();

        $stmtDet = $pdo->prepare(
            "INSERT INTO " . TABLA_PEDIDO_DETALLES . " (pedido_id, insumo_id, cantidad_pedida)
             VALUES (:pid, :iid, :cant)"
        );
        foreach ($itemsUnicos as $iid => $cant) {
            $stmtDet->execute([
                ':pid'  => $pedidoId,
                ':iid'  => $iid,
                ':cant' => number_format($cant, 3, '.', ''),
            ]);
        }

        $pdo->commit();

        responderJson(201, [
            'pedido_id'      => $pedidoId,
            'estado'         => 'PENDIENTE',
            'total_insumos'  => count($itemsUnicos),
            'dias_cobertura' => $diasCobertura,
            'mensaje'        => "Pedido #$pedidoId guardado en estado PENDIENTE para su recepción",
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al crear pedido: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al crear el pedido de reabastecimiento']);
    }
}

// =========================================================================
// 13. POST /api/ventas/importar-csv — Importación Masiva de Ventas CSV (RF-14)
// =========================================================================
if ($ruta === '/ventas/importar-csv' && $metodo === 'POST') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede importar ventas desde CSV'
    );

    $csvContenido = '';
    if (!empty($_FILES['archivo']) && is_array($_FILES['archivo'])) {
        $file = $_FILES['archivo'];
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            responderJson(400, ['error' => 'Error al recibir el archivo CSV']);
        }
        if ((int)$file['size'] > 2 * 1024 * 1024) {
            responderJson(400, ['error' => 'El archivo CSV supera el límite de 2 MB']);
        }
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true)) {
            responderJson(400, ['error' => 'Solo se permiten archivos .csv o .txt']);
        }
        $csvContenido = (string)file_get_contents((string)$file['tmp_name']);
    } else {
        $csvContenido = (string)($body['csv'] ?? '');
    }

    // Quitar BOM UTF-8 si existe
    $csvContenido = preg_replace('/^\xEF\xBB\xBF/', '', $csvContenido) ?? $csvContenido;
    $lineasRaw    = preg_split('/\r\n|\r|\n/', trim($csvContenido)) ?: [];

    if (count($lineasRaw) === 0 || trim($csvContenido) === '') {
        responderJson(400, ['error' => 'El contenido CSV está vacío']);
    }

    // Cargar catálogo activo de productos y recetas
    $productosRows = $pdo->query(
        "SELECT id, nombre, precio FROM " . TABLA_PRODUCTOS . " WHERE activo = 1"
    )->fetchAll();
    $recetasRows = $pdo->query(
        "SELECT producto_id, insumo_id, cantidad FROM " . TABLA_RECETAS
    )->fetchAll();

    $recetasPorProd = [];
    foreach ($recetasRows as $r) {
        $recetasPorProd[(int)$r['producto_id']][] = [
            'insumo_id' => (int)$r['insumo_id'],
            'cantidad'  => (float)$r['cantidad'],
        ];
    }

    $mapaProductos = [];
    foreach ($productosRows as $p) {
        $clave = mb_strtolower(trim($p['nombre']), 'UTF-8');
        $mapaProductos[$clave] = [
            'id'     => (int)$p['id'],
            'nombre' => $p['nombre'],
            'precio' => round((float)$p['precio'], 2),
        ];
    }

    $filasValidas    = [];
    $filasRechazadas = [];

    foreach ($lineasRaw as $idx => $linea) {
        $numFila = $idx + 1;
        $linea   = trim($linea);
        if ($linea === '') {
            continue;
        }

        $cols = str_getcsv($linea);
        $prodNombre = trim((string)($cols[0] ?? ''));
        $cantRaw    = trim((string)($cols[1] ?? ''));
        $fechaRaw   = trim((string)($cols[2] ?? ''));

        // Saltar fila de encabezado si aplica
        if ($numFila === 1 && mb_strtolower($prodNombre, 'UTF-8') === 'producto') {
            continue;
        }

        if ($prodNombre === '' || $cantRaw === '') {
            $filasRechazadas[] = [
                'fila'     => $numFila,
                'producto' => $prodNombre ?: '(vacío)',
                'motivo'   => 'Faltan columnas obligatorias (producto, cantidad)',
            ];
            continue;
        }

        if (!is_numeric($cantRaw) || (int)$cantRaw <= 0 || (float)$cantRaw != (int)$cantRaw) {
            $filasRechazadas[] = [
                'fila'     => $numFila,
                'producto' => $prodNombre,
                'motivo'   => "Cantidad inválida ($cantRaw); debe ser entero mayor a 0",
            ];
            continue;
        }
        $cantidad = (int)$cantRaw;

        $fechaSql = null;
        if ($fechaRaw !== '') {
            $dtFull = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
            $dtDate = DateTime::createFromFormat('Y-m-d', $fechaRaw);
            if ($dtFull && $dtFull->format('Y-m-d H:i:s') === $fechaRaw) {
                $fechaSql = $dtFull->format('Y-m-d H:i:s');
            } elseif ($dtDate && $dtDate->format('Y-m-d') === $fechaRaw) {
                $fechaSql = $dtDate->format('Y-m-d') . ' 12:00:00';
            } else {
                $filasRechazadas[] = [
                    'fila'     => $numFila,
                    'producto' => $prodNombre,
                    'motivo'   => "Formato de fecha inválido ($fechaRaw); usa YYYY-MM-DD",
                ];
                continue;
            }
        }

        $claveProd = mb_strtolower($prodNombre, 'UTF-8');
        if (!isset($mapaProductos[$claveProd])) {
            $filasRechazadas[] = [
                'fila'     => $numFila,
                'producto' => $prodNombre,
                'motivo'   => 'Producto inexistente o inactivo en el menú',
            ];
            continue;
        }

        $prod = $mapaProductos[$claveProd];
        $rec  = $recetasPorProd[$prod['id']] ?? [];
        if (count($rec) === 0) {
            $filasRechazadas[] = [
                'fila'     => $numFila,
                'producto' => $prod['nombre'],
                'motivo'   => 'El producto no tiene receta configurada',
            ];
            continue;
        }

        $filasValidas[] = [
            'fila'       => $numFila,
            'producto'   => $prod,
            'cantidad'   => $cantidad,
            'fecha_sql'  => $fechaSql,
            'receta'     => $rec,
        ];
    }

    if (count($filasValidas) === 0) {
        responderJson(422, [
            'ventas_importadas' => 0,
            'filas_rechazadas'  => $filasRechazadas,
            'error'             => 'No se encontró ninguna fila válida para importar',
        ]);
    }

    // Agrupar filas válidas por fecha (o 'ahora') para crear ventas ordenadas en una transacción ACID
    $grupos = [];
    foreach ($filasValidas as $fv) {
        $claveFecha = $fv['fecha_sql'] ?? '__NOW__';
        $grupos[$claveFecha][] = $fv;
    }

    $pdo->beginTransaction();
    try {
        $ventasCreadas      = [];
        $todasDiscrepancias = [];

        foreach ($grupos as $claveFecha => $lista) {
            $consumoGrupo = [];
            $lineasGrupo  = [];
            $totalGrupo   = 0.0;

            foreach ($lista as $item) {
                $prod = $item['producto'];
                $cant = $item['cantidad'];
                foreach ($item['receta'] as $ing) {
                    $iid = $ing['insumo_id'];
                    $consumoGrupo[$iid] = round(($consumoGrupo[$iid] ?? 0.0) + ($ing['cantidad'] * $cant), 3);
                }
                $sub = round($prod['precio'] * $cant, 2);
                $totalGrupo = round($totalGrupo + $sub, 2);
                $lineasGrupo[] = [
                    'producto_id'     => $prod['id'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod['precio'],
                    'subtotal'        => $sub,
                ];
            }

            $fechaParam = $claveFecha === '__NOW__' ? null : $claveFecha;
            $resVenta   = ejecutarVentaTransaccional($pdo, $usuario, $lineasGrupo, $consumoGrupo, $totalGrupo, $fechaParam);
            $ventasCreadas[] = $resVenta['venta_id'];
            foreach ($resVenta['discrepancias_detectadas'] as $d) {
                $todasDiscrepancias[] = $d;
            }
        }

        $pdo->commit();

        responderJson(201, [
            'ventas_importadas'        => count($filasValidas),
            'folios_venta'             => $ventasCreadas,
            'filas_rechazadas'         => $filasRechazadas,
            'discrepancias_detectadas' => $todasDiscrepancias,
            'mensaje'                  => 'Importación CSV procesada con descuento automático de recetas',
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al importar ventas CSV: ' . $e->getMessage());
        responderJson(500, ['error' => 'Error al procesar la importación CSV de ventas']);
    }
}

// =========================================================================
// 14. GET /api/reportes/consumo-merma — Reporte Analítico por Periodo (RF-15)
// =========================================================================
if ($ruta === '/reportes/consumo-merma' && $metodo === 'GET') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede consultar reportes analíticos'
    );

    $fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
    $fechaFin    = trim((string)($_GET['fecha_fin'] ?? ''));

    $reporte = construirReporteConsumoMerma($pdo, $fechaInicio, $fechaFin);
    responderJson(200, $reporte);
}

// =========================================================================
// 15. GET /api/exportar/csv — Exportación CSV UTF-8 BOM (RF-16)
// =========================================================================
if ($ruta === '/exportar/csv' && $metodo === 'GET') {
    $usuario = authenticateToken();
    authorizeRole(
        $usuario,
        ['GERENTE'],
        'Solo el Gerente puede exportar información en CSV'
    );

    $tipoExport  = strtolower(trim((string)($_GET['tipo'] ?? 'movimientos')));
    $fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
    $fechaFin    = trim((string)($_GET['fecha_fin'] ?? ''));

    $sanitizarCelda = static function (mixed $val): string {
        $str = (string)($val ?? '');
        if ($str === '') {
            return '';
        }
        $primer = $str[0];
        if (in_array($primer, ['=', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        if (($primer === '+' || $primer === '-') && !is_numeric($str)) {
            return "'" . $str;
        }
        return $str;
    };

    $nombreArchivo = 'tacos_tony_' . $tipoExport . '_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");

    if ($tipoExport === 'reporte') {
        $reporte = construirReporteConsumoMerma($pdo, $fechaInicio, $fechaFin);
        fputcsv($out, ['Insumo', 'Unidad', 'Categoria', 'Consumo Ventas', 'Merma Total', '% Merma', 'Entradas / Recepcion', 'Ajustes Conteo']);
        foreach ($reporte['resumen_insumos'] as $r) {
            fputcsv($out, [
                $sanitizarCelda($r['insumo_nombre']),
                $sanitizarCelda($r['unidad_medida']),
                $sanitizarCelda($r['categoria']),
                number_format($r['consumo_ventas'], 3, '.', ''),
                number_format($r['total_merma'], 3, '.', ''),
                number_format($r['porcentaje_merma'], 2, '.', ''),
                number_format($r['total_entradas'], 3, '.', ''),
                number_format($r['neto_ajustes'], 3, '.', ''),
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Motivo Merma', 'Eventos', 'Cantidad Total']);
        foreach ($reporte['mermas_por_motivo'] as $m) {
            fputcsv($out, [
                $sanitizarCelda($m['motivo']),
                (string)$m['eventos'],
                number_format($m['cantidad_total'], 3, '.', ''),
            ]);
        }
    } elseif ($tipoExport === 'conteo') {
        $conteoId = (int)($_GET['id'] ?? 0);
        if ($conteoId <= 0) {
            fclose($out);
            responderJson(400, ['error' => 'Se requiere el parámetro id del conteo']);
        }
        $stmtDet = $pdo->prepare(
            "SELECT i.nombre AS insumo_nombre, i.unidad_medida,
                    d.stock_sistema, d.stock_contado, d.diferencia, d.estado_resultante
             FROM " . TABLA_CONTEO_DETALLES . " d
             INNER JOIN " . TABLA_INSUMOS . " i ON i.id = d.insumo_id
             WHERE d.conteo_id = :cid
             ORDER BY d.diferencia ASC, i.nombre ASC"
        );
        $stmtDet->execute([':cid' => $conteoId]);
        fputcsv($out, ['Conteo ID', 'Insumo', 'Unidad', 'Stock Sistema', 'Stock Contado', 'Diferencia', 'Estado Resultante']);
        foreach ($stmtDet->fetchAll() as $d) {
            fputcsv($out, [
                (string)$conteoId,
                $sanitizarCelda($d['insumo_nombre']),
                $sanitizarCelda($d['unidad_medida']),
                number_format((float)$d['stock_sistema'], 3, '.', ''),
                number_format((float)$d['stock_contado'], 3, '.', ''),
                number_format((float)$d['diferencia'], 3, '.', ''),
                $sanitizarCelda($d['estado_resultante']),
            ]);
        }
    } else {
        $queryMov = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'tipo'         => $_GET['mov_tipo'] ?? ($_GET['movimiento_tipo'] ?? ''),
            'insumo_id'    => $_GET['insumo_id'] ?? 0,
        ];
        [$whereSql, $params] = construirFiltroMovimientos($queryMov);
        $stmtMov = $pdo->prepare(
            "SELECT m.id, m.fecha_hora, m.tipo_movimiento, i.nombre AS insumo_nombre, i.unidad_medida,
                    m.cantidad, m.stock_previo, m.stock_posterior, m.motivo, m.observaciones,
                    u.nombre AS usuario_nombre
             FROM " . TABLA_MOVIMIENTOS . " m
             INNER JOIN " . TABLA_INSUMOS . " i ON i.id = m.insumo_id
             LEFT JOIN " . TABLA_USUARIOS . " u ON u.id = m.usuario_id
             $whereSql
             ORDER BY m.fecha_hora DESC, m.id DESC
             LIMIT 2000"
        );
        $stmtMov->execute($params);
        fputcsv($out, ['ID', 'Fecha Hora', 'Tipo', 'Insumo', 'Unidad', 'Cantidad', 'Stock Previo', 'Stock Posterior', 'Motivo', 'Observaciones', 'Usuario']);
        foreach ($stmtMov->fetchAll() as $r) {
            fputcsv($out, [
                (string)$r['id'],
                $sanitizarCelda($r['fecha_hora']),
                $sanitizarCelda($r['tipo_movimiento']),
                $sanitizarCelda($r['insumo_nombre']),
                $sanitizarCelda($r['unidad_medida']),
                number_format((float)$r['cantidad'], 3, '.', ''),
                number_format((float)$r['stock_previo'], 3, '.', ''),
                number_format((float)$r['stock_posterior'], 3, '.', ''),
                $sanitizarCelda($r['motivo'] ?? ''),
                $sanitizarCelda($r['observaciones'] ?? ''),
                $sanitizarCelda($r['usuario_nombre'] ?? ''),
            ]);
        }
    }

    fclose($out);
    exit;
}

responderJson(404, ['error' => 'Endpoint no encontrado: ' . $metodo . ' ' . $ruta]);
