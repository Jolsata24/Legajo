<?php
session_start();
require '../php/db.php';
require_once '../includes/sidebar_admin.php';
// Verificar sesión solo secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre");
    $areas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
$foto_path = '../img/user2.png';
if (!empty($_SESSION['foto'])) {
    $ruta_foto_usuario = '../uploads/usuarios/' . $_SESSION['foto'];
    if (file_exists($ruta_foto_usuario)) {
        $foto_path = $ruta_foto_usuario;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Áreas - Documentos</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <h2>Panel de Admin</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($foto_path) ?>" alt="Foto del Usuario">
      <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?></h4>
      <p><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? '')) ?></p>
    </div>
    <nav class="menu">
      <a href="admin_dashboard.php"><i class="fas fa-home"></i> Inicio</a>
      <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>
      <a href="admin_documentos.php"><i class="fas fa-file-alt"></i> Ver Documentos</a>
      <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
      <a href="crear_usuario.php"><i class="fas fa-user-plus"></i> Crear Usuario</a>
      <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
      <a href="editar_perfil.php"><i class="fas fa-user-edit"></i> Editar Perfil</a>
    </nav>
</aside>

  <!-- Main -->
  <div class="main">
    <header class="topbar">
      <h1><i class="fas fa-building"></i> Áreas disponibles</h1>
      <div class="top-actions"><span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span></div>
    </header>

    <main class="content">
      <?php if ($areas): ?>
        <div class="card-grid">
          <?php foreach ($areas as $area): ?>
            <div class="card-mini">
              <div class="card-header"><i class="fas fa-building"></i></div>
              <div class="card-body"><h3><?= htmlspecialchars($area['nombre']); ?></h3></div>
              <div class="card-footer">
                <a class="btn-view" href="admin_documento_area.php?area_id=<?= $area['id']; ?>">
                  <i class="fas fa-eye"></i> Ver Documentos
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No hay áreas registradas.</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
