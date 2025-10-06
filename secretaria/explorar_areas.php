<?php
session_start();
require '../php/db.php';

// Seguridad: Solo Secretaría
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // Obtener todas las áreas para mostrarlas como tarjetas
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    // Manejar error
}

$page_title = "Explorar Documentos por Área";
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>
<style>
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .area-card {
        background: #fff; border-radius: 12px; padding: 20px; text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: all 0.2s ease;
        text-decoration: none; color: #333;
    }
    .area-card:hover { transform: translateY(-5px); box-shadow: 0 6px 14px rgba(0,0,0,0.1); }
    .area-card .icon { font-size: 40px; color: #007bff; margin-bottom: 15px; }
    .area-card h3 { margin: 0; font-size: 18px; }
</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-sitemap"></i> Explorar Documentos por Área</h1>
    </header>

    <main class="content">
        <div class="card">
            <p style="text-align: left;">Selecciona un área para ver los documentos que han sido asignados a ella.</p>
            <div class="card-grid">
                <?php if (empty($areas)): ?>
                    <p>No hay áreas registradas en el sistema.</p>
                <?php else: ?>
                    <?php foreach ($areas as $area): ?>
                        <a href="secretaria_documentos_area.php?area_id=<?= $area['id'] ?>" class="area-card">
                            <div class="icon"><i class="fas fa-folder-open"></i></div>
                            <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>