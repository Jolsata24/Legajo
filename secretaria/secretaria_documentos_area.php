<?php
session_start();
require '../php/db.php';

// Roles permitidos
$roles_permitidos = ['admin','rrhh','jefe_area','secretaria'];
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: ../into/login.html");
    exit;
}

// Mensaje de sesión (si existe)
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// Validar area_id en GET
if (!isset($_GET['area_id'])) {
    die("⚠️ No se especificó el área.");
}
$area_id = (int) $_GET['area_id'];

// Si el usuario es jefe_area, comprobar que su área coincide (intentar desde sesión o DB)
if ($_SESSION['rol'] === 'jefe_area') {
    // Intentamos usar id_area desde sesión si existe
    $mi_area = $_SESSION['id_area'] ?? null;
    if (!$mi_area) {
        // consultamos en BD
        $stmtA = $pdo->prepare("SELECT id_area FROM usuarios WHERE id = ?");
        $stmtA->execute([$_SESSION['id']]);
        $rowA = $stmtA->fetch();
        $mi_area = $rowA['id_area'] ?? null;
    }
    if ((int)$mi_area !== $area_id) {
        die("Acceso denegado: no puedes ver documentos de otra área.");
    }
}

try {
    // Nombre del área
    $stmt = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmt->execute([$area_id]);
    $area = $stmt->fetch();
    if (!$area) die("Área no encontrada.");

    // Traer documentos asignados a esa área
    $stmt = $pdo->prepare("
        SELECT d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida, d.estado, d.feedback,
               u.nombre AS empleado, u.email, d.revisado_por, d.fecha_revision
        FROM documentos d
        INNER JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ?
        ORDER BY d.fecha_subida DESC
    ");
    $stmt->execute([$area_id]);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Documentos del Área <?= htmlspecialchars($area['nombre']) ?></title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    textarea { width: 100%; min-height:60px; }
    .msg { padding:10px; margin-bottom:10px; border-radius:6px; }
    .msg-ok { background:#e6ffed; color:#165d33; }
    .msg-err { background:#ffe6e6; color:#8a1c1c; }
    .btn { padding:6px 10px; border-radius:6px; cursor:pointer; }
  </style>
</head>
<body>
  <h1>📂 Documentos del área: <?= htmlspecialchars($area['nombre']) ?></h1>
  <p><a href="secretaria_dashboard.php">⬅ Volver al Dashboard</a></p>

  <?php if($mensaje): ?>
    <div class="msg <?= strpos($mensaje,'✅')===0 ? 'msg-ok' : 'msg-err' ?>">
      <?= htmlspecialchars($mensaje) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($documentos)): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Empleado</th>
          <th>Documento</th>
          <th>Fecha subida</th>
          <th>Estado</th>
          <th>Feedback</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($documentos as $doc): ?>
        <tr>
          <td><?= $doc['id'] ?></td>
          <td><?= htmlspecialchars($doc['empleado']) ?><br><small><?= htmlspecialchars($doc['email']) ?></small></td>
          <td>
            <?= htmlspecialchars($doc['nombre_original']) ?><br>
            <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank">📥 Descargar</a>
          </td>
          <td><?= $doc['fecha_subida'] ?></td>

          <td>
            <!-- Formulario pequeño para cambiar estado + feedback -->
            <form action="actualiza estado.php" method="post" style="margin:0;">
              <input type="hidden" name="id_doc" value="<?= $doc['id'] ?>">
              <input type="hidden" name="area_id" value="<?= $area_id ?>">
              <select name="estado" required>
                <option value="pendiente" <?= $doc['estado']=='pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="observado" <?= $doc['estado']=='observado' ? 'selected' : '' ?>>Observado</option>
                <option value="rechazado" <?= $doc['estado']=='rechazado' ? 'selected' : '' ?>>Rechazado</option>
                <option value="revisado" <?= $doc['estado']=='revisado' ? 'selected' : '' ?>>Revisado</option>
              </select>
          </td>

          <td>
              <textarea name="feedback" placeholder="Escribir feedback..."><?= htmlspecialchars($doc['feedback']) ?></textarea>
              <?php if (!empty($doc['revisado_por'])): ?>
                <div style="font-size:12px;color:#666;margin-top:6px;">
                  Revisado por ID: <?= htmlspecialchars($doc['revisado_por']) ?> el <?= htmlspecialchars($doc['fecha_revision'] ?? '') ?>
                </div>
              <?php endif; ?>
          </td>

          <td style="vertical-align:top;">
              <button class="btn" type="submit">💾 Guardar</button>
              </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay documentos asignados a esta área.</p>
  <?php endif; ?>
</body>
</html>
