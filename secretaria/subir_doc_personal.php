<?php
session_start();
require '../php/db.php';
require_once '../php/funciones.php'; 

// 1. SEGURIDAD: Verificar rol
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$error = '';
$mensaje = '';

// Obtener el ID de la sección (ya sea por GET al entrar o POST al fallar)
$id_seccion = $_REQUEST['id_seccion'] ?? null;

// 2. LÓGICA DE PROCESAMIENTO (Cuando se envía el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_usuario = $_SESSION['id'];
    $archivo    = $_FILES['archivo'] ?? null;

    if ($id_seccion && $archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        
        $nombre_original = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $tipos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($ext, $tipos_permitidos)) {
            
            $nombre_guardado = time() . "_" . $id_usuario . "_" . uniqid() . "." . $ext;
            $ruta_destino = "../uploads/" . $nombre_guardado;
            
            if (!is_dir("../uploads/")) mkdir("../uploads/", 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                
                try {
                    $sql = "INSERT INTO documentos 
                            (id_usuario, id_seccion, nombre_original, nombre_guardado, tipo, fecha_subida, estado) 
                            VALUES (?, ?, ?, ?, ?, NOW(), 'Pendiente')";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$id_usuario, $id_seccion, $nombre_original, $nombre_guardado, $ext])) {
                        
                        // Auditoría
                        registrar_auditoria($pdo, $id_usuario, 'SUBIDA_LEGAJO_SECRETARIA', "Subió: $nombre_original");
                        
                        // Redirigir con éxito
                        header("Location: seccion_legajo.php?id=$id_seccion&msg=exito_personal");
                        exit;

                    } else {
                        $error = "Error al guardar en base de datos.";
                    }
                } catch (PDOException $e) {
                    $error = "Error SQL: " . $e->getMessage();
                }
            } else {
                $error = "Error al subir el archivo al servidor.";
            }
        } else {
            $error = "Formato no permitido (Use PDF, Word o Imágenes).";
        }
    } else {
        $error = "Seleccione un archivo válido.";
    }
}

// 3. OBTENER NOMBRE SECCIÓN (Para el título)
$nombre_seccion = "Sección Desconocida";
if ($id_seccion) {
    $stmt = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt->execute([$id_seccion]);
    $res = $stmt->fetch();
    if ($res) $nombre_seccion = $res['nombre'];
}

$page_title = "Subir a " . $nombre_seccion;
$extra_css = "../style/subir_documento.css"; // Asegúrate de que este CSS exista o usa uno genérico

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <div class="header-left">
            <a href="seccion_legajo.php?id=<?= $id_seccion ?>" class="btn-back-circle">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Subir Documento</h1>
        </div>
    </header>

    <main class="content">
        <div class="upload-container">
            
            <div class="upload-header">
                <div class="icon-wrapper">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h2><?= htmlspecialchars($nombre_seccion) ?></h2>
                <p>Seleccione el archivo que desea agregar a su legajo digital.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="background:#f8d7da; color:#842029; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center;">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="subir_doc_personal.php" method="POST" enctype="multipart/form-data" class="upload-form">
                
                <input type="hidden" name="id_seccion" value="<?= htmlspecialchars($id_seccion) ?>">

                <div class="file-drop-area" id="dropArea">
                    <span class="fake-btn">Elegir Archivo</span>
                    <span class="file-msg">o arrastra y suelta aquí</span>
                    <input class="file-input" type="file" name="archivo" id="archivoInput" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>

                <div id="filePreview" class="file-preview">
                    <i class="fas fa-file"></i> <span id="fileName">Ningún archivo seleccionado</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Guardar Documento
                    </button>
                    <a href="seccion_legajo.php?id=<?= $id_seccion ?>" class="btn-cancel">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
    document.getElementById('archivoInput').addEventListener('change', function() {
        var fileName = this.files[0] ? this.files[0].name : "Ningún archivo seleccionado";
        document.getElementById('fileName').textContent = fileName;
        document.querySelector('.file-drop-area').classList.add('active');
    });
</script>

<style>
/* Estilos Rápidos Embebidos por si falta el CSS externo */
.upload-container {
    max-width: 600px; margin: 40px auto; background: #fff; padding: 40px;
    border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e9ecef;
}
.upload-header { text-align: center; margin-bottom: 30px; }
.icon-wrapper { 
    width: 60px; height: 60px; background: rgba(211, 47, 47, 0.1); color: #d32f2f;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 24px; margin: 0 auto 15px;
}
.file-drop-area {
    position: relative; display: flex; align-items: center; justify-content: center;
    width: 100%; max-width: 100%; padding: 40px; border: 2px dashed #ccc;
    border-radius: 12px; transition: 0.2s; background: #f8f9fa; cursor: pointer;
    flex-direction: column; gap: 10px;
}
.file-drop-area.active { border-color: #d32f2f; background: rgba(211, 47, 47, 0.02); }
.fake-btn { 
    background: #fff; border: 1px solid #ccc; padding: 8px 16px; border-radius: 6px; 
    font-weight: 500; font-size: 14px; color: #555;
}
.file-input {
    position: absolute; left: 0; top: 0; height: 100%; width: 100%;
    opacity: 0; cursor: pointer;
}
.file-preview { margin-top: 15px; text-align: center; color: #555; font-size: 14px; }
.form-actions { margin-top: 30px; display: flex; gap: 15px; justify-content: center; }
.btn-submit {
    background: #d32f2f; color: white; border: none; padding: 12px 24px;
    border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;
    display: flex; align-items: center; gap: 8px; font-size: 15px;
}
.btn-submit:hover { background: #b71c1c; }
.btn-cancel {
    background: #f1f3f5; color: #495057; padding: 12px 24px; border-radius: 8px;
    text-decoration: none; font-weight: 600; transition: 0.2s; display: flex; align-items: center;
}
.btn-cancel:hover { background: #e9ecef; }
.header-left { display: flex; align-items: center; gap: 15px; }
.btn-back-circle { 
    width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #dee2e6;
    display: flex; align-items: center; justify-content: center; color: #555; text-decoration: none; transition: 0.2s;
}
.btn-back-circle:hover { background: #f8f9fa; color: #000; }
</style>

<?php require_once '../includes/footer.php'; ?>