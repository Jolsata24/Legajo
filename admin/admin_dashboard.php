<?php
session_start();
require_once "../php/db.php";

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // --- CONSULTAS PARA EL DASHBOARD ---

    // 1. Notificaciones (ya lo teníamos)
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 10");
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));

    // 2. Métricas para las tarjetas
    $total_docs     = $pdo->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
    $total_pendientes = $pdo->query("SELECT COUNT(*) FROM documentos WHERE estado = 'pendiente'")->fetchColumn();
    $total_usuarios   = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $total_areas      = $pdo->query("SELECT COUNT(*) FROM areas")->fetchColumn();

    // 3. Datos para el gráfico de barras (Documentos por Estado)
    $estado_docs = $pdo->query("SELECT estado, COUNT(*) as total FROM documentos GROUP BY estado")->fetchAll(PDO::FETCH_ASSOC);
    $labels_grafico = [];
    $data_grafico   = [];
    foreach ($estado_docs as $row) {
        $labels_grafico[] = ucfirst($row['estado']);
        $data_grafico[]   = (int)$row['total'];
    }

    // 4. Actividad Reciente (usando la tabla de historial)
    $actividad_reciente = $pdo->query(
        "SELECT h.descripcion, h.fecha, u.nombre as nombre_usuario
         FROM documentos_historial h
         JOIN usuarios u ON h.id_usuario_accion = u.id
         ORDER BY h.fecha DESC
         LIMIT 5" // Traemos las últimas 5 actividades
    )->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar los datos del dashboard: " . $e->getMessage());
}

$page_title = "Dashboard - Administrador";
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>
<style>
    .activity-list { list-style: none; padding: 0; }
    .activity-list li { border-bottom: 1px solid #eee; padding: 15px 0; }
    .activity-list li:last-child { border-bottom: none; }
    .activity-list p { margin: 0; font-size: 14px; color: #333; }
    .activity-list small { color: #777; }
    .btn-quick-action { display: inline-block; background-color: #007bff; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 15px; margin-top: 10px; }
    .btn-quick-action:hover { background-color: #0056b3; }
    /* ... Tus estilos de notificaciones ... */
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-tachometer-alt"></i> Dashboard de Administración</h1>
      <div class="top-actions">
          <div class="notifications">
            </div>
          <span style="margin-left: 20px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <main class="content">
      <div class="cards">
        <div class="card"><h3>Total Documentos</h3><p><?= $total_docs ?></p></div>
        <div class="card"><h3>Pendientes Globales</h3><p><?= $total_pendientes ?></p></div>
        <div class="card"><h3>Total Usuarios</h3><p><?= $total_usuarios ?></p></div>
        <div class="card"><h3>Total Áreas</h3><p><?= $total_areas ?></p></div>
      </div>

      <div class="card">
        <h3><i class="fas fa-rocket"></i> Acciones Rápidas</h3>
        <a href="crear_usuario.php" class="btn-quick-action"><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</a>
        <a href="#" class="btn-quick-action" style="background-color: #6c757d;"><i class="fas fa-sitemap"></i> Gestionar Áreas</a>
      </div>

      <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="card" style="flex: 1; min-width: 300px;">
          <h3><i class="fas fa-chart-bar"></i> Documentos por Estado</h3>
          <div style="height: 300px;">
            <canvas id="docEstadoChart"></canvas>
          </div>
        </div>

        <div class="card" style="flex: 1; min-width: 300px;">
          <h3><i class="fas fa-history"></i> Actividad Reciente del Sistema</h3>
          <ul class="activity-list">
            <?php if (empty($actividad_reciente)): ?>
              <li>No hay actividad reciente.</li>
            <?php else: ?>
              <?php foreach ($actividad_reciente as $actividad): ?>
                <li>
                  <p><?= htmlspecialchars($actividad['descripcion']) ?></p>
                  <small>Por: <?= htmlspecialchars($actividad['nombre_usuario']) ?> - <?= $actividad['fecha'] ?></small>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // ... tu script de notificaciones ...

  // Gráfico
  const ctx = document.getElementById('docEstadoChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels_grafico) ?>,
      datasets: [{
        label: 'Cantidad de Documentos',
        data: <?= json_encode($data_grafico) ?>,
        backgroundColor: ['#facc15', '#ffedd5', '#dcfce7', '#fee2e2'],
        borderColor: ['#ca8a04', '#c2410c', '#166534', '#991b1b'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>

<?php require_once '../includes/footer.php'; ?>