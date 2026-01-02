<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión (Secretaria)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Consultar Usuarios y sus Áreas
    // Filtramos para que NO vea al Admin (opcional, pero recomendado)
    $sql = "
        SELECT u.id, u.nombre, u.email, u.rol, u.foto, u.activo, a.nombre as area_nombre 
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.rol != 'admin' 
        ORDER BY u.nombre ASC
    ";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Directorio de Personal - Secretaria";
// RECICLAJE: Usamos el mismo CSS que el panel de admin
$extra_css = "../style/empleados_panel.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-users"></i> Directorio de Personal</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">

        <div class="control-bar">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="employeeSearch" placeholder="Buscar por nombre, email o área...">
            </div>
            
            </div>

        <div class="employee-grid" id="employeeGrid">
            <?php foreach ($usuarios as $user): 
                $foto = !empty($user['foto']) ? "../uploads/usuarios/" . $user['foto'] : "../img/user.png";
                $rol = ucfirst(str_replace('_', ' ', $user['rol'])); 
                $area = $user['area_nombre'] ?? 'Sin Área Asignada';
                $estadoClass = ($user['activo'] ?? 1) ? 'status-active' : 'status-inactive';
            ?>
            
            <div class="employee-card" data-name="<?= strtolower($user['nombre']) ?>" data-email="<?= strtolower($user['email']) ?>" data-area="<?= strtolower($area) ?>">
                <div class="card-banner"></div>
                
                <div class="card-body">
                    <div class="avatar-container">
                        <img src="<?= htmlspecialchars($foto) ?>" alt="Foto">
                        <span class="status-indicator <?= $estadoClass ?>"></span>
                    </div>
                    
                    <h3 class="emp-name"><?= htmlspecialchars($user['nombre']) ?></h3>
                    <span class="emp-role"><?= $rol ?></span>
                    
                    <div class="emp-info">
                        <i class="fas fa-envelope"></i>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="emp-info">
                        <i class="fas fa-building"></i>
                        <span><?= htmlspecialchars($area) ?></span>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="ver_empleado.php?id=<?= $user['id'] ?>" class="btn-icon-action" title="Ver Legajo del Empleado" style="color: #0d6efd; border-color: #0d6efd;">
                        <i class="fas fa-folder-open"></i>
                    </a>
                    
                    <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="btn-icon-action" title="Enviar Correo">
                        <i class="fas fa-paper-plane"></i>
                    </a>

                    </div>
            </div>
            <?php endforeach; ?>

            <div class="no-results" id="noResultsMessage">
                <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No se encontraron empleados.</p>
            </div>
        </div>

    </main>
</div>

<script>
document.getElementById('employeeSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll('.employee-card');
    let hasResults = false;

    cards.forEach(card => {
        let name = card.getAttribute('data-name');
        let email = card.getAttribute('data-email');
        let area = card.getAttribute('data-area');
        
        if (name.includes(filter) || email.includes(filter) || area.includes(filter)) {
            card.style.display = 'flex';
            hasResults = true;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('noResultsMessage').style.display = hasResults ? 'none' : 'block';
});
</script>

<?php require_once '../includes/footer.php'; ?>