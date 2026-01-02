<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión (Secretaria)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
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
    // 2. Info de la Sección
    $stmtSec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmtSec->execute([$id_seccion]);
    $seccion = $stmtSec->fetch();

    if (!$seccion) die("Sección no encontrada.");

    // 3. Documentos de la Secretaria en esta sección
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
// RECICLAJE: Usamos el mismo estilo que el admin
$extra_css = "../style/seccion_legajo.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="mi_legajo.php" class="btn-back-circle" title="Volver">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($seccion['nombre']) ?></h2>
                <span>Mi Legajo Digital</span>
            </div>
        </div>
        
        <div class="header-right">
            <a href="subir_doc_personal.php?seccion_id=<?= $id_seccion ?>" class="btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Carpeta Vacía</h3>
                    <p>No tienes documentos en esta sección.</p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file file-icon def';
                            if ($ext === 'pdf') $icon = 'fa-file-pdf file-icon pdf';
                            elseif (in_array($ext, ['doc','docx'])) $icon = 'fa-file-word file-icon word';
                            elseif (in_array($ext, ['jpg','png'])) $icon = 'fa-file-image file-icon img';

                            // Estado
                            $estado = strtolower($doc['estado'] ?? 'pendiente');
                            $badge = 'pendiente';
                            if ($estado === 'validado' || $estado === 'aprobado') $badge = 'validado';
                            elseif ($estado === 'rechazado') $badge = 'rechazado';
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
                            <td><?= htmlspecialchars($doc['tipo']) ?></td>
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            <td><span class="badge <?= $badge ?>"><?= ucfirst($estado) ?></span></td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="action-btn" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($estado !== 'validado'): ?>
                                    <a href="#" class="action-btn delete" onclick="return confirm('¿Eliminar?');">
                                        <i class="fas fa-trash"></i>
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

<?php require_once '../includes/footer.php'; ?>