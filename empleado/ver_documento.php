<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$rol        = $_SESSION['rol'];

// Seguridad: solo empleados pueden entrar aquí
if ($rol !== 'empleado') {
    die("❌ Acceso denegado.");
}

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
        WHERE d.id = ? AND d.id_usuario = ?
    ");
    $stmt->execute([$doc_id, $usuario_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        die("Documento no encontrado o no tienes permisos.");
    }

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Reemplazo de documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'reemplazo') {
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
                WHERE id = ? AND id_usuario = ?
            ");
            $stmt->execute([$nombre_original, $nombre_guardado, $doc_id, $usuario_id]);

            header("Location: ver_documento_empleado.php?id=" . $doc_id);
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
  <title>Mi Documento - <?php echo htmlspecialchars($doc['nombre_original']); ?></title>
</head>
<body>
  <h1>📄 Documento: <?php echo htmlspecialchars($doc['nombre_original']); ?></h1>

  <p><b>Área destino:</b> <?php echo htmlspecialchars($doc['area_destino'] ?? 'Personal'); ?></p>
  <p><b>Tipo:</b> <?php echo htmlspecialchars($doc['tipo']); ?></p>
  <p><b>Fecha subida:</b> <?php echo $doc['fecha_subida']; ?></p>
  <p><b>Estado:</b> <?php echo htmlspecialchars($doc['estado']); ?></p>
  <p><b>Revisado por:</b> <?php echo $doc['revisor'] ? htmlspecialchars($doc['revisor']) : 'No revisado aún'; ?></p>
  <p><b>Fecha revisión:</b> <?php echo $doc['fecha_revision'] ?? 'Pendiente'; ?></p>
  <p><b>Feedback:</b> <?php echo nl2br(htmlspecialchars($doc['feedback'] ?? '')); ?></p>

  <p><a href="../uploads/<?php echo htmlspecialchars($doc['nombre_guardado']); ?>" target="_blank">📥 Ver / Descargar</a></p>

  <hr>

  <?php if (in_array($doc['estado'], ['rechazado', 'observado'])): ?>
    <h2>📤 Reemplazar documento</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="accion" value="reemplazo">
      <input type="file" name="nuevo_doc" accept=".pdf,.doc,.docx,.jpg,.png" required>
      <br><br>
      <button type="submit">🔄 Subir Reemplazo</button>
    </form>
  <?php else: ?>
    <p>✅ No es necesario reemplazar este documento.</p>
  <?php endif; ?>

  <p><a href="mi_legajo.php">⬅ Volver a Mi Legajo</a></p>
</body>
</html>
