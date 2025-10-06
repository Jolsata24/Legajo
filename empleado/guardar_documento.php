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
    $nombre_guardado = uniqid() . "_" . basename($nombre_original);
    $ruta_destino = "../uploads/" . $nombre_guardado;

    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_destino)) {
        $tipo = $_FILES['documento']['type'];

        $stmt = $conn->prepare("INSERT INTO documentos (id_usuario, id_area_destino, nombre_original, nombre_guardado, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $id_usuario, $id_area_destino, $nombre_original, $nombre_guardado, $tipo);
        $stmt->execute();

        echo "Documento enviado correctamente.";
        echo "<br><a href='../into/dashboard_empleado.php'>Volver al inicio</a>";
    } else {
        echo "Error al mover el archivo.";
    }
}
?>
