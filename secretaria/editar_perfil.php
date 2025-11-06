<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$page_title = "Editar Mi Perfil";
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php'; 
// El sidebar_secretaria.php ya define la variable $foto_path
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-edit"></i> Editar Mi Perfil</h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-camera"></i> Cambiar Foto de Perfil</h3>
            
            <?php if (isset($_GET['status'])): ?>
                <div class="mensaje" style="padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: <?= $_GET['status'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?= $_GET['status'] === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?= htmlspecialchars(urldecode($_GET['msg'])); ?>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-bottom: 20px;">
                <p>Tu foto actual:</p>
                
                <img src="<?= htmlspecialchars($foto_path) ?>" alt="Foto actual" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
            </div>
            
            <form action="../php/guardar_foto.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                <div class="form-group">
                    <label for="nueva_foto">Seleccionar nueva foto (JPG o PNG):</label>
                    <input type="file" name="nueva_foto" id="nueva_foto" accept="image/jpeg, image/png" required>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar Foto</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>