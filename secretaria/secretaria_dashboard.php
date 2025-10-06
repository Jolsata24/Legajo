<?php
session_start();
require '../php/db.php';

// Solo secretaria puede entrar
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

// Traer todas las áreas
$stmt = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre");
$areas = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Secretaria</title>
</head>
<body>
  <h1>Bienvenido Secretaria</h1>
  <nav>
    <a href="mi_legajo.php">Mi legajo</a> | 
    <a href="empleados.php">Ver empleados</a> | 
    <a href="secretaria_documentos.php">Ver documentos</a> | 
    <a href="correspondencia.php">Correspondencia</a> | 
    <a href="reportes.php">Reportes</a> | 
    <a href="empleados_panel.php">Empleados</a> | 
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>
  <hr>

  <h2>Áreas disponibles</h2>
  <ul>
    <?php foreach ($areas as $area): ?>
      <li>
        <a href="secretaria_documentos_area.php?area_id=<?= $area['id']; ?>">
          📂 <?= htmlspecialchars($area['nombre']); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
