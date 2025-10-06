<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$rol        = $_SESSION['rol'];

$doc_id = $_GET['id'] ?? null;
if (!$doc_id) {
    die("Documento no especificado.");
}

try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.nombre AS empleado, a.nombre AS area_destino, r.nombre AS revisor
        FROM documentos d
        INNER JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN areas a ON d.id_area_destino = a.id
        LEFT JOIN usuarios r ON d.revisado_por = r.id
        WHERE d.id = ?
    ");
    $stmt->execute([$doc_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        die("Documento no encontrado.");
    }

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Guardar revisión (solo admin/rrhh/jefe_area)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'revision' && in_array($rol, ['admin', 'rrhh', 'jefe_area'])) {
    $estado   = $_POST['estado'] ?? 'pendiente';
    $feedback = $_POST['feedback'] ?? '';

    $stmt = $pdo->prepare("
        UPDATE documentos 
        SET estado = ?, feedback = ?, revisado_por = ?, fecha_revision = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$estado, $feedback, $usuario_id, $doc_id]);

    header("Location: ver_documento.php?id=" . $doc_id);
    exit;
}

// Reemplazar documento (solo empleado dueño)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'reemplazo' && $rol === 'empleado' && $doc['id_usuario'] == $usuario_id) {
    if (isset($_FILES['nuevo_doc']) && $_FILES['nuevo_doc']['error'] === UPLOAD_ERR_OK) {
        $nombre_original = $_FILES['nuevo_doc']['name'];
        $tmp_name = $_FILES['nuevo_doc']['tmp_name'];

        // generar nombre único
        $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
        $nombre_guardado = uniqid("doc_") . "." . $ext;
        $ruta_destino = "../uploads/" . $nombre_guardado;

        if (move_uploaded_file($tmp_name, $ruta_destino)) {
            // actualizar documento
            $stmt = $pdo->prepare("
                UPDATE documentos 
                SET nombre_original = ?, nombre_guardado = ?, estado = 'pendiente', feedback = NULL, revisado_por = NULL, fecha_revision = NULL
                WHERE id = ?
            ");
            $stmt->execute([$nombre_original, $nombre_guardado, $doc_id]);

            header("Location: ver_documento.php?id=" . $doc_id);
            exit;
        } else {
            echo "❌ Error al subir el nuevo archivo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Documento - <?= htmlspecialchars($doc['nombre_original']); ?></title>
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
      <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
      <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
      <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fas fa-file-alt"></i> Documento</h1>
      <div class="top-actions">
        
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>
    <a href="panel_jefes.php" class="btn-back">
          <i class="fas fa-arrow-left"></i> Atrás
        </a>
    <!-- Content -->
    <main class="content">
      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-file-alt"></i> <?= htmlspecialchars($doc['nombre_original']); ?></h2>
        </div>
        <div class="card-body">
  <p><span class="card-label">Empleado:</span> <span class="card-value"><?= htmlspecialchars($doc['empleado']); ?></span></p>
  <p><span class="card-label">Área destino:</span> <span class="card-value"><?= htmlspecialchars($doc['area_destino'] ?? 'Personal'); ?></span></p>
  <p><span class="card-label">Tipo:</span> <span class="card-value"><?= htmlspecialchars($doc['tipo']); ?></span></p>
  <p><span class="card-label">Fecha subida:</span> <span class="card-value"><?= $doc['fecha_subida']; ?></span></p>
  <p><span class="card-label">Estado:</span> <span class="card-value"><?= htmlspecialchars($doc['estado']); ?></span></p>
  <p><span class="card-label">Revisado por:</span> <span class="card-value"><?= $doc['revisor'] ? htmlspecialchars($doc['revisor']) : 'No revisado aún'; ?></span></p>
  <p><span class="card-label">Fecha revisión:</span> <span class="card-value"><?= $doc['fecha_revision'] ?? 'Pendiente'; ?></span></p>
  <p><span class="card-label">Feedback:</span><br>
     <span class="card-value"><?= nl2br(htmlspecialchars($doc['feedback'] ?? '')); ?></span>
  </p>
  
  <p>
    <a href="uploads/<?= $row['archivo']; ?>" target="_blank" class="btn-download">
       Descargar Documento
    </a>
  </p>
</div>

      </div>

      <!-- Sección revisión (Admin/RRHH/Jefe) -->
      <?php if (in_array($rol, ['admin'])): ?>
        <div class="card">
          <div class="card-header"><h3> Revisar Documento</h3></div>
          <div class="card-body">
            <form method="post" class="form-dashboard">
              <input type="hidden" name="accion" value="revision">

              <label for="estado">Estado:</label>
              <select name="estado" id="estado" required>
                <option value="pendiente" <?= $doc['estado']==='pendiente'?'selected':''; ?>>Pendiente</option>
                <option value="rechazado" <?= $doc['estado']==='rechazado'?'selected':''; ?>>Rechazado</option>
                <option value="observado" <?= $doc['estado']==='observado'?'selected':''; ?>>Observado</option>
                <option value="revisado" <?= $doc['estado']==='revisado'?'selected':''; ?>>Revisado</option>
              </select>

              <label for="feedback">Feedback:</label>
              <textarea name="feedback" id="feedback" rows="5"><?= htmlspecialchars($doc['feedback'] ?? ''); ?></textarea>

              <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Revisión</button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Sección reemplazo (Empleado dueño) -->
      <?php if ($rol === 'empleado' && $doc['id_usuario'] == $usuario_id && in_array($doc['estado'], ['rechazado','observado'])): ?>
        <div class="card">
          <div class="card-header"><h3> Reemplazar Documento</h3></div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="form-dashboard">
              <input type="hidden" name="accion" value="reemplazo">
              <input type="file" name="nuevo_doc" accept=".pdf,.doc,.docx,.jpg,.png" required>
              <button type="submit" class="btn-warning"><i class="fas fa-upload"></i> Subir Reemplazo</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
