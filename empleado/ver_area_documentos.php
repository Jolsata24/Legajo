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
    // 2. Info Área
    $stmt_area = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt_area->execute([$area_id]);
    $area = $stmt_area->fetch();
    if (!$area) die("Área no encontrada.");

    // 3. Obtener Documentos CORREGIDO
    // Mostramos documentos 'Aprobado' o 'Validado'.
    // NOTA: No mostramos 'Pendiente' ni 'Rechazado' porque esos son privados entre el empleado y la secretaria.
    $stmt_docs = $pdo->prepare("
        SELECT d.*, u.nombre AS usuario_creador, u.foto AS usuario_foto
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ? 
        AND (d.estado = 'Aprobado' OR d.estado = 'Validado')
        ORDER BY d.fecha_subida DESC
    ");
    $stmt_docs->execute([$area_id]);
    $documentos = $stmt_docs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = $area['nombre'];
$extra_css = "../style/seccion_legajo.css"; 

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="explorar_areas.php" class="btn-back-circle" title="Volver">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($area['nombre']) ?></h2>
                <span>Documentación Pública</span>
            </div>
        </div>
    </header>

    <main class="content">
        
        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state" style="padding: 50px; text-align: center; color: #777;">
                    <i class="fas fa-folder-open" style="font-size: 50px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>Esta área aún no tiene documentos aprobados para visualización pública.</p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Publicado por</th>
                            <th>Fecha</th>
                            <th style="text-align: right;">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file'; 
                            $color = '#6c757d';
                            
                            if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $color = '#dc3545'; }
                            elseif (in_array($ext, ['doc','docx'])) { $icon = 'fa-file-word'; $color = '#0d6efd'; }
                            elseif (in_array($ext, ['jpg','png','jpeg'])) { $icon = 'fa-file-image'; $color = '#198754'; }

                            $foto = !empty($doc['usuario_foto']) ? "../uploads/usuarios/".$doc['usuario_foto'] : "../img/user.png";
                        ?>
                        <tr>
                            <td>
                                <div class="file-info" style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas <?= $icon ?>" style="font-size: 20px; color: <?= $color ?>;"></i>
                                    <span class="file-name" style="font-weight: 500;"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img src="<?= $foto ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                    <span style="font-size: 13px; color: #555;"><?= htmlspecialchars($doc['usuario_creador']) ?></span>
                                </div>
                            </td>
                            <td style="color: #666; font-size: 13px;">
                                <?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="action-btn" title="Visualizar" style="color: var(--color-primario); font-size: 16px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" download class="action-btn" title="Descargar" style="color: #28a745; margin-left: 10px; font-size: 16px;">
                                    <i class="fas fa-download"></i>
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