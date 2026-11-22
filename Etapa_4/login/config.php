<?php
/**
 * Configuración de conexión a MySQL (PDO), JWT (HS256) y control de sesiones seguras.
 * Proyecto: Tacos Tony Inventario Móvil · Alumno: 3478788
 * Cumple la regla de seguridad de la Etapa 3: Ninguna credencial en código fuente;
 * todas las credenciales se cargan desde el archivo .env (excluido en .gitignore).
 */

/**
 * Carga variables desde un archivo .env sin requerir librerías externas (Composer)
 */
function cargarEnv(string $rutaArchivo): array
{
    $variables = [];
    if (!is_readable($rutaArchivo)) {
        return $variables;
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lineas === false) {
        return $variables;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);

        if (
            (str_starts_with($valor, '"') && str_ends_with($valor, '"')) ||
            (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
        ) {
            $valor = substr($valor, 1, -1);
        }

        $variables[$clave] = $valor;
        $_ENV[$clave]      = $valor;
    }

    return $variables;
}

$env = cargarEnv(__DIR__ . '/.env');

define('DB_HOST', $env['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', $env['DB_NAME'] ?? getenv('DB_NAME') ?: 'uzfjkpte_equipo4');
define('DB_USER', $env['DB_USER'] ?? getenv('DB_USER') ?: 'uzfjkpte_equipo4_user');
define('DB_PASS', $env['DB_PASS'] ?? getenv('DB_PASS') ?: '');
define('DB_PREFIX', $env['DB_PREFIX'] ?? getenv('DB_PREFIX') ?: '3478788_');

// Tablas físicas con prefijo del alumno (Sección 3.1 del Contrato Técnico v1.1)
define('TABLA_USUARIOS', '`' . DB_PREFIX . 'usuarios`');
define('TABLA_INSUMOS', '`' . DB_PREFIX . 'insumos`');
define('TABLA_PRODUCTOS', '`' . DB_PREFIX . 'productos`');
define('TABLA_RECETAS', '`' . DB_PREFIX . 'recetas`');
define('TABLA_VENTAS', '`' . DB_PREFIX . 'ventas`');
define('TABLA_VENTA_DETALLES', '`' . DB_PREFIX . 'venta_detalles`');
define('TABLA_MOVIMIENTOS', '`' . DB_PREFIX . 'movimientos_inventario`');

// Tiempo límite de inactividad de sesión (RNF-05): 30 minutos (1800 segundos)
define('SESSION_TIMEOUT', (int)($env['SESSION_TIMEOUT'] ?? getenv('SESSION_TIMEOUT') ?: 1800));

// Secreto de firma HMAC-SHA256 para JWT (ADR-04)
define(
    'JWT_SECRET',
    $env['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: hash('sha256', DB_NAME . '_' . DB_PREFIX . '_tacos_tony_jwt')
);

/**
 * Obtiene la instancia PDO singleton con configuración segura
 */
function obtenerConexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        if (DB_PASS === '' || DB_PASS === 'REEMPLAZA_CON_LA_CONTRASENA_REAL') {
            throw new PDOException(
                'Falta configurar el archivo .env con las credenciales de la base de datos (copia .env.example a .env).'
            );
        }
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * Inicia la sesión PHP configurando cookies seguras (HttpOnly, SameSite, Secure)
 */
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Verifica el límite de 30 minutos de inactividad (RNF-05).
 * Si expira, destruye la sesión y redirige al login con motivo explicativo.
 */
function verificarExpiracionSesion(): void
{
    if (!empty($_SESSION['usuario'])) {
        if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT)) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            header('Location: index.php?motivo=expirado');
            exit;
        }
        $_SESSION['ultimo_acceso'] = time();
    }
}

/**
 * Codificación Base64 URL-Safe para JWT (ADR-04, sin dependencias externas)
 */
function base64UrlEncode(string $datos): string
{
    return rtrim(strtr(base64_encode($datos), '+/', '-_'), '=');
}

function base64UrlDecode(string $datos): string
{
    $resto = strlen($datos) % 4;
    if ($resto) {
        $datos .= str_repeat('=', 4 - $resto);
    }
    return (string)base64_decode(strtr($datos, '-_', '+/'));
}

/**
 * Genera un token JWT firmado con HMAC-SHA256 con vigencia de 30 minutos (ADR-04 / RNF-05)
 */
function generarJwt(array $usuario): string
{
    $ahora   = time();
    $header  = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'sub'     => (int)$usuario['id'],
        'usuario' => $usuario['usuario'] ?? '',
        'correo'  => $usuario['correo'] ?? ($usuario['usuario'] ?? ''),
        'nombre'  => $usuario['nombre'] ?? '',
        'rol'     => strtoupper($usuario['rol'] ?? 'COLABORADOR'),
        'iat'     => $ahora,
        'exp'     => $ahora + SESSION_TIMEOUT,
    ];

    $segmentos   = [];
    $segmentos[] = base64UrlEncode((string)json_encode($header));
    $segmentos[] = base64UrlEncode((string)json_encode($payload));
    $entradaFirma = implode('.', $segmentos);
    $firma        = hash_hmac('sha256', $entradaFirma, JWT_SECRET, true);
    $segmentos[]  = base64UrlEncode($firma);

    return implode('.', $segmentos);
}

/**
 * Verifica un token JWT (firma e inactividad de 30 min). Retorna el payload o null.
 */
function verificarJwt(string $token): ?array
{
    $partes = explode('.', $token);
    if (count($partes) !== 3) {
        return null;
    }

    [$h64, $p64, $s64] = $partes;
    $firmaEsperada = base64UrlEncode(hash_hmac('sha256', $h64 . '.' . $p64, JWT_SECRET, true));

    if (!hash_equals($firmaEsperada, $s64)) {
        return null;
    }

    $payload = json_decode(base64UrlDecode($p64), true);
    if (!is_array($payload)) {
        return null;
    }

    if (isset($payload['exp']) && time() > (int)$payload['exp']) {
        return null;
    }

    return $payload;
}

/**
 * Calcula el estado de un insumo según las reglas del modelo (Sección 3.1 y RF-05 / RF-09):
 * - 'discrepancia' si stock_actual < 0 (CA-05.2)
 * - 'bajo' si 0 <= stock_actual <= stock_minimo (CA-05.3, CA-09.1, CA-09.3)
 * - 'normal' si stock_actual > stock_minimo
 */
function calcularEstadoInsumo(float $stockActual, float $stockMinimo): string
{
    if ($stockActual < -0.0001) {
        return 'discrepancia';
    }
    if ($stockActual <= $stockMinimo + 0.0001) {
        return 'bajo';
    }
    return 'normal';
}
