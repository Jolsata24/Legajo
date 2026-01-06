<?php
session_start();

// 1. Incluimos funciones.php (que a su vez incluye db.php y la conexión $pdo)
require_once '../php/funciones.php'; 

// Verificar sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id'];
    // Validamos que venga el área, si no, asignamos 0 o manejamos error
    $id_area_destino = isset($_POST['id_area_destino']) ? intval($_POST['id_area_destino']) : 0;

    // Verificar si se subió el archivo sin errores
    if (!isset($_FILES['documento']) || $_FILES['documento']['error'] != 0) {
        die("Error al subir el archivo. Código: " . ($_FILES['documento']['error'] ?? 'Desconocido'));
    }

    $nombre_original = $_FILES['documento']['name'];
    $tipo = $_FILES['documento']['type'];
    
    // Generar nombre único para evitar conflictos en la carpeta
    $nombre_guardado = uniqid() . "_" . basename($nombre_original);
    $ruta_destino = "../uploads/" . $nombre_guardado;

    // Intentar mover el archivo a la carpeta uploads
    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_destino)) {
        
        try {
            // 2. Usamos PDO para insertar (Compatible con tu db.php)
            $sql = "INSERT INTO documentos (id_usuario, id_area_destino, nombre_original, nombre_guardado, tipo, estado, fecha_subida) 
                    VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())";
            
            $stmt = $pdo->prepare($sql);
            
            // Ejecutamos la consulta
            if ($stmt->execute([$id_usuario, $id_area_destino, $nombre_original, $nombre_guardado, $tipo])) {
                
                // --- 3. AUDITORÍA: REGISTRAR LA SUBIDA ---
                registrar_auditoria(
                    $pdo, 
                    $id_usuario, 
                    'SUBIDA_DOCUMENTO', 
                    "Archivo: " . $nombre_original . " (Guardado como: " . $nombre_guardado . ")"
                );
                // ----------------------------------------

                // Mensaje de éxito y botón de volver
                echo "<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center;'>";
                echo "<h3 style='color: #198754;'>¡Documento enviado correctamente!</h3>";
                echo "<p>El estado inicial es: <strong>Pendiente</strong></p>";
                echo "<br><a href='empleado_dashboard.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Volver al Panel</a>";
                echo "</div>";

            } else {
                echo "Error al guardar la información en la base de datos.";
            }

        } catch (PDOException $e) {
            die("Error de Base de Datos: " . $e->getMessage());
        }

    } else {
        echo "Error al mover el archivo a la carpeta uploads. Verifica los permisos de la carpeta.";
    }
}
?>