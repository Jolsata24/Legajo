<?php
session_start();
require '../php/db.php';

// Verificar sesión Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

// Obtener ID del empleado a ver
$id_empleado = $_GET['id'] ?? null;
if (!$id_empleado) {
    header("Location: empleados_panel.php");
    exit;
}

try {
    // 1. Datos del Empleado
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$id_empleado]);
    $empleado = $stmt->fetch();

    if (!$empleado) die("Empleado no encontrado.");

    // 2. Secciones (Carpetas)
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();

    $foto_perfil = !empty($empleado['foto']) ? "../uploads/usuarios/" . $empleado['foto'] : "../img/user.png";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Legajo de: " . $empleado['nombre'];
$extra_css = "../style/ver_legajo_empleado.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Visor de Legajo</h1>
        <div class="top-actions">
            <span><i class="fas fa-user-shield"></i> Modo Administrador</span>
            <a href="../php/logout.php" class="topbar-logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <main class="content">
        
        <a href="empleados_panel.php" class="btn-back-directory">
            <i class="fas fa-arrow-left"></i> Volver al Directorio
        </a>

        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto">
            </div>
            <div class="profile-info">
                <h2>
                    <?= htmlspecialchars($empleado['nombre']) ?> 
                    <span class="badge-rol"><?= ucfirst($empleado['rol']) ?></span>
                </h2>
                <p><i class="fas fa-briefcase"></i> <strong>Área:</strong> <?= htmlspecialchars($empleado['area'] ?? 'Sin asignar') ?></p>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($empleado['email']) ?></p>
            </div>
        </div>

        <h3 style="margin-bottom: 15px; color: #495057;">Carpetas del Empleado</h3>
        
        <div class="sections-grid">
            <?php foreach ($secciones as $sec): ?>
                <a href="ver_seccion_empleado.php?id_seccion=<?= $sec['id'] ?>&id_empleado=<?= $id_empleado ?>" class="section-card">
                    <div class="section-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="section-info">
                        <h3><?= htmlspecialchars($sec['nombre']) ?></h3>
                        <p>Explorar documentos</p>
                    </div>
                    <i class="fas fa-chevron-right" style="margin-left: auto; color: #adb5bd;"></i>
                </a>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>