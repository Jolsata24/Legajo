<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];
$id_seccion = $_GET['id'] ?? null;

if (!$id_seccion) {
    header("Location: mi_legajo.php");
    exit;
}

try {
    // 2. Obtener Nombre de la Sección
    $stmtSec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmtSec->execute([$id_seccion]);
    $seccion = $stmtSec->fetch();

    if (!$seccion) die("Carpeta no encontrada.");

    // 3. Obtener Documentos del Usuario en esta Sección
    $stmtDocs = $pdo->prepare("
        SELECT * FROM documentos 
        WHERE id_usuario = ? AND id_seccion = ? 
        ORDER BY fecha_subida DESC
    ");
    $stmtDocs->execute([$id_usuario, $id_seccion]);
    $documentos = $stmtDocs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Carpeta: " . $seccion['nombre'];
// RECICLAJE: Usamos el estilo estándar de listas de archivos
$extra_css = "../style/seccion_legajo.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="mi_legajo.php" class="btn-back-circle" title="Volver a Carpetas">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($seccion['nombre']) ?></h2>
                <span>Explorador de Archivos</span>
            </div>
        </div>
        
        <div class="header-right">
            <a href="subir_documento.php?seccion=<?= $id_seccion ?>" class="btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir aquí
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>No tienes documentos registrados en esta sección.</p>
                    <a href="subir_documento.php?seccion=<?= $id_seccion ?>" style="color: var(--color-primario); text-decoration: none; font-weight: 500;">
                        Subir el primero &rarr;
                    </a>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Fecha de Subida</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Iconos según extensión
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file file-icon def';
                            if ($ext === 'pdf') $icon = 'fa-file-pdf file-icon pdf';
                            elseif (in_array($ext, ['doc','docx'])) $icon = 'fa-file-word file-icon word';
                            elseif (in_array($ext, ['jpg','png','jpeg'])) $icon = 'fa-file-image file-icon img';

                            // Estado visual
                            $st_lower = strtolower($doc['estado']);
                            $badge = 'pendiente'; // Clase CSS base
                            if ($st_lower === 'aprobado') $badge = 'validado';
                            elseif ($st_lower === 'rechazado') $badge = 'rechazado';
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
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            <td>
                                <span class="badge <?= $badge ?>"><?= ucfirst($doc['estado']) ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" class="action-btn" title="Ver Estado y Detalles">
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