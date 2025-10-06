<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../into/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Enviar Documento</title>
</head>
<body>
  <h1>Enviar Documento</h1>
  <form action="guardar_doc_area.php" method="post" enctype="multipart/form-data">
    <label>Seleccionar archivo:</label>
    <input type="file" name="documento" required><br><br>

    <label>Enviar a área:</label>
    <select name="id_area_destino" required>
      <option value="">-- Seleccione --</option>
      <option value="1">Fiscalización</option>
      <option value="2">Recursos Humanos</option>
      <option value="3">Asuntos Ambientales Mineros</option>
      <!-- más áreas -->
    </select><br><br>

    <button type="submit">Subir Documento</button>
  </form>
</body>
</html>
