<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_usuario = $_SESSION['id'];
    // 1. IMPORTANTE: Recibimos el ID del área desde el select del formulario
    $id_area_destino = isset($_POST['id_area']) ? (int)$_POST['id_area'] : 0; 
    $archivo = $_FILES['archivo'] ?? null;

    // Validación estricta: Si el ID es 0 o inválido, detenemos todo
    if ($id_area_destino <= 0) {
        header("Location: subir_documento.php?error=" . urlencode("Debes seleccionar un área válida."));
        exit;
    }

    if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            $ruta_destino = "../uploads/" . $nombre_guardado;
            
            if (!is_dir("../uploads/")) mkdir("../uploads/", 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                
                try {
                    // 2. INSERTAR CON EL ÁREA CORRECTA
                    // Asegúrate de que tu base de datos tenga la columna 'id_area_destino'
                    $sql = "INSERT INTO documentos 
                            (id_usuario, id_area_destino, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                            VALUES (?, ?, NULL, ?, ?, ?, NOW(), 'Pendiente')";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$id_usuario, $id_area_destino, $nombre_original, $nombre_guardado, $ext])) {
                        header("Location: documentos_enviados.php?msg=exito");
                        exit;
                    } else {
                        $error = "Error al guardar en BD.";
                    }

                } catch (PDOException $e) {
                    $error = "Error SQL: " . $e->getMessage();
                }

            } else {
                $error = "Error al mover el archivo.";
            }

        } else {
            $error = "Formato no permitido.";
        }
    } else {
        $error = "Datos incompletos o error en archivo.";
    }

    header("Location: subir_documento.php?error=" . urlencode($error));
    exit;
}
?>