<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

// CAMBIO CLAVE: Capturar la sección preseleccionada de la URL
$id_preseleccionado = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : 0;

// Obtener SECCIONES
try {
    $stmt = $pdo->prepare("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC");
    $stmt->execute();
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar secciones: " . $e->getMessage());
}

$page_title = "Subir a Mi Legajo";
$extra_css = "../style/subir_documento.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-folder-plus"></i> Archivar en Mi Legajo</h1>
        <div class="top-actions">
            <?php $link_volver = ($id_preseleccionado > 0) ? "seccion_legajo.php?id=$id_preseleccionado" : "mi_legajo.php"; ?>
            
            <a href="<?= $link_volver ?>" class="btn-back" style="color: #6c757d; text-decoration: none;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <div class="card-upload-container">
            <form action="guardar_doc_personal.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-section">
                    <label class="section-label"><i class="fas fa-folder-open"></i> Selecciona la Carpeta</label>
                    <div class="custom-select-wrapper">
                        <select name="id_seccion" required class="form-control-lg">
                            <option value="">-- Elige una carpeta --</option>
                            <?php foreach ($secciones as $sec): 
                                // CAMBIO CLAVE: Lógica de selección automática
                                $selected = ($sec['id'] == $id_preseleccionado) ? 'selected' : '';
                            ?>
                                <option value="<?= $sec['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($sec['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <label class="section-label"><i class="fas fa-file-import"></i> Archivo</label>
                    
                    <div class="drop-zone" id="dropZone">
                        <div class="drop-zone-content">
                            <i class="fas fa-cloud-upload-alt drop-icon"></i>
                            <span class="drop-text">Arrastra tu archivo aquí</span>
                            <span class="drop-subtext">Formatos: PDF, Word, JPG, PNG</span>
                            <input type="file" name="archivo" id="fileInput" class="drop-input" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <div class="file-preview" id="filePreview" style="display: none;">
                            <i class="fas fa-file-alt"></i>
                            <span id="fileName">archivo.pdf</span>
                            <i class="fas fa-check-circle success-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-lg">
                        <i class="fas fa-save"></i> Guardar en Legajo
                    </button>
                </div>

            </form>
        </div>

    </main>
</div>

<style>
    .card-upload-container { background: #fff; border-radius: 12px; padding: 30px; max-width: 700px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-section { margin-bottom: 25px; }
    .section-label { display: block; font-weight: 600; margin-bottom: 10px; color: #333; font-size: 15px; }
    .form-control-lg { width: 100%; padding: 12px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; background-color: #f8f9fa; cursor: pointer; }
    
    .drop-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: all 0.3s ease; position: relative; background: #fafafa; }
    .drop-zone:hover, .drop-zone.dragover { border-color: var(--color-primario); background: #f0f7ff; }
    .drop-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
    
    .drop-icon { font-size: 40px; color: #aaa; margin-bottom: 10px; }
    .file-preview { margin-top: 10px; padding: 10px; background: #e0f2fe; border-radius: 8px; color: #0284c7; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-primary-lg { width: 100%; padding: 14px; background: var(--color-primario); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
</style>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dropContent = document.querySelector('.drop-zone-content');
    const filePreview = document.getElementById('filePreview');
    const fileNameSpan = document.getElementById('fileName');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        fileInput.files = dt.files;
        updatePreview();
    }

    fileInput.addEventListener('change', updatePreview);

    function updatePreview() {
        if (fileInput.files && fileInput.files[0]) {
            dropContent.style.display = 'none';
            filePreview.style.display = 'flex';
            fileNameSpan.textContent = fileInput.files[0].name;
            dropZone.style.borderColor = '#198754';
            dropZone.style.background = '#f0fff4';
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>