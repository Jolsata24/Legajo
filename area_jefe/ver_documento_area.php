<?php
session_start();
require '../php/db.php';
require '../php/funciones.php';

// 1. Seguridad: Solo Jefes de Área
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_area') {
    header("Location: ../into/login.html");
    exit;
}

$id_documento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_area_jefe = (int)$_SESSION['id_area'];

if ($id_documento <= 0) {
    die("Documento no especificado.");
}

try {
    // 2. Obtener detalles del documento, ASEGURANDO que pertenece al área del jefe actual
    $stmt_doc = $pdo->prepare(
        "SELECT d.*, u.nombre AS empleado_nombre
         FROM documentos d
         JOIN usuarios u ON d.id_usuario = u.id
         WHERE d.id = ? AND d.id_area_destino = ?"
    );
    $stmt_doc->execute([$id_documento, $id_area_jefe]);
    $documento = $stmt_doc->fetch();

    if (!$documento) {
        die("Documento no encontrado o no pertenece a tu área.");
    }

    // 3. Obtener el historial de trazabilidad
    $stmt_historial = $pdo->prepare("SELECT h.*, u.nombre AS nombre_usuario_accion FROM documentos_historial h LEFT JOIN usuarios u ON h.id_usuario_accion = u.id WHERE h.id_documento = ? ORDER BY h.fecha DESC");
    $stmt_historial->execute([$id_documento]);
    $historial = $stmt_historial->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Revisar Documento";
require_once '../includes/header_jefe.php';
require_once '../includes/sidebar_jefe.php';
?>
<style>
    .timeline { list-style: none; padding: 20px 0; }
    .timeline li { margin-bottom: 20px; border-left: 2px solid #007bff; padding-left: 20px; position: relative; }
    .timeline li::before { content: ''; width: 12px; height: 12px; background: #007bff; border-radius: 50%; position: absolute; left: -7px; top: 5px; }
    .form-dashboard label { display: block; margin: 15px 0 5px; font-weight: 600; }
    .form-dashboard select, .form-dashboard textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-primary { padding: 10px 20px; border: none; border-radius: 5px; background-color: #007bff; color: white; cursor: pointer; margin-top: 15px; }
</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-file-signature"></i> Revisar Documento</h1>
    </header>

    <main class="content">
        <a href="area_documentos.php" style="text-decoration:none; color: #333; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        
        <div class="card">
            <h3><i class="fas fa-info-circle"></i> Información del Documento</h3>
            <p style="text-align:left;"><strong>Nombre del archivo:</strong> <?= htmlspecialchars($documento['nombre_original']) ?></p>
            <p style="text-align:left;"><strong>Enviado por:</strong> <?= htmlspecialchars($documento['empleado_nombre']) ?></p>
            <p style="text-align:left;"><strong>Estado Actual:</strong> <span style="font-weight:bold; color: #007bff;"><?= strtoupper(htmlspecialchars($documento['estado'])) ?></span></p>
            <p style="text-align:left;"><a href="../php/ver_documento.php?id=<?= $documento['id'] ?>&action=view" target="_blank">Ver Archivo</a> | <a href="../php/ver_documento.php?id=<?= $documento['id'] ?>&action=download">Descargar</a></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-edit"></i> Formulario de Revisión</h3>
            <form action="../secretaria/actualiza_estado.php" method="post" class="form-dashboard">
                <input type="hidden" name="id_doc" value="<?= $id_documento ?>">
                <input type="hidden" name="area_id" value="<?= $id_area_jefe ?>">
                
                <div>
                    <label for="estado">Cambiar Estado:</label>
                    <select name="estado" id="estado" required>
                        <option value="observado" <?= $documento['estado'] == 'observado' ? 'selected' : '' ?>>Observado</option>
                        <option value="rechazado" <?= $documento['estado'] == 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                        <option value="revisado" <?= $documento['estado'] == 'revisado' ? 'selected' : '' ?>>Revisado (Aprobado)</option>
                    </select>
                </div>

                <div>
                    <label for="feedback">Feedback (Comentarios):</label>
                    <textarea name="feedback" id="feedback" rows="4" placeholder="Añade aquí tus comentarios para el empleado..."><?= htmlspecialchars($documento['feedback']) ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Guardar Revisión</button>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Historial de Trazabilidad</h3>
            <ul class="timeline">
                <?php foreach ($historial as $evento): ?>
                    <li>
                        <div><strong><?= htmlspecialchars($evento['accion']) ?></strong></div>
                        <div style="color: #555;"><?= htmlspecialchars($evento['descripcion']) ?></div>
                        <div style="font-size: 0.9em; color: #777;">
                            Por: <?= htmlspecialchars($evento['nombre_usuario_accion'] ?? 'Sistema') ?> el <?= date("d/m/Y H:i", strtotime($evento['fecha'])) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>