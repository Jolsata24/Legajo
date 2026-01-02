<?php
session_start();
require '../php/db.php';
require '../php/funciones.php'; // Asegúrate de que este archivo exista para las notificaciones

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$id_documento = isset($_POST['id_documento']) ? (int)$_POST['id_documento'] : 0;
$id_usuario = (int)$_SESSION['id'];

if ($id_documento <= 0 || !isset($_FILES['nuevo_documento']) || $_FILES['nuevo_documento']['error'] !== UPLOAD_ERR_OK) {
    die("Error: No se ha enviado ningún archivo o el ID es inválido.");
}

try {
    // 2. Verificar permiso y estado actual
    $stmt = $pdo->prepare("SELECT nombre_guardado, estado FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_documento, $id_usuario]);
    $documento_actual = $stmt->fetch();

    if (!$documento_actual) {
        die("Documento no encontrado o acceso denegado.");
    }

    // Regla de negocio: Solo se puede reemplazar si NO está Aprobado/Validado
    // Bloqueamos si es 'Aprobado', 'Validado' o 'Revisado'
    $estados_bloqueados = ['aprobado', 'validado', 'revisado'];
    if (in_array(strtolower($documento_actual['estado']), $estados_bloqueados)) {
        die("Error: Este documento ya ha sido aprobado y no puede modificarse.");
    }

    // 3. Validación de Tipo de Archivo (SEGURIDAD)
    $archivo_nuevo = $_FILES['nuevo_documento'];
    $nombre_original_nuevo = basename($archivo_nuevo['name']);
    $ext = strtolower(pathinfo($nombre_original_nuevo, PATHINFO_EXTENSION));
    $permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    if (!in_array($ext, $permitidos)) {
        die("Error: Formato de archivo no permitido. Solo PDF, Word o Imágenes.");
    }

    // 4. Procesar Subida
    $directorio = "../uploads/";
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);

    $nombre_guardado_nuevo = time() . "_" . uniqid() . "." . $ext; // Nombre limpio
    $ruta_destino_nueva = $directorio . $nombre_guardado_nuevo;

    if (move_uploaded_file($archivo_nuevo['tmp_name'], $ruta_destino_nueva)) {
        
        // 5. Borrar archivo antiguo (Limpieza)
        $ruta_antigua = $directorio . $documento_actual['nombre_guardado'];
        if (file_exists($ruta_antigua) && is_file($ruta_antigua)) {
            unlink($ruta_antigua);
        }

        // 6. Actualizar Base de Datos (Reseteamos estado a 'Pendiente')
        $stmt_update = $pdo->prepare(
            "UPDATE documentos 
             SET nombre_original = ?, nombre_guardado = ?, tipo = ?, estado = 'Pendiente', feedback = NULL, fecha_revision = NULL, revisado_por = NULL, fecha_subida = NOW()
             WHERE id = ?"
        );
        $stmt_update->execute([$nombre_original_nuevo, $nombre_guardado_nuevo, $ext, $id_documento]);

        // 7. Registrar Historial
        $descripcion = "Archivo reemplazado por el empleado. Estado reiniciado a Pendiente.";
        $stmt_historial = $pdo->prepare(
            "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion, fecha) VALUES (?, ?, 'REEMPLAZADO', ?, NOW())"
        );
        $stmt_historial->execute([$id_documento, $id_usuario, $descripcion]);

        // 8. Notificar a Secretaría
        // (Opcional: Verificar si la función existe antes de llamarla para evitar errores fatales)
        if (function_exists('crear_notificacion')) {
            $stmt_secretaria = $pdo->query("SELECT id FROM usuarios WHERE rol = 'secretaria' LIMIT 1");
            $id_secretaria = $stmt_secretaria->fetchColumn();
            if ($id_secretaria) {
                crear_notificacion($pdo, $id_secretaria, "Corrección enviada: $nombre_original_nuevo", "../secretaria/ver_documento.php?id=$id_documento");
            }
        }

        // 9. Redirección Correcta (Alineada con el frontend)
        header("Location: ver_documento_enviado.php?id=" . $id_documento . "&msg=reemplazado");
        exit;

    } else {
        die("Error al mover el archivo al servidor.");
    }

} catch (PDOException $e) {
    die("Error SQL: " . $e->getMessage());
}
?>