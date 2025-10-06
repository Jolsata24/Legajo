<?php
session_start();
require '../php/db.php'; // Asegúrate que la ruta a db.php sea correcta

// 1. Verificar que el usuario esté logueado y sea un empleado
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Verificar que la solicitud sea por POST y que se haya enviado un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    
    $archivo = $_FILES['documento'];

    // Verificar si hubo un error en la subida del archivo
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        die("❌ Error al subir el archivo. Código: " . $archivo['error']);
    }

    $usuario_id = (int)$_SESSION['id'];
    $tipo = trim($_POST['tipo'] ?? 'General');
    $area_solicitada_id = !empty($_POST['area']) ? (int)$_POST['area'] : null;

    // 3. Procesar el guardado del archivo
    $directorio = "../uploads/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_original = basename($archivo['name']);
    $nombre_guardado = time() . "_" . uniqid() . "_" . $nombre_original;
    $ruta_destino = $directorio . $nombre_guardado;

    // Mover el archivo al directorio final
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        try {
            // 4. Guardar la información en la tabla 'documentos'
            // Nota: Usamos 'id_area_destino' para la columna del área solicitada
            $stmt = $pdo->prepare(
                "INSERT INTO documentos (id_usuario, nombre_original, nombre_guardado, tipo, id_area_destino, fecha_subida, estado)
                 VALUES (?, ?, ?, ?, ?, NOW(), 'pendiente')"
            );
            $stmt->execute([$usuario_id, $nombre_original, $nombre_guardado, $tipo, $area_solicitada_id]);

            // 5. REGISTRAR EL EVENTO EN EL HISTORIAL
            $id_documento_nuevo = $pdo->lastInsertId();
            $stmt_historial = $pdo->prepare(
                "INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) 
                 VALUES (?, ?, 'CREADO', 'El empleado ha subido el documento para revisión de Secretaría.')"
            );
            $stmt_historial->execute([$id_documento_nuevo, $usuario_id]);

            // Redirigir al dashboard del empleado con un mensaje de éxito
            header("Location: ../empleado/empleado_dashboard.php?status=success");
            exit;

        } catch (PDOException $e) {
            // Si hay un error en la BD, borramos el archivo para no dejar basura
            if (file_exists($ruta_destino)) {
                unlink($ruta_destino);
            }
            die("❌ Error al guardar en la base de datos: " . $e->getMessage());
        }
    } else {
        die("❌ Error al mover el archivo al servidor.");
    }

} else {
    die("❌ Solicitud no válida o no se subió ningún archivo.");
}