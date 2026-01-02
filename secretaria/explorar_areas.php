<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // Consulta de Áreas con conteo de empleados
    $sql = "
        SELECT a.id, a.nombre, a.descripcion, COUNT(u.id) as total_empleados
        FROM areas a
        LEFT JOIN usuarios u ON a.id = u.id_area
        GROUP BY a.id
        ORDER BY a.nombre ASC
    ";
    $areas = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Departamentos - Secretaria";
// RECICLAJE: Usamos el estilo de tarjetas de áreas del admin
$extra_css = "../style/panel_jefes.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-building"></i> Áreas de la Institución</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <?php if (empty($areas)): ?>
            <div class="empty-areas">
                <i class="fas fa-city" style="font-size: 48px; opacity: 0.3;"></i>
                <p>No hay áreas registradas.</p>
            </div>
        <?php else: ?>
            <div class="areas-grid">
                <?php foreach ($areas as $area): ?>
                    <a href="secretaria_documentos_area.php?id_area=<?= $area['id'] ?>" class="area-card">
                        <div class="area-header-bar"></div>
                        <div class="area-body">
                            <div class="area-title-row">
                                <div class="area-icon"><i class="fas fa-sitemap"></i></div>
                                <div class="area-info">
                                    <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($area['descripcion'] ?? 'Departamento') ?></p>
                                </div>
                            </div>
                            <div class="area-stats">
                                <div class="stat-pill users">
                                    <i class="fas fa-users"></i> <?= $area['total_empleados'] ?> Empleados
                                </div>
                            </div>
                        </div>
                        <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>