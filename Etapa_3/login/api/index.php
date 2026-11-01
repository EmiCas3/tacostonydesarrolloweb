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
    responderJson(500, ['error' => 'Error de conexión con la base de datos: ' . $e->getMessage()]);
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
    $categoriasValidas = ['Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables'];

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
        $stmtInsertVenta = $pdo->prepare(
            "INSERT INTO " . TABLA_VENTAS . " (usuario_id, total) VALUES (:uid, :total)"
        );
        $stmtInsertVenta->execute([
            ':uid'   => $usuario['id'],
            ':total' => number_format($totalVenta, 2, '.', ''),
        ]);
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
        $stmtMov = $pdo->prepare(
            "INSERT INTO " . TABLA_MOVIMIENTOS . "
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, referencia_id, observaciones)
             VALUES (:iid, :uid, 'VENTA', :cant, :prev, :post, :ref, :obs)"
        );

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

            $stmtMov->execute([
                ':iid'  => $insumoId,
                ':uid'  => $usuario['id'],
                ':cant' => number_format(-$consumoTotal, 3, '.', ''),
                ':prev' => number_format($stockPrevio, 3, '.', ''),
                ':post' => number_format($stockPosterior, 3, '.', ''),
                ':ref'  => $ventaId,
                ':obs'  => "Descuento automático por Venta #$ventaId",
            ]);

            if ($nuevoEstado === 'discrepancia') {
                $discrepancias[] = [
                    'insumo_id'        => $insumoId,
                    'insumo_nombre'    => $insumo['nombre'],
                    'stock_resultante' => $stockPosterior,
                    'mensaje'          => 'Stock resultante negativo. Marcado con discrepancia.',
                ];
            }
        }

        $pdo->commit();

        responderJson(201, [
            'venta_id'                 => $ventaId,
            'fecha_hora'               => gmdate('Y-m-d\TH:i:s\Z'),
            'total'                    => $totalVenta,
            'discrepancias_detectadas' => $discrepancias,
            'mensaje'                  => 'Venta registrada exitosamente con descuento automático de insumos',
        ]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        responderJson(500, ['error' => 'Error al procesar la transacción de venta: ' . $e->getMessage()]);
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
    $cantidadRaw   = $body['cantidad'] ?? null;
    $observaciones = trim((string)($body['observaciones'] ?? 'Entrada por compra a proveedor'));

    if ($insumoId <= 0 || !is_numeric($cantidadRaw) || (float)$cantidadRaw <= 0) {
        responderJson(400, ['error' => 'La cantidad de entrada debe ser superior a cero']);
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
             (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, observaciones)
             VALUES (:iid, :uid, 'ENTRADA', :cant, :prev, :post, :obs)"
        );
        $stmtMov->execute([
            ':iid'  => $insumoId,
            ':uid'  => $usuario['id'],
            ':cant' => number_format($cantidad, 3, '.', ''),
            ':prev' => number_format($stockAnterior, 3, '.', ''),
            ':post' => number_format($stockNuevo, 3, '.', ''),
            ':obs'  => $observaciones,
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

    $fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
    $fechaFin    = trim((string)($_GET['fecha_fin'] ?? ''));
    $tipo        = strtoupper(trim((string)($_GET['tipo'] ?? '')));
    $insumoId    = (int)($_GET['insumo_id'] ?? 0);
    $pagina      = max(1, (int)($_GET['pagina'] ?? 1));
    $limite      = 25; // CA-10.3: Paginación LIMIT 25 OFFSET 0
    $offset      = ($pagina - 1) * $limite;

    $conds  = [];
    $params = [];

    if ($fechaInicio !== '') {
        $conds[]          = 'm.fecha_hora >= :fini';
        $params[':fini']  = $fechaInicio . ' 00:00:00';
    }
    if ($fechaFin !== '') {
        $conds[]          = 'm.fecha_hora <= :ffin';
        $params[':ffin']  = $fechaFin . ' 23:59:59';
    }
    if (in_array($tipo, ['VENTA', 'ENTRADA', 'MERMA'], true)) {
        $conds[]          = 'm.tipo_movimiento = :tipo';
        $params[':tipo']  = $tipo;
    }
    if ($insumoId > 0) {
        $conds[]          = 'm.insumo_id = :iid';
        $params[':iid']   = $insumoId;
    }

    $whereSql = count($conds) > 0 ? ('WHERE ' . implode(' AND ', $conds)) : '';

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

responderJson(404, ['error' => 'Endpoint no encontrado: ' . $metodo . ' ' . $ruta]);
