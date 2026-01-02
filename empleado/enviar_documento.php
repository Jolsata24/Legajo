<?php
// php/enviar_documento.php
session_start();
require '../php/db.php';

// 1. SEGURIDAD: Verificar sesión y rol
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    // Si intenta acceder directo sin permiso
    header("Location: ../into/login.html");
    exit;
}

// 2. VERIFICAR MÉTODO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibir datos del formulario
    $id_usuario = $_SESSION['id'];
    $id_seccion = $_POST['seccion'] ?? null;
    $archivo    = $_FILES['archivo'] ?? null;

    // Validar que existan datos
    if ($id_seccion && $archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        
        // --- LÓGICA DE VALIDACIÓN DE ARCHIVO ---
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        
        // Lista blanca de extensiones permitidas
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            
            // --- LÓGICA DE RENOMBRADO (Anti-colisión) ---
            // Formato: timestamp_idusuario_hash.ext
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            $ruta_destino = "../uploads/" . $nombre_guardado;

            // --- MOVER ARCHIVO ---
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                
                try {
                    // --- INSERTAR EN BASE DE DATOS ---
                    // Estado inicial siempre 'Pendiente' para que Secretaría revise
                    $sql = "INSERT INTO documentos 
                            (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                            VALUES (?, ?, ?, ?, ?, NOW(), 'Pendiente')";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$id_usuario, $id_seccion, $nombre_original, $nombre_guardado, $ext])) {
                        
                        // ÉXITO: Redirigir a la carpeta específica
                        header("Location: ../empleado/seccion_legajo.php?id=$id_seccion&msg=exito");
                        exit;

                    } else {
                        $error = "Error al registrar en la base de datos.";
                    }

                } catch (PDOException $e) {
                    $error = "Error de conexión: " . $e->getMessage();
                }

            } else {
                $error = "Error al mover el archivo al servidor. Verifica permisos de la carpeta uploads.";
            }

        } else {
            $error = "Formato de archivo no válido. Solo se permiten: PDF, Word e Imágenes.";
        }

    } else {
        // Códigos de error de subida (Debug)
        $code = $archivo['error'] ?? 'Desconocido';
        $error = "Error en la subida. Código: $code. Asegúrate de seleccionar una carpeta y un archivo.";
    }

} else {
    $error = "Acceso no autorizado.";
}

// SI ALGO FALLÓ: Redirigir de vuelta al formulario con el error
// Usamos urlencode para pasar el mensaje de forma segura
header("Location: ../empleado/subir_documento.php?error=" . urlencode($error));
exit;
?>