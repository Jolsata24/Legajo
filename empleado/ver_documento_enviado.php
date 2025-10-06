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
    // 1. Obtener los detalles del documento, asegurándonos de que pertenece al usuario
    $stmt_doc = $pdo->prepare(
        "SELECT d.*, a.nombre AS area_destino_nombre
         FROM documentos d
         LEFT JOIN areas a ON d.id_area_destino = a.id
         WHERE d.id = ? AND d.id_usuario = ?"
    );
    $stmt_doc->execute([$id_documento, $id_usuario]);
    $documento = $stmt_doc->fetch();

    if (!$documento) {
        die("Documento no encontrado o no tienes permiso para verlo.");
    }

    // 2. Obtener el historial de trazabilidad de este documento
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
    .timeline { list-style: none; padding: 0; }
    .timeline li { margin-bottom: 20px; border-left: 2px solid #007bff; padding-left: 20px; position: relative; }
    .timeline li::before { content: ''; width: 12px; height: 12px; background: #007bff; border-radius: 50%; position: absolute; left: -7px; top: 5px; }
    .timeline .accion { font-weight: bold; }
    .timeline .descripcion { color: #555; }
    .timeline .fecha { font-size: 0.9em; color: #777; }
</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-file-alt"></i> Detalle del Documento Enviado</h1>
    </header>

    <main class="content">
        <a href="documentos_enviados.php" class="btn-back" style="text-decoration:none; color: white; background: #333; padding: 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Volver a Mis Envíos</a>
        
        <div class="card">
            <h3>Información del Documento</h3>
            <p><strong>Nombre del archivo:</strong> <?= htmlspecialchars($documento['nombre_original']) ?></p>
            <p><strong>Tipo:</strong> <?= htmlspecialchars($documento['tipo']) ?></p>
            <p><strong>Estado Actual:</strong> <span class="badge" style="background-color: #007bff; color: white; padding: 5px 10px; border-radius: 10px;"><?= strtoupper(htmlspecialchars($documento['estado'])) ?></span></p>
            <p><strong>Área de Destino:</strong> <?= htmlspecialchars($documento['area_destino_nombre'] ?? 'Pendiente de asignación') ?></p>
            <p><strong>Fecha de Envío:</strong> <?= $documento['fecha_subida'] ?></p>
            <p><strong>Feedback:</strong> <?= !empty($documento['feedback']) ? htmlspecialchars($documento['feedback']) : 'Sin comentarios.' ?></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Historial de Trazabilidad</h3>
            <ul class="timeline">
                <?php foreach ($historial as $evento): ?>
                    <li>
                        <div class="accion"><?= htmlspecialchars($evento['accion']) ?></div>
                        <div class="descripcion"><?= htmlspecialchars($evento['descripcion']) ?></div>
                        <div class="fecha">
                            Por: <?= htmlspecialchars($evento['nombre_usuario_accion'] ?? 'Sistema') ?> el <?= $evento['fecha'] ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>