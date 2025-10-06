<?php
session_start();
require '../php/db.php';

// Seguridad
$roles_permitidos = ['admin','rrhh','jefe_area','secretaria'];
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: ../into/login.html");
    exit;
}

$area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;
if ($area_id <= 0) die("Área no válida.");

try {
    // Datos del área y sus documentos
    $stmt_area = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt_area->execute([$area_id]);
    $area = $stmt_area->fetch();
    if (!$area) die("Área no encontrada.");

    $stmt_docs = $pdo->prepare(
        "SELECT d.id, d.nombre_original, d.nombre_guardado, d.estado, d.feedback, u.nombre AS empleado
         FROM documentos d
         JOIN usuarios u ON d.id_usuario = u.id
         WHERE d.id_area_destino = ?
         ORDER BY d.fecha_subida DESC"
    );
    $stmt_docs->execute([$area_id]);
    $documentos = $stmt_docs->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Documentos de " . $area['nombre'];
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>
<style>
    .styled-table { width: 100%; border-collapse: collapse; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    .styled-table th { background-color: #f2f2f2; }
    .styled-table select, .styled-table textarea, .styled-table button { padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%; }
    .styled-table button { cursor: pointer; background-color: #28a745; color: white; border-color: #28a745; }
</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Documentos del Área: <?= htmlspecialchars($area['nombre']) ?></h1>
    </header>

    <main class="content">
        <a href="secretaria_dashboard.php" class="btn-back" style="text-decoration:none; color: white; background: #333; padding: 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>

        <div class="card">
            <?php if (empty($documentos)): ?>
                <p>No hay documentos asignados a esta área.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Enviado por</th>
                            <th>Estado y Feedback</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($doc['nombre_original']) ?><br>
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank">Descargar</a>
                            </td>
                            <td><?= htmlspecialchars($doc['empleado']) ?></td>
                            <td>
                                <form action="actualiza_estado.php" method="post">
                                    <input type="hidden" name="id_doc" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="area_id" value="<?= $area_id ?>">
                                    <select name="estado" required>
                                        <option value="pendiente" <?= $doc['estado']=='pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="observado" <?= $doc['estado']=='observado' ? 'selected' : '' ?>>Observado</option>
                                        <option value="rechazado" <?= $doc['estado']=='rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                        <option value="revisado" <?= $doc['estado']=='revisado' ? 'selected' : '' ?>>Revisado</option>
                                    </select>
                                    <textarea name="feedback" placeholder="Escribir feedback..."><?= htmlspecialchars($doc['feedback']) ?></textarea>
                            </td>
                            <td>
                                    <button type="submit">💾 Guardar</button>
                                </form>
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