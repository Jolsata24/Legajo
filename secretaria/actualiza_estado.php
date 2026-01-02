<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php';

// Validar permisos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin','secretaria'])) {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doc = $_POST['id_doc'] ?? 0;
    // Forzar primera mayúscula: 'aprobado' -> 'Aprobado'
    $estado = ucfirst(strtolower($_POST['estado'] ?? '')); 
    $feedback = $_POST['feedback'] ?? '';
    $area_id = $_POST['area_id'] ?? ''; // Importante para volver a la lista

    if ($id_doc && $estado) {
        // Actualizar
        $stmt = $pdo->prepare("UPDATE documentos SET estado = ?, feedback = ?, revisado_por = ?, fecha_revision = NOW() WHERE id = ?");
        $stmt->execute([$estado, $feedback, $_SESSION['id'], $id_doc]);

        // Registrar historial
        $historial = "Secretaría cambió estado a: " . $estado;
        $stmtH = $pdo->prepare("INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion, fecha) VALUES (?, ?, 'REVISION', ?, NOW())");
        $stmtH->execute([$id_doc, $_SESSION['id'], $historial]);

        // Notificar (Opcional)
        // ... código de notificación ...
    }
    
    // Redirección segura
    if ($area_id) {
        header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
    } else {
        // Si falló el ID de área, volver al dashboard o lista general
        header("Location: secretaria_dashboard.php");
    }
    exit;
}
?>