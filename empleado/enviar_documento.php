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
  <title>Enviar Documento</title>
</head>
<body>
  <h1>Enviar Documento</h1>
  <p>Bienvenido <?php echo htmlspecialchars($nombre); ?>, aquí puedes subir tus documentos.</p>

  <form action="../php/upload.php" method="POST" enctype="multipart/form-data">
    <label for="documento">Selecciona un documento:</label><br>
    <input type="file" name="documento" id="documento" required><br><br>

    <label for="tipo">Tipo de documento:</label><br>
    <select name="tipo" id="tipo" required>
      <option value="cv">Currículum Vitae</option>
      <option value="dni">DNI</option>
      <option value="certificado">Certificado</option>
      <option value="otro">Otro</option>
    </select><br><br>
    <label for="area">Área destino solicitada:</label><br>
<select name="area" id="area" required>
  <option value="">-- Selecciona área --</option>
  <?php
  // Traer todas las áreas
  $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre")->fetchAll();
  foreach ($areas as $a) {
      echo "<option value='{$a['id']}'>" . htmlspecialchars($a['nombre']) . "</option>";
  }
  ?>
</select><br><br>
    <button type="submit">Subir Documento</button>
  </form>

  <br>
  <a href="empleado_dashboard.php">⬅ Volver al Dashboard</a>
</body>
</html>
