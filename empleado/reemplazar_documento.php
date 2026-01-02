<?php
session_start();
require '../php/db.php';
require '../php/funciones.php'; 

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$error = null;
$msg = null;

// ==========================================
//  LÓGICA HÍBRIDA: POST (Procesar) y GET (Mostrar)
// ==========================================

// --- A. PROCESAR EL ENVÍO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_documento = isset($_POST['id_documento']) ? (int)$_POST['id_documento'] : 0;
    // NUEVO: Recibir el área confirmada
    $id_area_destino = isset($_POST['id_area_destino']) ? (int)$_POST['id_area_destino'] : 0;
    $id_usuario = (int)$_SESSION['id'];

    // Validaciones
    if ($id_documento <= 0 || $id_area_destino <= 0) {
        $error = "Error: Datos inválidos. Asegúrate de seleccionar un área.";
    } elseif (!isset($_FILES['nuevo_documento']) || $_FILES['nuevo_documento']['error'] !== UPLOAD_ERR_OK) {
        $error = "Error: Debes seleccionar un archivo válido.";
    } else {
        // Verificar estado actual
        $stmt = $pdo->prepare("SELECT nombre_guardado, estado FROM documentos WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_documento, $id_usuario]);
        $doc = $stmt->fetch();

        if (!$doc) {
            $error = "Documento no encontrado.";
        } elseif (in_array(strtolower($doc['estado']), ['aprobado', 'validado'])) {
            $error = "El documento ya fue aprobado, no se puede editar.";
        } else {
            // Subir nuevo archivo
            $archivo = $_FILES['nuevo_documento'];
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (!in_array($ext, $permitidos)) {
                $error = "Formato no permitido. (Solo PDF, Word o Imágenes)";
            } else {
                $nombre_nuevo = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
                $ruta_destino = "../uploads/" . $nombre_nuevo;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    // 1. Borrar archivo viejo
                    if (file_exists("../uploads/" . $doc['nombre_guardado'])) {
                        unlink("../uploads/" . $doc['nombre_guardado']);
                    }

                    // 2. ACTUALIZAR BASE DE DATOS (Incluyendo el Área)
                    // Actualizamos id_area_destino para "rescatar" documentos huérfanos o corregir destino
                    $sql = "UPDATE documentos SET 
                                id_area_destino = ?,  
                                nombre_original = ?, 
                                nombre_guardado = ?, 
                                tipo = ?, 
                                estado = 'Pendiente',  
                                feedback = NULL, 
                                fecha_subida = NOW() 
                            WHERE id = ?";
                    $stmtup = $pdo->prepare($sql);
                    
                    if ($stmtup->execute([$id_area_destino, $archivo['name'], $nombre_nuevo, $ext, $id_documento])) {
                        
                        // 3. Registrar Historial
                        $desc = "Archivo corregido y reenviado a área ID: $id_area_destino";
                        $stmth = $pdo->prepare("INSERT INTO documentos_historial (id_documento, id_usuario_accion, accion, descripcion) VALUES (?, ?, 'REEMPLAZADO', ?)");
                        $stmth->execute([$id_documento, $id_usuario, $desc]);

                        header("Location: documentos_enviados.php?msg=exito");
                        exit;
                    } else {
                        $error = "Error al actualizar la base de datos.";
                    }
                } else {
                    $error = "Error al guardar el archivo en el servidor.";
                }
            }
        }
    }
}

// --- B. OBTENER DATOS PARA MOSTRAR (GET) ---
$id_get = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_get === 0 && isset($_POST['id_documento'])) $id_get = (int)$_POST['id_documento'];

$doc_info = null;
if ($id_get > 0) {
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_get, $_SESSION['id']]);
    $doc_info = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$doc_info) {
    header("Location: documentos_enviados.php");
    exit;
}

// Obtener Lista de Áreas para el Select
try {
    $stmtAreas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC");
    $areas = $stmtAreas->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar áreas.");
}

