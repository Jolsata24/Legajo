<?php
session_start();
require '../php/db.php';

// Seguridad: solo el usuario logueado puede cambiar su propia foto
if (!isset($_SESSION['id'])) {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// El script es genérico y funciona para cualquier rol logueado
$id_usuario = (int)$_SESSION['id'];
$rol_usuario = $_SESSION['rol'];
$pagina_perfil = "../" . $rol_usuario . "/editar_perfil.php"; // Redirección dinámica

// ... (El resto del código es EXACTAMENTE IGUAL al de empleado/guardar_foto.php) ...
// (Validación de archivo, mover archivo, actualizar BD, borrar foto antigua, etc.)

// Al final, en lugar de una redirección fija:
if (move_uploaded_file($foto_nueva['tmp_name'], $ruta_destino)) {
    // ...
    header("Location: " . $pagina_perfil . "?status=success&msg=" . urlencode("¡Foto actualizada!"));
    exit;
} else {
    header("Location: " . $pagina_perfil . "?status=error&msg=" . urlencode("Error al guardar la foto."));
    exit;
}
?>