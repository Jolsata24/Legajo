<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$id_doc = $_GET['id'] ?? null;
if (!$id_doc) die("Documento no especificado.");

// A. Procesar Cambio de Estado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = $_POST['estado'];
    $observacion  = trim($_POST['observacion'] ?? '');
    
    // Actualizar documento
    $stmtUpd = $pdo->prepare("UPDATE documentos SET estado = ?, observaciones = ? WHERE id = ?");
    if ($stmtUpd->execute([$nuevo_estado, $observacion, $id_doc])) {
        
        // (Opcional) Crear notificación para el usuario
        // ... Lógica de notificación aquí ...
        
        $mensaje_exito = "El documento ha sido actualizado a: " . strtoupper($nuevo_estado);
    } else {
        $mensaje_error = "Error al actualizar.";
    }
}

// B. Obtener Datos del Documento + Usuario
try {
    $sql = "
        SELECT d.*, u.nombre as autor, u.email, u.rol, s.nombre as seccion
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
        WHERE d.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_doc]);
    $doc = $stmt->fetch();

    if (!$doc) die("Documento no encontrado.");

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Revisión: " . $doc['nombre_original'];
$extra_css = "../style/ver_documento.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';

// Preparar visor
$ruta_archivo = "../uploads/" . $doc['nombre_guardado'];
$ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
$es_imagen = in_array($ext, ['jpg','jpeg','png','gif']);
$es_pdf = ($ext === 'pdf');

// Clases de estado para visualización
$estado_class = 'st-pendiente';
if ($doc['estado'] == 'validado') $estado_class = 'st-validado';
if ($doc['estado'] == 'rechazado') $estado_class = 'st-rechazado';
?>

<div class="main">
    
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="javascript:history.back()" class="btn-back-circle">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Revisión de Documento</h1>
        </div>
        <div class="top-actions">
            <a href="<?= $ruta_archivo ?>" download="<?= $doc['nombre_original'] ?>" class="btn-secondary">
                <i class="fas fa-download"></i> Descargar
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if (isset($mensaje_exito)): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> <?= $mensaje_exito ?>
            </div>
        <?php endif; ?>

        <div class="doc-viewer-layout">
            
            <div class="viewer-container">
                <?php if ($es_pdf): ?>
                    <iframe src="<?= $ruta_archivo ?>" class="viewer-iframe"></iframe>
                <?php elseif ($es_imagen): ?>
                    <img src="<?= $ruta_archivo ?>" class="viewer-img" alt="Documento">
                <?php else: ?>
                    <div class="no-preview">
                        <i class="fas fa-file-alt"></i>
                        <p>Este archivo no tiene vista previa.</p>
                        <a href="<?= $ruta_archivo ?>" class="btn-primary">Descargar para ver</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="controls-container">
                
                <div class="info-card">
                    <div class="doc-meta-title">Detalles del Archivo</div>
                    
                    <div class="meta-row">
                        <span class="meta-label">Nombre del Archivo</span>
                        <span class="meta-value"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Subido Por</span>
                        <span class="meta-value">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($doc['autor']) ?>
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Sección / Carpeta</span>
                        <span class="meta-value"><?= htmlspecialchars($doc['seccion'] ?? 'General') ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Fecha de Subida</span>
                        <span class="meta-value"><?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?></span>
                    </div>
                </div>

                <div class="action-card">
                    <div class="doc-meta-title">Validación</div>

                    <div class="status-current <?= $estado_class ?>">
                        Estado Actual: <?= ucfirst($doc['estado']) ?>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Observaciones (Opcional):</label>
                            <textarea name="observacion" class="form-control" placeholder="Escribe un motivo si rechazas u observas..."><?= htmlspecialchars($doc['observaciones'] ?? '') ?></textarea>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button type="submit" name="estado" value="validado" class="btn-block btn-validate">
                                <i class="fas fa-check"></i> Validar Documento
                            </button>
                            
                            <button type="submit" name="estado" value="observado" class="btn-block btn-observe">
                                <i class="fas fa-exclamation-triangle"></i> Observar
                            </button>
                            
                            <button type="submit" name="estado" value="rechazado" class="btn-block btn-reject">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    </form>
                </div>
                
                <div style="text-align: center; margin-top: 10px;">
                    <a href="../php/eliminar_doc.php?id=<?= $doc['id'] ?>" onclick="return confirm('¿Borrar definitivamente?')" style="color: #dc3545; font-size: 13px; text-decoration: underline;">
                        Eliminar este documento permanentemente
                    </a>
                </div>

            </div>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>