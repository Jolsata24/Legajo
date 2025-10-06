<?php
session_start();
require '../php/db.php';

// Verificar sesión
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

    // Definir ruta de la foto
    $foto_usuario = "../img/user2.png"; // valor por defecto
    if (!empty($usuario['foto']) && file_exists("../uploads/usuarios/" . $usuario['foto'])) {
        $foto_usuario = "../uploads/usuarios/" . htmlspecialchars($usuario['foto']);
    }

    // Documentos del usuario
    $stmt = $pdo->prepare("
        SELECT id, nombre_original, nombre_guardado, tipo, fecha_subida
        FROM documentos
        WHERE id_usuario = ?
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$usuario_id]);
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Legajo - Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/mi_legajo.css"> <!-- 👈 usamos el mismo CSS del dashboard -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
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
      <a href="enviar_documento.php"><i class="fas fa-upload"></i> Subir Documento</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>

  </aside>

  <!-- Main content -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Mi Legajo</h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
</header>


    <!-- Content -->
    <section class="content">
        <div class="card">
            <h3><i class="fas fa-user"></i> Mis Datos</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']); ?></p>
            <p><strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>
        </div>



      <div class="cards">
        <div class="card full-width">
          <h3><i class="fas fa-folder"></i> Mis Documentos</h3>
          <?php if (count($documentos) > 0): ?>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Nombre Original</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($documentos as $doc): ?>
                  <tr>
                    <td><?= $doc['id']; ?></td>
                    <td><?= htmlspecialchars($doc['tipo']); ?></td>
                    <td><?= htmlspecialchars($doc['nombre_original']); ?></td>
                    <td><?= $doc['fecha_subida']; ?></td>
                    <td>
                        <a class="btn-download" href="../php/ver_documento.php?id=<?= $doc['id']; ?>">
                        <i class="fas fa-download"></i> Descargar
                        </a>
                    </td>

                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="empty">No has subido documentos aún.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
