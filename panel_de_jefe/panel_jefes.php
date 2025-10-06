<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_area', 'rrhh', 'admin','secretaria'])) {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$rol        = $_SESSION['rol'];
$id_area    = $_SESSION['id_area'] ?? null;

$area_seleccionada = $_GET['area'] ?? null;

try {
    // Traer todas las áreas
    $stmt = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre");
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Documentos solo si se selecciona un área
    $documentos_area = [];
    if ($area_seleccionada) {
        $stmt = $pdo->prepare("
            SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida, d.estado, d.feedback,
                   u.nombre AS empleado, a.nombre AS area_destino
            FROM documentos d
            INNER JOIN usuarios u ON d.id_usuario = u.id
            INNER JOIN areas a ON d.id_area_destino = a.id
            WHERE d.id_area_destino = ?
            ORDER BY d.fecha_subida DESC
        ");
        $stmt->execute([$area_seleccionada]);
        $documentos_area = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Supervisión</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/main.css"> <!-- reutilizamos el mismo CSS -->
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
      <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
      <a href="panel_supervision.php" class="active"><i class="fas fa-building"></i> Supervisión</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fas fa-building"></i> Panel de Supervisión</h1>
      <div class="top-actions">
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <!-- Contenido -->
    <main class="content">
      <h2>🏢 Áreas disponibles</h2>
      <div class="card-grid">
        <?php foreach ($areas as $a): ?>
          <div class="card-mini">
            <div class="card-header"><i class="fas fa-briefcase"></i></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($a['nombre']); ?></h3>
            </div>
            <div class="card-footer">
              <a class="btn-view" href="?area=<?= $a['id']; ?>">
                <i class="fas fa-folder-open"></i> Ver Documentos
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($area_seleccionada): ?>
        <hr>
        <h2>📂 Documentos del Área</h2>
        <?php if (count($documentos_area) > 0): ?>
          <div class="card-grid">
            <?php foreach ($documentos_area as $doc): ?>
              <div class="card-mini">
                <div class="card-header"><i class="fas fa-file-alt"></i></div>
                <div class="card-body">
                  <h3><?= htmlspecialchars($doc['nombre_original']); ?></h3>
                  <p><b>Empleado:</b> <?= htmlspecialchars($doc['empleado']); ?></p>
                  <p><b>Tipo:</b> <?= htmlspecialchars($doc['tipo']); ?></p>
                  <p><b>Fecha:</b> <?= $doc['fecha_subida']; ?></p>
                </div>
                <div class="card-footer">
                  <form action="actualizar_estado.php" method="post">
                    <input type="hidden" name="id_doc" value="<?= $doc['id']; ?>">
                    <select name="estado" class="estado-select">
                      <option value="pendiente"  <?= $doc['estado']=='pendiente' ? 'selected':''; ?>>Pendiente</option>
                      <option value="rechazado"  <?= $doc['estado']=='rechazado' ? 'selected':''; ?>>Rechazado</option>
                      <option value="observado"  <?= $doc['estado']=='observado' ? 'selected':''; ?>>Observado</option>
                      <option value="revisado"   <?= $doc['estado']=='revisado' ? 'selected':''; ?>>Revisado</option>
                    </select>
                    <input type="text" name="feedback" value="<?= htmlspecialchars($doc['feedback']); ?>" placeholder="Escribir feedback">
                    <button type="submit" class="btn-view"><i class="fas fa-save"></i> Guardar</button>
                    <a href="ver_documento.php?id=<?= $doc['id']; ?>" target="_blank" class="btn-view"><i class="fas fa-eye"></i> Ver</a>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p>No hay documentos en esta área.</p>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
