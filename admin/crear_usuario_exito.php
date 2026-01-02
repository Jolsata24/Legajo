<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$id_nuevo = $_GET['id'] ?? 0;
$pass_temp = $_GET['pass'] ?? '';

if (!$id_nuevo) {
    header("Location: crear_usuario.php");
    exit;
}

// Obtener datos para mostrar
try {
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, a.nombre as area 
        FROM usuarios u 
        LEFT JOIN areas a ON u.id_area = a.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$id_nuevo]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Error al consultar usuario.");
}

$page_title = "¡Usuario Creado!";
$extra_css = "../style/exito.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    <div class="success-container">
        
        <div class="success-card">
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>
            
            <h2>¡Usuario Registrado!</h2>
            <p>El usuario ha sido creado correctamente en el sistema. Aquí tienes las credenciales temporales.</p>
            
            <div class="credentials-box">
                <div class="credential-item">
                    <span class="credential-label">Nombre:</span>
                    <span class="credential-value"><?= htmlspecialchars($usuario['nombre']) ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Usuario:</span>
                    <span class="credential-value"><?= htmlspecialchars($usuario['email']) ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Contraseña:</span>
                    <span class="credential-value"><?= htmlspecialchars($pass_temp) ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Área:</span>
                    <span class="credential-value" style="font-weight: 400;"><?= htmlspecialchars($usuario['area'] ?? 'General') ?></span>
                </div>
            </div>

            <div class="actions">
                <a href="generar_credenciales_pdf.php?id=<?= $id_nuevo ?>&pass=<?= urlencode($pass_temp) ?>" class="btn-pdf">
                    <i class="fas fa-file-pdf"></i> Descargar Ficha de Acceso
                </a>

                <div style="margin-top: 10px;">
                    <a href="crear_usuario.php" class="btn-link">Registrar otro usuario</a>
                    <span style="color: #ccc;">|</span>
                    <a href="empleados_panel.php" class="btn-link">Ir al directorio</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>