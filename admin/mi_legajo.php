<?php
session_start();
require '../php/db.php';

// Verificar sesión de Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // Datos del usuario con foto
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    // Traer las secciones del legajo
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$foto_usuario = "../img/user2.png";
if (!empty($usuario['foto']) && file_exists("../uploads/usuarios/" . $usuario['foto'])) {
    $foto_usuario = "../uploads/usuarios/" . htmlspecialchars($usuario['foto']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Legajo - Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/mi_legajo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <aside class="sidebar">
    <div class="brand">
      <h2>Bienvenido</h2>
    </div>
    <div class="user-info">
      <img src="<?= $foto_usuario; ?>" alt="Foto de usuario">
      <h4><?= htmlspecialchars($usuario['nombre']); ?></h4>
      <p><?= htmlspecialchars(ucfirst($usuario['rol'])); ?> - <?= htmlspecialchars($usuario['area'] ?? "Sin área"); ?></p>
    </div>
    <nav class="menu">
      <a href="admin_dashboard.php"><i class="fas fa-home"></i> Inicio</a>
      <a href="mi_legajo.php" class="active"><i class="fas fa-folder-open"></i> Mi Legajo</a>
      <a href="admin_documentos.php"><i class="fas fa-file-alt"></i> Ver Documentos</a>
      <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
      <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
  </aside>

  <div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Mi Legajo</h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <section class="content">
        <div class="card">
            <h3><i class="fas fa-user"></i> Mis Datos</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']); ?></p>
            <p><strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-folder"></i> Secciones de Mi Legajo</h3>
            <p>Selecciona una sección para ver o subir tus documentos personales.</p>
            <?php if (!empty($secciones)): ?>
                <ul>
                <?php foreach ($secciones as $sec): ?>
                    <li>
                        <a href="seccion_legajo_admin.php?id=<?= $sec['id']; ?>" style="display: block; padding: 10px; background: #f0f0f0; margin-bottom: 5px; text-decoration: none; color: #333; border-radius: 5px;">
                            <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($sec['nombre']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay secciones definidas en el sistema.</p>
            <?php endif; ?>
        </div>
    </section>
  </div>
</body>
</html>