<?php
session_start();
require '../php/db.php';

// 1. LÓGICA PHP (Se mantiene tu lógica original)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre");
    $areas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// --- INICIO DE CORRECCIÓN DE ESTILO ---

$page_title = "Documentos por Área";
// 2. Incluimos los headers y sidebars correctos
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-building"></i> Documentos por Área</h1>
      
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        <div class="card">
            <h3><i class="fas fa-sitemap"></i> Explorar Áreas</h3>
            <p style="text-align: left;">Selecciona un área para ver todos los documentos que han sido asignados a ella.</p>
            
            <?php if ($areas): ?>
                <div class="card-grid" style="margin-top: 20px;">
                  
                  <?php foreach ($areas as $area): ?>
                    <a href="admin_documento_area.php?area_id=<?= $area['id']; ?>" class="area-card">
                        <div class="area-card-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="area-card-body">
                            <h3><?= htmlspecialchars($area['nombre']); ?></h3>
                        </div>
                    </a>
                  <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p>No hay áreas registradas.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
    .area-card {
        background: var(--color-fondo-tarjeta);
        border-radius: 12px;
        border: 1px solid var(--color-borde);
        box-shadow: var(--sombra-tarjeta);
        padding: 24px;
        text-align: center;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        color: var(--color-texto-principal);
        min-height: 150px;
    }
    .area-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border-color: var(--color-primario);
    }
    .area-card-icon {
        font-size: 36px;
        color: var(--color-primario);
        margin-bottom: 15px;
    }
    .area-card-body h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        padding: 0;
        border: none;
        background: none;
    }
</style>

<?php require_once '../includes/footer.php'; ?>