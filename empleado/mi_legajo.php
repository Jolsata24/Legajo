<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // 1. NOTIFICACIONES (Necesario para la carga inicial)
    $stmt_notif = $pdo->prepare("SELECT id, mensaje, leido, enlace FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 5");
    $stmt_notif->execute([$id_usuario]);
    $notificaciones = $stmt_notif->fetchAll();
    $num_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));

    // 2. Datos Perfil
    $stmtUser = $pdo->prepare("SELECT u.nombre, u.email, u.foto, a.nombre as area FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmtUser->execute([$id_usuario]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $foto_perfil = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

    // 3. Secciones
    $sql = "SELECT s.id, s.nombre, COUNT(d.id) as cantidad_docs FROM secciones_legajo s LEFT JOIN documentos d ON s.id = d.id_seccion AND d.id_usuario = ? GROUP BY s.id ORDER BY s.nombre ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Mi Legajo Digital";
$extra_css = "../style/mi_legajo.css"; 

require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<link rel="stylesheet" href="../style/mi_legajo.css">

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Mi Legajo Digital</h1>
        
        <div class="top-actions">
            
            <div class="notifications">
                <a href="#" id="notification-bell">
                    <i class="fas fa-bell"></i>
                    <?php if ($num_no_leidas > 0): ?>
                        <span class="notification-count"><?= $num_no_leidas ?></span>
                    <?php endif; ?>
                </a>
                <div class="notification-dropdown" id="notification-dropdown-list">
                    <div class="dropdown-header">Notificaciones</div>
                    <div class="dropdown-body">
                        <?php if (empty($notificaciones)): ?>
                            <div style="padding:15px; text-align:center; color:#777;">Sin novedades</div>
                        <?php else: ?>
                            <?php foreach($notificaciones as $n): ?>
                                <a href="../php/marcar_leido.php?id=<?= $n['id'] ?>" style="<?= $n['leido']?'':'background:#f0f8ff; border-left:3px solid #fd490dff;' ?>">
                                    <p style="margin:0; font-size:13px;"><?= htmlspecialchars($n['mensaje']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-footer">
                        <a href="notificaciones.php">Ver todas</a>
                    </div>
                </div>
            </div>
            <span style="margin-left: 15px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
            <a href="../php/logout.php" class="topbar-logout-btn" style="margin-left: 15px;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="content">
        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto">
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p><i class="fas fa-briefcase"></i> <?= htmlspecialchars($usuario['area'] ?? 'General') ?></p>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email']) ?></p>
            </div>
            <div class="profile-actions" style="margin-left: auto;">
                 <a href="subir_documento.php" class="btn-primary"><i class="fas fa-plus"></i> Nuevo</a>
            </div>
        </div>

        <div class="sections-grid" style="margin-top: 20px;">
            <?php foreach ($secciones as $sec): 
                $icon = ($sec['cantidad_docs'] > 0) ? "fa-folder-open" : "fa-folder";
                $color = ($sec['cantidad_docs'] > 0) ? "#adb5bd" : "#adb5bd"; 
            ?>
            <a href="seccion_legajo.php?id=<?= $sec['id'] ?>" class="section-card">
                <div class="section-icon"><i class="fas <?= $icon ?>" style="color: <?= $color ?>;"></i></div>
                <div class="section-info">
                    <h3><?= htmlspecialchars($sec['nombre']) ?></h3>
                    <p><?= $sec['cantidad_docs'] ?> documentos</p>
                </div>
                <div class="section-arrow"><i class="fas fa-chevron-right"></i></div>
            </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>