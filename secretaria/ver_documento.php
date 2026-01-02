<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; 

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$id_doc = $_GET['id'] ?? null;
if (!$id_doc) {
    header("Location: secretaria_documentos.php");
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// --- LÓGICA: PROCESAR ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ÚNICA ACCIÓN: GESTIONAR ESTADO Y DESTINO
    if (isset($_POST['accion']) && $_POST['accion'] === 'gestion_completa') {
        
        $nuevo_estado = $_POST['estado']; // 'Aprobado', 'Observado', 'Rechazado'
        $feedback_texto = trim($_POST['feedback'] ?? '');
        $id_area_destino = $_POST['id_area_destino'] ?? null;
        $id_usuario_actual = $_SESSION['id'];

        $proceder = true;

        // VALIDACIÓN 1: Si es APROBADO, debe tener ÁREA DE DESTINO
        if ($nuevo_estado === 'Aprobado' && empty($id_area_destino)) {
            $mensaje_error = "⚠️ Para APROBAR el documento, debes seleccionar el Área de Destino a la que será enviado.";
            $proceder = false;
        }

        // VALIDACIÓN 2: Si es OBSERVADO/RECHAZADO, debe tener FEEDBACK
        if (($nuevo_estado === 'Observado' || $nuevo_estado === 'Rechazado') && empty($feedback_texto)) {
            $mensaje_error = "⚠️ Para Observar o Rechazar, es obligatorio escribir el motivo (Feedback).";
            $proceder = false;
        }

        if ($proceder) {
            try {
                // Preparamos variables según el caso
                $area_sql = null;
                $notif_extra = "";
                
                // Si se aprueba, asignamos el área. Si se rechaza, el área se limpia o se mantiene null.
                if ($nuevo_estado === 'Aprobado') {
                    $area_sql = $id_area_destino;
                    
                    // Obtener nombre del área para el historial/notificación
                    $stmtA = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
                    $stmtA->execute([$area_sql]);
                    $nombre_area = $stmtA->fetchColumn();
                    $notif_extra = " y ha sido enviado al área de: <strong>$nombre_area</strong>";
                }

                // ACTUALIZAR DOCUMENTO
                $stmt = $pdo->prepare("
                    UPDATE documentos 
                    SET estado = ?, feedback = ?, id_area_destino = ?, revisado_por = ?, fecha_revision = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$nuevo_estado, $feedback_texto, $area_sql, $id_usuario_actual, $id_doc]);
                
                // GUARDAR HISTORIAL
                $desc_historial = "Estado cambiado a " . strtoupper($nuevo_estado);
                if ($nuevo_estado === 'Aprobado') {
                    $desc_historial .= ". Derivado a: " . $nombre_area;
                }
                if (!empty($feedback_texto)) {
                    $desc_historial .= ". Feedback: " . $feedback_texto;
                }
                
                $stmtH = $pdo->prepare("INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) VALUES (?, ?, 'GESTION', ?)");
                $stmtH->execute([$id_doc, $id_usuario_actual, $desc_historial]);
                
                // NOTIFICAR AL EMPLEADO
                $stmtUser = $pdo->prepare("SELECT id_usuario FROM documentos WHERE id = ?");
                $stmtUser->execute([$id_doc]);
                $id_empleado = $stmtUser->fetchColumn();
                
                if ($id_empleado) {
                    $icono = ($nuevo_estado === 'Aprobado') ? "✅" : "⚠️";
                    $msj_notif = "$icono Tu documento ha sido " . strtoupper($nuevo_estado) . "$notif_extra.";
                    $link_notif = "../empleado/ver_documento_enviado.php?id=$id_doc";
                    
                    crear_notificacion($pdo, $id_empleado, $msj_notif, $link_notif);
                }
                
                $mensaje_exito = "Proceso completado: Documento " . $nuevo_estado;
                
            } catch (Exception $e) {
                $mensaje_error = "Error en base de datos: " . $e->getMessage();
            }
        }
    }
}

