<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$page_title = "Editar Mi Perfil";
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-edit"></i> Editar Mi Perfil</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-camera"></i> Cambiar Foto de Perfil</h3>
            <div style="text-align: center; margin-bottom: 20px;">
                <p>Tu foto actual:</p>
                <img src="<?= htmlspecialchars($foto_a_mostrar) ?>" alt="Foto actual" style="width: 150px; height: 150px; border-radius: 50%;">
            </div>
            <form action="guardar_foto.php" method="post" enctype="multipart/form-data">
                <label for="nueva_foto">Seleccionar nueva foto:</label>
                <input type="file" name="nueva_foto" id="nueva_foto" required>
                <button type="submit">Actualizar Foto</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>