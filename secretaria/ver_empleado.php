<?php
session_start();
require '../php/db.php';

// --- ¡CORRECCIÓN DE SEGURIDAD! ---
// Esta página es solo para la secretaria
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$empleado_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($empleado_id <= 0) {
    die("Empleado no válido.");
}

try {
    // La lógica de consulta es la misma que usan otros roles
    $stmt_emp = $pdo->prepare("SELECT u.id, u.nombre, u.email, u.rol, a.nombre AS area FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt_emp->execute([$empleado_id]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);
    if (!$empleado) die("Empleado no encontrado.");

    $secciones = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    $stmt_docs = $pdo->prepare("SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida, d.estado, a.nombre AS area_destino FROM documentos d LEFT JOIN areas a ON d.id_area_destino = a.id WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL ORDER BY d.fecha_subida DESC");
    $stmt_docs->execute([$empleado_id]);
    $docs_enviados = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Legajo de " . htmlspecialchars($empleado['nombre']);

// --- ¡CORRECCIÓN DE INTERFAZ! ---
// Cargamos el header y sidebar de SECRETARIA
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<style>
    .seccion-link { 
        display: flex; align-items: center; gap: 10px; padding: 16px; 
        background-color: #f8f9fa; border: 1px solid var(--color-borde); 
        margin-bottom: 10px; text-decoration: none; color: var(--color-texto-principal); 
        border-radius: 8px; transition: all 0.2s ease; font-weight: 500;
    } 
    .seccion-link:hover { 
        background-color: #e9ecef; border-color: #ced4da; transform: translateY(-2px);
    }
    .seccion-link i { color: var(--color-primario); }
</style>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-user-tie"></i> Legajo de <?= htmlspecialchars($empleado['nombre']) ?></h1>
       <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        <a href="empleados_panel.php" class="btn-back" style="margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Volver al Panel de Empleados
        </a>

        <div class="card">
            <h3><i class="fas fa-id-card"></i> Datos del Empleado</h3>
            <p style="text-align:left;"><strong>Nombre:</strong> <?= htmlspecialchars($empleado['nombre']); ?></p>
            <p style="text-align:left;"><strong>Email:</strong> <?= htmlspecialchars($empleado['email']); ?></p>
            <p style="text-align:left;"><strong>Área:</strong> <?= htmlspecialchars($empleado['area'] ?? 'No asignada'); ?></p>
        </div>

        <div class="card">
            <h3><i class="fas fa-folder-open"></i> Documentos Personales del Legajo</h3>
            <div class="seccion-list-wrapper">
            <?php if ($secciones): ?>
                <?php foreach ($secciones as $sec): ?>
                    <a class="seccion-link" href="ver_seccion_legajo.php?id=<?= $empleado_id; ?>&seccion=<?= $sec['id']; ?>">
                        <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($sec['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay secciones de legajo configuradas en el sistema.</p>
            <?php endif; ?>
            </div>
        </div>
        
        </main>
</div>

<?php require_once '../includes/footer.php'; ?>