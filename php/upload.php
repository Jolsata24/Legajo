<?php
session_start();
require '../php/db.php'; // ajusta la ruta según tu estructura

// Verificar que el usuario esté logueado y sea empleado
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../html/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $usuario_id = $_SESSION['id'];
    $tipo = $_POST['tipo'] ?? 'otro';

    // Carpeta de subida
    $directorio = "../uploads/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Generar nombre único
    $nombre_original = $_FILES["documento"]["name"];
    $nombre_guardado = time() . "_" . basename($nombre_original);
    $ruta_destino = $directorio . $nombre_guardado;

    // Mover archivo al directorio final
    if (move_uploaded_file($_FILES["documento"]["tmp_name"], $ruta_destino)) {
    try {
        // Guardar en la BD (todos van a "secretaria")
        $area_solicitada_id = $_POST['area'] ?? null;

$stmt = $pdo->prepare("
    INSERT INTO documentos (id_usuario, nombre_original, nombre_guardado, tipo, fecha_subida, area_solicitada_id)
    VALUES (?, ?, ?, ?, NOW(), ?)
");
$stmt->execute([$usuario_id, $nombre_original, $nombre_guardado, $tipo, $area_solicitada_id]);


        echo "✅ Documento enviado a Secretaría con éxito. <a href='empleado_dashboard.php'>Volver al dashboard</a>";
    } catch (PDOException $e) {
        echo "❌ Error al guardar en la base de datos: " . $e->getMessage();
    }
} else {
    echo "❌ Error al mover el archivo.";
}

} else {
    echo "❌ Solicitud no válida.";
}
