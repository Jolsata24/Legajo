<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_area') {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // Traer datos del usuario con área
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Traer secciones del legajo
    $stmt = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY id ASC");
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Traer documentos enviados a áreas
    $stmt = $pdo->prepare("
        SELECT d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida, a.nombre AS area_destino
        FROM documentos d
        INNER JOIN areas a ON d.id_area_destino = a.id
        WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL
        ORDER BY d.fecha_subida DESC
    ");
    $stmt->execute([$usuario_id]);
    $documentos_areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Legajo</title>
</head>
<body>
  <h1>📌 Mi Legajo</h1>

  <nav>
    <a href="empleado_dashboard.php">Inicio</a> |
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>

  <hr>

  <h2>👤 Mis Datos</h2>
  <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
  <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
  <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol']); ?></p>
  <p><strong>Área:</strong> <?php echo htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>

  <hr>

  <h2>📂 Secciones del Legajo</h2>
  <?php if (!empty($secciones)): ?>
    <ul>
      <?php foreach ($secciones as $sec): ?>
        <li>
          <a href="seccion_legajo.php?id=<?php echo $sec['id']; ?>">
            <?php echo htmlspecialchars($sec['nombre']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No hay secciones definidas.</p>
  <?php endif; ?>

  <hr>

  <h2>📤 Documentos Enviados a Áreas</h2>
  <?php if (!empty($documentos_areas)): ?>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr>
        <th>ID</th>
        <th>Área destino</th>
        <th>Tipo</th>
        <th>Nombre original</th>
        <th>Fecha</th>
        <th>Acción</th>
      </tr>
      <?php foreach ($documentos_areas as $doc): ?>
        <tr>
          <td><?php echo $doc['id']; ?></td>
          <td><?php echo htmlspecialchars($doc['area_destino']); ?></td>
          <td><?php echo htmlspecialchars($doc['tipo']); ?></td>
          <td><?php echo htmlspecialchars($doc['nombre_original']); ?></td>
          <td><?php echo $doc['fecha_subida']; ?></td>
          <td><a href="../php/ver_documento.php?id=<?php echo $doc['id']; ?>">Ver / Descargar</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No has enviado documentos a áreas.</p>
  <?php endif; ?>

</body>
</html>
