<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Lógica CORREGIDA: 
    // - Usamos una subconsulta para contar solo los documentos Aprobados/Validados.
    // - Seleccionamos DE la tabla areas directamente para que aparezcan TODAS.
    $sql = "
        SELECT a.id, a.nombre, a.descripcion,
               (SELECT COUNT(*) FROM documentos d 
                WHERE d.id_area_destino = a.id 
                AND (d.estado = 'Aprobado' OR d.estado = 'Validado')) as total_docs
        FROM areas a
        ORDER BY a.nombre ASC
    ";
    $areas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Explorar Áreas";
$extra_css = "../style/mi_legajo.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-network-wired"></i> Repositorio General</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div style="margin-bottom: 25px; color: #666; font-size: 14px;">
            <p><i class="fas fa-info-circle"></i> Acceso a la documentación aprobada de todas las áreas de la institución.</p>
        </div>

        <div class="sections-grid">
            <?php if (empty($areas)): ?>
                <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p>No se encontraron áreas registradas en el sistema.</p>
                </div>
            <?php else: ?>
                <?php foreach ($areas as $area): 
                    $count = $area['total_docs'];
                    $texto_conteo = ($count == 1) ? "1 documento" : "$count documentos";
                    $clase_vacia = ($count == 0) ? "opacity: 0.7;" : ""; // Un poco transparente si está vacía
                ?>
                    <a href="ver_area_documentos.php?id=<?= $area['id'] ?>" class="section-card" style="<?= $clase_vacia ?>">
                        <div class="section-icon" style="background: rgba(240, 100, 13, 0.1);">
                            <i class="fas fa-building" style="color: #f00d0dff;"></i>
                        </div>
                        
                        <div class="section-info">
                            <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                            <p style="color: <?= $count > 0 ? '#28a745' : '#999' ?>;">
                                <i class="fas fa-file-alt"></i> <?= $texto_conteo ?>
                            </p>
                        </div>
                        
                        <div class="section-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>