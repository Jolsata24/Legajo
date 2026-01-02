<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Lógica: Obtener áreas que tengan documentos públicos ('revisado')
    // Agregamos un conteo para mostrar cuántos docs hay en cada área
    $sql = "
        SELECT a.id, a.nombre, COUNT(d.id) as total_docs
        FROM areas a
        JOIN documentos d ON a.id = d.id_area_destino
        WHERE d.estado = 'revisado'
        GROUP BY a.id
        ORDER BY a.nombre ASC
    ";
    $areas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Explorar Áreas";
// RECICLAJE: Usamos el mismo estilo de grilla que en Mi Legajo
$extra_css = "../style/mi_legajo.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-network-wired"></i> Documentos Públicos por Área</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div style="margin-bottom: 25px; color: #666; font-size: 14px; padding-left: 5px;">
            <p><i class="fas fa-info-circle"></i> Estas son las áreas que han compartido documentos de acceso público para los empleados.</p>
        </div>

        <div class="sections-grid">
            <?php if (empty($areas)): ?>
                <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-folder-minus" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No hay documentos públicos disponibles en este momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($areas as $area): 
                    $texto_conteo = ($area['total_docs'] == 1) ? "1 documento disponible" : $area['total_docs'] . " documentos disponibles";
                ?>
                    <a href="ver_area_documentos.php?id=<?= $area['id'] ?>" class="section-card">
                        <div class="section-icon" style="background: rgba(13,202,240,0.1);">
                            <i class="fas fa-building" style="color: #0dcaf0;"></i>
                        </div>
                        
                        <div class="section-info">
                            <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                            <p><?= $texto_conteo ?></p>
                        </div>
                        
                        <div class="section-arrow">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>