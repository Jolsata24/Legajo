<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

// --- ¡NUEVO! CÓDIGO PARA OBTENER LAS ÁREAS Y NOTIFICACIONES ---
$id_usuario = $_SESSION['id'];
$notificaciones = [];
$num_no_leidas = 0;
$areas = [];

try {
    // 1. Obtener notificaciones (código que ya tenías)
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 10");
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));

    // 2. Obtener todas las áreas que tienen al menos un documento
    $areas = $pdo->query(
        "SELECT DISTINCT a.id, a.nombre 
         FROM areas a
         JOIN documentos d ON a.id = d.id_area_destino
         ORDER BY a.nombre ASC"
    )->fetchAll();

} catch (PDOException $e) {
    // Manejar error
}
// --- FIN DEL CÓDIGO NUEVO ---

$page_title = "Dashboard - " . $_SESSION['nombre'];
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>
<style>
    /* ... tus estilos de notificaciones ... */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .area-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: #333;
    }
    .area-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.1);
    }
    .area-card .icon { font-size: 40px; color: #007bff; margin-bottom: 15px; }
    .area-card h3 { margin: 0; font-size: 18px; }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-home"></i> Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
      <div class="top-actions">
        <div class="notifications">
            </div>
        <span style="margin-left: 20px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
      </div>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-sitemap"></i> Explorador de Documentos por Área</h3>
            <p>Selecciona un área para ver los documentos que han sido asignados a ella.</p>

            <div class="card-grid">
                <?php if (empty($areas)): ?>
                    <p>Aún no hay documentos públicos en ninguna área.</p>
                <?php else: ?>
                    <?php foreach ($areas as $area): ?>
                        <a href="ver_area_documentos.php?id=<?= $area['id'] ?>" class="area-card">
                            <div class="icon"><i class="fas fa-folder"></i></div>
                            <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        </main>
</div>

<script>
    // ... tu script de toggleNotifications ...
</script>

<?php require_once '../includes/footer.php'; ?>