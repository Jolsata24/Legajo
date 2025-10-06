<?php
session_start();
require '../php/db.php';

// Solo RRHH, Admin o Secretaría pueden ver esto
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['rrhh', 'admin','secretaria'])) {
    header("Location: ../into/login.html");
    exit;
}

// Si se envía un documento a un área
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doc_id'], $_POST['area_id'])) {
    $doc_id  = $_POST['doc_id'];
    $area_id = $_POST['area_id'];

    $stmt = $pdo->prepare("
        UPDATE documentos 
        SET enviado_a_area_id = ?, estado = 'enviado_area' 
        WHERE id = ?
    ");
    $stmt->execute([$area_id, $doc_id]);
}

// Traer todos los documentos con info del empleado y áreas
try {
    $stmt = $pdo->query("
    SELECT d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida,
           u.nombre AS empleado_nombre, u.email,
           a1.nombre AS area_solicitada,
           a2.nombre AS area_destino
    FROM documentos d
    INNER JOIN usuarios u ON d.id_usuario = u.id
    LEFT JOIN areas a1 ON d.id_area_destino = a1.id
    LEFT JOIN areas a2 ON d.enviado_a_area_id = a2.id
    WHERE d.estado = 'pendiente'
    ORDER BY d.fecha_subida DESC
");

    $documentos = $stmt->fetchAll();

    // Todas las áreas disponibles para asignar
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre")->fetchAll();
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
    <a href="rrhh_dashboard.php">Volver al Dashboard</a> | 
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>
  <hr>

  <?php if (count($documentos) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
      <tr>
        <th>ID</th>
        <th>Empleado</th>
        <th>Email</th>
        <!--<th>Tipo</th>-->
        <th>Documento</th>
        <th>Fecha subida</th>
        <th>Área solicitada</th>
        <th>Área destino</th>
        <th>Descargar</th>
      </tr>
      
      <?php foreach ($documentos as $doc): ?>
      <tr>
        <td><?= $doc['id']; ?></td>
        <td><?= htmlspecialchars($doc['empleado_nombre']); ?></td>
        <td><?= htmlspecialchars($doc['email']); ?></td>
        <!--<td><//?= htmlspecialchars($doc['tipo']); ?></td>-->
        <td><?= htmlspecialchars($doc['nombre_original']); ?></td>
        <td><?= $doc['fecha_subida']; ?></td>

        <!-- Área solicitada -->
        <td><?= $doc['area_solicitada'] ? htmlspecialchars($doc['area_solicitada']) : "No indicado"; ?></td>

        <!-- Área destino (asignada por Secretaría) -->
        <td>
          <form method="POST" action="asignar_area.php" style="display:inline;">
    <input type="hidden" name="doc_id" value="<?= $doc['id']; ?>">
    <select name="area_id" required>
        <option value="">-- Seleccionar área --</option>
        <?php foreach ($areas as $area): ?>
            <option value="<?= $area['id']; ?>" <?= ($doc['area_destino'] == $area['nombre']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($area['nombre']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Enviar</button>
</form>

        </td>

        <!-- Descargar -->
        <td>
          <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']); ?>" download>📥 Descargar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No hay documentos subidos.</p>
  <?php endif; ?>
</body>
</html>
