<?php
session_start();
require 'db.php'; // La ruta es correcta porque este archivo está en la misma carpeta que db.php

// 1. Seguridad y validación de datos
if (!isset($_SESSION['id'])) {
    die("Acceso no autorizado.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? 'view'; // Si no se especifica, la acción por defecto es 'ver'

if ($id <= 0) {
    die("ID de documento no válido.");
}

try {
    // 2. Obtener la información del documento
    $stmt = $pdo->prepare("SELECT nombre_original, nombre_guardado FROM documentos WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        die("Documento no encontrado.");
    }

    $ruta_archivo = "../uploads/" . $doc['nombre_guardado'];

    if (!file_exists($ruta_archivo)) {
        die("El archivo físico no existe en el servidor.");
    }

    // 3. Determinar el tipo de contenido (MIME Type)
    // Esto es crucial para que el navegador sepa qué hacer con el archivo.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $ruta_archivo);
    finfo_close($finfo);

    // 4. LA LÓGICA CLAVE: Decidir si mostrar o descargar
    
    // Limpiamos cualquier salida anterior para asegurar que solo se envíe el archivo
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviamos las cabeceras HTTP correctas
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($ruta_archivo));
    
    if ($action === 'download') {
        // Forzar la descarga con el nombre original
        header('Content-Disposition: attachment; filename="' . basename($doc['nombre_original']) . '"');
    } else {
        // Sugerir al navegador que muestre el archivo "en línea"
        header('Content-Disposition: inline; filename="' . basename($doc['nombre_original']) . '"');
    }
    
    // Leemos y enviamos el contenido del archivo
    readfile($ruta_archivo);
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>