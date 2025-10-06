<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'rrhh') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$seccion_id = $_GET['id'] ?? null;

if (!$seccion_id) {
    die("Sección no especificada.");
}

try {
    // Traer datos de la sección
    $stmt = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt->execute([$seccion_id]);
    $seccion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seccion) {
        die("Sección no encontrada.");
    }

    // Traer documentos de esa sección
    $stmt = $pdo->prepare("
        SELECT id, nombre_original, nombre_guardado, tipo, fecha_subida
        FROM documentos
        WHERE id_usuario = ? AND id_seccion = ?
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$usuario_id, $seccion_id]);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sección - <?php echo htmlspecialchars($seccion['nombre']); ?></title>
</head>
<body>
  <h1>📂 Sección: <?php echo htmlspecialchars($seccion['nombre']); ?></h1>

  <nav>
    <a href="mi_legajo.php">⬅ Volver a Mi Legajo</a> | 
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>
  <hr>

  <h2>📑 Documentos en esta sección</h2>
  <?php if (!empty($documentos)): ?>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Nombre original</th>
        <th>Fecha</th>
        <th>Acción</th>
      </tr>
      <?php foreach ($documentos as $doc): ?>
        <tr>
          <td><?php echo $doc['id']; ?></td>
          <td><?php echo htmlspecialchars($doc['tipo']); ?></td>
          <td><?php echo htmlspecialchars($doc['nombre_original']); ?></td>
          <td><?php echo $doc['fecha_subida']; ?></td>
          <td><a href="../uploads/<?php echo htmlspecialchars($doc['nombre_guardado']); ?>" target="_blank">📄 Ver Documento</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No has subido documentos en esta sección aún.</p>
  <?php endif; ?>

  <hr>

  <h2>📤 Subir nuevo documento a esta sección</h2>
  <form action="subir_doc_personal.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="seccion_id" value="<?php echo $seccion_id; ?>">
    <label for="tipo">Tipo de documento:</label>
    <input type="text" name="tipo" id="tipo" required>
    <br><br>
    <input type="file" name="documento" required>
    <br><br>
    <button type="submit">Subir documento</button>
  </form>

</body>
</html>
