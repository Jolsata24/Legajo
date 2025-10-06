<?php
session_start();
require '../php/db.php';

// Seguridad: Roles permitidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin', 'rrhh', 'jefe_area'])) {
    die("Acceso denegado");
}

$empleado_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seccion_id  = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;

if ($empleado_id <= 0 || $seccion_id <= 0) {
    die("Parámetros inválidos.");
}

try {
    // Consultar datos del empleado y la sección para los títulos
    $stmt_emp = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt_emp->execute([$empleado_id]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

    $stmt_sec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt_sec->execute([$seccion_id]);
    $seccion = $stmt_sec->fetch(PDO::FETCH_ASSOC);

    if (!$empleado || !$seccion) {
        die("Empleado o sección no encontrados.");
    }

    // Consultar documentos de esa sección para ese empleado
    $stmt_docs = $pdo->prepare(
        "SELECT id, nombre_original, tipo, fecha_subida
         FROM documentos
         WHERE id_usuario = ? AND id_seccion = ?
         ORDER BY fecha_subida DESC"
    );
    $stmt_docs->execute([$empleado_id, $seccion_id]);
    $documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Legajo de " . htmlspecialchars($empleado['nombre']);
// Usamos el sidebar del admin, que es el rol más completo que puede acceder aquí
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>
<style>
    .styled-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; }
    .styled-table th { background-color: #f2f2f2; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Sección: <?= htmlspecialchars($seccion['nombre']) ?></h1>
      <small style="margin-top: -10px; display: block;">Legajo de: <?= htmlspecialchars($empleado['nombre']) ?></small>
    </header>

    <main class="content">
        <a href="ver_empleado.php?id=<?= $empleado_id; ?>" style="text-decoration: none; color: #333; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver al Legajo del Empleado
        </a>

        <div class="card">
            <?php if ($documentos): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Nombre del Documento</th>
                            <th>Tipo</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']); ?></td>
                                <td><?= htmlspecialchars($doc['tipo']); ?></td>
                                <td><?= $doc['fecha_subida']; ?></td>
                                <td style="display:flex; gap:10px;">
                                    <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" target="_blank">Ver</a>
                                    <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=download">Descargar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No se encontraron documentos en esta sección para este empleado.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>