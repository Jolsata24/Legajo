<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$page_title = "Editar Mi Perfil";
// Incluimos el header que deberías crear para admin, si no, puedes construir el HTML aquí.
// Por simplicidad, lo dejamos como en tu código original.

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $page_title ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php
    // --- ¡CORRECCIÓN CLAVE! ---
    // Incluimos el sidebar centralizado para el admin
    require_once '../includes/sidebar_admin.php'; 
    ?>

    <div class="main">
        <header class="topbar">
          <h1><i class="fas fa-user-edit"></i> Editar Perfil de Administrador</h1>
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
                    <img src="<?= htmlspecialchars($foto_a_mostrar) ?>" alt="Foto actual" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                </div>
                
                <form action="../php/guardar_foto.php" method="post" enctype="multipart/form-data" class="form-dashboard">
                    <label for="nueva_foto">Seleccionar nueva foto (JPG o PNG):</label>
                    <input type="file" name="nueva_foto" id="nueva_foto" required>
                    <br><br>
                    <button type="submit" style="padding: 10px 20px; border:none; background-color:#007bff; color:white; border-radius:5px; cursor:pointer;">Actualizar Foto</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>