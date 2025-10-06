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
    // La lógica PHP para obtener los empleados no cambia
    if ($rol === 'jefe_area' && $id_area) {
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id_area = ? AND rol = 'empleado'");
        $stmt->execute([$id_area]);
    } else {
        $stmt = $pdo->query("SELECT id, nombre, email, rol FROM usuarios WHERE rol = 'empleado'");
    }
    $empleados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Panel de Empleados";
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<link rel="stylesheet" href="../style/main.css">

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-users"></i> Panel de Empleados</h1>
    </header>

    <main class="content">
        <div class="card">
            <input type="text" id="searchEmpleados" placeholder="Buscar empleado por nombre o email..." style="width: 100%; padding: 12px; margin-bottom: 25px; border-radius: 8px; border: 1px solid #ddd; font-size: 16px;">

            <?php if (count($empleados) > 0): ?>
                <div class="card-grid" id="empleadosGrid">
                  <?php foreach ($empleados as $emp): ?>
                    <div class="card-mini">
                      <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                      </div>
                      <div class="card-body">
                        <h3><?= htmlspecialchars($emp['nombre']); ?></h3>
                        <p style="font-size: 15px;"><?= htmlspecialchars($emp['email']); ?></p>
                      </div>
                      <div class="card-footer">
                        <a class="btn-view" href="../panel_de_jefe/ver_empleado.php?id=<?= $emp['id']; ?>">
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
        const cards = grid.getElementsByClassName('card-mini');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const cardText = card.textContent || card.innerText;
                if (cardText.toLowerCase().includes(filter)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>