<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];
$filtro = $_GET['filtro'] ?? 'todos';

try {
    // CAMBIO: JOIN con la tabla 'areas' para mostrar el destino
    $sql = "
        SELECT d.*, a.nombre as nombre_area 
        FROM documentos d
        LEFT JOIN areas a ON d.id_area_destino = a.id
        WHERE d.id_usuario = ?
    ";

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
        <h1><i class="fas fa-history"></i> <?= $titulo_pagina ?></h1>
        <div class="top-actions">
             <a href="subir_documento.php" class="btn-primary" style="padding: 8px 15px; font-size: 13px; text-decoration: none;">
                <i class="fas fa-paper-plane"></i> Nuevo Envío
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'exito'): ?>
            <div style="background: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> Documento enviado exitosamente para revisión.
            </div>
        <?php endif; ?>

        <div class="card-container" style="padding: 0; overflow: hidden;">
            <?php if (empty($documentos)): ?>
                <div style="text-align: center; padding: 50px 20px; color: #6c757d;">
                    <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No hay documentos enviados.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #495057;">Documento</th>
                            <th style="padding: 15px; text-align: left; color: #495057;">Destino (Área)</th>
                            <th style="padding: 15px; text-align: left; color: #495057;">Fecha Envío</th>
                            <th style="padding: 15px; text-align: center; color: #495057;">Estado</th>
                            <th style="padding: 15px; text-align: right; color: #495057;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            $estado = ucfirst($doc['estado']);
                            $badgeClass = 'bg-secondary';
                            if ($doc['estado'] == 'Aprobado') $badgeClass = 'bg-success';
                            if ($doc['estado'] == 'Pendiente') $badgeClass = 'bg-warning';
                            if ($doc['estado'] == 'Rechazado') $badgeClass = 'bg-danger';
                            if ($doc['estado'] == 'Observado') $badgeClass = 'bg-info';
                        ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-file-alt" style="color: #6c757d;"></i>
                                    <span style="font-weight: 500;">
                                        <?= htmlspecialchars(substr($doc['nombre_original'], 0, 30)) ?>
                                    </span>
                                </div>
                                <?php if (!empty($doc['mensaje_observacion'])): ?>
                                    <div style="font-size: 11px; color: #dc3545; margin-top: 4px;">
                                        Obs: <?= htmlspecialchars($doc['mensaje_observacion']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; color: #666;">
                                <i class="fas fa-building" style="color: #adb5bd; margin-right: 5px;"></i>
                                <?= htmlspecialchars($doc['nombre_area'] ?? 'Sin asignar') ?>
                            </td>
                            <td style="padding: 15px; color: #666; font-size: 14px;">
                                <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>
                            </td>
                            <td style="padding: 15px; text-align: right;">
                                <?php if ($doc['estado'] == 'Observado' || $doc['estado'] == 'Rechazado'): ?>
                                    <a href="reemplazar_documento.php?id=<?= $doc['id'] ?>" class="btn-action-fix">
                                        <i class="fas fa-sync-alt"></i> Corregir
                                    </a>
                                <?php else: ?>
                                    <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" style="color: var(--color-primario);">
                                        <i class="fas fa-eye"></i>
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
    
    .btn-action-fix { background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; }
    .btn-action-fix:hover { background-color: #b02a37; }
</style>

<?php require_once '../includes/footer.php'; ?>