<?php
session_start();
require '../php/db.php';

// Seguridad: Solo Secretaría
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // Traer datos del usuario (Secretaría)
    $stmt_user = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt_user->execute([$usuario_id]);
    $usuario = $stmt_user->fetch();

    // Traer las secciones del legajo para que pueda subir sus propios documentos
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Mi Legajo - Secretaría";
// Incluimos las plantillas que ya creamos para Secretaría
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>
<style>
    .seccion-link { 
        display: block; 
        padding: 12px; 
        background-color: #f8f9fa; 
        border: 1px solid #dee2e6; 
        margin-bottom: 8px; 
        text-decoration: none; 
        color: #495057; 
        border-radius: 5px; 
        transition: background-color 0.2s; 
    } 
    .seccion-link:hover { 
        background-color: #e9ecef; 
    }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Mi Legajo Personal</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-user"></i> Mis Datos</h3>
            <p style="text-align: left;"><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']); ?></p>
            <p style="text-align: left;"><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p style="text-align: left;"><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($usuario['rol'])); ?></p>
            <p style="text-align: left;"><strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-list-alt"></i> Secciones de Mi Legajo</h3>
            <p style="text-align: left;">Selecciona una sección para subir o gestionar tus documentos personales.</p>
            
            <?php if (empty($secciones)): ?>
                <p>No hay secciones de legajo definidas en el sistema.</p>
            <?php else: ?>
                <?php foreach ($secciones as $sec): ?>
                    <a href="seccion_legajo.php?id=<?= $sec['id']; ?>" class="seccion-link">
                        <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($sec['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>