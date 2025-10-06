<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$page_title = "Editar Mi Perfil";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-edit"></i> Editar Mi Perfil</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-camera"></i> Cambiar Foto de Perfil</h3>
            
            <?php if (isset($_GET['status'])): ?>
                <div style="padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: <?= $_GET['status'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?= $_GET['status'] === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?= htmlspecialchars(urldecode($_GET['msg'])); ?>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-bottom: 20px;">
                <p>Tu foto actual:</p>
                <img src="<?= htmlspecialchars($foto_a_mostrar) ?>" alt="Foto de perfil actual" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
            </div>

            <form action="guardar_foto.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                <div class="form-group">
                    <label for="nueva_foto">Seleccionar nueva foto (JPG o PNG):</label>
                    <input type="file" name="nueva_foto" id="nueva_foto" accept="image/jpeg, image/png" required>
                </div>
                <button type="submit" class="btn-primary">Actualizar Foto</button>
            </form>
        </div>
    </main>
</div>
<style>.form-dashboard label { display: block; margin-bottom: 5px; font-weight: 600; } .form-dashboard input { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 15px; } .form-dashboard .btn-primary { padding: 10px 20px; }</style>

<?php require_once '../includes/footer.php'; ?>