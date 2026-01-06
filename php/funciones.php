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


function registrar_auditoria($pdo, $id_usuario, $accion, $detalle = null) {
    try {
        // Obtener datos básicos del usuario si es posible
        $rol = 'desconocido';
        $nombre = 'Anónimo';
        
        if ($id_usuario) {
            $stmtUser = $pdo->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
            $stmtUser->execute([$id_usuario]);
            $user = $stmtUser->fetch();
            if ($user) {
                $nombre = $user['nombre'];
                $rol = $user['rol'];
            }
        }

        // Obtener IP del cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt = $pdo->prepare("
            INSERT INTO auditoria_logs (id_usuario, usuario_nombre, rol_usuario, accion, detalle, ip_origen) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id_usuario, $nombre, $rol, $accion, $detalle, $ip]);

    } catch (PDOException $e) {
        // Error silencioso para no romper la app principal
        error_log("Error auditoría: " . $e->getMessage());
    }
}

// Aquí puedes añadir más funciones globales en el futuro.
