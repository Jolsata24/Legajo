<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>DREMH - Sistema de Legajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <!-- Barra superior -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">📂 Legajo DREMH</a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#">Notificaciones 🔔</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Perfil 👤</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Salir 🚪</a></li>
      </ul>
    </div>
  </nav>

  <!-- Contenido dinámico -->
  <div class="container mt-4">
    {{CONTENT}}
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
