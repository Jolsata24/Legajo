<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // 2. Consulta Inteligente: Secciones + Conteo de documentos del usuario
    // Usamos LEFT JOIN para traer todas las carpetas, aunque estén vacías
    $sql = "
        SELECT s.id, s.nombre, COUNT(d.id) as cantidad_docs
        FROM secciones_legajo s
        LEFT JOIN documentos d ON s.id = d.id_seccion AND d.id_usuario = ?
        GROUP BY s.id
        ORDER BY s.nombre ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Mis Carpetas";
// RECICLAJE: Usamos el estilo específico para grillas de carpetas
$extra_css = "../style/mi_legajo.css";

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Mi Legajo Digital</h1>
        <div class="top-actions">
            <a href="subir_documento.php" class="btn-primary">
                <i class="fas fa-plus"></i> Nuevo Documento
            </a>
        </div>
    </header>

    <main class="content">
        
        <div style="margin-bottom: 25px; color: #666; font-size: 14px;">
            <p>Aquí se encuentran organizados todos tus documentos. Selecciona una carpeta para ver su contenido o descargar tus archivos.</p>
        </div>

        <div class="sections-grid">
            
            <?php foreach ($secciones as $sec): 
                // Lógica visual: Si la carpeta tiene documentos, se ve llena, si no, vacía
                $icon = ($sec['cantidad_docs'] > 0) ? "fa-folder-open" : "fa-folder";
                $color_icon = ($sec['cantidad_docs'] > 0) ? "#0d6efd" : "#adb5bd"; // Azul si tiene cosas, gris si no
                $texto_conteo = ($sec['cantidad_docs'] == 1) ? "1 documento" : $sec['cantidad_docs'] . " documentos";
            ?>
            
            <a href="seccion_legajo.php?id=<?= $sec['id'] ?>" class="section-card">
                <div class="section-icon">
                    <i class="fas <?= $icon ?>" style="color: <?= $color_icon ?>;"></i>
                </div>
                
                <div class="section-info">
                    <h3><?= htmlspecialchars($sec['nombre']) ?></h3>
                    <p><?= $texto_conteo ?></p>
                </div>
                
                <div class="section-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <?php endforeach; ?>

        </div>
        
        <?php if (empty($secciones)): ?>
            <div class="empty-state">
                <p>No hay secciones definidas en el sistema. Contacta al administrador.</p>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>