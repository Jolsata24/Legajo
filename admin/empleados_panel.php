<?php
session_start();
require '../php/db.php';

// Seguridad: Verificar sesión y roles permitidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_area', 'rrhh', 'admin', 'secretaria'])) {
    header("Location: ../into/login.html");
    exit;
}

$rol = $_SESSION['rol'];
$id_area = $_SESSION['id_area'] ?? null;

try {
    // --- TU LÓGICA PHP (SIN CAMBIOS) ---
    if ($rol === 'jefe_area' && $id_area) {
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id_area = ? AND rol = 'empleado'");
        $stmt->execute([$id_area]);
    } else {
        // RRHH, Admin y Secretaria ven a todos
        $stmt = $pdo->query("SELECT u.id, u.nombre, u.email, u.rol, a.nombre as area_nombre 
                             FROM usuarios u 
                             LEFT JOIN areas a ON u.id_area = a.id
                             WHERE u.rol = 'empleado'
                             ORDER BY u.nombre");
    }
    $empleados = $stmt->fetchAll();
    // --- FIN DE TU LÓGICA PHP ---

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Panel de Empleados";
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-users"></i> Panel de Empleados</h1>
      <div class="top-actions">
          <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
          <a href="../php/logout.php" class="topbar-logout-btn">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
      </div>
    </header>

    <main class="content">
        <div class="card">
            
            <div class="search-wrapper form-group">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    id="searchEmpleados" 
                    class="form-dashboard" 
                    placeholder="Buscar empleado por nombre, email o área..."
                >
            </div>
            <?php if (count($empleados) > 0): ?>
                <div class="card-grid" id="empleadosGrid">
                  
                  <?php foreach ($empleados as $emp): ?>
                    <div class="employee-card">
                      <div class="employee-card-icon">
                        <i class="fas fa-user-circle"></i>
                      </div>
                      <div class="employee-card-body">
                        <h3><?= htmlspecialchars($emp['nombre']); ?></h3>
                        <p><?= htmlspecialchars($emp['email']); ?></p>
                        <small style="color: #6c757d; font-weight: 500;">
                            Área: <?= htmlspecialchars($emp['area_nombre'] ?? 'Sin asignar'); ?>
                        </small>
                      </div>
                      <div class="employee-card-footer">
                        
                        <a class="btn btn-outline-primary" href="../panel_de_jefe/ver_empleado.php?id=<?= $emp['id']; ?>">
                          <i class="fas fa-eye"></i> Ver Legajo
                        </a>
                        </div>
                    </div>
                  <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p>No hay empleados registrados.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchEmpleados');
    const grid = document.getElementById('empleadosGrid');
    if (grid) {
        const cards = grid.getElementsByClassName('employee-card'); 
        
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const cardText = card.textContent || card.innerText;
                if (cardText.toLowerCase().includes(filter)) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>