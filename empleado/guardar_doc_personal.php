<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; // <--- IMPORTANTE: Incluir funciones

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_usuario = $_SESSION['id'];
    $id_seccion = $_POST['id_seccion'] ?? null; 
    $archivo    = $_FILES['archivo'] ?? null;

    if ($id_seccion && $archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            $ruta_destino = "../uploads/" . $nombre_guardado;
            
            if (!is_dir("../uploads/")) mkdir("../uploads/", 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                
                try {
                    $sql = "INSERT INTO documentos 
                            (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                            VALUES (?, ?, ?, ?, ?, NOW(), 'Pendiente')";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$id_usuario, $id_seccion, $nombre_original, $nombre_guardado, $ext])) {
                        
                        // --- AUDITORÍA AQUÍ ---
                        registrar_auditoria(
                            $pdo, 
                            $id_usuario, 
                            'SUBIDA_LEGAJO', 
                            "Subió: " . $nombre_original . " a sección ID: " . $id_seccion
                        );
                        // ----------------------

                        header("Location: seccion_legajo.php?id=$id_seccion&msg=exito_personal");
                        exit;

                    } else {
                        $error = "Error al guardar en la base de datos.";
                    }

                } catch (PDOException $e) {
                    $error = "Error SQL: " . $e->getMessage();
                }

            } else {
                $error = "Error al mover el archivo al servidor.";
            }

        } else {
            $error = "Formato no permitido (Solo PDF, Word, Imágenes).";
        }
    } else {
        $error = "Faltan datos o hubo un error en la subida.";
    }

    header("Location: subir_doc_personal.php?error=" . urlencode($error));
    exit;
}
?>