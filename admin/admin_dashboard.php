<?php
session_start();
require_once "../php/db.php";

// Verificamos si hay sesión iniciada
if (!isset($_SESSION['id'])) {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

// Obtenemos datos del usuario logueado
$stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
                       FROM usuarios u
                       LEFT JOIN areas a ON u.id_area = a.id
                       WHERE u.id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Métricas rápidas
$total_legajos     = $pdo->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
$pendientes        = $pdo->query("SELECT COUNT(*) FROM documentos WHERE estado = 'pendiente'")->fetchColumn();
$revisados         = $pdo->query("SELECT COUNT(*) FROM documentos WHERE estado = 'revisado'")->fetchColumn();
$usuarios_activos  = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();

// Ruta de la foto
if (!empty($usuario['foto']) && file_exists("../uploads/usuarios/" . $usuario['foto'])) {
    $foto_usuario = "../uploads/usuarios/" . $usuario['foto'];
} else {
    $foto_usuario = "../img/user2.png";
}

$nombre_usuario = $usuario['nombre'];
$rol_usuario    = $usuario['rol'];
$area_usuario   = $usuario['area'] ?? "Sin área";


// Documentos por estado
$estado_docs = $pdo->query("
    SELECT estado, COUNT(*) as total 
    FROM documentos 
    GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data   = [];
$colors = [];

foreach ($estado_docs as $row) {
    $labels[] = ucfirst($row['estado']);
    $data[]   = (int)$row['total'];

    // Colores personalizados según estado
    switch ($row['estado']) {
        case 'pendiente': $colors[] = '#facc15'; break; // Amarillo
        case 'revisado':  $colors[] = '#22c55e'; break; // Verde
        case 'rechazado': $colors[] = '#ef4444'; break; // Rojo
        default:          $colors[] = '#94a3b8'; // Gris
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Sistema de Legajo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <h2>Bienvenido</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($foto_usuario) ?>" alt="Foto del Usuario">
      <h4><?= htmlspecialchars($nombre_usuario) ?></h4>
      <p><?= htmlspecialchars(ucfirst($rol_usuario)) ?> - <?= htmlspecialchars($area_usuario) ?></p>
    </div>
    <nav class="menu">
  <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>
  <a href="admin_documentos.php"><i class="fas fa-file-alt"></i> Ver Documentos</a>
  <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
  <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
  <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</nav>

  </aside>

  <!-- Main content -->
  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <h1><i class="fa-solid fa-book"></i>  Sistema de Legajo</h1>
      <div class="top-actions">
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <!-- Content -->
    <section class="content">
  <div class="cards">
    <div class="card">
      <h3>Total de Legajos</h3>
      <p><?= $total_legajos ?></p>
    </div>
    <div class="card">
      <h3>Documentos Pendientes</h3>
      <p><?= $pendientes ?></p>
    </div>
    <div class="card">
      <h3>Documentos Revisados</h3>
      <p><?= $revisados ?></p>
    </div>
    <div class="card">
      <h3>Usuarios Activos</h3>
      <p><?= $usuarios_activos ?></p>
    </div>
  </div>

  <!-- Nuevo gráfico -->
  <div class="card chart-card">
  <h3><i class="fas fa-file-alt"></i> Documentos por Estado</h3>
  <canvas id="docEstadoChart"></canvas>
</div>

</section>
  </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('docEstadoChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',   // 👈 cambia aquí a barras
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Empleados por área',
        data: <?= json_encode($data) ?>,
        backgroundColor: <?= json_encode($colors) ?>,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, // 👈 esto evita que se estire infinito
      plugins: {
        legend: {
          display: false // 👈 ocultamos leyenda si solo es una serie
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
</script>
</html>
