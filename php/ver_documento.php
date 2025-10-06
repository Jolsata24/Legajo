<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id'])) {
    die("Acceso no autorizado.");
}

$id = intval($_GET['id'] ?? 0);

// Buscar el documento
$stmt = $pdo->prepare("SELECT nombre_original, nombre_guardado FROM documentos WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Documento no encontrado.");
}

$archivo = "../uploads/" . $doc['nombre_guardado'];

if (file_exists($archivo)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($doc['nombre_original']) . '"');
    header('Content-Length: ' . filesize($archivo));
    readfile($archivo);
    exit;
} else {
    die("El archivo no existe en el servidor.");
}
