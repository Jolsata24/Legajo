<?php
session_start();
require '../php/db.php';

// Seguridad: Solo Empleados
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

// Traer las áreas para el menú desplegable
try {
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar las áreas: " . $e->getMessage());
}

$page_title = "Enviar Documento";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-paper-plane"></i> Enviar Documento para Revisión</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Nuevo Envío</h3>
            <p>El documento que subas será enviado primero a Secretaría para su revisión y posterior asignación al área correspondiente.</p>
            
            <form action="../php/upload.php" method="POST" enctype="multipart/form-data" class="form-dashboard">
                <div class="form-group">
                    <label for="tipo">Tipo de documento (ej. Solicitud, Informe, etc.):</label>
                    <input type="text" name="tipo" id="tipo" required placeholder="Ej: Solicitud de Vacaciones">
                </div>

                <div class="form-group">
                    <label for="area">Área a la que solicitas enviar el documento:</label>
                    <select name="area" id="area" required>
                        <option value="">-- Selecciona el área de destino --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="documento">Selecciona un documento (PDF, Word, JPG, PNG):</label>
                    <input type="file" name="documento" id="documento" required>
                </div>
                
                <button type="submit" class="btn-primary">Enviar a Secretaría</button>
            </form>
        </div>
    </main>
</div>
<style>.form-dashboard label { display: block; margin-bottom: 5px; font-weight: 600; } .form-dashboard input, .form-dashboard select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 15px; } .form-dashboard .btn-primary { padding: 10px 20px; }</style>

<?php require_once '../includes/footer.php'; ?>