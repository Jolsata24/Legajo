<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

// 2. Opción: Marcar todo como leído al entrar aquí (Descomenta si deseas esta función)
// $pdo->query("UPDATE notificaciones SET leido = 1 WHERE id_usuario_destino = $id_usuario");

try {
    // 3. Obtener TODAS las notificaciones (sin límite)
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$id_usuario]);
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Historial de Notificaciones";
// Reutilizamos admin_dashboard.css porque ya tiene estilos de listas limpios
$extra_css = "../style/admin_dashboard.css"; 

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-bell"></i> Mis Notificaciones</h1>
        <div class="top-actions">
            <a href="empleado_dashboard.php" class="btn-primary" style="background: #6c757d; padding: 8px 15px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="card-container">
            <?php if (empty($todas)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="far fa-bell-slash" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No tienes notificaciones registradas en el historial.</p>
                </div>
            <?php else: ?>
                <ul class="activity-list" style="list-style: none; padding: 0;">
                    <?php foreach ($todas as $n): 
                        // Estilo diferente para no leídas
                        $bg_style = $n['leido'] ? '' : 'background-color: #f0f7ff; border-left: 4px solid #0d6efd;';
                        $icon_color = $n['leido'] ? '#adb5bd' : '#0d6efd';
                    ?>
                    <li style="padding: 15px; border-bottom: 1px solid #eee; transition: background 0.2s; <?= $bg_style ?>">
                        <div style="display: flex; gap: 15px; align-items: start;">
                            
                            <div style="margin-top: 3px;">
                                <i class="fas fa-info-circle" style="color: <?= $icon_color ?>; font-size: 18px;"></i>
                            </div>

                            <div style="flex-grow: 1;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <strong style="color: #333; font-size: 14px;">Sistema de Legajos</strong>
                                    <small style="color: #888; font-size: 12px;">
                                        <?= date("d/m/Y H:i", strtotime($n['fecha_creacion'])) ?>
                                    </small>
                                </div>
                                
                                <p style="color: #555; font-size: 14px; margin: 0 0 8px 0; line-height: 1.4;">
                                    <?= htmlspecialchars($n['mensaje']) ?>
                                </p>
                                
                                <?php if (!empty($n['enlace'])): ?>
                                    <a href="<?= htmlspecialchars($n['enlace']) ?>" style="font-size: 13px; color: var(--color-primario); text-decoration: none; font-weight: 500;">
                                        Ver detalles <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
    .activity-list li:hover { background-color: #fafafa; }
    .activity-list li:last-child { border-bottom: none; }
</style>

<?php require_once '../includes/footer.php'; ?>