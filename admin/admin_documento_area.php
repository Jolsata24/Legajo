<?php
session_start();
require '../php/db.php';

// Solo secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$area_id = $_GET['area_id'] ?? null;
if (!$area_id) {
    die("⚠️ No se especificó el área.");
}

try {
    // Nombre del área
    $stmt = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt->execute([$area_id]);
    $area = $stmt->fetch();

    // Documentos del área
    // Documentos del área
$stmt = $pdo->prepare("
    SELECT id, nombre_original, tipo, fecha_subida, estado, feedback
    FROM documentos 
    WHERE id_area_destino = ?
    ORDER BY fecha_subida DESC
");
$stmt->execute([$area_id]);
$documentos = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Documentos - <?= htmlspecialchars($area['nombre']); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand"><h2>Bienvenido</h2></div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($_SESSION['foto'] ?? '../img/user2.png') ?>" alt="Foto Usuario">
      <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h4>
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
    <header class="topbar">
      <h1><i class="fas fa-file-alt"></i> Documentos - <?= htmlspecialchars($area['nombre']); ?></h1>
      <div class="top-actions"><span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span></div>
    </header>
    <a href="panel_jefes.php" class="btn-back">
  <i class="fas fa-arrow-left"></i> Atrás
</a>
    <main class="content">
      <?php if ($documentos): ?>
        <div class="card-grid">
          <?php foreach ($documentos as $doc): ?>
            <div class="card-mini">
              <div class="card-header"><i class="fas fa-file"></i></div>
              <div class="card-body">
  <h3><?= htmlspecialchars($doc['nombre_original']); ?></h3>
  <p><strong>Fecha:</strong> <?= htmlspecialchars($doc['fecha_subida']); ?></p>
  <p><strong>Estado:</strong> 
    <span class="estado <?= strtolower($doc['estado']); ?>">
      <?= htmlspecialchars($doc['estado']); ?>
    </span>
  </p>
  <?php if (!empty($doc['feedback'])): ?>
    <p><strong>Feedback:</strong> <?= htmlspecialchars($doc['feedback']); ?></p>
  <?php endif; ?>
</div>

              <div class="card-footer">
                <a class="btn-view" href="ver_documento.php?id=<?= $doc['id']; ?>">
                  <i class="fas fa-eye"></i> Ver Detalles
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No hay documentos en esta área.</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
