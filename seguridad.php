<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["autentificado"]) || $_SESSION["autentificado"] !== "SI") {
    session_unset();
    session_destroy();
    // Calcular la ruta al index.php desde cualquier profundidad
    $dirActual = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
    $raiz = str_replace('\\', '/', realpath(__DIR__));
    $loginPath = 'index.php';
    if ($dirActual !== $raiz) {
        $rel = str_replace($raiz, '', $dirActual);
        $niveles = substr_count(trim($rel, '/'), '/') + 1;
        $loginPath = str_repeat('../', $niveles) . 'index.php';
    }
    header("Location: " . $loginPath . "?error=auth_required");
    exit();
}
?>