$page_title = "Corregir Documento";
$extra_css = "../style/subir_documento.css"; 

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-sync-alt"></i> Corregir / Reemplazar Documento</h1>
        <div class="top-actions">
            <a href="documentos_enviados.php" class="btn-back" style="color: #6c757d; text-decoration: none;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card-upload-container">
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #ffc107;">
                <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #333;">
                    <i class="fas fa-file-alt"></i> Documento: <?= htmlspecialchars($doc_info['nombre_original']) ?>
                </h3>
                <?php if (!empty($doc_info['feedback'])): ?>
                    <div style="margin-top: 10px; font-size: 14px; color: #856404;">
                        <strong>Observación:</strong> "<?= htmlspecialchars($doc_info['feedback']) ?>"
                    </div>
                <?php endif; ?>
            </div>

            <form action="reemplazar_documento.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_documento" value="<?= $doc_info['id'] ?>">

                <div class="form-section">
                    <label class="section-label"><i class="fas fa-building"></i> Confirmar Área de Destino</label>
                    <div class="custom-select-wrapper">
                        <select name="id_area_destino" required class="form-control-lg" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                            <option value="">-- Selecciona el Área --</option>
                            <?php foreach ($areas as $area): ?>
                                <?php 
                                    // Pre-seleccionar el área que tenía el documento (si tenía una)
                                    $selected = ($area['id'] == $doc_info['id_area_destino']) ? 'selected' : '';
                                ?>
                                <option value="<?= $area['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <small style="color: #666; font-size: 12px;">Si el documento no aparecía, asegúrate de elegir el área correcta aquí.</small>
                </div>

                <div class="form-section">
                    <label class="section-label"><i class="fas fa-file-upload"></i> Subir versión corregida</label>
                    
                    <div class="drop-zone" id="dropZone">
                        <div class="drop-zone-content">
                            <i class="fas fa-cloud-upload-alt drop-icon"></i>
                            <span class="drop-text">Arrastra tu archivo corregido aquí</span>
                            <span class="drop-subtext">o haz clic para buscar</span>
                            <input type="file" name="nuevo_documento" id="fileInput" class="drop-input" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        
                        <div class="file-preview" id="filePreview" style="display: none;">
                            <i class="fas fa-file-signature"></i>
                            <span id="fileName">archivo.pdf</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-lg" style="background-color: #ffc107; color: #000;">
                        <i class="fas fa-check"></i> Confirmar y Enviar
                    </button>
                </div>

            </form>
        </div>

    </main>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dropContent = document.querySelector('.drop-zone-content');
    const filePreview = document.getElementById('filePreview');
    const fileNameSpan = document.getElementById('fileName');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); });
    });
    
    dropZone.addEventListener('dragover', () => dropZone.classList.add('dragover'));
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    
    dropZone.addEventListener('drop', e => {
        fileInput.files = e.dataTransfer.files;
        updatePreview();
    });
    
    fileInput.addEventListener('change', updatePreview);

    function updatePreview() {
        if (fileInput.files && fileInput.files[0]) {
            dropContent.style.display = 'none';
            filePreview.style.display = 'flex';
            fileNameSpan.textContent = fileInput.files[0].name;
            dropZone.style.background = '#fff3cd';
            dropZone.style.borderColor = '#ffc107';
        }
    }
</script>

<style>
    .card-upload-container { background: #fff; border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-section { margin-bottom: 25px; }
    .section-label { display: block; font-weight: 600; margin-bottom: 10px; color: #333; }
    .drop-zone { border: 2px dashed #ccc; border-radius: 10px; padding: 30px; text-align: center; position: relative; transition: .3s; }
    .drop-zone:hover { border-color: #ffc107; background: #fffdf5; }
    .drop-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .drop-icon { font-size: 40px; color: #aaa; margin-bottom: 10px; }
    .file-preview { margin-top: 10px; padding: 10px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-primary-lg { width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
</style>

<?php require_once '../includes/footer.php'; ?>