<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];
$filtro = $_GET['filtro'] ?? 'todos'; // Capturamos el filtro

try {
    // Construcción dinámica de la consulta
    $sql = "
        SELECT d.*, s.nombre as nombre_seccion 
        FROM documentos d
        LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
        WHERE d.id_usuario = ?
    ";

    // Si el filtro es "atencion", solo traemos lo urgente
    if ($filtro === 'atencion') {
        $sql .= " AND (d.estado = 'Observado' OR d.estado = 'Rechazado')";
        $titulo_pagina = "Documentos para Corregir";
    } else {
        $titulo_pagina = "Historial de Envíos";
    }

    $sql .= " ORDER BY d.fecha_subida DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = $titulo_pagina;
$extra_css = "../style/admin_dashboard.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1>
            <i class="<?= ($filtro === 'atencion') ? 'fas fa-exclamation-circle' : 'fas fa-history' ?>"></i> 
            <?= $titulo_pagina ?>
        </h1>
        <div class="top-actions">
            <?php if ($filtro === 'atencion'): ?>
                <a href="documentos_enviados.php" class="btn-primary" style="background-color: #6c757d; padding: 8px 15px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-list"></i> Ver Todos
                </a>
            <?php else: ?>
                <a href="subir_documento.php" class="btn-primary" style="padding: 8px 15px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-plus"></i> Nuevo
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="content">
        
        <?php if ($filtro === 'atencion'): ?>
            <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffeeba;">
                <i class="fas fa-info-circle"></i> 
                Estos documentos requieren tu intervención. Por favor, revisa las observaciones y sube una versión corregida.
            </div>
        <?php endif; ?>

        <div class="card-container" style="padding: 0; overflow: hidden;">
            <?php if (empty($documentos)): ?>
                <div style="text-align: center; padding: 50px 20px; color: #6c757d;">
                    <i class="fas fa-check-circle" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5; color: #198754;"></i>
                    <p>
                        <?= ($filtro === 'atencion') ? '¡Excelente! No tienes documentos pendientes de corrección.' : 'Aún no has enviado ningún documento.' ?>
                    </p>
                    <?php if ($filtro === 'atencion'): ?>
                        <a href="documentos_enviados.php" style="color: var(--color-primario);">Ver historial completo</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #495057;">Documento</th>
                            <th style="padding: 15px; text-align: left; color: #495057;">Carpeta</th>
                            <th style="padding: 15px; text-align: left; color: #495057;">Fecha</th>
                            <th style="padding: 15px; text-align: center; color: #495057;">Estado</th>
                            <th style="padding: 15px; text-align: right; color: #495057;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            $estado = ucfirst($doc['estado']);
                            $badgeClass = 'bg-secondary';
                            if ($doc['estado'] == 'Aprobado' || $doc['estado'] == 'Validado') $badgeClass = 'bg-success';
                            if ($doc['estado'] == 'Pendiente') $badgeClass = 'bg-warning';
                            if ($doc['estado'] == 'Rechazado') $badgeClass = 'bg-danger';
                            if ($doc['estado'] == 'Observado') $badgeClass = 'bg-info'; 
                            
                            $ext = $doc['tipo'];
                            $iconClass = 'fa-file';
                            if(in_array($ext, ['pdf'])) $iconClass = 'fa-file-pdf';
                            if(in_array($ext, ['doc','docx'])) $iconClass = 'fa-file-word';
                            if(in_array($ext, ['jpg','png'])) $iconClass = 'fa-file-image';
                        ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas <?= $iconClass ?>" style="color: #6c757d; font-size: 18px;"></i>
                                    <span style="font-weight: 500; color: #212529;">
                                        <?= htmlspecialchars(substr($doc['nombre_original'], 0, 40)) ?>
                                    </span>
                                </div>
                                <?php if (!empty($doc['mensaje_observacion'])): ?>
                                    <div style="font-size: 12px; color: #dc3545; margin-top: 5px; max-width: 300px;">
                                        <i class="fas fa-comment-dots"></i> <em>"<?= htmlspecialchars($doc['mensaje_observacion']) ?>"</em>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; color: #666;">
                                <?= htmlspecialchars($doc['nombre_seccion'] ?? 'General') ?>
                            </td>
                            <td style="padding: 15px; color: #666; font-size: 14px;">
                                <?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $estado ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: right;">
                                
                                <?php if ($doc['estado'] == 'Observado' || $doc['estado'] == 'Rechazado'): ?>
                                    <a href="reemplazar_documento.php?id=<?= $doc['id'] ?>" class="btn-action-fix" title="Corregir Documento">
                                        <i class="fas fa-sync-alt"></i> Corregir
                                    </a>
                                <?php else: ?>
                                    <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" style="color: var(--color-primario); margin-right: 10px;" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../uploads/<?= $doc['nombre_guardado'] ?>" target="_blank" style="color: #6c757d;" title="Descargar">
                                        <i class="fas fa-download"></i>
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
    .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #fff; display: inline-block; min-width: 80px; }
    .bg-success { background-color: #198754; }
    .bg-warning { background-color: #ffc107; color: #000; }
    .bg-danger { background-color: #dc3545; }
    .bg-info { background-color: #0dcaf0; color: #000; }
    .bg-secondary { background-color: #6c757d; }
    
    .btn-action-fix {
        background-color: #dc3545; 
        color: white; 
        padding: 6px 12px; 
        border-radius: 6px; 
        font-size: 12px; 
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    .btn-action-fix:hover { background-color: #b02a37; color: white; }

    tr:hover { background-color: #f8f9fa; }
</style>

<?php require_once '../includes/footer.php'; ?>