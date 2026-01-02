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
    // 1. Contar el TOTAL real de notificaciones no leídas
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino = ? AND leido = 0");
    $stmt_count->execute([$id_usuario]);
    $total_no_leidas = $stmt_count->fetchColumn();

    // 2. Traer las últimas notificaciones para la lista
    $stmt_notif = $pdo->prepare(
        "SELECT id, mensaje, enlace, fecha_creacion, leido 
         FROM notificaciones 
         WHERE id_usuario_destino = ? 
         ORDER BY fecha_creacion DESC 
         LIMIT 10"
    );
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);

    // 3. Devolver ambos datos
    header('Content-Type: application/json');
    echo json_encode([
        'unread_total' => (int)$total_no_leidas,
        'list' => $notificaciones
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de base de datos']);
}
?>