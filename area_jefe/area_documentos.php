<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_area') {
    header("Location: ../into/login.html");
    exit;
}

$id_area_jefe = $_SESSION['id_area'] ?? 0;

try {
    // Traer solo los documentos de el área del jefe
    $stmt = $pdo->prepare("
        SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida, u.nombre AS empleado_nombre
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ?
        ORDER BY d.fecha_subida DESC
    ");
    $stmt->execute([$id_area_jefe]);
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Documentos de mi Área";
require_once '../includes/header_jefe.php';
require_once '../includes/sidebar_jefe.php';
?>
<style>
    /* ... (puedes añadir los estilos de tabla y búsqueda aquí) ... */
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Documentos Recibidos en tu Área</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Listado de Documentos</h3>
            <?php if (empty($documentos)): ?>
                <p>Tu área aún no ha recibido documentos.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Enviado por</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                            <td><?= htmlspecialchars($doc['tipo']) ?></td>
                            <td><?= htmlspecialchars($doc['empleado_nombre']) ?></td>
                            <td><?= $doc['fecha_subida'] ?></td>
                            <td>
                                <a href="ver_documento_area.php?id=<?= $doc['id'] ?>" class="btn-download">Revisar</a>
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