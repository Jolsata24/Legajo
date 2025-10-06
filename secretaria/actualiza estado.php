<?php
session_start();
require '../php/db.php';

$roles_permitidos = ['admin','rrhh','jefe_area','secretaria'];
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: ../into/login.html");
    exit;
}

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

// validar estados permitidos
$estados_permitidos = ['pendiente','observado','rechazado','revisado'];
if (!in_array($estado, $estados_permitidos)) {
    $_SESSION['mensaje'] = "❌ Estado no válido.";
    header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
    exit;
}

try {
    // Si el usuario es jefe_area, verificar que el documento pertenece a su área
    if ($_SESSION['rol'] === 'jefe_area') {
        // obtener id_area del jefe (desde sesión o BD)
        $mi_area = $_SESSION['id_area'] ?? null;
        if (!$mi_area) {
            $stmtA = $pdo->prepare("SELECT id_area FROM usuarios WHERE id = ?");
            $stmtA->execute([$_SESSION['id']]);
            $r = $stmtA->fetch();
            $mi_area = $r['id_area'] ?? null;
        }
        // comprobar que el documento pertenece a esa área
        $stmtC = $pdo->prepare("SELECT id_area_destino FROM documentos WHERE id = ?");
        $stmtC->execute([$id_doc]);
        $rowC = $stmtC->fetch();
        if (!$rowC || (int)$rowC['id_area_destino'] !== (int)$mi_area) {
            $_SESSION['mensaje'] = "❌ Acceso denegado: el documento no pertenece a tu área.";
            header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
            exit;
        }
    }

    // Actualizar documento
    $stmt = $pdo->prepare("
        UPDATE documentos
        SET estado = ?, feedback = ?, revisado_por = ?, fecha_revision = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$estado, $feedback, $_SESSION['id'], $id_doc]);

    $_SESSION['mensaje'] = "✅ Estado y feedback guardados correctamente.";
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "❌ Error al guardar: " . $e->getMessage();
}

// Volver a la vista del área
header("Location: secretaria_documentos_area.php?area_id=" . $area_id);
exit;
