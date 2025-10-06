<?php
session_start();

// Verificar si está logueado y si es empleado
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.html");
    exit;
}

$nombre = $_SESSION['nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Legajo</title>
</head>
<body>
  <h1>Bienvenido <?php echo htmlspecialchars($nombre); ?></h1>
  <nav>
    <a href="mi_legajo.php">Mi legajo</a> | 
    <a href="enviar_documento.php">Enviar documento</a> | 
    <a href="enviar_doc_area.php">Enviar documento a areas</a> |
    <a href="../php/logout.php">Cerrar sesión</a>
  </nav>
  <hr>
  <h2>Mi Información</h2>
  <p>Aquí puedes consultar tus datos personales y tus documentos asociados.</p>
</body>
</html>
