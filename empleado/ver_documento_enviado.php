<?php
session_start();
require '../php/db.php';

// Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_doc = $_GET['id'] ?? null;
if (!$id_doc) header("Location: empleado_dashboard.php");

try {
    // 1. Datos del Documento
    $sql = "SELECT d.*, s.nombre as seccion, a.nombre as area_destino 
            FROM documentos d
            LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
            LEFT JOIN areas a ON d.id_area_destino = a.id
            WHERE d.id = ? AND d.id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_doc, $_SESSION['id']]);
    $doc = $stmt->fetch();

    if (!$doc) die("Acceso denegado o documento no encontrado.");

    // 2. TRAZABILIDAD (Historial) - CORREGIDO
    // Se cambió 'h.fecha_accion' por 'h.fecha'
    $stmtHist = $pdo->prepare("
        SELECT h.*, u.nombre as actor 
        FROM documentos_historial h
        LEFT JOIN usuarios u ON h.id_usuario_accion = u.id
        WHERE h.id_documento = ? 
        ORDER BY h.fecha DESC 
    ");
    $stmtHist->execute([$id_doc]);
    $historial = $stmtHist->fetchAll();

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

// Colores
$estado_class = 'st-pendiente';
if ($st_lower === 'aprobado') $estado_class = 'st-validado';
if ($st_lower === 'rechazado') $estado_class = 'st-rechazado';
?>

<div class="main">
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="empleado_dashboard.php" class="btn-back-circle"><i class="fas fa-arrow-left"></i></a>
            <h1>Seguimiento de Documento</h1>
        </div>
    </header>

    <main class="content">
        
        <?php if(isset($_GET['msg']) && $_GET['msg']=='reemplazado'): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> ¡Corrección enviada correctamente! El documento está pendiente de revisión.
            </div>
        <?php endif; ?>

        <div class="doc-viewer-layout">
            
            <div class="viewer-container">
                <?php if ($ext === 'pdf'): ?>
                    <iframe src="<?= $ruta_archivo ?>" class="viewer-iframe"></iframe>
                <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                    <img src="<?= $ruta_archivo ?>" class="viewer-img">
                <?php else: ?>
                    <div class="no-preview">
                        <i class="fas fa-file-alt"></i>
                        <p>Vista previa no disponible.</p>
                        <a href="<?= $ruta_archivo ?>" class="btn-primary">Descargar</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="controls-container">
                
                <div class="info-card">
                    <div class="doc-meta-title">Datos</div>
                    <div class="meta-row"><span class="meta-label">Archivo:</span> <?= htmlspecialchars($doc['nombre_original']) ?></div>
                    <div class="meta-row"><span class="meta-label">Carpeta:</span> <?= htmlspecialchars($doc['seccion']) ?></div>
                    
                    <?php if (!empty($doc['area_destino'])): ?>
                    <div class="meta-row"><span class="meta-label">Destino:</span> <strong><?= htmlspecialchars($doc['area_destino']) ?></strong></div>
                    <?php endif; ?>
                </div>

                <div class="action-card" style="border-top-color: var(--color-primario);">
                    <div class="doc-meta-title">Estado Actual</div>
                    
                    <div class="status-current <?= $estado_class ?>">
                        <?= ucfirst($doc['estado']) ?>
                    </div>

                    <?php if (!empty($doc['feedback'])): ?>
                        <div style="background: #fff3cd; color: #664d03; padding: 15px; border-radius: 8px; font-size: 14px; border-left: 4px solid #ffc107;">
                            <strong><i class="fas fa-comment"></i> Motivo / Observación:</strong><br>
                            <?= nl2br(htmlspecialchars($doc['feedback'])) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($st_lower === 'rechazado' || $st_lower === 'observado'): ?>
                        <hr style="margin: 20px 0; border: 0; border-top: 1px dashed #ccc;">
                        
                        <h4 style="font-size: 14px; margin-bottom: 10px; color: #333;">
                            <i class="fas fa-sync-alt"></i> Subir Corrección
                        </h4>
                        
                        <form action="reemplazar_doc.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_documento" value="<?= $doc['id'] ?>">
                            
                            <div class="form-group">
                                <label style="font-size: 12px; color: #666;">Selecciona el archivo corregido:</label>
                                <input type="file" name="nuevo_documento" class="form-control" required accept=".pdf,.doc,.docx,.jpg,.png">
                            </div>

                            <button type="submit" class="btn-block" style="background: var(--color-primario); color: white;">
                                Reemplazar y Enviar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="info-card" style="margin-top: 20px;">
                    <div class="doc-meta-title"><i class="fas fa-history"></i> Historial de Acciones</div>
                    
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php if(empty($historial)): ?>
                            <p style="font-size: 12px; color: #999;">Sin historial registrado.</p>
                        <?php else: ?>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach($historial as $h): ?>
                                    <li style="border-bottom: 1px solid #f0f0f0; padding: 10px 0;">
                                        <small style="display: block; color: #999; font-size: 11px;">
                                            <?= date("d/m/Y H:i", strtotime($h['fecha'])) ?>
                                        </small>
                                        <div style="font-size: 13px; font-weight: 500; color: #333;">
                                            <?= htmlspecialchars($h['accion']) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?= htmlspecialchars($h['descripcion']) ?>
                                        </div>
                                        <small style="color: var(--color-primario);">
                                            Por: <?= htmlspecialchars($h['actor']) ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>