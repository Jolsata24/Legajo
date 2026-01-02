<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // --- CONSULTA DE NOTIFICACIONES ---
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 5");
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();
    // Contar no leídas
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));

    // --- MÉTRICAS ---
    $total_docs = $pdo->query("SELECT COUNT(*) FROM documentos WHERE id_usuario = $id_usuario")->fetchColumn();
    $docs_aprobados = $pdo->query("SELECT COUNT(*) FROM documentos WHERE id_usuario = $id_usuario AND (estado = 'Aprobado' OR estado = 'Validado')")->fetchColumn();
    $docs_pendientes = $pdo->query("SELECT COUNT(*) FROM documentos WHERE id_usuario = $id_usuario AND estado = 'Pendiente'")->fetchColumn();
    $docs_atencion = $pdo->query("SELECT COUNT(*) FROM documentos WHERE id_usuario = $id_usuario AND (estado = 'Observado' OR estado = 'Rechazado')")->fetchColumn();

    // --- ACTIVIDAD RECIENTE ---
    $stmtRecent = $pdo->prepare("
        SELECT d.id, d.nombre_original, d.fecha_subida, d.estado, s.nombre as seccion 
        FROM documentos d
        LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
        WHERE d.id_usuario = ?
        ORDER BY d.fecha_subida DESC LIMIT 5
    ");
    $stmtRecent->execute([$id_usuario]);
    $actividad = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Inicio - Empleado";
// Aseguramos que se cargue el CSS global donde pusimos los estilos
$extra_css = "../style/admin_dashboard.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">

    <header class="topbar">
        <h1><i class="fas fa-tachometer-alt"></i> Panel Principal</h1>

        <div class="top-actions">
            <div class="notifications">
                <a href="#" id="notification-bell">
                    <i class="fas fa-bell"></i>
                    <?php if ($num_no_leidas > 0): ?>
                        <span class="notification-count"><?= $num_no_leidas ?></span>
                    <?php endif; ?>
                </a>

                <div class="notification-dropdown" id="notification-dropdown-list">
                    <div class="dropdown-header">Notificaciones</div>
                    <div class="dropdown-body">
                        <?php if (empty($notificaciones)): ?>
                            <a href="#">
                                <p>No tienes notificaciones nuevas</p>
                            </a>
                        <?php else: ?>
                            <?php foreach ($notificaciones as $n): ?>
                                <a href="<?= htmlspecialchars($n['enlace'] ?: '#') ?>" style="<?= $n['leido'] ? '' : 'background-color: #f0f7ff;' ?>">
                                    <p><?= htmlspecialchars($n['mensaje']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-footer">
                        <a href="notificaciones.php">Ver todas</a>
                    </div>
                </div>
            </div>
            <a href="subir_documento.php" class="btn-primary" style="padding: 8px 15px; font-size: 13px; text-decoration: none; margin-left: 15px;">
                <i class="fas fa-cloud-upload-alt"></i> Subir
            </a>

            <span style="margin-left: 15px; font-size: 14px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>

            <a href="../php/logout.php" class="topbar-logout-btn" title="Cerrar Sesión" style="margin-left: 15px;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <main class="content">
        <h2 style="font-size: 20px; color: #444; margin-bottom: 20px;">
            Hola, <?= htmlspecialchars($_SESSION['nombre']) ?> 👋
        </h2>

        <div class="cards">
            <div class="card">
                <div class="card-icon" style="background: rgba(13,110,253,0.1); color: #0d6efd;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3>Mis Documentos</h3>
                <p><?= $total_docs ?></p>
            </div>

            <div class="card" style="border-left-color: #198754;">
                <div class="card-icon" style="background: rgba(25,135,84,0.1); color: #198754;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Aprobados</h3>
                <p><?= $docs_aprobados ?></p>
            </div>

            <div class="card" style="border-left-color: #ffc107;">
                <div class="card-icon" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>En Revisión</h3>
                <p><?= $docs_pendientes ?></p>
            </div>

            <?php if ($docs_atencion > 0): ?>
                <a href="documentos_enviados.php?filtro=atencion" class="card" style="border-left-color: #dc3545; text-decoration: none; cursor: pointer;">
                    <div class="card-icon" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3 style="color: #dc3545;">Requiere Atención</h3>
                    <p><?= $docs_atencion ?></p>
                </a>
            <?php else: ?>
                <div class="card" style="border-left-color: #0dcaf0;">
                    <div class="card-icon" style="background: rgba(13,202,240,0.1); color: #0dcaf0;">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <h3>Estado General</h3>
                    <p style="font-size: 16px; color: #aaa;">¡Al día!</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-container" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3><i class="fas fa-history"></i> Mis Subidas Recientes</h3>
                <a href="documentos_enviados.php" style="font-size: 13px; color: var(--color-primario); text-decoration: none;">Ver historial completo &rarr;</a>
            </div>

            <?php if (empty($actividad)): ?>
                <div style="text-align: center; padding: 30px; color: #999;">
                    <p>Aún no has subido documentos.</p>
                </div>
            <?php else: ?>
                <table class="activity-list" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #f0f0f0; color: #6c757d; font-size: 13px;">
                            <th style="padding: 10px;">Documento</th>
                            <th style="padding: 10px;">Carpeta</th>
                            <th style="padding: 10px;">Fecha</th>
                            <th style="padding: 10px;">Estado</th>
                            <th style="text-align: right; padding: 10px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividad as $doc):
                            $st = ucfirst($doc['estado']);
                            $bg = '#eee';
                            $col = '#333';
                            if ($st == 'Aprobado' || $st == 'Validado') {
                                $bg = '#d1e7dd';
                                $col = '#0f5132';
                            }
                            if ($st == 'Pendiente') {
                                $bg = '#fff3cd';
                                $col = '#664d03';
                            }
                            if ($st == 'Rechazado') {
                                $bg = '#f8d7da';
                                $col = '#842029';
                            }
                            if ($st == 'Observado') {
                                $bg = '#cff4fc';
                                $col = '#055160';
                            }
                        ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 12px 10px; font-weight: 500; color: #333;">
                                    <?= htmlspecialchars(substr($doc['nombre_original'], 0, 30)) ?>
                                </td>
                                <td style="padding: 12px 10px; color: #666; font-size: 14px;">
                                    <?= htmlspecialchars($doc['seccion'] ?? 'General') ?>
                                </td>
                                <td style="padding: 12px 10px; color: #888; font-size: 13px;">
                                    <?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?>
                                </td>
                                <td style="padding: 12px 10px;">
                                    <span style="background: <?= $bg ?>; color: <?= $col ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                        <?= $st ?>
                                    </span>
                                </td>
                                <td style="text-align: right; padding: 12px 10px;">
                                    <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" title="Ver Detalles" style="color: var(--color-primario);">
                                        <i class="fas fa-eye"></i>
                                    </a>
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