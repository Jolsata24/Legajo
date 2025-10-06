<?php
session_start();
require 'db.php';

// Seguridad: Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    die("Acceso denegado.");
}

$id_notificacion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_usuario = (int)$_SESSION['id'];

if ($id_notificacion > 0) {
    try {
        // Buscamos la notificación para obtener el enlace y asegurarnos de que pertenece al usuario
        $stmt = $pdo->prepare(
            "SELECT enlace FROM notificaciones WHERE id = ? AND id_usuario_destino = ?"
        );
        $stmt->execute([$id_notificacion, $id_usuario]);
        $notificacion = $stmt->fetch();

        if ($notificacion) {
            // Marcamos la notificación como leída
            $stmt_update = $pdo->prepare(
                "UPDATE notificaciones SET leido = TRUE WHERE id = ?"
            );
            $stmt_update->execute([$id_notificacion]);

            // Redirigimos al enlace del documento o a una página por defecto
            if (!empty($notificacion['enlace'])) {
                header("Location: " . $notificacion['enlace']);
                exit;
            }
        }
    } catch (PDOException $e) {
        // Manejar error si es necesario
    }
}

// Si no hay enlace o algo falla, redirigimos al dashboard principal del rol
$dashboard_url = '../' . $_SESSION['rol'] . '/' . $_SESSION['rol'] . '_dashboard.php';
header("Location: " . $dashboard_url);
exit;
?>