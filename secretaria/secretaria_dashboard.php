<?php
session_start();
require '../php/db.php';

// 1. Seguridad: Verificar Rol Secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // --- CONSULTAS DASHBOARD ---
    
    // 1. Totales Generales (O ajusta a "WHERE id_area = ..." si solo ve su área)
    $total_docs = $pdo->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
    $total_pendientes = $pdo->query("SELECT COUNT(*) FROM documentos WHERE estado = 'pendiente'")->fetchColumn();
    $total_empleados = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'empleado'")->fetchColumn();
    
    // 2. Documentos Recientes (Globales o de área)
    $stmtRecent = $pdo->prepare("
        SELECT d.nombre_original, d.fecha_subida, d.estado, u.nombre as autor 
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        ORDER BY d.fecha_subida DESC LIMIT 5
    ");
    $stmtRecent->execute();
    $docs_recientes = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Panel de Secretaria";
// ¡AQUÍ ESTÁ LA MAGIA! RECICLAMOS EL ESTILO DEL ADMIN
$extra_css = "../style/admin_dashboard.css"; 

// Usamos header_secretaria si tienes uno específico, o el general adaptado
require_once '../includes/header_secretaria.php'; 
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-columns"></i> Panel de Control</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
            <a href="../php/logout.php" class="topbar-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="cards">
            <div class="card">
                <h3>Documentos Totales</h3>
                <p><?= $total_docs ?></p>
            </div>
            <div class="card">
                <h3>Pendientes de Revisión</h3>
                <p><?= $total_pendientes ?></p>
            </div>
            <div class="card">
                <h3>Empleados Activos</h3>
                <p><?= $total_empleados ?></p>
            </div>
            <a href="subir_doc_personal.php" class="card" style="text-decoration: none; border-left-color: var(--color-exito);">
                <h3 style="color: var(--color-exito);"><i class="fas fa-plus-circle"></i> Nueva Subida</h3>
                <p style="font-size: 16px; margin-top: 5px; color: #666;">Subir documento al sistema</p>
            </a>
        </div>

        <div class="card-container">
            <h3><i class="fas fa-clock"></i> Últimos Documentos Recibidos</h3>
            
            <?php if(empty($docs_recientes)): ?>
                <p style="color: #888; text-align: center; padding: 20px;">No hay documentos recientes.</p>
            <?php else: ?>
                <table class="activity-list" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #eee;">
                            <th style="padding: 10px; font-size: 12px; color: #666;">Documento</th>
                            <th style="padding: 10px; font-size: 12px; color: #666;">Subido Por</th>
                            <th style="padding: 10px; font-size: 12px; color: #666;">Fecha</th>
                            <th style="padding: 10px; font-size: 12px; color: #666;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($docs_recientes as $doc): 
                            $estadoColor = $doc['estado'] == 'pendiente' ? '#ffc107' : '#198754';
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px 10px; font-weight: 500;">
                                <i class="far fa-file-alt" style="margin-right: 8px; color: #666;"></i>
                                <?= htmlspecialchars($doc['nombre_original']) ?>
                            </td>
                            <td style="padding: 12px 10px;"><?= htmlspecialchars($doc['autor']) ?></td>
                            <td style="padding: 12px 10px; font-size: 13px;"><?= date("d/m H:i", strtotime($doc['fecha_subida'])) ?></td>
                            <td style="padding: 12px 10px;">
                                <span style="font-size: 11px; padding: 3px 8px; border-radius: 10px; background: <?= $estadoColor ?>20; color: <?= $estadoColor ?>; font-weight: 600;">
                                    <?= ucfirst($doc['estado']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>