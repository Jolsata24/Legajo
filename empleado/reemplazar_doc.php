<?php
session_start();
require '../php/db.php';
require '../php/funciones.php'; // Para notificaciones

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$id_documento = isset($_POST['id_documento']) ? (int)$_POST['id_documento'] : 0;
$id_usuario = (int)$_SESSION['id'];

// Validar subida
if ($id_documento <= 0 || !isset($_FILES['nuevo_documento']) || $_FILES['nuevo_documento']['error'] !== UPLOAD_ERR_OK) {
    die("Error: No se ha enviado ningún archivo o el ID es inválido.");
}

try {
    // 2. Verificar estado actual
    $stmt = $pdo->prepare("SELECT nombre_guardado, estado FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_documento, $id_usuario]);
    $documento_actual = $stmt->fetch();

    if (!$documento_actual) {
        die("Documento no encontrado o acceso denegado.");
    }

    // Bloquear si ya está aprobado
    $estados_bloqueados = ['aprobado', 'validado', 'revisado'];
    if (in_array(strtolower($documento_actual['estado']), $estados_bloqueados)) {
        die("Error: Este documento ya ha sido aprobado y no puede modificarse.");
    }

    // 3. Procesar Archivo
    $archivo_nuevo = $_FILES['nuevo_documento'];
    $nombre_original_nuevo = basename($archivo_nuevo['name']);
    $ext = strtolower(pathinfo($nombre_original_nuevo, PATHINFO_EXTENSION));
    $permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    if (!in_array($ext, $permitidos)) {
        die("Error: Formato de archivo no permitido.");
    }

    $directorio = "../uploads/";
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);

    $nombre_guardado_nuevo = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
    $ruta_destino_nueva = $directorio . $nombre_guardado_nuevo;

    if (move_uploaded_file($archivo_nuevo['tmp_name'], $ruta_destino_nueva)) {
        
        // 4. Borrar archivo antiguo
        $ruta_antigua = $directorio . $documento_actual['nombre_guardado'];
        if (file_exists($ruta_antigua) && is_file($ruta_antigua)) {
            unlink($ruta_antigua);
        }

        // 5. ACTUALIZACIÓN EN BASE DE DATOS (SOLUCIÓN DEL PROBLEMA)
        // Agregamos: fecha_subida = NOW() -> Para que suba al inicio de la lista
        // Agregamos: tipo = ? -> Por si el usuario cambia de Word a PDF
        $stmt_update = $pdo->prepare(
            "UPDATE documentos 
             SET nombre_original = ?, 
                 nombre_guardado = ?, 
                 tipo = ?, 
                 estado = 'Pendiente', 
                 feedback = NULL, 
                 fecha_revision = NULL, 
                 revisado_por = NULL, 
                 fecha_subida = NOW() 
             WHERE id = ?"
        );
        $stmt_update->execute([$nombre_original_nuevo, $nombre_guardado_nuevo, $ext, $id_documento]);

        // 6. Historial
        $descripcion = "Archivo corregido y reenviado por el empleado.";
        $stmt_historial = $pdo->prepare(
            "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion, fecha) VALUES (?, ?, 'REEMPLAZADO', ?, NOW())"
        );
        $stmt_historial->execute([$id_documento, $id_usuario, $descripcion]);

        // 7. Notificar a Secretaría
        if (function_exists('crear_notificacion')) {
            $stmt_sec = $pdo->query("SELECT id FROM usuarios WHERE rol = 'secretaria'");
            while ($sec = $stmt_sec->fetch()) {
                crear_notificacion($pdo, $sec['id'], "Corrección recibida: $nombre_original_nuevo", "../secretaria/ver_documento.php?id=$id_documento");
            }
        }

        // Redirigir
        header("Location: ver_documento_enviado.php?id=" . $id_documento . "&msg=reemplazado");
        exit;

    } else {
        die("Error al mover el archivo al servidor.");
    }

} catch (PDOException $e) {
    die("Error SQL: " . $e->getMessage());
}
?>