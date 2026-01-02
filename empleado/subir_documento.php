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

// 2. Obtener Secciones
try {
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// 3. Procesar Subida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_seccion = $_POST['seccion'];
    $archivo = $_FILES['archivo'];

    if ($id_seccion && $archivo['error'] === UPLOAD_ERR_OK) {
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            
            if (move_uploaded_file($archivo['tmp_name'], "../uploads/" . $nombre_guardado)) {
                $stmt = $pdo->prepare("INSERT INTO documentos (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) VALUES (?, ?, ?, ?, ?, NOW(), 'Pendiente')");
                if ($stmt->execute([$id_usuario, $id_seccion, $nombre_original, $nombre_guardado, $ext])) {
                    header("Location: seccion_legajo.php?id=$id_seccion&msg=exito");
                    exit;
                }
            } else {
                $error = "Error al guardar el archivo.";
            }
        } else {
            $error = "Formato no permitido.";
        }
    } else {
        $error = "Faltan datos.";
    }
}

$page_title = "Subir Documento";
// AQUÍ SE LLAMA AL NUEVO CSS
$extra_css = "../style/subir_documento.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-cloud-upload-alt"></i> Cargar Documento</h1>
        <div class="top-actions">
            <a href="mi_legajo.php" class="btn-back-circle"><i class="fas fa-times"></i></a>
        </div>
    </header>

    <main class="content">
        <div class="upload-container">
            
            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                
                <div class="form-group">
                    <label>Selecciona la Carpeta:</label>
                    <div class="select-wrapper">
                        <select name="seccion" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($secciones as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ($seccion_preseleccionada == $sec['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sec['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>

                <div class="file-drop-area" id="dropArea">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <span class="file-msg">Arrastra tu archivo aquí</span>
                    <small style="color: #999;">o haz clic para buscar</small>
                    <input class="file-input" type="file" name="archivo" id="archivo" required>
                </div>
                
                <div class="file-help-text">Soporta: PDF, Word, JPG. Máx 10MB.</div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Subir Documento
                </button>
            </form>
        </div>
    </main>
</div>

<script>
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('archivo');
    const fileMsg = document.querySelector('.file-msg');

    ['dragenter', 'dragover'].forEach(e => {
        dropArea.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropArea.classList.add('highlight');
        });
    });

    ['dragleave', 'drop'].forEach(e => {
        dropArea.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropArea.classList.remove('highlight');
        });
    });

    dropArea.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        fileInput.files = files;
        updateName(files[0].name);
    });

    fileInput.addEventListener('change', function() {
        if(this.files[0]) updateName(this.files[0].name);
    });

    function updateName(name) {
        fileMsg.innerHTML = `<strong>${name}</strong>`;
        fileMsg.style.color = '#198754';
        document.querySelector('.upload-icon').className = 'fas fa-check-circle upload-icon';
        document.querySelector('.upload-icon').style.color = '#198754';
    }
</script>

<?php require_once '../includes/footer.php'; ?>