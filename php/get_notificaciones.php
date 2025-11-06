<?php
session_start();
require 'db.php';

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_usuario = (int)$_SESSION['id'];

try {
    // Traer las 5 notificaciones más recientes NO LEÍDAS
    $stmt_notif = $pdo->prepare(
        "SELECT id, mensaje, enlace, fecha_creacion, leido 
         FROM notificaciones 
         WHERE id_usuario_destino = ? 
         ORDER BY fecha_creacion DESC 
         LIMIT 10"
    );
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($notificaciones);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de base de datos']);
}
?>