<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Consultar Usuarios y sus Áreas
    // Usamos LEFT JOIN para que traiga el usuario incluso si no tiene área asignada
    $sql = "
        SELECT u.id, u.nombre, u.email, u.rol, u.foto, u.activo, a.nombre as area_nombre 
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        ORDER BY u.nombre ASC
    ";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Directorio de Personal";
$extra_css = "../style/empleados_panel.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">

    <header class="topbar">
        <h1><i class="fas fa-users"></i> Directorio de Personal</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
            <a href="../php/logout.php" class="topbar-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>

    <main class="content">

        <div class="control-bar">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="employeeSearch" placeholder="Buscar por nombre, email o área...">
            </div>

            <a href="crear_usuario.php" class="btn-primary">
                <i class="fas fa-user-plus"></i> Registrar Nuevo Empleado
            </a>
        </div>

        <div class="employee-grid" id="employeeGrid">
            <?php foreach ($usuarios as $user):
                // Preparar datos visuales
                $foto = !empty($user['foto']) ? "../uploads/usuarios/" . $user['foto'] : "../img/user.png";
                $rol = ucfirst(str_replace('_', ' ', $user['rol'])); // Ej: "jefe_area" -> "Jefe area"
                $area = $user['area_nombre'] ?? 'Sin Área Asignada';
                $estadoClass = ($user['activo'] ?? 1) ? 'status-active' : 'status-inactive';
            ?>

                <div class="employee-card" data-name="<?= strtolower($user['nombre']) ?>" data-email="<?= strtolower($user['email']) ?>" data-area="<?= strtolower($area) ?>">
                    <div class="card-banner">
                    </div>

                    <div class="card-body">
                        <div class="avatar-container">
                            <img src="<?= htmlspecialchars($foto) ?>" alt="Foto">
                            <span class="status-indicator <?= $estadoClass ?>" title="<?= ($user['activo'] ?? 1) ? 'Activo' : 'Inactivo' ?>"></span>
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
                        <a href="ver_legajo_empleado.php?id=<?= $user['id'] ?>" class="btn-icon-action" title="Ver Legajo de Documentos" style="color: #0d6efd; border-color: #0d6efd;">
                            <i class="fas fa-folder-open"></i>
                        </a>

                        <a href="editar_perfil.php?id=<?= $user['id'] ?>" class="btn-icon-action" title="Editar Perfil">
                            <i class="fas fa-pen"></i>
                        </a>

                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="btn-icon-action" title="Enviar Correo">
                            <i class="fas fa-paper-plane"></i>
                        </a>

                        <?php if ($user['id'] != $_SESSION['id']): ?>
                            <a href="../php/eliminar_usuario.php?id=<?= $user['id'] ?>"
                                class="btn-icon-action delete"
                                title="Eliminar Empleado"
                                onclick="return confirm('¿Estás seguro?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="no-results" id="noResultsMessage">
                <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No se encontraron empleados con ese criterio.</p>
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

        // Mostrar/Ocultar mensaje de "Sin resultados"
        document.getElementById('noResultsMessage').style.display = hasResults ? 'none' : 'block';
    });
</script>

<?php require_once '../includes/footer.php'; ?>