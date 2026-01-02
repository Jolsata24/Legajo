<?php
session_start();
require '../php/db.php';

// 1. Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // 2. CORRECCIÓN: Eliminamos "u.dni" de la consulta porque no existe en tu tabla usuarios
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    // 3. Obtener las SECCIONES (Carpetas)
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();

    // Imagen por defecto si no tiene
    // Aseguramos que la ruta sea correcta dependiendo de dónde guardes las fotos
    $foto_perfil = !empty($usuario['foto']) ? "../uploads/usuarios/" . $usuario['foto'] : "../img/user.png";

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Mi Legajo Digital";
// Cargamos el CSS de Mi Legajo
$extra_css = "../style/mi_legajo.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-folder-open"></i> Mi Legajo</h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        
        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de Perfil">
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p><i class="fas fa-briefcase"></i> <strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'Sin asignar') ?></p>
                <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                <p><i class="fas fa-user-tag"></i> <strong>Rol:</strong> <?= ucfirst($usuario['rol']) ?></p>
            </div>
        </div>

        <div>
            <h3 style="font-size: 18px; margin-bottom: 15px; color: var(--color-texto-principal);">
                <i class="fas fa-layer-group"></i> Carpetas de Documentos
            </h3>
            
            <?php if (empty($secciones)): ?>
                <div class="card" style="text-align: center; color: var(--color-texto-secundario); padding: 30px;">
                    <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>No hay secciones definidas en el sistema.</p>
                </div>
            <?php else: ?>
                
                <div class="sections-grid">
                    <?php foreach ($secciones as $sec): ?>
                        <a href="seccion_legajo_admin.php?id=<?= $sec['id']; ?>" class="section-card">
                            
                            <div class="section-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            
                            <div class="section-info">
                                <h3><?= htmlspecialchars($sec['nombre']); ?></h3>
                                <p>Ver documentos</p>
                            </div>
                            
                            <div class="section-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>

                        </a>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>