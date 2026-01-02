<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];
$seccion_preseleccionada = $_GET['seccion'] ?? '';

// 2. Obtener Secciones (Carpetas) disponibles
try {
    $stmt = $pdo->prepare("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC");
    $stmt->execute();
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar secciones: " . $e->getMessage());
}

// 3. Procesar Subida
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_seccion = $_POST['seccion'] ?? '';
    $archivo = $_FILES['archivo'] ?? null;

    if ($id_seccion && $archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            // Nombre único: timestamp + id_usuario + random + ext
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            $ruta_destino = "../uploads/" . $nombre_guardado;
            
            // Verificar directorio
            if (!is_dir("../uploads/")) mkdir("../uploads/", 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                try {
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO documentos (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                        VALUES (?, ?, ?, ?, ?, NOW(), 'Pendiente')
                    ");
                    if ($stmtInsert->execute([$id_usuario, $id_seccion, $nombre_original, $nombre_guardado, $ext])) {
                        // Redirección con éxito
                        header("Location: seccion_legajo.php?id=$id_seccion&msg=exito_subida");
                        exit;
                    }
                } catch (PDOException $e) {
                    $mensaje = "Error en base de datos: " . $e->getMessage();
                    $tipo_mensaje = "danger";
                }
            } else {
                $mensaje = "Error al mover el archivo al servidor.";
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "Formato de archivo no permitido (Solo PDF, Word, JPG, PNG).";
            $tipo_mensaje = "warning";
        }
    } else {
        $mensaje = "Por favor selecciona una carpeta y un archivo válido.";
        $tipo_mensaje = "warning";
    }
}

$page_title = "Subir Documento";
// RECICLAJE: Usamos el estilo específico para formularios de subida
$extra_css = "../style/subir_documento.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-cloud-upload-alt"></i> Cargar Nuevo Documento</h1>
        <div class="top-actions">
            <a href="mi_legajo.php" class="btn-back" style="color: #6c757d; text-decoration: none; font-weight: 500;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="card-upload-container">
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                
                <div class="form-section">
                    <label class="section-label"><i class="fas fa-folder"></i> ¿Dónde guardar este documento?</label>
                    <div class="custom-select-wrapper">
                        <select name="seccion" required class="form-control-lg">
                            <option value="">-- Selecciona una Carpeta --</option>
                            <?php foreach ($secciones as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ($seccion_preseleccionada == $sec['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sec['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <label class="section-label"><i class="fas fa-file-import"></i> Archivo a subir</label>
                    
                    <div class="drop-zone" id="dropZone">
                        <div class="drop-zone-content">
                            <i class="fas fa-cloud-upload-alt drop-icon"></i>
                            <span class="drop-text">Arrastra y suelta tu archivo aquí</span>
                            <span class="drop-subtext">o haz clic para explorar</span>
                            <input type="file" name="archivo" id="fileInput" class="drop-input" required>
                        </div>
                        <div class="file-preview" id="filePreview" style="display: none;">
                            <i class="fas fa-file-alt"></i>
                            <span id="fileName">nombre_archivo.pdf</span>
                            <i class="fas fa-check-circle success-icon"></i>
                        </div>
                    </div>
                    <p class="help-text">Formatos permitidos: PDF, DOCX, JPG, PNG. Tamaño máx: 10MB.</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-lg">
                        <i class="fas fa-paper-plane"></i> Subir Documento
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
    .form-control-lg { width: 100%; padding: 12px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; background-color: #f8f9fa; }
    
    /* Drag & Drop Zone */
    .drop-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: all 0.3s ease; position: relative; background: #fafafa; }
    .drop-zone:hover, .drop-zone.dragover { border-color: var(--color-primario); background: #f0f7ff; }
    .drop-icon { font-size: 48px; color: #94a3b8; margin-bottom: 15px; display: block; }
    .drop-text { display: block; font-size: 16px; font-weight: 500; color: #333; }
    .drop-subtext { display: block; font-size: 13px; color: #64748b; margin-top: 5px; }
    .drop-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    
    .file-preview { margin-top: 10px; padding: 10px; background: #e0f2fe; border-radius: 8px; color: #0284c7; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .help-text { font-size: 12px; color: #999; margin-top: 8px; text-align: center; }
    
    .btn-primary-lg { width: 100%; padding: 14px; background: var(--color-primario); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-primary-lg:hover { background: var(--color-primario-hover); }
    
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-warning { background: #fff3cd; color: #856404; }
    .alert-danger { background: #f8d7da; color: #721c24; }
</style>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dropContent = document.querySelector('.drop-zone-content');
    const filePreview = document.getElementById('filePreview');
    const fileNameSpan = document.getElementById('fileName');

    // Eventos Drag & Drop
    ['dragenter', 'dragover'].forEach(e => {
        dropZone.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(e => {
        dropZone.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if(files.length > 0) {
            fileInput.files = files;
            showPreview(files[0].name);
        }
    });

    fileInput.addEventListener('change', function() {
        if(this.files[0]) showPreview(this.files[0].name);
    });

    function showPreview(name) {
        dropContent.style.display = 'none';
        filePreview.style.display = 'flex';
        fileNameSpan.textContent = name;
        dropZone.style.borderStyle = 'solid';
        dropZone.style.borderColor = '#198754';
        dropZone.style.background = '#f0fff4';
    }
</script>

<?php require_once '../includes/footer.php'; ?>