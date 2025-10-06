<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = (int)$_SESSION['id'];

try {
    // Buscamos todos los documentos que el empleado ha enviado a un área
    $stmt = $pdo->prepare(
        "SELECT d.id, d.nombre_original, d.estado, d.fecha_subida, a.nombre as area_nombre
         FROM documentos d
         LEFT JOIN areas a ON d.id_area_destino = a.id
         WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL
         ORDER BY d.fecha_subida DESC"
    );
    $stmt->execute([$id_usuario]);
    $documentos_enviados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar los documentos: " . $e->getMessage());
}

$page_title = "Mis Documentos Enviados";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-share-square"></i> Mis Envíos</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Historial de Documentos Enviados</h3>
            <?php if (empty($documentos_enviados)): ?>
                <p>Aún no has enviado ningún documento a un área.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Área Destino</th>
                            <th>Fecha de Envío</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos_enviados as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                                <td><?= htmlspecialchars($doc['area_nombre'] ?? 'Asignación pendiente') ?></td>
                                <td><?= $doc['fecha_subida'] ?></td>
                                <td><?= htmlspecialchars(strtoupper($doc['estado'])) ?></td>
                                <td>
                                    <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" class="btn-download">Ver Detalles</a>
                                </td>
                                <td style="display: flex; gap: 10px;">
    <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" target="_blank" class="btn-view">
        <i class="fas fa-eye"></i> Ver
    </a>
    <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=download" class="btn-download">
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
<style>.styled-table { width: 100%; border-collapse: collapse; } .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; } .styled-table th { background-color: #f2f2f2; } .btn-download{ text-decoration: none; color: #007bff; font-weight: bold; }</style>

<?php require_once '../includes/footer.php'; ?>