<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_documento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_usuario = (int)$_SESSION['id'];

if ($id_documento <= 0) {
    die("Documento no especificado.");
}

try {
    // 1. Obtener los detalles del documento, incluyendo el nombre del revisor
    $stmt_doc = $pdo->prepare(
        "SELECT d.*, a.nombre AS area_destino_nombre, r.nombre AS nombre_revisor
         FROM documentos d
         LEFT JOIN areas a ON d.id_area_destino = a.id
         LEFT JOIN usuarios r ON d.revisado_por = r.id
         WHERE d.id = ? AND d.id_usuario = ?"
    );
    $stmt_doc->execute([$id_documento, $id_usuario]);
    $documento = $stmt_doc->fetch();

    if (!$documento) {
        die("Documento no encontrado o no tienes permiso para verlo.");
    }

    // 2. Obtener el historial de trazabilidad (como ya lo teníamos)
    $stmt_historial = $pdo->prepare(
        "SELECT h.*, u.nombre AS nombre_usuario_accion
         FROM documentos_historial h
         LEFT JOIN usuarios u ON h.id_usuario_accion = u.id
         WHERE h.id_documento = ?
         ORDER BY h.fecha DESC"
    );
    $stmt_historial->execute([$id_documento]);
    $historial = $stmt_historial->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Detalle del Documento";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>
<style>
    .timeline { list-style: none; padding: 20px 0; }
    .timeline li { margin-bottom: 20px; border-left: 2px solid #007bff; padding-left: 20px; position: relative; }
    .timeline li::before { content: ''; width: 12px; height: 12px; background: #007bff; border-radius: 50%; position: absolute; left: -7px; top: 5px; }
    .locked { background-color: #f8f9fa; color: #6c757d; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6; }
    .feedback-box { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px; border-radius: 8px; margin-top: 15px; }
    .form-dashboard label { display: block; margin: 15px 0 5px; font-weight: 600; }
    .form-dashboard input[type="file"] { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-warning { background-color: #ffc107; border: none; padding: 10px 20px; color: #212529; cursor: pointer; border-radius: 5px; font-weight: 600; margin-top: 15px; }
</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-file-alt"></i> Detalle del Documento Enviado</h1>
    </header>

    <main class="content">
        <a href="documentos_enviados.php" style="text-decoration:none; color: #333; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver a Mis Envíos
        </a>
        
        <div class="card">
            <h3><i class="fas fa-info-circle"></i> Información del Documento</h3>
            <p style="text-align:left;"><strong>Nombre del archivo:</strong> <?= htmlspecialchars($documento['nombre_original']) ?></p>
            <p style="text-align:left;"><strong>Estado Actual:</strong> <span style="font-weight:bold; color: #007bff;"><?= strtoupper(htmlspecialchars($documento['estado'])) ?></span></p>
            <p style="text-align:left;"><strong>Área de Destino:</strong> <?= htmlspecialchars($documento['area_destino_nombre'] ?? 'Pendiente de asignación') ?></p>
            <p style="text-align:left;"><strong>Fecha de Última Revisión:</strong> <?= $documento['fecha_revision'] ?? 'N/A' ?></p>
            <p style="text-align:left;"><strong>Revisado por:</strong> <?= htmlspecialchars($documento['nombre_revisor'] ?? 'N/A') ?></p>
            
            <?php if (!empty($documento['feedback'])): ?>
                <div class="feedback-box">
                    <strong>Feedback del revisor:</strong>
                    <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($documento['feedback'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-upload"></i> Reemplazar Documento</h3>
            <?php if ($documento['estado'] === 'revisado'): ?>
                <div class="locked">
                    <p><i class="fas fa-lock"></i> Este documento ya ha sido revisado y aprobado. No se puede modificar.</p>
                </div>
            <?php else: ?>
                <p style="text-align:left;">Si tu documento fue observado o rechazado, puedes subir una nueva versión aquí. El archivo anterior será reemplazado.</p>
                <form action="reemplazar_documento.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                    <input type="hidden" name="id_documento" value="<?= $id_documento ?>">
                    <div>
                        <label for="nuevo_documento">Seleccionar nuevo archivo:</label>
                        <input type="file" name="nuevo_documento" id="nuevo_documento" required>
                    </div>
                    <button type="submit" class="btn-warning">Reemplazar y Enviar a Revisión</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Historial de Trazabilidad</h3>
            <ul class="timeline">
                <?php if (empty($historial)): ?>
                    <li>No hay eventos en el historial de este documento.</li>
                <?php else: ?>
                    <?php foreach ($historial as $evento): ?>
                        <li>
                            <div class="accion"><?= htmlspecialchars($evento['accion']) ?></div>
                            <div class="descripcion"><?= htmlspecialchars($evento['descripcion']) ?></div>
                            <div class="fecha">
                                Por: <strong><?= htmlspecialchars($evento['nombre_usuario_accion'] ?? 'Sistema') ?></strong> el <?= date("d/m/Y H:i", strtotime($evento['fecha'])) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>