<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
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
    $stmt = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt->execute([$seccion_id]);
    $seccion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seccion) {
        die("Sección no encontrada.");
    }

    // Traer documentos de esa sección
    $stmt = $pdo->prepare("
        SELECT id, nombre_original, nombre_guardado, tipo, fecha_subida
        FROM documentos
        WHERE id_usuario = ? AND id_seccion = ?
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$usuario_id, $seccion_id]);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// --- ¡INICIO DE LA CORRECCIÓN DE ESTILO! ---

$page_title = "Sección: " . htmlspecialchars($seccion['nombre']);
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Sección: <?= htmlspecialchars($seccion['nombre']); ?></h1>
      
      <div class="top-actions">
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        
        <a href="mi_legajo.php" class="btn-back" style="margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Volver a Mi Legajo
        </a>

        <div class="card">
            <h3><i class="fas fa-file-alt"></i> Documentos en esta sección</h3>
            
            <?php if (empty($documentos)): ?>
                <p>No has subido documentos en esta sección aún.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Nombre Original</th>
                                <th>Tipo</th>
                                <th>Fecha de Subida</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['nombre_original']); ?></td>
                                    <td><?= htmlspecialchars($doc['tipo']); ?></td>
                                    <td><?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])); ?></td>
                                    <td>
                                        <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" class="btn-download" target="_blank">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-upload"></i> Subir Nuevo Documento</h3>
            
            <form action="subir_doc_personal.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                <input type="hidden" name="seccion_id" value="<?= $seccion_id; ?>">
                
                <div class="form-group">
                    <label for="tipo">Tipo de documento:</label>
                    <input type="text" name="tipo" id="tipo" required placeholder="Ej: Certificado de Trabajo">
                </div>
                
                <div class="form-group">
                    <label for="documento">Archivo (PDF, Word, JPG, PNG):</label>
                    <input type="file" name="documento" id="documento" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Subir Documento
                </button>
            </form>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>