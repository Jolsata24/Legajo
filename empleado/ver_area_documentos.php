<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$area_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($area_id <= 0) {
    header("Location: explorar_areas.php");
    exit;
}

try {
    // 2. Obtener Info del Área
    $stmt_area = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt_area->execute([$area_id]);
    $area = $stmt_area->fetch();
    if (!$area) die("Área no encontrada.");

    // 3. Obtener Documentos (Solo 'revisado')
    $stmt_docs = $pdo->prepare("
        SELECT d.*, u.nombre AS usuario_creador
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ? AND d.estado = 'revisado'
        ORDER BY d.fecha_subida DESC
    ");
    $stmt_docs->execute([$area_id]);
    $documentos = $stmt_docs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = $area['nombre'] . " - Documentos";
// RECICLAJE: Usamos el estilo estándar de tablas de documentos
$extra_css = "../style/seccion_legajo.css"; 

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="explorar_areas.php" class="btn-back-circle" title="Volver a Áreas">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($area['nombre']) ?></h2>
                <span>Repositorio Público</span>
            </div>
        </div>
    </header>

    <main class="content">
        
        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>No hay documentos públicos en esta área actualmente.</p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Publicado por</th>
                            <th>Fecha</th>
                            <th style="text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Iconos por extensión
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file file-icon def';
                            if ($ext === 'pdf') $icon = 'fa-file-pdf file-icon pdf';
                            elseif (in_array($ext, ['doc','docx'])) $icon = 'fa-file-word file-icon word';
                            elseif (in_array($ext, ['jpg','png','jpeg'])) $icon = 'fa-file-image file-icon img';
                        ?>
                        <tr>
                            <td>
                                <div class="file-info">
                                    <i class="fas <?= $icon ?>"></i>
                                    <div>
                                        <span class="file-name"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color: #666; font-size: 14px;">
                                <i class="fas fa-user-check" style="margin-right:5px; color:#aaa;"></i>
                                <?= htmlspecialchars($doc['usuario_creador']) ?>
                            </td>
                            <td style="color: #666; font-size: 14px;">
                                <?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" class="action-btn" download title="Descargar Archivo">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="action-btn" title="Visualizar">
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