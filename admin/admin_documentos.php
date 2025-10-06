<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['rrhh', 'admin'])) {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

// Obtenemos datos del usuario logueado
$stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
                       FROM usuarios u
                       LEFT JOIN areas a ON u.id_area = a.id
                       WHERE u.id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Ruta de la foto
if (!empty($usuario['foto']) && file_exists("../uploads/usuarios/" . $usuario['foto'])) {
    $foto_usuario = "../uploads/usuarios/" . $usuario['foto'];
} else {
    $foto_usuario = "../img/user2.png"; // Fallback
}

$nombre_usuario = $usuario['nombre'];
$rol_usuario    = $usuario['rol'];
$area_usuario   = $usuario['area'] ?? "Sin área";

// Traer documentos
try {
    $stmt = $pdo->query("
        SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida,
               u.nombre AS empleado_nombre, u.email
        FROM documentos d
        INNER JOIN usuarios u ON d.id_usuario = u.id
        ORDER BY d.fecha_subida DESC
    ");
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Documentos de Empleados</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/documentos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <h2>Bienvenido</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($foto_usuario) ?>" alt="Foto del Usuario">
      <h4><?= htmlspecialchars($nombre_usuario) ?></h4>
      <p><?= htmlspecialchars(ucfirst($rol_usuario)) ?> - <?= htmlspecialchars($area_usuario) ?></p>
    </div>
    <nav class="menu">
      <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>
      <a href="admin_documentos.php" class="active"><i class="fas fa-file-alt"></i> Ver Documentos</a>
      <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
      <a href="../panel_de_jefe/panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
  </aside>
  <!-- Main content -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fas fa-file-alt"></i>  Documentos de empleados</h1>
      <div class="top-actions">
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>
  <!-- Contenido -->
  <main class="content">

    <?php if (count($documentos) > 0): ?>
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Empleado</th>
            <th>Email</th>
            <th>Nombre Original</th>
            <th>Fecha</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $doc): ?>
          <tr>
            <td><?= $doc['id'] ?></td>
            <td><?= htmlspecialchars($doc['empleado_nombre']) ?></td>
            <td><?= htmlspecialchars($doc['email']) ?></td>
            <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
            <td><?= $doc['fecha_subida'] ?></td>
            <td>
              <a class="btn-download" href="../php/ver_documento.php?id=<?= $doc['id'] ?>">
                <i class="fas fa-download"></i> Descargar
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay documentos subidos.</p>
    <?php endif; ?>
    </main>
</body>
</html>
