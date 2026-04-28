<?php
/**
 * seguridad_admin.php
 * Incluir este archivo en las páginas que solo pueden ver los administradores.
 * Primero verifica autenticación (igual que seguridad.php), luego verifica rol.
 */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función auxiliar para calcular ruta relativa desde el script actual hasta la raíz del proyecto
function _calcularRutaBase() {
    $dirActual = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
    $raiz = str_replace('\\', '/', realpath(__DIR__));
    if ($dirActual === $raiz) {
        return '';
    }
    $rel = str_replace($raiz, '', $dirActual);
    $niveles = substr_count(trim($rel, '/'), '/') + 1;
    return str_repeat('../', $niveles);
}

// Verificar autenticación
if (!isset($_SESSION["autentificado"]) || $_SESSION["autentificado"] !== "SI") {
    session_unset();
    session_destroy();
    $base = _calcularRutaBase();
    header("Location: " . $base . "index.php?error=auth_required");
    exit();
}

// Verificar rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    $base = _calcularRutaBase();
    $dashPath = $base . 'barra_lateral/Dashboard.php';
    echo "<script>alert('No cuentas con los permisos necesarios para acceder a esta sección.'); window.location.href='" . $dashPath . "';</script>";
    exit();
}
?>
