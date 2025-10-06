<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$area_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($area_id <= 0) {
    die("Área no especificada.");
}

try {
    // 1. Obtener el nombre del área seleccionada
    $stmt_area = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt_area->execute([$area_id]);
    $area = $stmt_area->fetch();
    if (!$area) die("Área no encontrada.");

    // 2. Obtener los documentos que pertenecen a esta área
    $stmt_docs = $pdo->prepare(
        "SELECT d.nombre_original, d.nombre_guardado, d.fecha_subida, u.nombre AS usuario_creador
         FROM documentos d
         JOIN usuarios u ON d.id_usuario = u.id
         WHERE d.id_area_destino = ?
         ORDER BY d.fecha_subida DESC"
    );
    $stmt_docs->execute([$area_id]);
    $documentos_del_area = $stmt_docs->fetchAll();

} catch (PDOException $e) {
    die("Error al consultar la base de datos.");
}

$page_title = "Documentos de " . $area['nombre'];
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>
<style>
    /* Estilos para la tabla (los mismos que usamos antes) */
    .styled-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    .styled-table th { background-color: #f2f2f2; }
    .btn-download { text-decoration: none; color: #007bff; font-weight: bold; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Documentos del Área: <?= htmlspecialchars($area['nombre']) ?></h1>
    </header>

    <main class="content">
        <a href="empleado_dashboard.php" class="btn-back" style="text-decoration:none; color: white; background: #333; padding: 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Volver a todas las áreas</a>
        
        <div class="card">
            <?php if (empty($documentos_del_area)): ?>
                <p>No hay documentos disponibles en esta área.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Nombre del Documento</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos_del_area as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                                <td><?= htmlspecialchars($doc['usuario_creador']) ?></td>
                                <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                                <td>
                                    <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" class="btn-download" download>
                                        <i class="fas fa-download"></i> Descargar
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