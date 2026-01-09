<?php
session_start();
require '../php/db.php';

// 1. SEGURIDAD: Verificar rol de Secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Validar ID de la sección
$id_seccion = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_seccion <= 0) {
    header("Location: mi_legajo.php");
    exit;
}

try {
    // 3. Obtener Info de la Sección
    $stmtSec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmtSec->execute([$id_seccion]);
    $seccion = $stmtSec->fetch(PDO::FETCH_ASSOC);

    if (!$seccion) {
        die("Sección no encontrada.");
    }

    // 4. Obtener Documentos de la SECRETARIA en esta sección
    // Filtramos por id_usuario = sesión actual
    $stmtDocs = $pdo->prepare("
        SELECT * FROM documentos 
        WHERE id_usuario = ? AND id_seccion = ? 
        ORDER BY fecha_subida DESC
    ");
    $stmtDocs->execute([$_SESSION['id'], $id_seccion]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Carpeta: " . $seccion['nombre'];
$extra_css = "../style/seccion_legajo.css"; // Reutilizamos el estilo estándar

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="mi_legajo.php" class="btn-back-circle" title="Volver a Carpetas">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($seccion['nombre']) ?></h2>
                <span><?= count($documentos) ?> documentos en esta carpeta</span>
            </div>
        </div>
        
        <div class="header-actions">
            <a href="subir_doc_personal.php?id_seccion=<?= $id_seccion ?>" class="btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'exito_personal'): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px; border-left: 5px solid #198754;">
                <i class="fas fa-check-circle"></i> Documento subido correctamente.
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
            <div style="background:#f8d7da; color:#842029; padding:15px; border-radius:8px; margin-bottom:20px; border-left: 5px solid #dc3545;">
                <i class="fas fa-trash-alt"></i> Documento eliminado correctamente.
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state" style="text-align: center; padding: 50px; color: #6c757d;">
                    <i class="fas fa-folder-open" style="font-size: 50px; opacity: 0.3; margin-bottom: 20px;"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>No tienes documentos cargados en esta sección.</p>
                    <a href="subir_doc_personal.php?id_seccion=<?= $id_seccion ?>" class="btn-primary" style="margin-top: 15px; display: inline-block;">
                        Subir el primer documento
                    </a>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Fecha Subida</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Determinación de Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file';
                            $iconColor = '#6c757d'; // Default gris

                            if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $iconColor = '#dc3545'; } // Rojo
                            elseif (in_array($ext, ['doc','docx'])) { $icon = 'fa-file-word'; $iconColor = '#0d6efd'; } // Azul
                            elseif (in_array($ext, ['jpg','png','jpeg'])) { $icon = 'fa-file-image'; $iconColor = '#198754'; } // Verde

                            // Determinación de Estado (Badge)
                            $st = strtolower($doc['estado'] ?? 'pendiente');
                            $badgeClass = 'badge-warning'; // Pendiente (Amarillo/Naranja)
                            if ($st == 'aprobado' || $st == 'validado') $badgeClass = 'badge-success'; // Verde
                            if ($st == 'rechazado') $badgeClass = 'badge-danger'; // Rojo
                        ?>
                        <tr>
                            <td>
                                <div class="file-info" style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas <?= $icon ?>" style="font-size: 20px; color: <?= $iconColor ?>;"></i>
                                    <span class="file-name" style="font-weight: 500;"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                </div>
                            </td>
                            <td><?= strtoupper($ext) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?></td>
                            <td>
                                <span class="badge <?= $badgeClass ?>" style="padding: 5px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                    <?= ucfirst($doc['estado']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="action-btn" title="Ver Documento" style="color: #6c757d; margin-right: 10px; font-size: 16px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($st !== 'validado' && $st !== 'aprobado'): ?>
                                    <a href="eliminar_doc_personal.php?id=<?= $doc['id'] ?>&seccion=<?= $id_seccion ?>" 
                                       class="action-btn delete" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás segura de eliminar este documento permanentemente?');"
                                       style="color: #dc3545; font-size: 16px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
    .badge-success { background-color: #d1e7dd; color: #0f5132; }
    .badge-warning { background-color: #fff3cd; color: #664d03; }
    .badge-danger { background-color: #f8d7da; color: #842029; }
</style>

<?php require_once '../includes/footer.php'; ?>