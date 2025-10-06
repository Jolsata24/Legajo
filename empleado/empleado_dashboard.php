<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = (int)$_SESSION['id'];
$notificaciones = [];
$num_no_leidas = 0;
$metricas = ['en_proceso' => 0, 'aprobados' => 0, 'con_observaciones' => 0];
$actividad_reciente = [];

try {
    // ... (Tu código PHP para obtener notificaciones, métricas y actividad reciente se mantiene igual) ...
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace, fecha_creacion FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 5");
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));
    $stmt_metricas = $pdo->prepare("SELECT estado, COUNT(*) as total FROM documentos WHERE id_usuario = ? AND id_area_destino IS NOT NULL GROUP BY estado");
    $stmt_metricas->execute([$id_usuario]);
    foreach ($stmt_metricas->fetchAll() as $row) {
        if (in_array($row['estado'], ['pendiente', 'observado'])) { $metricas['en_proceso'] += $row['total']; }
        if ($row['estado'] === 'revisado') { $metricas['aprobados'] = $row['total']; }
        if ($row['estado'] === 'rechazado') { $metricas['con_observaciones'] = $row['total']; }
    }
    $stmt_historial = $pdo->prepare("SELECT h.descripcion, h.fecha, d.nombre_original FROM documentos_historial h JOIN documentos d ON h.id_documento = d.id WHERE d.id_usuario = ? ORDER BY h.fecha DESC LIMIT 3");
    $stmt_historial->execute([$id_usuario]);
    $actividad_reciente = $stmt_historial->fetchAll();

} catch (PDOException $e) { /*...*/ }

$page_title = "Dashboard - " . $_SESSION['nombre'];
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>
<style>
    /* ... (Tus estilos necesarios) ... */
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-home"></i> Inicio</h1>
      <div class="top-actions">
          </div>
    </header>

    <main class="content">
        <div class="cards">
            <div class="card"><h3>En Proceso</h3><p><?= $metricas['en_proceso'] ?></p></div>
            <div class="card"><h3>Aprobados</h3><p style="color: #198754;"><?= $metricas['aprobados'] ?></p></div>
            <div class="card"><h3>Con Observaciones</h3><p style="color: #dc3545;"><?= $metricas['con_observaciones'] ?></p></div>
        </div>

        <div class="card">
            <h3>Acciones Rápidas</h3>
            <a href="enviar_documento.php" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar Nuevo Documento</a>
            <a href="documentos_enviados.php" class="btn btn-info"><i class="fas fa-history"></i> Ver Todos Mis Envíos</a>
        </div>

        <div class="card">
          <h3><i class="fas fa-history"></i> Actividad Reciente en mis Documentos</h3>
          </div>
    </main>
</div>

<script>
    // ... tu script de notificaciones ...
</script>
<?php require_once '../includes/footer.php'; ?>