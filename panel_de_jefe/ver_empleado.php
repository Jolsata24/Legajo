<?php
session_start();
require '../php/db.php';

// Seguridad: Roles permitidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin', 'rrhh', 'jefe_area'])) {
    die("Acceso denegado");
}

$empleado_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($empleado_id <= 0) {
    die("Empleado no válido.");
}

try {
    // Consultamos los datos del empleado
    $stmt_emp = $pdo->prepare("SELECT u.id, u.nombre, u.email, u.rol, a.nombre AS area FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt_emp->execute([$empleado_id]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);
    if (!$empleado) die("Empleado no encontrado.");

    // Consultamos todas las secciones del legajo
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Consultamos documentos enviados a áreas por este empleado
    $stmt_docs = $pdo->prepare("SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida, d.estado, a.nombre AS area_destino FROM documentos d JOIN areas a ON d.id_area_destino = a.id WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL ORDER BY d.fecha_subida DESC");
    $stmt_docs->execute([$empleado_id]);
    $docs_enviados = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Legajo de " . htmlspecialchars($empleado['nombre']);
// Usaremos el sidebar del admin, ya que es el rol con más permisos que puede acceder aquí.
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>
<style>
    .seccion-link { 
        display: block; padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6;
        margin-bottom: 8px; text-decoration: none; color: #495057; border-radius: 5px; transition: background-color 0.2s; 
    }
    .seccion-link:hover { background-color: #e9ecef; }
    .styled-table { width: 100%; border-collapse: collapse; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; }
    .styled-table th { background-color: #f2f2f2; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-tie"></i> Legajo de <?= htmlspecialchars($empleado['nombre']) ?></h1>
    </header>

    <main class="content">
        <a href="../admin/empleados_panel.php" style="text-decoration: none; color: #333; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver al Panel de Empleados
        </a>

        <div class="card">
            <h3><i class="fas fa-id-card"></i> Datos del Empleado</h3>
            <p style="text-align:left;"><strong>Nombre:</strong> <?= htmlspecialchars($empleado['nombre']); ?></p>
            <p style="text-align:left;"><strong>Email:</strong> <?= htmlspecialchars($empleado['email']); ?></p>
            <p style="text-align:left;"><strong>Área:</strong> <?= htmlspecialchars($empleado['area'] ?? 'No asignada'); ?></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-folder-open"></i> Documentos Personales del Legajo</h3>
            <?php if ($secciones): ?>
                <?php foreach ($secciones as $sec): ?>
                    <a class="seccion-link" href="ver_seccion_legajo.php?id=<?= $empleado_id; ?>&seccion=<?= $sec['id']; ?>">
                        <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($sec['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay secciones de legajo configuradas en el sistema.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-share-square"></i> Documentos Enviados a Áreas</h3>
            <?php if ($docs_enviados): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Área Destino</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($docs_enviados as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                            <td><?= htmlspecialchars($doc['area_destino']) ?></td>
                            <td><?= $doc['fecha_subida'] ?></td>
                            <td><?= htmlspecialchars(strtoupper($doc['estado'])) ?></td>
                            <td style="display:flex; gap:10px;">
                                <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" target="_blank">Ver</a>
                                <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=download">Descargar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Este empleado no ha enviado documentos a otras áreas.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>