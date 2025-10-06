<?php
session_start();
require '../php/db.php';

// Solo roles válidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin', 'rrhh', 'jefe_area'])) {
    die("Acceso denegado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doc  = $_POST['id_doc'] ?? null;
    $estado  = $_POST['estado'] ?? null;
    $feedback = $_POST['feedback'] ?? '';

    if ($id_doc && $estado) {
        try {
            $stmt = $pdo->prepare("UPDATE documentos SET estado = ?, feedback = ? WHERE id = ?");
            $stmt->execute([$estado, $feedback, $id_doc]);
            header("Location: panel_jefes.php");
            exit;
        } catch (PDOException $e) {
            die("Error al actualizar: " . $e->getMessage());
        }
    } else {
        die("Datos incompletos.");
    }
} else {
    die("Método no permitido.");
}
?>
