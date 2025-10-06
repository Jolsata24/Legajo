<?php
session_start();
require '../php/db.php';

// Solo RRHH o Admin pueden ver esto
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['rrhh', 'admin', 'jefe_area'])) {
    header("Location: login.html");
    exit;
}

try {
    // Traer todos los documentos con info del empleado
    $stmt = $pdo->query("
        SELECT d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida,
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
</head>
<body>
  <h1>📂 Documentos de Empleados</h1>
  <nav>
    <a href="area_dashboard.php">Volver al Dashboard</a> | 
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>
  <hr>

  <?php if (count($documentos) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
      <tr>
        <th>ID</th>
        <th>Empleado</th>
        <th>Email</th>
        <th>Tipo</th>
        <th>Nombre Original</th>
        <th>Fecha</th>
        <th>Acción</th>
      </tr>
      <?php foreach ($documentos as $doc): ?>
      <tr>
        <td><?php echo $doc['id']; ?></td>
        <td><?php echo htmlspecialchars($doc['empleado_nombre']); ?></td>
        <td><?php echo htmlspecialchars($doc['email']); ?></td>
        <td><?php echo htmlspecialchars($doc['tipo']); ?></td>
        <td><?php echo htmlspecialchars($doc['nombre_original']); ?></td>
        <td><?php echo $doc['fecha_subida']; ?></td>
        <td>
          <a href="../php/ver_documento.php?id=<?php echo $doc['id']; ?>">📥 Descargar</a>

        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No hay documentos subidos.</p>
  <?php endif; ?>
</body>
</html>
