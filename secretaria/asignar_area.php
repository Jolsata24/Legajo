<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; // Incluimos las funciones para las notificaciones

// 1. Seguridad: Solo Secretaría puede acceder
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Verificar que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doc_id'], $_POST['area_id'])) {
    $doc_id = (int)$_POST['doc_id'];
    $area_id = (int)$_POST['area_id'];
    $id_secretaria = (int)$_SESSION['id'];

    if ($doc_id <= 0 || $area_id <= 0) {
        die("Datos inválidos.");
    }

    try {
        // 3. ACTUALIZAR el documento para asignarle el área y cambiar su estado
        $stmt_update = $pdo->prepare(
            "UPDATE documentos 
             SET id_area_destino = ?, estado = 'observado'
             WHERE id = ?"
        );
        $stmt_update->execute([$area_id, $doc_id]);

        // 4. REGISTRAR EN EL HISTORIAL
        $stmt_area = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
        $stmt_area->execute([$area_id]);
        $nombre_area = $stmt_area->fetchColumn();

        $descripcion = "Documento asignado al área '" . htmlspecialchars($nombre_area) . "' por Secretaría.";
        $stmt_historial = $pdo->prepare(
            "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) 
             VALUES (?, ?, 'ASIGNADO', ?)"
        );
        $stmt_historial->execute([$doc_id, $id_secretaria, $descripcion]);

        // 5. GENERAR NOTIFICACIONES
        // Notificación para el empleado
        $stmt_doc = $pdo->prepare("SELECT id_usuario FROM documentos WHERE id = ?");
        $stmt_doc->execute([$doc_id]);
        $id_empleado = $stmt_doc->fetchColumn();
        
        $mensaje_empleado = "Secretaría ha asignado tu documento al área '" . htmlspecialchars($nombre_area) . "'.";
        $enlace_empleado = "../empleado/ver_documento_enviado.php?id=" . $id_doc;
        crear_notificacion($pdo, $id_empleado, $mensaje_empleado, $enlace_empleado);

        // Notificación para el Jefe del Área de destino
        $stmt_jefe = $pdo->prepare("SELECT id FROM usuarios WHERE id_area = ? AND rol = 'jefe_area'");
        $stmt_jefe->execute([$area_id]);
        $id_jefe_area = $stmt_jefe->fetchColumn();

        if ($id_jefe_area) {
            $mensaje_jefe = "Has recibido un nuevo documento para revisar en tu área.";
            $enlace_jefe = "../secretaria/secretaria_documentos_area.php?area_id=" . $area_id;
            crear_notificacion($pdo, $id_jefe_area, $mensaje_jefe, $enlace_jefe);
        }
        
        $_SESSION['msg'] = "Documento enviado al área '" . htmlspecialchars($nombre_area) . "' correctamente.";

    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error en la base de datos: " . $e->getMessage();
    }
} else {
    $_SESSION['msg'] = "Solicitud no válida.";
}

// 6. Redirigir de vuelta al panel de Secretaría
header("Location: secretaria_documentos.php");
exit;
?>