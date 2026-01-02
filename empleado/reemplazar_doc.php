<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nuevo_documento'])) {
    $id_doc = $_POST['id_documento'];
    $id_usuario = $_SESSION['id'];
    $archivo = $_FILES['nuevo_documento'];

    try {
        // 1. Obtener datos del documento actual (para borrar el archivo viejo)
        $stmt = $pdo->prepare("SELECT nombre_guardado FROM documentos WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_doc, $id_usuario]);
        $doc_actual = $stmt->fetch();

        if (!$doc_actual) die("No tienes permiso o el documento no existe.");

        // 2. Procesar el NUEVO archivo
        if ($archivo['error'] === UPLOAD_ERR_OK) {
            $directorio = "../uploads/";
            $nombre_original = basename($archivo['name']);
            $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            
            // Generar nuevo nombre para evitar caché
            $nombre_guardado = time() . "_v2_" . uniqid() . "." . $ext;
            $ruta_destino = $directorio . $nombre_guardado;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                
                // 3. Borrar el archivo VIEJO del servidor
                $ruta_vieja = $directorio . $doc_actual['nombre_guardado'];
                if (file_exists($ruta_vieja)) {
                    unlink($ruta_vieja);
                }

                // 4. ACTUALIZAR BASE DE DATOS (Manteniendo el ID)
                // Cambiamos estado a 'Pendiente' para que la secretaria lo revise de nuevo
                $sql = "UPDATE documentos 
                        SET nombre_original = ?, nombre_guardado = ?, estado = 'Pendiente', fecha_subida = NOW() 
                        WHERE id = ?";
                $stmtUpd = $pdo->prepare($sql);
                $stmtUpd->execute([$nombre_original, $nombre_guardado, $id_doc]);

                // 5. REGISTRAR TRAZABILIDAD (Historial)
                $desc = "El usuario reemplazó el archivo (Corrección enviada).";
                $stmtH = $pdo->prepare("INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) VALUES (?, ?, 'REEMPLAZO', ?)");
                $stmtH->execute([$id_doc, $id_usuario, $desc]);

                // Éxito
                header("Location: ver_documento_enviado.php?id=$id_doc&msg=reemplazado");
                exit;

            } else {
                $error = "Error al mover el archivo nuevo.";
            }
        } else {
            $error = "Error en la subida del archivo.";
        }

    } catch (PDOException $e) {
        $error = "Error de BD: " . $e->getMessage();
    }
}

// Si hubo error, volver
header("Location: ver_documento_enviado.php?id={$_POST['id_documento']}&error=" . urlencode($error));
?>