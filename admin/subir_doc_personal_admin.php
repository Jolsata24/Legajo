<?php
session_start();
require '../php/db.php';

// 1. Seguridad: Solo Admin y método POST
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// 2. Recibir datos del formulario
$usuario_id = $_SESSION['id']; // El ID del propio admin
$seccion_id = $_POST['seccion_id'] ?? null;
$tipo = trim($_POST['tipo'] ?? '');

if (!$seccion_id || empty($tipo) || !isset($_FILES['documento'])) {
    die("Error: Faltan datos.");
}

// 3. Procesar el archivo (lógica de subida)
$archivo = $_FILES['documento'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir el archivo.");
}

$directorio = "../uploads/";
$nombre_original = basename($archivo['name']);
$extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
$nombre_guardado = time() . "_" . uniqid() . "." . $extension;
$ruta_destino = $directorio . $nombre_guardado;

// Mover el archivo de forma segura
if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    die("Error al guardar el archivo en el servidor.");
}

// 4. Insertar en la base de datos
try {
    $stmt = $pdo->prepare(
        "INSERT INTO documentos (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado)
         VALUES (?, ?, ?, ?, ?, NOW(), 'revisado')"
    );
    // El admin sube sus propios docs, así que los ponemos como 'revisado'
    $stmt->execute([$usuario_id, $seccion_id, $nombre_original, $nombre_guardado, $tipo]);

    // Redirigir de vuelta a la página de la sección con un mensaje de éxito
    header("Location: seccion_legajo_admin.php?id=" . $seccion_id . "&status=success");
    exit;

} catch (PDOException $e) {
    // Si falla la BD, borrar el archivo subido
    if (file_exists($ruta_destino)) {
        unlink($ruta_destino);
    }
    die("Error al guardar en la base de datos: " . $e->getMessage());
}
?>