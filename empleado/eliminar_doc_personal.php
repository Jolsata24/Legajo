<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; // Incluir para auditar

// 1. Seguridad: Solo empleados
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    die("Acceso denegado.");
}

$id_doc = $_GET['id'] ?? null;
$id_seccion_retorno = $_GET['seccion'] ?? 0;

if (!$id_doc) {
    header("Location: mi_legajo.php");
    exit;
}

try {
    // 2. Verificar que el documento pertenece al usuario (Seguridad Crítica)
    $stmt = $pdo->prepare("SELECT id, nombre_guardado, nombre_original, estado FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_doc, $_SESSION['id']]);
    $doc = $stmt->fetch();

    if ($doc) {
        // Opcional: Impedir borrar si ya está validado (Mejor práctica profesional)
        if ($doc['estado'] === 'Aprobado' || $doc['estado'] === 'Validado') {
             // Redirigir con error si intentan borrar algo aprobado
             header("Location: seccion_legajo.php?id=$id_seccion_retorno&err=no_borrar_validado");
             exit;
        }

        // 3. Borrar archivo físico
        $ruta = "../uploads/" . $doc['nombre_guardado'];
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // 4. Borrar de BD
        $del = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
        $del->execute([$id_doc]);

        // --- 5. AUDITORÍA: REGISTRAR ELIMINACIÓN ---
        registrar_auditoria(
            $pdo, 
            $_SESSION['id'], 
            'ELIMINAR_DOC_PROPIO', 
            "Eliminó: " . $doc['nombre_original'] . " (ID: $id_doc)"
        );
        // -------------------------------------------
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Volver a la carpeta
header("Location: seccion_legajo.php?id=$id_seccion_retorno&msg=eliminado");
exit;
?>