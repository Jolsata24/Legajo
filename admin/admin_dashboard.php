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

// Documentos por estado (para el gráfico)
$estado_docs = $pdo->query("SELECT estado, COUNT(*) as total FROM documentos GROUP BY estado")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data   = [];
$colors = []; // Puedes definir colores aquí si quieres

foreach ($estado_docs as $row) {
    $labels[] = ucfirst($row['estado']);
    $data[]   = (int)$row['total'];
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
  
  <?php 
    // --- ¡AQUÍ ESTÁ LA CORRECCIÓN! ---
    // Incluimos el menú lateral centralizado que ya tiene el enlace a "Editar Perfil"
    require_once '../includes/sidebar_admin.php'; 
  ?>

  <div class="main">
    <header class="topbar">
      <h1><i class="fa-solid fa-book"></i> Sistema de Legajo</h1>
      <div class="top-actions">
        <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

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

      <div class="card chart-card">
        <h3><i class="fas fa-chart-bar"></i> Documentos por Estado</h3>
        <canvas id="docEstadoChart"></canvas>
      </div>

    </section>
  </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('docEstadoChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Cantidad de Documentos',
        data: <?= json_encode($data) ?>,
        backgroundColor: ['#facc15', '#22c55e', '#ef4444', '#94a3b8'], // Colores para pendiente, revisado, etc.
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
  });
</script>
</html>