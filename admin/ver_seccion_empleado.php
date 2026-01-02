<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

// Recibimos AMBOS IDs
$id_seccion  = $_GET['id_seccion'] ?? null;
$id_empleado = $_GET['id_empleado'] ?? null;

if (!$id_seccion || !$id_empleado) {
    header("Location: empleados_panel.php");
    exit;
}

try {
    // 1. Info Sección
    $stmtSec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmtSec->execute([$id_seccion]);
    $seccion = $stmtSec->fetch();

    // 2. Info Empleado (Para el título)
    $stmtEmp = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmtEmp->execute([$id_empleado]);
    $empleado = $stmtEmp->fetch();

    // 3. Documentos del Empleado en esta Sección
    $stmtDocs = $pdo->prepare("
        SELECT * FROM documentos 
        WHERE id_usuario = ? AND id_seccion = ? 
        ORDER BY fecha_subida DESC
    ");
    $stmtDocs->execute([$id_empleado, $id_seccion]);
    $documentos = $stmtDocs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Documentos: " . $seccion['nombre'];
$extra_css = "../style/ver_seccion_empleado.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="visor-header">
        <div class="visor-title">
            <h2>Carpeta: <?= htmlspecialchars($seccion['nombre']) ?></h2>
            <span>Viendo legajo de: <strong><?= htmlspecialchars($empleado['nombre']) ?></strong></span>
        </div>
        <a href="ver_legajo_empleado.php?id=<?= $id_empleado ?>" class="btn-secondary" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Volver a Carpetas
        </a>
    </header>

    <main class="content">
        
        <div class="table-responsive">
            <?php if (empty($documentos)): ?>
                <div style="padding: 40px; text-align: center; color: #6c757d;">
                    <i class="fas fa-folder-open" style="font-size: 40px; opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>El empleado no tiene documentos en esta carpeta.</p>
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
                             // Iconos
                             $ext = strtolower(pathinfo($doc['nombre_guardado'] ?? '', PATHINFO_EXTENSION));
                             $icon = 'fa-file';
                             $color = '#6c757d';
                             if($ext == 'pdf') { $icon='fa-file-pdf'; $color='#dc3545'; }
                             if(in_array($ext, ['doc','docx'])) { $icon='fa-file-word'; $color='#0d6efd'; }
                             if(in_array($ext, ['jpg','png'])) { $icon='fa-file-image'; $color='#198754'; }

                             // Estado
                             $estado = strtolower($doc['estado'] ?? 'pendiente');
                             $badge = 'pendiente';
                             if($estado == 'validado') $badge = 'validado';
                             if($estado == 'rechazado') $badge = 'rechazado';
                        ?>
                        <tr>
                            <td>
                                <i class="fas <?= $icon ?> file-icon" style="color: <?= $color ?>;"></i>
                                <strong><?= htmlspecialchars($doc['titulo'] ?? $doc['nombre_original']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($doc['tipo']) ?></td>
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            <td><span class="badge <?= $badge ?>"><?= ucfirst($estado) ?></span></td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="btn-download-sm">
                                    <i class="fas fa-eye"></i> Ver
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