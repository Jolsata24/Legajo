<?php
// En php/funciones.php

require_once 'db.php';

/**
 * Crea una notificación para un usuario específico.
 *
 * @param PDO $pdo La conexión a la base de datos.
 * @param int $id_usuario_destino El ID del usuario que recibirá la notificación.
 * @param string $mensaje El texto de la notificación.
 * @param string|null $enlace El link al que dirigirá la notificación.
 */
function crear_notificacion($pdo, $id_usuario_destino, $mensaje, $enlace = null) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO notificaciones (id_usuario_destino, mensaje, enlace) VALUES (?, ?, ?)"
        );
        $stmt->execute([$id_usuario_destino, $mensaje, $enlace]);
    } catch (PDOException $e) {
        // En un sistema real, aquí se registraría el error en un log.
        // Por ahora, no hacemos nada para no detener el flujo principal.
    }
}

// Aquí puedes añadir más funciones globales en el futuro.