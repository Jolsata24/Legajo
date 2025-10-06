<?php
session_start();
require 'db.php';

if ($_SESSION['rol'] === 'rrhh') {
    // RRHH ve todos
    $stmt = $pdo->query("SELECT d.id, u.nombre, d.nombre_original, d.fecha_subida 
                         FROM documentos d 
                         JOIN usuarios u ON d.id_usuario = u.id 
                         ORDER BY d.fecha_subida DESC");
} else {
    // Usuario solo ve los suyos
    $stmt = $pdo->prepare("SELECT id, nombre_original, fecha_subida 
                           FROM documentos 
                           WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id']]);
}

$docs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Documentos</title>
</head>
<body>
  <h2>Documentos</h2>
  <ul>
    <?php foreach ($docs as $doc): ?>
      <li>
        <?= htmlspecialchars($doc['nombre_original']) ?> - 
        <?= $doc['fecha_subida'] ?> - 
        <a href="descargar.php?id=<?= $doc['id'] ?>">Descargar</a>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
