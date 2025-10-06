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
  <title>Documento - <?php echo htmlspecialchars($doc['nombre_original']); ?></title>
</head>
<body>
  <h1>📄 Documento: <?php echo htmlspecialchars($doc['nombre_original']); ?></h1>

  <p><b>Empleado:</b> <?php echo htmlspecialchars($doc['empleado']); ?></p>
  <p><b>Área destino:</b> <?php echo htmlspecialchars($doc['area_destino'] ?? 'Personal'); ?></p>
  <p><b>Tipo:</b> <?php echo htmlspecialchars($doc['tipo']); ?></p>
  <p><b>Fecha subida:</b> <?php echo $doc['fecha_subida']; ?></p>
  <p><b>Estado:</b> <?php echo htmlspecialchars($doc['estado']); ?></p>
  <p><b>Revisado por:</b> <?php echo $doc['revisor'] ? htmlspecialchars($doc['revisor']) : 'No revisado aún'; ?></p>
  <p><b>Fecha revisión:</b> <?php echo $doc['fecha_revision'] ?? 'Pendiente'; ?></p>
  <p><b>Feedback:</b> <?php echo nl2br(htmlspecialchars($doc['feedback'] ?? '')); ?></p>

  <p><a href="../uploads/<?php echo htmlspecialchars($doc['nombre_guardado']); ?>" target="_blank">📥 Ver / Descargar</a></p>

  <hr>

  <?php if (in_array($rol, ['admin', 'rrhh', 'jefe_area'])): ?>
    <!-- Supervisores revisan -->
    <h2>✏️ Revisar documento</h2>
    <form method="post">
      <input type="hidden" name="accion" value="revision">
      <label for="estado">Estado:</label>
      <select name="estado" id="estado" required>
        <option value="pendiente"   <?php if ($doc['estado'] === 'pendiente') echo 'selected'; ?>>Pendiente</option>
        <option value="rechazado"   <?php if ($doc['estado'] === 'rechazado') echo 'selected'; ?>>Rechazado</option>
        <option value="observado"   <?php if ($doc['estado'] === 'observado') echo 'selected'; ?>>Observado</option>
        <option value="revisado"    <?php if ($doc['estado'] === 'revisado') echo 'selected'; ?>>Revisado</option>
      </select>
      <br><br>

      <label for="feedback">Feedback:</label><br>
      <textarea name="feedback" id="feedback" rows="5" cols="50"><?php echo htmlspecialchars($doc['feedback'] ?? ''); ?></textarea>
      <br><br>

      <button type="submit">✅ Guardar Revisión</button>
    </form>
  <?php endif; ?>

  <?php if ($rol === 'empleado' && $doc['id_usuario'] == $usuario_id && in_array($doc['estado'], ['rechazado', 'observado'])): ?>
    <!-- Empleado reemplaza -->
    <h2>📤 Reemplazar documento</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="accion" value="reemplazo">
      <input type="file" name="nuevo_doc" accept=".pdf,.doc,.docx,.jpg,.png" required>
      <br><br>
      <button type="submit">🔄 Subir Reemplazo</button>
    </form>
  <?php endif; ?>

  <p><a href="panel_supervision.php">⬅ Volver</a></p>
</body>
</html>
