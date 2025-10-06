<?php
session_start();
require '../php/db.php';

// Seguridad: Solo el admin puede acceder
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

// Traer las áreas para el menú desplegable
try {
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar las áreas: " . $e->getMessage());
}

$page_title = "Crear Nuevo Usuario";
require_once '../includes/header_admin.php'; // Asegúrate de tener este archivo o crea uno similar
require_once '../includes/sidebar_admin.php';
?>

<style>
    .form-dashboard label { display: block; margin: 15px 0 5px; font-weight: 600; }
    .form-dashboard input, .form-dashboard select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-primary { padding: 10px 20px; border: none; border-radius: 5px; background-color: #007bff; color: white; cursor: pointer; margin-top: 20px; }
    .mensaje { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .msg-ok { background-color: #d4edda; color: #155724; }
    .msg-err { background-color: #f8d7da; color: #721c24; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
    </header>

    <main class="content">
      <div class="card">
        <h3><i class="fas fa-edit"></i> Ingresa los datos del nuevo usuario</h3>
        
        <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
            <div class="mensaje <?= $_GET['status'] === 'success' ? 'msg-ok' : 'msg-err'; ?>">
                <?= htmlspecialchars(urldecode($_GET['msg'])); ?>
            </div>
        <?php endif; ?>

        <form action="guardar_usuario.php" method="post" enctype="multipart/form-data" class="form-dashboard">
          <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" id="nombre" required>
          </div>
          <div class="form-group">
            <label for="email">Correo Electrónico (será su usuario):</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div class="form-group">
            <label for="clave">Contraseña (mínimo 6 caracteres):</label>
            <input type="password" name="clave" id="clave" required minlength="6">
          </div>
          <div class="form-group">
            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
              <option value="empleado">Empleado</option>
              <option value="jefe_area">Jefe de Área</option>
              <option value="rrhh">Recursos Humanos (RRHH)</option>
              <option value="secretaria">Secretaria</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          <div class="form-group">
            <label for="id_area">Asignar a un Área (Opcional):</label>
            <select name="id_area" id="id_area">
              <option value="">-- Sin área --</option>
              <?php foreach ($areas as $area): ?>
                <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="foto">Foto de Perfil (Opcional):</label>
            <input type="file" name="foto" id="foto" accept="image/png, image/jpeg">
          </div>
          <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Usuario</button>
        </form>
      </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>