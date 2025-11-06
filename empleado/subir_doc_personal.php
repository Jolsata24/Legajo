<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

// Verificar si llegó la sección
$seccion_id = $_POST['seccion_id'] ?? null;
$tipo = $_POST['tipo'] ?? '';

if (!$seccion_id) {
    die("Error: no se especificó sección.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $archivo = $_FILES['documento'];

    // Validar errores
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir el archivo.");
    }

    // Configuración
    $directorio = "../uploads/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_original = basename($archivo['name']);
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $nombre_guardado = time() . "_" . uniqid() . "." . $extension;
    $ruta_destino = $directorio . $nombre_guardado;

    // Validar tipo de archivo (ejemplo: solo PDF, DOCX, JPG, PNG)
    $permitidos = ["pdf", "doc", "docx", "jpg", "jpeg", "png"];
    if (!in_array(strtolower($extension), $permitidos)) {
        die("Error: tipo de archivo no permitido.");
    }

    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        die("Error al guardar el archivo en el servidor.");
    }

    try {
        // Insertar en base de datos (¡CORREGIDO!)
        $stmt = $pdo->prepare("
        INSERT INTO documentos (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado)
        VALUES (?, ?, ?, ?, ?, NOW(), 'revisado')
    ");
        // Se añadió la columna 'estado' y el valor 'revisado'
        $stmt->execute([$usuario_id, $seccion_id, $nombre_original, $nombre_guardado, $tipo]);
        // ... (resto del código)

        // Redirigir de vuelta a la sección
        header("Location: seccion_legajo.php?id=" . $seccion_id);
        exit;
    } catch (PDOException $e) {
        die("Error al guardar en la base de datos: " . $e->getMessage());
    }
} else {
    die("Solicitud inválida.");
}
