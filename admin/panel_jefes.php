<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. CONSULTA CORREGIDA
    // Relación: Área -> Usuarios -> Documentos
    // Unimos 'areas' con 'usuarios' por id_area
    // Y luego 'usuarios' con 'documentos' por id_usuario
    $sql = "
        SELECT 
            a.id, 
            a.nombre, 
            a.descripcion,
            COUNT(DISTINCT u.id) as total_empleados,
            COUNT(DISTINCT d.id) as total_documentos
        FROM areas a
        LEFT JOIN usuarios u ON a.id = u.id_area
        LEFT JOIN documentos d ON u.id = d.id_usuario
        GROUP BY a.id
        ORDER BY a.nombre ASC
    ";
    $stmt = $pdo->query($sql);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Gestión de Áreas";
// Usamos el mismo CSS que ya creamos, no hace falta cambiarlo
$extra_css = "../style/panel_jefes.css"; 

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-building"></i> Departamentos y Áreas</h1>
        <div class="top-actions">
            <div style="position: relative; margin-right: 15px;">
                <input type="text" id="areaSearch" placeholder="Filtrar áreas..." 
                       style="padding: 8px 15px; border-radius: 20px; border: 1px solid #dee2e6; font-size: 14px; width: 200px;">
            </div>
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div style="margin-bottom: 20px; color: #6c757d; font-size: 14px;">
            <p>Selecciona un área para ver y gestionar sus documentos centralizados.</p>
        </div>

        <?php if (empty($areas)): ?>
            <div class="empty-areas">
                <i class="fas fa-city" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                <p>No hay áreas registradas en el sistema.</p>
            </div>
        <?php else: ?>
            
            <div class="areas-grid" id="areasGrid">
                <?php foreach ($areas as $area): ?>
                    <a href="admin_documento_area.php?id_area=<?= $area['id'] ?>" class="area-card" data-name="<?= strtolower($area['nombre']) ?>">
                        <div class="area-header-bar"></div>
                        
                        <div class="area-body">
                            <div class="area-title-row">
                                <div class="area-icon">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <div class="area-info">
                                    <h3><?= htmlspecialchars($area['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($area['descripcion'] ?? 'Departamento') ?></p>
                                </div>
                            </div>
                            
                            <div class="area-stats">
                                <div class="stat-pill users" title="Empleados asignados">
                                    <i class="fas fa-users"></i> <?= $area['total_empleados'] ?>
                                </div>
                                <div class="stat-pill docs" title="Documentos generados">
                                    <i class="fas fa-file-alt"></i> <?= $area['total_documentos'] ?>
                                </div>
                            </div>
                        </div>

                        <div class="action-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>
</div>

<script>
document.getElementById('areaSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll('.area-card');

    cards.forEach(card => {
        let name = card.getAttribute('data-name');
        if (name.includes(filter)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>