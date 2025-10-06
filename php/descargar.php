<?php
session_start();
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID inválido");

if ($_SESSION['rol'] === 'rrhh') {
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id, $_SESSION['id']]);
}

$doc = $stmt->fetch();
if (!$doc) {
    die("No tienes permiso para acceder a este documento.");
}

$ruta = __DIR__ . "/../uploads/documentos/" . $doc['nombre_guardado'];

if (!file_exists($ruta)) {
    die("Archivo no encontrado.");
}

header("Content-Disposition: attachment; filename=\"" . basename($doc['nombre_original']) . "\"");
header("Content-Type: application/octet-stream");
readfile($ruta);
exit;
