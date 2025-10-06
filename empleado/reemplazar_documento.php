<?php
session_start();
require '../php/db.php';
require '../php/funciones.php';

// 1. Seguridad y validaciones iniciales
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$id_documento = isset($_POST['id_documento']) ? (int)$_POST['id_documento'] : 0;
$id_usuario = (int)$_SESSION['id'];

if ($id_documento <= 0 || !isset($_FILES['nuevo_documento']) || $_FILES['nuevo_documento']['error'] !== UPLOAD_ERR_OK) {
    die("Datos incompletos o error en la subida del archivo.");
}

try {
    // 2. Obtener el documento actual y verificar permisos
    $stmt = $pdo->prepare("SELECT nombre_guardado, estado FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_documento, $id_usuario]);
    $documento_actual = $stmt->fetch();

    if (!$documento_actual) {
        die("Documento no encontrado o no tienes permiso.");
    }

    // 3. ¡Regla de negocio clave! Bloquear si ya está revisado
    if ($documento_actual['estado'] === 'revisado') {
        die("Este documento ya fue revisado y no se puede modificar.");
    }

    // 4. Procesar el nuevo archivo
    $archivo_nuevo = $_FILES['nuevo_documento'];
    $directorio = "../uploads/";
    $nombre_original_nuevo = basename($archivo_nuevo['name']);
    $nombre_guardado_nuevo = time() . "_" . uniqid() . "_" . $nombre_original_nuevo;
    $ruta_destino_nueva = $directorio . $nombre_guardado_nuevo;

    if (move_uploaded_file($archivo_nuevo['tmp_name'], $ruta_destino_nueva)) {
        // Si se subió el nuevo archivo con éxito, procedemos a borrar el antiguo
        $ruta_antigua = $directorio . $documento_actual['nombre_guardado'];
        if (file_exists($ruta_antigua)) {
            unlink($ruta_antigua); // Borramos el archivo antiguo del servidor
        }

        // 5. Actualizar la base de datos
        $stmt_update = $pdo->prepare(
            "UPDATE documentos 
             SET nombre_original = ?, nombre_guardado = ?, estado = 'pendiente', feedback = NULL, fecha_revision = NULL, revisado_por = NULL 
             WHERE id = ?"
        );
        $stmt_update->execute([$nombre_original_nuevo, $nombre_guardado_nuevo, $id_documento]);

        // 6. Registrar en el historial
        $descripcion = "El empleado ha reemplazado el archivo del documento. Vuelve a estar pendiente de revisión.";
        $stmt_historial = $pdo->prepare(
            "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) VALUES (?, ?, 'REEMPLAZADO', ?)"
        );
        $stmt_historial->execute([$id_documento, $id_usuario, $descripcion]);

        // 7. Notificar a Secretaría (asumiendo que tienen rol 'secretaria')
        $stmt_secretaria = $pdo->query("SELECT id FROM usuarios WHERE rol = 'secretaria' LIMIT 1");
        $id_secretaria = $stmt_secretaria->fetchColumn();
        if ($id_secretaria) {
            $mensaje = "Un empleado ha actualizado un documento que requiere tu revisión.";
            $enlace = "../secretaria/secretaria_documentos.php";
            crear_notificacion($pdo, $id_secretaria, $mensaje, $enlace);
        }

        // Redirigir de vuelta a la página de detalle con un mensaje de éxito
        header("Location: ver_documento_enviado.php?id=" . $id_documento . "&status=success");
        exit;

    } else {
        die("Error al guardar el nuevo archivo.");
    }

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>