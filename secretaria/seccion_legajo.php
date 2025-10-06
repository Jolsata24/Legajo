<?php
session_start();
require '../php/db.php';

// Seguridad: Solo Secretaría
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$seccion_id = $_GET['id'] ?? null;

if (!$seccion_id) {
    die("Sección no especificada.");
}

try {
    // Traer datos de la sección
    $stmt_seccion = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt_seccion->execute([$seccion_id]);
    $seccion = $stmt_seccion->fetch(PDO::FETCH_ASSOC);

    if (!$seccion) {
        die("Sección no encontrada.");
    }

    // Traer documentos de esa sección para este usuario
    $stmt_docs = $pdo->prepare(
        "SELECT id, nombre_original, nombre_guardado, tipo, fecha_subida
        FROM documentos
        WHERE id_usuario = ? AND id_seccion = ?
        ORDER BY fecha_subida DESC"
    );
    $stmt_docs->execute([$usuario_id, $seccion_id]);
    $documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Sección: " . htmlspecialchars($seccion['nombre']);
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>
<style>
    .styled-table { width: 100%; border-collapse: collapse; }
    .styled-table th, .styled-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    .styled-table th { background-color: #f2f2f2; }
    .form-dashboard label { display: block; margin: 15px 0 5px; font-weight: 600; }
    .form-dashboard input { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-primary { padding: 10px 20px; border: none; border-radius: 5px; background-color: #007bff; color: white; cursor: pointer; margin-top: 15px; }
    .btn-download { text-decoration: none; color: #007bff; font-weight: bold; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder"></i> Sección: <?= htmlspecialchars($seccion['nombre']); ?></h1>
    </header>

    <main class="content">
        <a href="mi_legajo.php" style="text-decoration: none; color: #333; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver a Mi Legajo
        </a>

        <div class="card">
            <h3><i class="fas fa-file-alt"></i> Documentos en esta sección</h3>
            <?php if (!empty($documentos)): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Nombre Original</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']); ?></td>
                                <td><?= htmlspecialchars($doc['tipo']); ?></td>
                                <td><?= $doc['fecha_subida']; ?></td>
                                <td><a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']); ?>" class="btn-download" target="_blank">📄 Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No has subido documentos en esta sección aún.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-upload"></i> Subir Nuevo Documento a esta Sección</h3>
            <form action="subir_doc_personal.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                <input type="hidden" name="seccion_id" value="<?= $seccion_id; ?>">
                
                <label for="tipo">Tipo de documento:</label>
                <input type="text" name="tipo" id="tipo" required placeholder="Ej: Certificado de Trabajo">
                
                <label for="documento">Archivo:</label>
                <input type="file" name="documento" required>
                
                <button type="submit" class="btn-primary">Subir Documento</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>