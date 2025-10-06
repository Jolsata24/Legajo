<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php';

// 1. Seguridad: Roles permitidos
$roles_permitidos = ['admin','rrhh','jefe_area','secretaria'];
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: ../into/login.html");
    exit;
}

// 2. Validaciones iniciales
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Solicitud inválida.");
}

$id_doc   = isset($_POST['id_doc']) ? (int) $_POST['id_doc'] : 0;
$estado   = $_POST['estado'] ?? '';
$feedback = trim($_POST['feedback'] ?? '');
$area_id  = isset($_POST['area_id']) ? (int) $_POST['area_id'] : null;

if (!$id_doc || !$estado || !$area_id) {
    $_SESSION['mensaje'] = "❌ Datos incompletos.";
    header("Location: secretaria_documentos_area.php?area_id=" . ($area_id ?: ''));
    exit;
}
// ... (resto de validaciones) ...

try {
    // 3. Verificación de permisos para Jefe de Área (tu código estaba bien aquí)
    if ($_SESSION['rol'] === 'jefe_area') {
        $mi_area = $_SESSION['id_area'] ?? null;
        if (!$mi_area) {
            $stmtA = $pdo->prepare("SELECT id_area FROM usuarios WHERE id = ?");
            $stmtA->execute([$_SESSION['id']]);
            $mi_area = $stmtA->fetchColumn();
        }
        $stmtC = $pdo->prepare("SELECT id_area_destino FROM documentos WHERE id = ?");
        $stmtC->execute([$id_doc]);
        $doc_area = $stmtC->fetchColumn();
        if ((int)$doc_area !== (int)$mi_area) {
            $_SESSION['mensaje'] = "❌ Acceso denegado: el documento no pertenece a tu área.";
            header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
            exit;
        }
    }

    // 4. Actualizar el documento (SOLO UNA VEZ)
    $stmt = $pdo->prepare(
        "UPDATE documentos SET estado = ?, feedback = ?, revisado_por = ?, fecha_revision = NOW() WHERE id = ?"
    );
    $stmt->execute([$estado, $feedback, $_SESSION['id'], $id_doc]);
    
    // --- ¡NUEVO! REGISTRAR CAMBIO DE ESTADO EN EL HISTORIAL ---
    $nombre_revisor = $_SESSION['nombre'];
    $descripcion_historial = "El estado del documento fue cambiado a '" . strtoupper($estado) . "' por " . $nombre_revisor . ".";
    if (!empty($feedback)) {
        $descripcion_historial .= " Feedback: '" . $feedback . "'";
    }
    
    $stmt_historial = $pdo->prepare(
        "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) VALUES (?, ?, 'CAMBIO_ESTADO', ?)"
    );
    $stmt_historial->execute([$id_doc, $_SESSION['id'], $descripcion_historial]);
    // --- FIN DEL REGISTRO DE HISTORIAL ---

    // 5. NOTIFICACIÓN PARA EL EMPLEADO (tu código estaba bien aquí)
    $stmt_doc = $pdo->prepare("SELECT id_usuario FROM documentos WHERE id = ?");
    $stmt_doc->execute([$id_doc]);
    $id_empleado = $stmt_doc->fetchColumn();

    if ($id_empleado) {
        $mensaje_empleado = "Tu documento ha sido actualizado al estado: '" . strtoupper($estado) . "' por " . $nombre_revisor . ".";
        $enlace_empleado = "../empleado/ver_documento_enviado.php?id=" . $id_doc;
        crear_notificacion($pdo, $id_empleado, $mensaje_empleado, $enlace_empleado);
    }

    $_SESSION['mensaje'] = "✅ Estado y feedback guardados correctamente.";

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "❌ Error al guardar: " . $e->getMessage();
}

// 6. Volver a la vista del área
header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
exit;