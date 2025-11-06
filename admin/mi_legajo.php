<?php
session_start();
require '../php/db.php';

// Verificar sesión de Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../php/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // --- TU LÓGICA PHP (SIN CAMBIOS) ---
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    // Traer las secciones del legajo
    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// --- INICIO DE CORRECCIONES DE ESTILO ---

$page_title = "Mi Legajo";
// 1. Incluimos los headers y sidebars correctos
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
        <div class="card">
            <h3><i class="fas fa-user"></i> Mis Datos</h3>
            <p style="text-align: left;"><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']); ?></p>
            <p style="text-align: left;"><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p style="text-align: left;"><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($usuario['rol'])); ?></p>
            <p style="text-align: left;"><strong>Área:</strong> <?= htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-list-alt"></i> Secciones de Mi Legajo</h3>
            <p style="text-align: left;">Selecciona una sección para ver o subir tus documentos personales.</p>
            
            <div class="seccion-list-wrapper">
                <?php if (!empty($secciones)): ?>
                    <?php foreach ($secciones as $sec): ?>
                        <a href="seccion_legajo_admin.php?id=<?= $sec['id']; ?>" class="seccion-link">
                            <i class="fas fa-chevron-right"></i> 
                            <?= htmlspecialchars($sec['nombre']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay secciones definidas en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
    .seccion-link { 
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px; 
        background-color: #f8f9fa; 
        border: 1px solid var(--color-borde); 
        margin-bottom: 10px; 
        text-decoration: none; 
        color: var(--color-texto-principal); 
        border-radius: 8px; 
        transition: all 0.2s ease;
        font-weight: 500;
    } 
    .seccion-link:hover { 
        background-color: #e9ecef;
        border-color: #ced4da;
        transform: translateY(-2px);
    }
    .seccion-link i {
        color: var(--color-primario);
    }
</style>

<?php require_once '../includes/footer.php'; ?>