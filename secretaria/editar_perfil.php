<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // 2. Obtener datos actuales
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Procesar Formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $email  = trim($_POST['email']);
        
        // Manejo de Foto
        $foto_nombre = $usuario['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $nuevo_nombre = "sec_" . $id_usuario . "_" . time() . "." . $ext;
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

        // Actualizar
        $sql = "UPDATE usuarios SET nombre=?, email=?, foto=? $sql_pass WHERE id=?";
        $stmtUpdate = $pdo->prepare($sql);
        
        if ($stmtUpdate->execute($params)) {
            // Actualizar sesión
            $_SESSION['nombre'] = $nombre;
            echo "<script>alert('Perfil actualizado correctamente.'); window.location.href='secretaria_dashboard.php';</script>";
        } else {
            $error = "Error al actualizar.";
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Editar Mi Perfil";
// RECICLAJE: Usamos el estilo que creamos para Admin
$extra_css = "../style/editar_perfil.css";
$foto_actual = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-user-edit"></i> Editar Mi Perfil</h1>
        <div class="top-actions">
            <a href="secretaria_dashboard.php" class="btn-back" style="color: #6c757d; text-decoration: none;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </header>

    <main class="content">
        <form method="POST" enctype="multipart/form-data" class="edit-card">
            <div class="edit-layout">
                
                <div class="photo-column">
                    <h3 style="font-size: 16px; margin-bottom: 15px;">Mi Foto</h3>
                    <img src="<?= htmlspecialchars($foto_actual) ?>" alt="Avatar" class="current-avatar-preview" id="previewImg">
                    <div class="upload-btn-wrapper">
                        <button class="btn-upload-trigger" type="button"><i class="fas fa-camera"></i> Cambiar</button>
                        <input type="file" name="foto" id="fileInput" accept="image/*">
                    </div>
                </div>

                <div class="form-column">
                    <div class="section-title"><i class="fas fa-id-card"></i> Datos Personales</div>
                    
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
                            <label>Rol</label>
                            <input type="text" class="form-control" value="<?= ucfirst($usuario['rol']) ?>" readonly>
                        </div>
                    </div>

                    <div class="security-box">
                        <div class="section-title" style="border:none; padding:0; margin-bottom:10px;">
                            <i class="fas fa-key"></i> Seguridad
                        </div>
                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
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
    document.getElementById('fileInput').addEventListener('change', function(e) {
        if (e.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) { document.getElementById('previewImg').src = e.target.result; }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>