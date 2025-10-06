<?php
session_start();
require '../php/db.php'; // ajusta ruta si es ../php/db.php

// Verificar sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido');
}

// Comprobación POST/FILES
$id_usuario = intval($_SESSION['id']);
$id_area_destino = isset($_POST['id_area_destino']) ? intval($_POST['id_area_destino']) : 0;

if ($id_area_destino <= 0) {
    die('Debe seleccionar un área destino válida.');
}

// Verificar que el área exista
$stmt = $pdo->prepare("SELECT id FROM areas WHERE id = ?");
$stmt->execute([$id_area_destino]);
if (!$stmt->fetch()) {
    die('Área destino no válida.');
}

if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
    die('Error: no se envió archivo o hubo error en la subida.');
}

// Preparar upload
$archivo = $_FILES['documento'];
$nombre_original = $archivo['name'];
$mime = $archivo['type'];
$ext = pathinfo($nombre_original, PATHINFO_EXTENSION);

// Generar nombre único seguro
$nombre_guardado = time() . '_' . bin2hex(random_bytes(6)) . ($ext ? ".$ext" : '');
$directorio = __DIR__ . '/../uploads/';
if (!is_dir($directorio)) mkdir($directorio, 0775, true);

$ruta_destino = $directorio . $nombre_guardado;

// Mover archivo
if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    die('Error al mover el archivo al destino.');
}

// Insert en BD (usa los nombres de tu tabla)
try {
    $stmt = $pdo->prepare("INSERT INTO documentos (id_usuario, id_area_destino, nombre_original, nombre_guardado, tipo, fecha_subida)
                           VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$id_usuario, $id_area_destino, $nombre_original, $nombre_guardado, $mime]);
} catch (PDOException $e) {
    // si falla el INSERT, elimina el archivo subido para no dejar basura
    if (file_exists($ruta_destino)) unlink($ruta_destino);
    die('❌ Error al guardar en la base de datos: ' . $e->getMessage());
}

echo "✅ Documento enviado correctamente. <a href='../empleado/empleado_dashboard.php'>Volver</a>";
