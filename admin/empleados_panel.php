<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_area', 'rrhh', 'admin','secretaria'])) {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$rol = $_SESSION['rol'];
$id_area = $_SESSION['id_area'] ?? null;

try {
    if ($rol === 'jefe_area' && $id_area) {
        // Jefe de área solo ve empleados de su área
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id_area = ? AND rol = 'empleado'");
        $stmt->execute([$id_area]);
    } else {
        // RRHH y Admin ven todos
        $stmt = $pdo->query("SELECT id, nombre, email, rol FROM usuarios WHERE rol = 'empleado'");
    }
    $empleados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Empleados</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/empleados.css"> <!-- reutilizamos mismo css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <h2>Bienvenido</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($_SESSION['foto'] ?? '../img/user2.png') ?>" alt="Foto Usuario">
      <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h4>
      <p><?= htmlspecialchars(ucfirst($rol)) ?></p>
    </div>
    <nav class="menu">
      <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>
      <a href="admin_documentos.php"><i class="fas fa-file-alt"></i> Ver Documentos</a>
      <a href="empleados_panel.php" class="active"><i class="fas fa-users"></i> Empleados</a>
      <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fas fa-users"></i> Panel de Empleados</h1>
      <div class="top-actions">
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <!-- Contenido -->
    <main class="content">
  <?php if (count($empleados) > 0): ?>
    <div class="card-grid">
      <?php foreach ($empleados as $emp): ?>
        <div class="card-mini">
          <div class="card-header">
            <i class="fas fa-user-circle"></i>
          </div>
          <div class="card-body">
            <h3><?= htmlspecialchars($emp['nombre']); ?></h3>
            <p><?= htmlspecialchars($emp['email']); ?></p>
          </div>
          <div class="card-footer">
            <a class="btn-view" href="../panel_de_jefe/ver_empleado.php?id=<?= $emp['id']; ?>">
              <i class="fas fa-eye"></i> Ver
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No hay empleados registrados.</p>
  <?php endif; ?>
</main>

  </div>
</body>
</html>
