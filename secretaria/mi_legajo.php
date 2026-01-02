<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // Datos del perfil de la secretaria
    $stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, u.foto, a.nombre as area FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    // Secciones (Carpetas)
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();
    
    $foto_perfil = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Mi Legajo - Secretaria";
// RECICLAJE DE ESTILO:
$extra_css = "../style/mi_legajo.css"; 

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Mi Legajo Digital</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto">
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p><i class="fas fa-briefcase"></i> Área: <?= htmlspecialchars($usuario['area'] ?? 'General') ?></p>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email']) ?></p>
                <p><i class="fas fa-id-badge"></i> Rol: Secretaria</p>
            </div>
        </div>

        <div class="sections-grid">
            <?php foreach ($secciones as $sec): ?>
                <a href="seccion_legajo.php?id=<?= $sec['id'] ?>" class="section-card">
                    <div class="section-icon"><i class="fas fa-folder"></i></div>
                    <div class="section-info">
                        <h3><?= htmlspecialchars($sec['nombre']) ?></h3>
                        <p>Ver mis documentos</p>
                    </div>
                    <div class="section-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>