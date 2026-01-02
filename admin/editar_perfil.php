<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

// 2. Determinar a QUIÉN estamos editando
$id_usuario_editar = $_GET['id'] ?? $_SESSION['id']; // Si no hay GET, es mi propio perfil
$es_mi_perfil = ($id_usuario_editar == $_SESSION['id']);

try {
    // A. Obtener datos del Usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario_editar]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }

    // B. Obtener lista de Áreas (para el select)
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

    // C. Procesar Formulario (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $email  = trim($_POST['email']);
        $rol    = $_POST['rol']; // El admin puede cambiar roles
        $id_area = !empty($_POST['id_area']) ? $_POST['id_area'] : null;
        
        // Manejo de Foto
        $foto_nombre = $usuario['foto']; // Mantener la anterior por defecto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $nuevo_nombre = "user_" . $id_usuario_editar . "_" . time() . "." . $ext;
                $ruta_destino = "../uploads/usuarios/" . $nuevo_nombre;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    $foto_nombre = $nuevo_nombre;
                }
            }
        }

        // Manejo de Contraseña (Solo si se escribe algo)
        $sql_pass = "";
        $params = [$nombre, $email, $rol, $id_area, $foto_nombre];

        if (!empty($_POST['password'])) {
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql_pass = ", password_hash = ?";
            $params[] = $password_hash;
        }

        // Ejecutar UPDATE
        $params[] = $id_usuario_editar; // El ID va al final para el WHERE
        $sql = "UPDATE usuarios SET nombre=?, email=?, rol=?, id_area=?, foto=? $sql_pass WHERE id=?";
        
        $stmtUpdate = $pdo->prepare($sql);
        if ($stmtUpdate->execute($params)) {
            // Si me edité a mí mismo, actualizo la sesión también
            if ($es_mi_perfil) {
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = $rol;
            }
            // Redirección inteligente
            $redirect = $es_mi_perfil ? 'admin_dashboard.php' : 'empleados_panel.php';
            echo "<script>alert('Perfil actualizado correctamente.'); window.location.href='$redirect';</script>";
        } else {
            $error = "Error al actualizar la base de datos.";
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = $es_mi_perfil ? "Editar Mi Perfil" : "Editar Usuario: " . $usuario['nombre'];
$extra_css = "../style/editar_perfil.css";

// Determinar la foto actual para mostrar
$foto_actual = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-user-edit"></i> <?= $es_mi_perfil ? 'Mi Perfil' : 'Gestionar Usuario' ?></h1>
        <div class="top-actions">
            <a href="<?= $es_mi_perfil ? 'admin_dashboard.php' : 'empleados_panel.php' ?>" class="btn-back" style="color: #6c757d; text-decoration: none; font-weight: 500;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        
        <form method="POST" enctype="multipart/form-data" class="edit-card">
            <div class="edit-layout">
                
                <div class="photo-column">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: #495057;">Foto Actual</h3>
                    
                    <img src="<?= htmlspecialchars($foto_actual) ?>" alt="Avatar" class="current-avatar-preview" id="previewImg">
                    
                    <div class="upload-btn-wrapper">
                        <button class="btn-upload-trigger" type="button"><i class="fas fa-camera"></i> Cambiar Foto</button>
                        <input type="file" name="foto" id="fileInput" accept="image/*">
                    </div>
                    <p style="font-size: 12px; color: #adb5bd; margin-top: 10px;">JPG o PNG, máx 5MB</p>
                </div>

                <div class="form-column">
                    
                    <div>
                        <div class="section-title"><i class="fas fa-id-card"></i> Información Personal</div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="section-title"><i class="fas fa-sitemap"></i> Organización</div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Rol del Sistema</label>
                                <select name="rol" class="form-control">
                                    <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="jefe_area" <?= $usuario['rol'] == 'jefe_area' ? 'selected' : '' ?>>Jefe de Área</option>
                                    <option value="rrhh" <?= $usuario['rol'] == 'rrhh' ? 'selected' : '' ?>>Recursos Humanos</option>
                                    <option value="empleado" <?= $usuario['rol'] == 'empleado' ? 'selected' : '' ?>>Empleado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Área Asignada</label>
                                <select name="id_area" class="form-control">
                                    <option value="">-- Sin Área --</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area['id'] ?>" <?= $usuario['id_area'] == $area['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($area['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="security-box">
                        <div class="section-title" style="border:none; padding:0; margin-bottom:10px;">
                            <i class="fas fa-lock"></i> Seguridad
                        </div>
                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Escribe para cambiar la contraseña...">
                            <span class="password-hint">Déjalo en blanco si no deseas cambiar la contraseña actual.</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" style="padding: 12px 30px;">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>

                </div>
            </div>
        </form>

    </main>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    const previewImg = document.getElementById('previewImg');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>