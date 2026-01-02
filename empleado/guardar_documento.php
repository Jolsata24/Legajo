<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id'];
    $id_area_destino = intval($_POST['id_area_destino']);

    if (!isset($_FILES['documento']) || $_FILES['documento']['error'] != 0) {
        die("Error al subir el archivo");
    }

    $nombre_original = $_FILES['documento']['name'];
    // Generar nombre único para evitar conflictos
    $nombre_guardado = uniqid() . "_" . basename($nombre_original);
    $ruta_destino = "../uploads/" . $nombre_guardado;

    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_destino)) {
        $tipo = $_FILES['documento']['type'];

        // CORRECCIÓN: Se agrega 'estado' como 'Pendiente' y 'fecha_subida' explícitamente
        $sql = "INSERT INTO documentos (id_usuario, id_area_destino, nombre_original, nombre_guardado, tipo, estado, fecha_subida) VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())";
        
        $stmt = $conn->prepare($sql);
        // "iisss" se convierte en "iissss" por el parámetro extra del tipo de archivo (si 'estado' va hardcoded en la query no cuenta como parametro bind)
        // Revisamos los bind_param: 
        // 1. id_usuario (i)
        // 2. id_area_destino (i)
        // 3. nombre_original (s)
        // 4. nombre_guardado (s)
        // 5. tipo (s)
        $stmt->bind_param("iisss", $id_usuario, $id_area_destino, $nombre_original, $nombre_guardado, $tipo);
        
        if ($stmt->execute()) {
            echo "Documento enviado correctamente. Estado inicial: Pendiente.";
            echo "<br><a href='../into/dashboard_empleado.php'>Volver al inicio</a>";
        } else {
            echo "Error en la base de datos: " . $stmt->error;
        }
    } else {
        echo "Error al mover el archivo a la carpeta uploads.";
    }
}
?>