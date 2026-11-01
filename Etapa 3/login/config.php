<?php
/**
 * Configuración de conexión a MySQL (PDO) y control de sesiones seguras.
 * Proyecto: Tacos Tony Inventario Móvil · Alumno: 3478788
 * ⚠️ Este archivo contiene credenciales sensibles: NO subirlo a repositorios públicos.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'uzfjkpte_equipo4');
define('DB_USER', 'uzfjkpte_equipo4_user');
define('DB_PASS', 'EY4VLKyFpnlyICJ)');

// Tabla con prefijo del alumno (BD compartida por el equipo)
define('TABLA_USUARIOS', '`3478788_usuarios`');

// Tiempo límite de inactividad de sesión (RNF-05): 30 minutos (1800 segundos)
define('SESSION_TIMEOUT', 1800);

/**
 * Obtiene la instancia PDO singleton con configuración segura
 */
function obtenerConexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
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
            // Sesión expirada por inactividad
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            header('Location: index.php?motivo=expirado');
            exit;
        }
        // Renueva la marca de tiempo de último acceso con la actividad actual
        $_SESSION['ultimo_acceso'] = time();
    }
}
