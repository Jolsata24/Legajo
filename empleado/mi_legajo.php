<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
try {
    $stmt_user = $pdo->prepare("SELECT u.nombre, u.email, u.rol, a.nombre AS area FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt_user->execute([$usuario_id]);
    $usuario = $stmt_user->fetch();

    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY id ASC")->fetchAll();
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Mi Legajo";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Mi Legajo</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-user"></i> Mis Datos</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']); ?></p>
            <p><strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-list-alt"></i> Secciones del Legajo</h3>
            <p>Selecciona una sección para ver o subir tus documentos.</p>
            <?php foreach ($secciones as $sec): ?>
                <a href="seccion_legajo.php?id=<?= $sec['id']; ?>" class="seccion-link">
                    <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($sec['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<style>.seccion-link { display: block; padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; margin-bottom: 8px; text-decoration: none; color: #495057; border-radius: 5px; transition: background-color 0.2s; } .seccion-link:hover { background-color: #e9ecef; }</style>

<?php require_once '../includes/footer.php'; ?>