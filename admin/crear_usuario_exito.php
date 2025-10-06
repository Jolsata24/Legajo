<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$id_nuevo_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$clave_temporal = isset($_GET['clave']) ? $_GET['clave'] : '';

if ($id_nuevo_usuario <= 0) {
    die("No se encontró el usuario.");
}

// Obtenemos los datos completos del usuario recién creado
try {
    $stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, a.nombre as area_nombre FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt->execute([$id_nuevo_usuario]);
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    die("Error al buscar el usuario.");
}

$page_title = "Usuario Creado con Éxito";
require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-check-circle"></i> Usuario Creado Exitosamente</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Detalles del Nuevo Usuario</h3>
            <p style="text-align:left;"><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
            <p style="text-align:left;"><strong>Usuario (Email):</strong> <?= htmlspecialchars($usuario['email']) ?></p>
            <p style="text-align:left;"><strong>Contraseña Temporal:</strong> <?= htmlspecialchars($clave_temporal) ?></p>
            <p style="text-align:left;"><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></p>
            <p style="text-align:left;"><strong>Área:</strong> <?= htmlspecialchars($usuario['area_nombre'] ?? 'No asignada') ?></p>
            <hr>
            <p>Puedes generar un documento PDF con estas credenciales para entregarlo al nuevo usuario.</p>
            
            <a href="generar_credenciales_pdf.php?id=<?= $id_nuevo_usuario ?>&clave=<?= urlencode($clave_temporal) ?>" class="btn-primary" style="background-color: #dc3545; text-decoration: none; display: inline-block;">
                <i class="fas fa-file-pdf"></i> Generar y Descargar PDF
            </a>
            <br><br>
            <a href="crear_usuario.php">Crear otro usuario</a> | <a href="admin_dashboard.php">Volver al Inicio</a>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>