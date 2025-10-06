<?php
require '../php/db.php';

try {
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Nueva Cuenta</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/login.css">
</head>
<body>
  <main class="login-wrapper" role="main">
    <section class="card">
        <div class="brand">
            <img src="../img/dremhlogo.png" alt="Logo DREMH Pasco">
        </div>
        <h2 id="login-title">Crear Nueva Cuenta</h2>

        <?php if (!empty($_GET['msg'])): ?>
            <div style="padding: 10px; ">
                <?= htmlspecialchars(urldecode($_GET['msg'])); ?>
            </div>
        <?php endif; ?>

        <form action="guardar_registro.php" method="post" enctype="multipart/form-data" class="login-form">
          <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" id="nombre" required>
          </div>
          <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div class="form-group">
            <label for="clave">Contraseña:</label>
            <input type="password" name="clave" id="clave" required minlength="6">
          </div>
          <div class="form-group">
            <label for="id_area">Selecciona tu Área (Opcional):</label>
            <select name="id_area" id="id_area" style="width: 100%;">
              <option value="">-- Sin área específica --</option>
              <?php foreach ($areas as $area): ?>
                <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="foto">Foto de Perfil (Opcional):</label>
            <input type="file" name="foto" id="foto" accept="image/png, image/jpeg">
          </div>
          <button type="submit" class="btn-submit">Registrarme</button>
        </form>
        <div class="form-footer">
            <a href="../into/login.html">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>
    </section>
  </main>
</body>
</html>