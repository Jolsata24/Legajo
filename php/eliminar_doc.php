<?php
session_start();
require 'db.php';
require_once 'funciones.php'; // Importante para usar registrar_auditoria

// 1. SEGURIDAD: Solo el admin puede eliminar documentos definitivamente
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado. Permiso insuficiente.");
}

// Obtener ID y parámetro de redirección (para saber a dónde volver)
$id_doc = $_GET['id'] ?? null;
$redirect = $_GET['redirect'] ?? 'admin_docs'; 

if (!$id_doc) {
    header("Location: ../admin/admin_documentos.php");
    exit;
}

try {
    // 2. Obtener datos del archivo ANTES de borrar (para saber nombre y ruta)
    $stmt = $pdo->prepare("SELECT nombre_guardado, nombre_original, id_usuario FROM documentos WHERE id = ?");
    $stmt->execute([$id_doc]);
    $doc = $stmt->fetch();

    if ($doc) {
        // 3. Borrar el archivo FÍSICO de la carpeta uploads
        $ruta_archivo = "../uploads/" . $doc['nombre_guardado'];
        
        // Verificamos si existe antes de intentar borrarlo para evitar errores
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo); // Esta función borra el archivo del servidor
        }

        // 4. Borrar el registro de la BASE DE DATOS
        $stmtDelete = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
        
        if ($stmtDelete->execute([$id_doc])) {
            
            // 5. AUDITORÍA: Registrar la acción
            registrar_auditoria(
                $pdo, 
                $_SESSION['id'], 
                'ELIMINAR_DOC', 
                "Eliminó el archivo: " . $doc['nombre_original'] . " (ID Doc: $id_doc - Propietario ID: " . $doc['id_usuario'] . ")"
            );
        }
    }

} catch (PDOException $e) {
    // En producción podrías redirigir con un error, aquí lo mostramos para depurar
    die("Error al eliminar: " . $e->getMessage());
}

// 6. Redireccionar a la página correcta
// Esto permite usar este mismo script desde diferentes pantallas (lista general o legajo personal)
if ($redirect === 'ver_legajo') {
    // Si vienes del legajo de un empleado, intentamos volver ahí (necesitaríamos pasar el ID del empleado, 
    // pero por simplicidad volvemos a la lista general o usamos HTTP_REFERER)
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: ../admin/empleados_panel.php");
    }
} else {
    // Por defecto volver a la lista global
    header("Location: ../admin/admin_documentos.php");
}
exit;
?>