<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$mensaje_error = '';

// 2. PROCESAR EL FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seccion_id = $_POST['seccion_id'] ?? null;
    $tipo = trim($_POST['tipo'] ?? '');

    if (!$seccion_id || empty($tipo) || !isset($_FILES['documento'])) {
        $mensaje_error = "Por favor completa todos los campos.";
    } else {
        $archivo = $_FILES['documento'];
        if ($archivo['error'] === UPLOAD_ERR_OK) {
            $directorio = "../uploads/";
            if (!is_dir($directorio)) mkdir($directorio, 0777, true);

            $nombre_original = basename($archivo['name']);
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            
            // Validar extensiones (Opcional pero recomendado)
            $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowed)) {
                $mensaje_error = "Formato de archivo no permitido.";
            } else {
                // Generar nombre único
                $nombre_guardado = time() . "_" . uniqid() . "." . $extension;
                $ruta_destino = $directorio . $nombre_guardado;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    // Guardar en BD
                    try {
                        $stmt = $pdo->prepare(
                            "INSERT INTO documentos (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado)
                             VALUES (?, ?, ?, ?, ?, NOW(), 'revisado')" // Admin se auto-aprueba o queda revisado
                        );
                        $stmt->execute([$usuario_id, $seccion_id, $nombre_original, $nombre_guardado, $tipo]);

                        // Redirigir Éxito
                        header("Location: seccion_legajo_admin.php?id=" . $seccion_id . "&upload=success");
                        exit;
                    } catch (PDOException $e) {
                        unlink($ruta_destino); // Borrar si falla BD
                        $mensaje_error = "Error de base de datos: " . $e->getMessage();
                    }
                } else {
                    $mensaje_error = "Error al mover el archivo al servidor.";
                }
            }
        } else {
            $mensaje_error = "Error en la subida del archivo (Código: {$archivo['error']}).";
        }
    }
}

// 3. PREPARAR VISTA (GET)
$seccion_id = $_GET['seccion_id'] ?? $_POST['seccion_id'] ?? null;
if (!$seccion_id) {
    header("Location: mi_legajo.php");
    exit;
}

// Obtener nombre de la sección para mostrarlo
try {
    $stmtSec = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmtSec->execute([$seccion_id]);
    $seccion = $stmtSec->fetch();
    $nombre_seccion = $seccion ? $seccion['nombre'] : 'General';
} catch(PDOException $e) {
    $nombre_seccion = 'Desconocida';
}

$page_title = "Subir Documento";
$extra_css = "../style/subir_documento.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-cloud-upload-alt"></i> Subir Documento</h1>
        <a href="seccion_legajo_admin.php?id=<?= $seccion_id ?>" class="btn-back" style="color: #6c757d; text-decoration: none;">
            <i class="fas fa-times"></i> Cancelar
        </a>
    </header>

    <main class="content">
        
        <div class="upload-container">
            <form action="" method="POST" enctype="multipart/form-data" class="upload-card">
                
                <input type="hidden" name="seccion_id" value="<?= htmlspecialchars($seccion_id) ?>">

                <div class="upload-header">
                    <div class="upload-icon-circle">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h2>Subir a: <?= htmlspecialchars($nombre_seccion) ?></h2>
                    <p>Agrega un nuevo documento a tu legajo digital</p>
                </div>

                <?php if ($mensaje_error): ?>
                    <div style="background: #f8d7da; color: #842029; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                        <i class="fas fa-exclamation-circle"></i> <?= $mensaje_error ?>
                    </div>
                <?php endif; ?>

                <div class="drop-zone" id="dropZone">
                    <input type="file" name="documento" id="fileInput" class="file-input" required>
                    <div class="drop-zone-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span class="drop-zone-text">Arrastra tu archivo aquí</span>
                        <span class="drop-zone-hint">o haz clic para explorar</span>
                    </div>
                </div>

                <div class="file-preview" id="filePreview">
                    <i class="fas fa-file-alt preview-icon" id="fileIcon"></i>
                    <div class="file-details">
                        <span class="file-name" id="fileName">nombre_archivo.pdf</span>
                        <span class="file-size" id="fileSize">2.5 MB</span>
                    </div>
                    <button type="button" class="btn-remove-file" id="btnRemoveFile"><i class="fas fa-times"></i></button>
                </div>

                <div class="form-group">
                    <label for="tipo" class="form-label">Nombre o Tipo de Documento</label>
                    <input type="text" name="tipo" id="tipo" class="form-input" placeholder="Ej: Título Universitario, DNI Escaneado..." required>
                </div>

                <button type="submit" class="btn-upload">
                    <i class="fas fa-save"></i> Guardar Documento
                </button>

            </form>
        </div>

    </main>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const btnRemove = document.getElementById('btnRemoveFile');
    const dropZoneContent = document.querySelector('.drop-zone-content');

    // Efectos visuales al arrastrar
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    // Manejar archivo seleccionado (Click o Drop)
    fileInput.addEventListener('change', handleFiles);

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files; // Asignar archivos al input real
        handleFiles();
    });

    function handleFiles() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            // Mostrar Info
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            
            // Mostrar Preview y ocultar texto de drop
            filePreview.classList.add('active');
            dropZone.style.display = 'none'; // Ocultamos la zona grande para ahorrar espacio
            
            // Intentar adivinar el nombre si el campo tipo está vacío
            const tipoInput = document.getElementById('tipo');
            if(tipoInput.value === '') {
                // Quitar extensión
                let nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                tipoInput.value = nameWithoutExt.charAt(0).toUpperCase() + nameWithoutExt.slice(1);
            }
        }
    }

    // Botón eliminar archivo
    btnRemove.addEventListener('click', () => {
        fileInput.value = ''; // Limpiar input
        filePreview.classList.remove('active');
        dropZone.style.display = 'block'; // Volver a mostrar zona
    });
</script>

<?php require_once '../includes/footer.php'; ?>