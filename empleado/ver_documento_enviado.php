<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_doc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_doc) header("Location: documentos_enviados.php");

try {
    // 1. NOTIFICACIONES
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 5");
    $stmt_notif->execute([$_SESSION['id']]);
    $notificaciones = $stmt_notif->fetchAll();
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));

    // 2. Datos del Documento
    $sql = "SELECT d.*, s.nombre as seccion, a.nombre as area_destino 
            FROM documentos d
            LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
            LEFT JOIN areas a ON d.id_area_destino = a.id
            WHERE d.id = ? AND d.id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_doc, $_SESSION['id']]);
    $doc = $stmt->fetch();

    if (!$doc) die("Documento no encontrado o acceso denegado.");

    // 3. Historial
    $stmtHist = $pdo->prepare("
        SELECT h.*, u.nombre as actor 
        FROM documentos_historial h
        LEFT JOIN usuarios u ON h.id_usuario_accion = u.id
        WHERE h.id_documento = ? 
        ORDER BY h.fecha DESC 
    ");
    $stmtHist->execute([$id_doc]);
    $historial = $stmtHist->fetchAll();

    // 4. NUEVO: Obtener lista de Áreas para el formulario de corrección
    $stmtAreas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC");
    $areas = $stmtAreas->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Detalle: " . $doc['nombre_original'];
$extra_css = "../style/ver_documento.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';

$ruta_archivo = "../uploads/" . $doc['nombre_guardado'];
$ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
$st_lower = strtolower($doc['estado']);

$estado_class = 'st-pendiente';
if (in_array($st_lower, ['aprobado', 'validado'])) $estado_class = 'st-validado';
if ($st_lower === 'rechazado') $estado_class = 'st-rechazado';
if ($st_lower === 'observado') $estado_class = 'st-observado';
?>

<div class="main">
    
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="documentos_enviados.php" class="btn-back-circle" title="Volver"><i class="fas fa-arrow-left"></i></a>
            <h1>Detalle del Documento</h1>
        </div>

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
                            <div style="padding:15px; text-align:center; color:#777;">Sin novedades</div>
                        <?php else: ?>
                            <?php foreach($notificaciones as $n): ?>
                                <a href="../php/marcar_leido.php?id=<?= $n['id'] ?>" style="<?= $n['leido']?'':'background:#f0f8ff; border-left:3px solid #0d6efd;' ?>">
                                    <p style="margin:0; font-size:13px;"><?= htmlspecialchars($n['mensaje']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-footer">
                        <a href="notificaciones.php">Ver todas</a>
                    </div>
                </div>
            </div>
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
            
        </div>
    </header>

    <main class="content">
        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'reemplazado'): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px; border: 1px solid #badbcc;">
                <i class="fas fa-check-circle"></i> <strong>¡Enviado!</strong> El documento ha sido corregido y enviado a revisión nuevamente.
            </div>
        <?php endif; ?>

        <div class="doc-viewer-layout">
            <div class="viewer-container">
                <?php if ($ext === 'pdf'): ?>
                    <iframe src="<?= $ruta_archivo ?>" class="viewer-iframe"></iframe>
                <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                    <div style="text-align:center; padding: 20px;">
                        <img src="<?= $ruta_archivo ?>" class="viewer-img" style="max-width: 100%; border-radius: 8px;">
                    </div>
                <?php else: ?>
                    <div class="no-preview">
                        <i class="fas fa-file-download" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i>
                        <p>Vista previa no disponible.</p>
                        <a href="<?= $ruta_archivo ?>" class="btn-primary" download>Descargar</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="controls-container">
                <div class="info-card">
                    <div class="doc-meta-title"><i class="fas fa-info-circle"></i> Información</div>
                    <div class="meta-row"><span class="meta-label">Archivo:</span> <?= htmlspecialchars($doc['nombre_original']) ?></div>
                    <div class="meta-row"><span class="meta-label">Enviado:</span> <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?></div>
                    
                    <div class="meta-row">
                        <span class="meta-label">Destino:</span> 
                        <?php if (!empty($doc['area_destino'])): ?>
                            <span class="badge bg-primary" style="background:#e7f1ff; color:#0d6efd; padding:2px 6px; border-radius:4px;">
                                <?= htmlspecialchars($doc['area_destino']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:red;">⚠ Sin asignar</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-card" style="border-top: 4px solid var(--color-primario);">
                    <div class="doc-meta-title">Estado</div>
                    <div class="status-current <?= $estado_class ?>"><?= ucfirst($doc['estado']) ?></div>

                    <?php if (!empty($doc['feedback'])): ?>
                        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; font-size: 13px; margin-top: 15px; border-left: 4px solid #ffc107;">
                            <strong><i class="fas fa-exclamation-triangle"></i> Observación:</strong><br>
                            <?= nl2br(htmlspecialchars($doc['feedback'])) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($st_lower === 'rechazado' || $st_lower === 'observado'): ?>
                        <hr style="margin: 20px 0; border: 0; border-top: 1px dashed #ccc;">
                        <h4 style="font-size: 15px; margin-bottom: 15px; color: #dc3545;"><i class="fas fa-sync-alt"></i> Corregir Documento</h4>
                        
                        <form action="reemplazar_documento.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_documento" value="<?= $doc['id'] ?>">
                            
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="font-size: 13px; font-weight: 500; display: block; margin-bottom: 5px; color:#555;">Confirmar Área de Destino:</label>
                                <select name="id_area_destino" class="form-control" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area['id'] ?>" <?= ($area['id'] == $doc['id_area_destino']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($area['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="font-size: 13px; font-weight: 500; display: block; margin-bottom: 5px; color:#555;">Subir nueva versión:</label>
                                <input type="file" name="nuevo_documento" class="form-control" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="width: 100%; font-size: 12px;">
                            </div>
                            
                            <button type="submit" class="btn-block" style="background: #dc3545; color: white; width: 100%; padding: 10px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                                <i class="fas fa-paper-plane"></i> Enviar Corrección
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="info-card" style="margin-top: 20px;">
                    <div class="doc-meta-title"><i class="fas fa-history"></i> Trazabilidad</div>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach($historial as $h): ?>
                                <li style="border-left: 2px solid #e0e0e0; padding-left: 15px; margin-bottom: 15px; position: relative;">
                                    <div style="position: absolute; left: -5px; top: 0; width: 8px; height: 8px; background: #bbb; border-radius: 50%;"></div>
                                    <div style="font-size: 11px; color: #888;"><?= date("d/m/Y H:i", strtotime($h['fecha'])) ?></div>
                                    <div style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($h['accion']) ?></div>
                                    <?php if(!empty($h['descripcion'])): ?>
                                        <div style="font-size: 12px; color: #555;"><?= htmlspecialchars($h['descripcion']) ?></div>
                                    <?php endif; ?>
                                    <div style="font-size: 11px; color: var(--color-primario);"><i class="fas fa-user"></i> <?= htmlspecialchars($h['actor'] ?? 'Sistema') ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>