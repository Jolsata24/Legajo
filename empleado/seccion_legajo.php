<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Validar ID de sección
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

    // 4. Obtener Documentos de esta sección
    $stmtDocs = $pdo->prepare("
        SELECT * FROM documentos 
        WHERE id_usuario = ? AND id_seccion = ? 
        ORDER BY fecha_subida DESC
    ");
    $stmtDocs->execute([$_SESSION['id'], $id_seccion]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = $seccion['nombre'];
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
                <span><?= count($documentos) ?> documentos</span>
            </div>
        </div>

        <div class="header-actions">
            <a href="subir_doc_personal.php?id_seccion=<?= $id_seccion ?>" class="btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Aquí
            </a>
        </div>
    </header>

    <main class="content">

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'exito_personal'): ?>
            <div style="background:#d1e7dd; color:#0f5132; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> Documento guardado correctamente en esta carpeta.
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>No tienes documentos en esta sección.</p>
                    <a href="subir_doc_personal.php?id_seccion=<?= $id_seccion ?>" class="btn-primary" style="margin-top: 15px;">
                        Subir el primer documento
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
                            // Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file';
                            if ($ext === 'pdf') $icon = 'fa-file-pdf';
                            elseif (in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word';
                            elseif (in_array($ext, ['jpg', 'png'])) $icon = 'fa-file-image';

                            // Badges
                            $st = ucfirst($doc['estado']);
                            $badgeClass = 'badge-secondary';
                            if ($st == 'Aprobado') $badgeClass = 'badge-success';
                            if ($st == 'Pendiente') $badgeClass = 'badge-warning';
                            if ($st == 'Rechazado') $badgeClass = 'badge-danger';
                        ?>
                            <tr>
                                <td>
                                    <div class="file-info">
                                        <i class="fas <?= $icon ?> file-icon"></i>
                                        <div>
                                            <span class="file-name"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= $st ?></span></td>
                                <td style="text-align: right;">
                                    <a href="../uploads/<?= $doc['nombre_guardado'] ?>" class="action-btn" download title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="../uploads/<?= $doc['nombre_guardado'] ?>" target="_blank" class="action-btn" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <?php if ($doc['estado'] !== 'Aprobado' && $doc['estado'] !== 'Validado'): ?>
                                        <a href="eliminar_doc_personal.php?id=<?= $doc['id'] ?>&seccion=<?= $id_seccion ?>"
                                            class="action-btn btn-delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar este documento de tu legajo?');"
                                            style="color: #dc3545;">
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
    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }

    .badge-success {
        background: #198754;
    }

    .badge-warning {
        background: #ffc107;
        color: #000;
    }

    .badge-danger {
        background: #dc3545;
    }

    .badge-secondary {
        background: #6c757d;
    }
</style>

<?php require_once '../includes/footer.php'; ?>