// --- OBTENER DATOS ---
try {
    $sql = "
        SELECT d.*, u.nombre as autor, u.email, s.nombre as seccion, a.nombre as area_actual
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
        LEFT JOIN areas a ON d.id_area_destino = a.id
        WHERE d.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_doc]);
    $doc = $stmt->fetch();
    
    if (!$doc) die("Documento no encontrado.");

    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Revisión: " . $doc['nombre_original'];
$extra_css = "../style/ver_documento.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';

$ruta_archivo = "../uploads/" . $doc['nombre_guardado'];
$ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
?>

<div class="main">
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="secretaria_documentos.php" class="btn-back-circle"><i class="fas fa-arrow-left"></i></a>
            <h1>Gestión de Documento</h1>
        </div>
        <div class="top-actions">
            <a href="<?= $ruta_archivo ?>" download class="btn-secondary"><i class="fas fa-download"></i> Descargar</a>
        </div>
    </header>

    <main class="content">
        
        <?php if ($mensaje_exito): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> <?= $mensaje_exito ?>
            </div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div style="background:#f8d7da; color:#842029; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= $mensaje_error ?>
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
                    <div class="doc-meta-title">Información</div>
                    <div class="meta-row"><span class="meta-label">Autor:</span> <?= htmlspecialchars($doc['autor']) ?></div>
                    <div class="meta-row"><span class="meta-label">Fecha:</span> <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?></div>
                    <div class="meta-row"><span class="meta-label">Destino Actual:</span> 
                        <?= !empty($doc['area_actual']) ? htmlspecialchars($doc['area_actual']) : '<span style="color:#999">Sin asignar</span>' ?>
                    </div>
                    
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                        <span class="meta-label">Estado:</span>
                        <span class="status-current st-<?= strtolower($doc['estado']) ?>" style="display:inline-block; width:auto; margin:0; padding: 2px 8px; font-size: 12px;">
                            <?= ucfirst($doc['estado']) ?>
                        </span>
                    </div>
                </div>

                <div class="action-card" style="border-top-color: var(--color-primario);">
                    <div class="doc-meta-title"><i class="fas fa-tasks"></i> Gestión del Documento</div>
                    
                    <form method="POST" id="gestionForm">
                        <input type="hidden" name="accion" value="gestion_completa">
                        
                        <div class="form-group" style="background: #f0f8ff; padding: 10px; border-radius: 6px; border: 1px solid #cce5ff;">
                            <label style="color: #0d6efd;"><i class="fas fa-share"></i> ¿A dónde se envía?</label>
                            <select name="id_area_destino" id="areaSelect" class="form-control">
                                <option value="">-- Seleccionar Destino (Para Aprobar) --</option>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?= $a['id'] ?>" <?= ($doc['id_area_destino'] == $a['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="font-size: 11px; color: #666;">* Obligatorio para aprobar.</small>
                        </div>

                        <div class="form-group">
                            <label>Feedback / Observaciones:</label>
                            <textarea name="feedback" class="form-control" rows="3" placeholder="Obligatorio si observas o rechazas..."><?= htmlspecialchars($doc['feedback'] ?? '') ?></textarea>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                            
                            <button type="submit" name="estado" value="Aprobado" class="btn-block btn-validate" onclick="return validarAprobacion()">
                                <i class="fas fa-paper-plane"></i> Aprobar y Enviar
                            </button>
                            
                            <div style="display:flex; gap: 5px;">
                                <button type="submit" name="estado" value="Observado" class="btn-block btn-observe">
                                    <i class="fas fa-exclamation-circle"></i> Observar
                                </button>
                                <button type="submit" name="estado" value="Rechazado" class="btn-block btn-reject">
                                    <i class="fas fa-times-circle"></i> Rechazar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
function validarAprobacion() {
    var area = document.getElementById('areaSelect').value;
    if (area === "") {
        alert("⚠️ ATENCIÓN: Para APROBAR el documento, debes seleccionar obligatoriamente el Área de Destino en la lista desplegable azul.");
        return false; // Detiene el envío
    }
    return true; // Permite el envío
}
</script>

<?php require_once '../includes/footer.php'; ?>