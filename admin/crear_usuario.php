<?php
session_start();
require '../php/db.php';

// 1. Verificación de Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Lógica Original: Obtener Áreas para el Select
try {
    $stmt = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC");
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar áreas: " . $e->getMessage());
}

$page_title = "Registrar Nuevo Usuario";
$extra_css = "../style/crear_usuario.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-user-plus"></i> Registrar Usuario</h1>
        <div class="top-actions">
             <a href="empleados_panel.php" class="btn-back" style="color: var(--color-texto-secundario); text-decoration: none; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Cancelar y Volver
            </a>
        </div>
    </header>

    <main class="content">
        
        <form action="guardar_usuario.php" method="POST" enctype="multipart/form-data" class="form-card" id="createUserForm">
            
            <div class="form-layout">
                
                <div class="photo-section">
                    <h3 style="font-size: 16px; margin-bottom: 20px;">Foto de Perfil</h3>
                    
                    <div class="avatar-upload">
                        <div class="avatar-edit">
                            <input type='file' name="foto" id="imageUpload" accept=".png, .jpg, .jpeg" />
                            <label for="imageUpload"><i class="fas fa-pencil-alt"></i></label>
                        </div>
                        <div class="avatar-preview">
                            <div id="imagePreview" style="background-image: url('../img/user.png');">
                            </div>
                        </div>
                    </div>
                    
                    <p class="photo-hint">
                        Formatos permitidos: JPG, PNG.<br>
                        Tamaño máx: 5MB.
                    </p>
                </div>

                <div class="data-section">
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="nombre"><i class="fas fa-user"></i> Nombre Completo</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="juan.perez@dremh.gob.pe" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Crear contraseña segura" required>
                        </div>
                        <div class="form-group">
                            <label for="rol"><i class="fas fa-user-tag"></i> Rol en el Sistema</label>
                            <select name="rol" id="rol" class="form-control" required>
                                <option value="empleado">Empleado</option>
                                <option value="jefe_area">Jefe de Área</option>
                                <option value="rrhh">Recursos Humanos</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_area"><i class="fas fa-building"></i> Área Asignada</label>
                        <select name="id_area" id="id_area" class="form-control">
                            <option value="">-- Sin área asignada (General) --</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #6c757d; font-size: 12px; margin-top: 5px; display: block;">
                            Si seleccionas "Jefe de Área", asegúrate de asignarle el área correspondiente.
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </div>

                </div>
            </div>
        </form>

    </main>
</div>

<script>
    // Al cambiar el input file
    document.getElementById('imageUpload').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                // Cambiamos el background-image del div de previsualización
                document.getElementById('imagePreview').style.backgroundImage = "url('"+e.target.result+"')";
                
                // Hacemos que la imagen cubra todo el div
                document.getElementById('imagePreview').style.backgroundSize = "cover";
                document.getElementById('imagePreview').style.backgroundPosition = "center";
                document.getElementById('imagePreview').style.width = "100%";
                document.getElementById('imagePreview').style.height = "100%";
            }
            
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>