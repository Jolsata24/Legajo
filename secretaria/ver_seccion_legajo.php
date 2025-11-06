<?php
session_start();
require '../php/db.php';

// --- ¡CORRECCIÓN DE SEGURIDAD! ---
// Esta página es solo para la secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$empleado_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seccion_id  = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;

if ($empleado_id <= 0 || $seccion_id <= 0) {
    die("Parámetros inválidos.");
}

try {
    // Lógica de consulta (es la misma)
    $stmt_emp = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt_emp->execute([$empleado_id]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

    $stmt_sec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt_sec->execute([$seccion_id]);
    $seccion = $stmt_sec->fetch(PDO::FETCH_ASSOC);

    if (!$empleado || !$seccion) {
        die("Empleado o sección no encontrados.");
    }

    $stmt_docs = $pdo->prepare(
        "SELECT id, nombre_original, tipo, fecha_subida, nombre_guardado
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

// --- ¡CORRECCIÓN DE INTERFAZ! ---
// Cargamos el header y sidebar de SECRETARIA
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Sección: <?= htmlspecialchars($seccion['nombre']) ?></h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        <a href="ver_empleado.php?id=<?= $empleado_id; ?>" class="btn-back" style="margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Volver al Legajo del Empleado
        </a>

        <div class="card">
            <p style="text-align: left; font-size: 1.1em; margin-top: -10px;">
                Mostrando documentos para <strong><?= htmlspecialchars($empleado['nombre']) ?></strong>
            </p>

            <?php if ($documentos): ?>
                <div class="table-responsive">
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
                                    <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" target="_blank" class="btn-download">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p>No se encontraron documentos en esta sección para este empleado.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>