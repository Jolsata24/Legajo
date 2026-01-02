<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // 2. Obtener datos actuales del empleado
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Procesar Formulario de Actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $email  = trim($_POST['email']); // Opcional: podrías hacerlo readonly si no quieres que lo cambien
        
        // Manejo de Foto
        $foto_nombre = $usuario['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                // Prefijo 'emp_' para diferenciar
                $nuevo_nombre = "emp_" . $id_usuario . "_" . time() . "." . $ext;
                // Asegurarse de que la carpeta exista
                if (!is_dir("../uploads/usuarios/")) {
                    mkdir("../uploads/usuarios/", 0777, true);
                }
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/usuarios/" . $nuevo_nombre)) {
                    $foto_nombre = $nuevo_nombre;
                }
            }
        }

        // Manejo de Contraseña
        $sql_pass = "";
        $params = [$nombre, $email, $foto_nombre];

        if (!empty($_POST['password'])) {
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql_pass = ", password_hash = ?";
            $params[] = $password_hash;
        }

        $params[] = $id_usuario;

        // Actualizar en BD
        $sql = "UPDATE usuarios SET nombre=?, email=?, foto=? $sql_pass WHERE id=?";
        $stmtUpdate = $pdo->prepare($sql);
        
        if ($stmtUpdate->execute($params)) {
            // Actualizar sesión para reflejar cambios inmediatos
            $_SESSION['nombre'] = $nombre;
            echo "<script>alert('Perfil actualizado correctamente.'); window.location.href='empleado_dashboard.php';</script>";
        } else {
            $error = "Error al actualizar la información.";
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Editar Mi Perfil";
// RECICLAJE: Usamos el CSS global de edición de perfil
$extra_css = "../style/editar_perfil.css"; 
$foto_actual = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-user-cog"></i> Configuración de Perfil</h1>
        <div class="top-actions">
            <a href="empleado_dashboard.php" class="btn-back" style="color: #6c757d; text-decoration: none; font-weight: 500;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edit-card">
            <div class="edit-layout">
                
                <div class="photo-column">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: #555;">Foto de Perfil</h3>
                    <div class="avatar-wrapper">
                        <img src="<?= htmlspecialchars($foto_actual) ?>" alt="Avatar" class="current-avatar-preview" id="previewImg">
                    </div>
                    <div class="upload-btn-wrapper">
                        <button class="btn-upload-trigger" type="button"><i class="fas fa-camera"></i> Subir nueva foto</button>
                        <input type="file" name="foto" id="fileInput" accept="image/*">
                    </div>
                    <p style="font-size: 12px; color: #999; margin-top: 10px;">Formatos: JPG, PNG</p>
                </div>

                <div class="form-column">
                    <div class="section-title"><i class="fas fa-id-card"></i> Información Personal</div>
                    
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Rol asignado</label>
                            <input type="text" class="form-control" value="Empleado" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Registro</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['fecha_creacion'] ?? '-') ?>" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="security-box">
                        <div class="section-title" style="border:none; padding:0; margin-bottom:10px;">
                            <i class="fas fa-lock"></i> Seguridad
                        </div>
                        <div class="form-group">
                            <label>Cambiar Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Escribe para cambiar (o deja vacío)">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
    // Previsualización de imagen
    document.getElementById('fileInput').addEventListener('change', function(e) {
        if (e.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) { document.getElementById('previewImg').src = e.target.result; }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>