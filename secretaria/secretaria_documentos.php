<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php';

// Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // Documentos pendientes de asignar
    $stmt_pendientes = $pdo->query("
        SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida,
               u.nombre AS empleado_nombre,
               a_sol.nombre AS area_solicitada
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN areas a_sol ON d.id_area_destino = a_sol.id
        WHERE d.estado = 'pendiente'
        ORDER BY d.fecha_subida ASC
    ");
    $documentos_pendientes = $stmt_pendientes->fetchAll();

    // Áreas para el desplegable de asignación
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre")->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Bandeja de Entrada";
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>
<style>
    .styled-table { width: 100%; border-collapse: collapse; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; text-align: left;}
    .styled-table th { background-color: #f2f2f2; }
    .styled-table select, .styled-table button { padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-download { text-decoration: none; color: #007bff; font-weight: bold; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-inbox"></i> Bandeja de Entrada de Documentos</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Documentos Pendientes de Asignación</h3>
            <p>Estos son los documentos que los empleados han enviado y están esperando tu revisión y asignación a un área.</p>
            
            <?php if (empty($documentos_pendientes)): ?>
                <p style="text-align:center; padding: 20px; font-weight: bold;">¡Excelente! No hay documentos pendientes por ahora.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Enviado por</th>
                            <th>Área Solicitada</th>
                            <th>Fecha de Envío</th>
                            <th>Asignar a Área</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos_pendientes as $doc): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($doc['nombre_original']) ?><br>
                                    <small><?= htmlspecialchars($doc['tipo']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($doc['empleado_nombre']) ?></td>
                                <td><?= htmlspecialchars($doc['area_solicitada'] ?? 'No especificada') ?></td>
                                <td><?= $doc['fecha_subida'] ?></td>
                                <td>
                                    <form action="asignar_area.php" method="POST" style="display:flex; gap:10px;">
                                        <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                        <select name="area_id" required>
                                            <option value="">-- Seleccionar --</option>
                                            <?php foreach ($areas as $area): ?>
                                                <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-primary" style="background: #22c55e; border:none; cursor: pointer;">Asignar</button>
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