<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; 

// 1. Seguridad: Solo secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    die("Acceso denegado.");
}

$id_doc = $_GET['id'] ?? null;
$id_seccion_retorno = $_GET['seccion'] ?? 0;

if (!$id_doc) {
    header("Location: mi_legajo.php");
    exit;
}

try {
    // 2. Verificar propiedad (id_usuario = SESSION id)
    $stmt = $pdo->prepare("SELECT id, nombre_guardado, nombre_original, estado FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_doc, $_SESSION['id']]);
    $doc = $stmt->fetch();

    if ($doc) {
        // Bloquear borrado si está validado
        if (strtolower($doc['estado']) === 'validado' || strtolower($doc['estado']) === 'aprobado') {
             header("Location: seccion_legajo.php?id=$id_seccion_retorno&err=no_borrar_validado");
             exit;
        }

        // 3. Borrar archivo
        $ruta = "../uploads/" . $doc['nombre_guardado'];
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // 4. Borrar de BD
        $del = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
        $del->execute([$id_doc]);

        // 5. Auditoría
        registrar_auditoria($pdo, $_SESSION['id'], 'ELIMINAR_DOC_SECRETARIA', "Eliminó: " . $doc['nombre_original']);
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

header("Location: seccion_legajo.php?id=$id_seccion_retorno&msg=eliminado");
exit;
?>