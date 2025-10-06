<?php
session_start();
require '../php/db.php';

// Solo la secretaría puede asignar
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doc_id'], $_POST['area_id'])) {  
    $doc_id  = $_POST['doc_id'];
    $area_id = $_POST['area_id'];

    $stmt = $pdo->prepare("
        UPDATE documentos 
        SET id_area_destino = ?, estado = 'observado'
        WHERE id = ?
    ");
    $stmt->execute([$area_id, $doc_id]);

    try {
        // 1. Traer datos del documento
        $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ?");
        $stmt->execute([$doc_id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($doc) {
            // 2. Insertar en documentos_area
            $insert = $pdo->prepare("
                INSERT INTO documentos_area 
                    (id_documento_origen, id_usuario, id_area_destino, 
                     nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')
            ");
            $insert->execute([
                $doc['id'],
                $doc['id_usuario'],
                $area_id,
                $doc['nombre_original'],
                $doc['nombre_guardado'],
                $doc['tipo'],
                $doc['fecha_subida']
            ]);

            // 3. Eliminar de documentos
            $delete = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
            $delete->execute([$doc_id]);

            $_SESSION['msg'] = "Documento enviado correctamente al área.";
        } else {
            $_SESSION['msg'] = "Documento no encontrado.";
        }

    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
    }
}

// Redirigir de vuelta
header("Location: secretaria_documentos.php");
exit;
?>
