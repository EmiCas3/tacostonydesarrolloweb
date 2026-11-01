<?php
/**
 * Plantilla de configuración SIN credenciales.
 * Copiar como config.php y rellenar los valores reales para tu entorno.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'NOMBRE_DE_LA_BD');
define('DB_USER', 'USUARIO_MYSQL');
define('DB_PASS', 'CONTRASEÑA_MYSQL');

define('TABLA_USUARIOS', '`3478788_usuarios`');
define('SESSION_TIMEOUT', 1800); // 30 minutos

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
